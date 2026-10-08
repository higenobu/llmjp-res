<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';
require_once APP_ROOT . '/models/User.php';
require_once APP_ROOT . '/models/Book.php';
require_once APP_ROOT . '/models/Loan.php';
foreach (['Auth', 'Register', 'BookIn', 'Search', 'Loan', 'Return', 'History'] as $c) {
    require_once APP_ROOT . "/controllers/{$c}Controller.php";
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (strpos($path, BASE_URL) === 0) {
    $path = substr($path, strlen(BASE_URL));
}
$route = trim(preg_replace('#\.php$#', '', trim($path, '/')), '/');
if ($route === 'index' || $route === 'home') {
    $route = '';
}

$routes = [
    ''        => [AuthController::class, 'home'],
    'login'   => [AuthController::class, 'login'],
    'logout'  => [AuthController::class, 'logout'],
    'register'=> [RegisterController::class, 'handle'],
    'book_in' => [BookInController::class, 'handle'],
    'search'  => [SearchController::class, 'handle'],
    'loan'    => [LoanController::class, 'handle'],
    'return'  => [ReturnController::class, 'handle'],
    'history' => [HistoryController::class, 'handle'],
];

if (!isset($routes[$route])) {
    http_response_code(404);
    exit('404 Not Found');
}

[$class, $method] = $routes[$route];
(new $class())->$method();
