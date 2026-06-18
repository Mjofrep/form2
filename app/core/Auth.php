<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\UserModel;

final class Auth
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_INVALID = 'invalid';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_LOCKED = 'locked';

    private static array $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    public static function attempt(string $email, string $password): string
    {
        $users = new UserModel();
        $user = $users->findByEmail($email);

        if (!$user) {
            return self::STATUS_INVALID;
        }

        if (!(bool) ($user['is_active'] ?? true)) {
            return self::STATUS_INACTIVE;
        }

        $lockedUntil = (string) ($user['locked_until'] ?? '');

        if ($lockedUntil !== '' && strtotime($lockedUntil) !== false && strtotime($lockedUntil) > time()) {
            return self::STATUS_LOCKED;
        }

        if (!password_verify($password, (string) $user['password'])) {
            $attempts = $users->incrementFailedAttempts((int) $user['id']);
            $maxAttempts = (int) config('app.login_max_attempts', 5);

            if ($attempts >= $maxAttempts) {
                $lockMinutes = (int) config('app.login_lock_minutes', 15);
                $users->setLock((int) $user['id'], date('Y-m-d H:i:s', time() + ($lockMinutes * 60)));
                return self::STATUS_LOCKED;
            }

            return self::STATUS_INVALID;
        }

        $users->touchLastLogin((int) $user['id']);
        $user = $users->find((int) $user['id']) ?? $user;

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user'] = $user;
        session_regenerate_id(true);

        return self::STATUS_SUCCESS;
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

        $user = (new UserModel())->find((int) $_SESSION['user_id']);

        if (!$user) {
            self::logout();
            return null;
        }

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
