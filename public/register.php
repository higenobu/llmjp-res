<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

if ($auth->isLoggedIn()) {
    redirect('home.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf((string)($_POST['csrf'] ?? ''))) {
        $_SESSION['register_error'] = '不正なリクエストです。';
        redirect('register.php');
    }
    $username = trim((string)($_POST['username'] ?? ''));
    $email    = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $len = mb_strlen($username);
    if ($len < 3 || $len > 20) {
        $_SESSION['register_error'] = 'ユーザー名は3〜20文字で入力してください。';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = '有効なメールアドレスを入力してください。';
    } elseif (strlen($password) < 6) {
        $_SESSION['register_error'] = 'パスワードは6文字以上で入力してください。';
    } elseif ($auth->register($username, $email, $password)) {
        redirect('home.php');
    }
    redirect('register.php');
}

render('register', ['title' => '会員登録']);
