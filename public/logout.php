<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_csrf((string)($_POST['csrf'] ?? ''))) {
    $auth->logout();
    redirect('login.php');
}
redirect('home.php');
