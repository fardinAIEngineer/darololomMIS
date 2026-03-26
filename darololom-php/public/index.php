<?php

declare(strict_types=1);

require dirname(__DIR__) . '/config/bootstrap.php';

use App\Core\Router;

$router = new Router();
$registerRoutes = require dirname(__DIR__) . '/config/routes.php';
$registerRoutes($router);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';

$scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
if ($scriptName !== '/' && str_starts_with($path, $scriptName)) {
    $path = substr($path, strlen($scriptName)) ?: '/';
}

$router->dispatch($method, rtrim($path, '/') ?: '/');
