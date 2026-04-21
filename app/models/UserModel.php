<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class UserModel
{
    public function find(int $id): ?array
    {
        $stmt = DB::pdo()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
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
}
