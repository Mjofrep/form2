<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\UserModel;

final class Auth
{
    private static array $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = (new UserModel())->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user'] = $user;
        session_regenerate_id(true);

        return true;
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (!empty($_SESSION['user'])) {
            return $_SESSION['user'];
        }

        $user = (new UserModel())->find((int) $_SESSION['user_id']);
        $_SESSION['user'] = $user;

        return $user;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }
}
