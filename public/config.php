<?php
declare(strict_types=1);

/* Base settings */
define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/') . '/');
define('APP_NAME', '図書管理システム');
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'admin@example.com');

/* Database settings (override with environment variables) */
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'library');
define('DB_USER', getenv('DB_USER') ?: 'library_user');
define('DB_PASS', getenv('DB_PASS') ?: '');

/* Session */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
