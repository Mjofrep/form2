<?php

declare(strict_types=1);

use App\Core\Auth;

date_default_timezone_set((string) ((require __DIR__ . '/config/config.php')['app']['timezone'] ?? 'UTC'));

function config(string $key, mixed $default = null): mixed
{
    static $config;

    if ($config === null) {
        $config = require __DIR__ . '/config/config.php';
    }

    $segments = explode('.', $key);
    $value = $config;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function base_url(string $path = ''): string
{
    $base = rtrim((string) config('app.base_url', ''), '/');
    $path = ltrim($path, '/');

    if ($path === '') {
        return ($base !== '' ? $base : '') . '/index.php';
    }

    return ($base !== '' ? $base : '') . '/index.php?path=/' . $path;
}

function asset_url(string $path): string
{
    $base = rtrim((string) config('app.base_url', ''), '/');
    return ($base !== '' ? $base : '') . '/public/' . ltrim($path, '/');
}

function full_url(string $path = ''): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . base_url($path);
}

function storage_url(string $path): string
{
    $base = rtrim((string) config('app.base_url', ''), '/');
    return ($base !== '' ? $base : '') . '/public/uploads/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function request_path(): string
{
    if (isset($_GET['path'])) {
        $path = (string) $_GET['path'];
        return $path !== '' ? $path : '/';
    }

    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = rtrim((string) config('app.base_url', ''), '/');

    if (str_ends_with($uri, '/index.php')) {
        $uri = substr($uri, 0, -10) ?: '/';
    }

    if ($base !== '' && str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base)) ?: '/';
    }

    return $uri === '' ? '/' : $uri;
}

function old(string $key, mixed $default = null): mixed
{
    $old = $_SESSION['_old'] ?? [];
    return array_key_exists($key, $old) ? $old[$key] : $default;
}

function flash(string $key, mixed $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

function get_flash(string $key, mixed $default = null): mixed
{
    $flash = $_SESSION['_flash'][$key] ?? $default;
    unset($_SESSION['_flash'][$key]);
    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_token'] ?? '';

    if (!hash_equals((string) ($_SESSION['_csrf'] ?? ''), (string) $token)) {
        http_response_code(419);
        exit('CSRF token mismatch.');
    }
}

function auth_user(): ?array
{
    return Auth::user();
}

function is_logged_in(): bool
{
    return Auth::check();
}

function require_guest(): void
{
    if (is_logged_in()) {
        redirect(base_url('admin'));
    }
}

function require_auth(): void
{
    if (!is_logged_in()) {
        flash('error', 'Debes iniciar sesión para continuar.');
        redirect(base_url('login'));
    }
}

function render(string $view, array $data = [], string $layout = 'layouts/main'): void
{
    extract($data, EXTR_SKIP);
    $viewFile = __DIR__ . '/views/' . $view . '.php';
    $layoutFile = __DIR__ . '/views/' . $layout . '.php';

    ob_start();
    require $viewFile;
    $content = ob_get_clean();

    require $layoutFile;

    clear_old_input();
    clear_validation_errors();
}

function view_partial(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require __DIR__ . '/views/' . $view . '.php';
}

function input(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function to_datetime_local(?string $value): string
{
    if (!$value) {
        return '';
    }

    $timestamp = strtotime($value);
    return $timestamp ? date('Y-m-d\TH:i', $timestamp) : '';
}

function slugify(string $value): string
{
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = preg_replace('/[^A-Za-z0-9]+/', '_', strtolower($value)) ?: '';
    return trim($value, '_');
}

function normalize_lines(?string $value): array
{
    $lines = preg_split('/\r\n|\r|\n/', (string) $value) ?: [];
    $lines = array_map(static fn (string $line): string => trim($line), $lines);
    return array_values(array_filter($lines, static fn (string $line): bool => $line !== ''));
}

function video_embed_url(string $url): string
{
    $url = trim($url);

    if ($url === '') {
        return '';
    }

    $parts = parse_url($url);
    $host = strtolower((string) ($parts['host'] ?? ''));

    if (str_contains($host, 'youtube.com')) {
        parse_str((string) ($parts['query'] ?? ''), $query);
        if (!empty($query['v'])) {
            return 'https://www.youtube.com/embed/' . rawurlencode((string) $query['v']);
        }
    }

    if (str_contains($host, 'youtu.be')) {
        $videoId = trim((string) ($parts['path'] ?? ''), '/');
        if ($videoId !== '') {
            return 'https://www.youtube.com/embed/' . rawurlencode($videoId);
        }
    }

    if (str_contains($host, 'vimeo.com')) {
        $videoId = trim((string) ($parts['path'] ?? ''), '/');
        if ($videoId !== '') {
            return 'https://player.vimeo.com/video/' . rawurlencode($videoId);
        }
    }

    return $url;
}

function set_old_input(array $input): void
{
    $_SESSION['_old'] = $input;
}

function clear_old_input(): void
{
    unset($_SESSION['_old']);
}

function validation_errors(): array
{
    return $_SESSION['_errors'] ?? [];
}

function set_validation_errors(array $errors): void
{
    $_SESSION['_errors'] = $errors;
}

function clear_validation_errors(): void
{
    unset($_SESSION['_errors']);
}

function error_for(string $key): ?string
{
    $errors = validation_errors();
    return $errors[$key] ?? null;
}

function append_query(string $path, array $query): string
{
    return $path . (str_contains($path, '?') ? '&' : '?') . http_build_query($query);
}
