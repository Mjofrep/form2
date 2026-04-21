<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class PasswordResetModel
{
    public function create(string $email, string $token, string $expiresAt): void
    {
        DB::pdo()->prepare('DELETE FROM password_resets WHERE email = :email')->execute(['email' => $email]);

        $stmt = DB::pdo()->prepare('INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (:email, :token, :expires_at, NOW())');
        $stmt->execute([
            'email' => $email,
            'token' => password_hash($token, PASSWORD_DEFAULT),
            'expires_at' => $expiresAt,
        ]);
    }

    public function findValidByEmail(string $email): ?array
    {
        $stmt = DB::pdo()->prepare('SELECT * FROM password_resets WHERE email = :email AND expires_at >= NOW() ORDER BY created_at DESC LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteByEmail(string $email): void
    {
        DB::pdo()->prepare('DELETE FROM password_resets WHERE email = :email')->execute(['email' => $email]);
    }
}
