<?php

session_start();

require 'core/http.php';
require 'core/router.php';
require 'core/html.php';
require 'app/models/operator.php';
require 'app/models/item.php';
require 'app/models/category.php';
require 'core/helpers.php';

require 'config/database.php';

$base = __DIR__ . '/app';

$segments = http_in($_SERVER['REQUEST_URI']);

if (isset($segments[0]) && $segments[0] === 'admin') {
    if (empty($_SESSION['user_id'])) {
        redirect('/login');
    }
    $base = __DIR__ . '/app/admin';
}

$route = route($segments);

try {
    $main = run($route, $base, $pdo);
    $status = 200;
} catch (RuntimeException $e) {
    $main = render(__DIR__ . '/app/views/404.php', []);
    $status = 404;
}

$body = render(__DIR__ . '/app/views/_layout.php', [
    'page_content' => $main,
    'page'         => $route['entity'],
]);

http_out($status, $body);