<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

if ($auth->isLoggedIn()) {
    redirect('home.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf((string)($_POST['csrf'] ?? ''))) {
        $_SESSION['login_error'] = '不正なリクエストです。';
        redirect('login.php');
    }
    $email    = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($auth->login($email, $password)) {
        redirect('home.php');
    }
    redirect('login.php');
}

render('login', ['title' => 'ログイン']);
