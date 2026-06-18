<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class UserModel
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    public function find(int $id): ?array
    {
        $stmt = DB::pdo()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(): array
    {
        $stmt = DB::pdo()->query('SELECT * FROM users ORDER BY name ASC, email ASC');
        return $stmt->fetchAll();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = DB::pdo()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updatePassword(int $id, string $hash): void
    {
        $stmt = DB::pdo()->prepare('UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['password' => $hash, 'id' => $id]);
    }

    public function updatePasswordByEmail(string $email, string $hash): void
    {
        $stmt = DB::pdo()->prepare('UPDATE users SET password = :password, updated_at = NOW() WHERE email = :email');
        $stmt->execute(['password' => $hash, 'email' => $email]);
    }

    public function create(array $data): int
    {
        $stmt = DB::pdo()->prepare('INSERT INTO users (name, email, password, role, is_active, must_change_password, failed_login_attempts, locked_until, last_login_at, created_at, updated_at) VALUES (:name, :email, :password, :role, :is_active, :must_change_password, 0, NULL, NULL, NOW(), NOW())');
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'is_active' => $data['is_active'],
            'must_change_password' => $data['must_change_password'],
        ]);

        return (int) DB::pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = DB::pdo()->prepare('UPDATE users SET name = :name, email = :email, role = :role, is_active = :is_active, must_change_password = :must_change_password, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => $data['is_active'],
            'must_change_password' => $data['must_change_password'],
        ]);
    }

    public function updatePasswordAndRequireChange(int $id, string $hash): void
    {
        $stmt = DB::pdo()->prepare('UPDATE users SET password = :password, must_change_password = 1, failed_login_attempts = 0, locked_until = NULL, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['password' => $hash, 'id' => $id]);
    }

    public function markPasswordChanged(int $id, string $hash): void
    {
        $stmt = DB::pdo()->prepare('UPDATE users SET password = :password, must_change_password = 0, failed_login_attempts = 0, locked_until = NULL, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['password' => $hash, 'id' => $id]);
    }

    public function touchLastLogin(int $id): void
    {
        $stmt = DB::pdo()->prepare('UPDATE users SET last_login_at = NOW(), failed_login_attempts = 0, locked_until = NULL, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function incrementFailedAttempts(int $id): int
    {
        $stmt = DB::pdo()->prepare('UPDATE users SET failed_login_attempts = failed_login_attempts + 1, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $user = $this->find($id);
        return (int) ($user['failed_login_attempts'] ?? 0);
    }

    public function setLock(int $id, ?string $lockedUntil): void
    {
        $stmt = DB::pdo()->prepare('UPDATE users SET locked_until = :locked_until, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id, 'locked_until' => $lockedUntil]);
    }

    public function resetFailedAttempts(int $id): void
    {
        $stmt = DB::pdo()->prepare('UPDATE users SET failed_login_attempts = 0, locked_until = NULL, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE email = :email';
        $params = ['email' => $email];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }

        $sql .= ' LIMIT 1';

        $stmt = DB::pdo()->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetch();
    }

    public static function roles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_USER => 'Usuario',
        ];
    }

    public function hasUsablePassword(array $user): bool
    {
        return trim((string) ($user['password'] ?? '')) !== '';
    }
}
