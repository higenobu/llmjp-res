<?php
declare(strict_types=1);

class AuthController
{
    public function login(): void
    {
        if (getCurrentUser()) {
            redirect('');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $email = trim((string)($_POST['email'] ?? ''));
            $user = User::findByEmail($email, db());
            if ($user && verify_password((string)($_POST['password'] ?? ''), $user->getPasswordHash())) {
                login_user($user);
                redirect('');
            }
            flash('error', 'メールアドレスまたはパスワードが違います。');
            redirect('login');
        }
        render('login');
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('');
        }
        verify_csrf();
        $_SESSION = [];
        session_destroy();
        session_start();
        redirect('login');
    }

    public function home(): void
    {
        render('home', ['user' => require_login()]);
    }
}
