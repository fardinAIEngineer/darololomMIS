<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function csrfCheck(): void
    {
        $token = $_POST['_token'] ?? '';
        if (!hash_equals(csrf_token(), $token)) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            exit;
        }
    }

    protected function intParam(array $params, string $key): int
    {
        return (int) ($params[$key] ?? 0);
    }
}
