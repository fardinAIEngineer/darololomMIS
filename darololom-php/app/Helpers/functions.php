<?php

declare(strict_types=1);

function config(string $key, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require dirname(__DIR__, 2) . '/config/app.php';
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

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim((string) config('base_url', ''), '/');
    $path = '/' . ltrim($path, '/');
    return $base . ($path === '/' ? '' : $path);
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function with_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function paginated_sizes(): array
{
    return (array) config('pagination.allowed', [10, 20, 50, 100]);
}

function to_persian_number(int|string $number): string
{
    $map = ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹'];
    return strtr((string) $number, $map);
}

function upload_file(string $field, string $subDir, array $allowedExtensions = []): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($_FILES[$field]['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return null;
    }

    $tmp = $_FILES[$field]['tmp_name'] ?? '';
    $original = $_FILES[$field]['name'] ?? '';
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

    if ($allowedExtensions !== [] && !in_array($ext, $allowedExtensions, true)) {
        return null;
    }

    $filename = bin2hex(random_bytes(16)) . ($ext ? '.' . $ext : '');
    $baseDir = dirname(__DIR__, 2) . '/public/assets/uploads/' . trim($subDir, '/');

    if (!is_dir($baseDir) && !mkdir($baseDir, 0775, true) && !is_dir($baseDir)) {
        return null;
    }

    $target = $baseDir . '/' . $filename;
    if (!move_uploaded_file($tmp, $target)) {
        return null;
    }

    return '/assets/uploads/' . trim($subDir, '/') . '/' . $filename;
}
