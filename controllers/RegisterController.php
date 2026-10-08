<?php
declare(strict_types=1);

class RegisterController
{
    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            render('register');
            return;
        }
        verify_csrf();
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        $errors = [];
        if ($name === '' || mb_strlen($name) > 100) {
            $errors[] = '名前は1〜100文字で入力してください。';
        }
        if (!is_valid_email($email)) {
            $errors[] = '有効なメールアドレスを入力してください。';
        }
        if (strlen($password) < 6) {
            $errors[] = 'パスワードは6文字以上で入力してください。';
        }
        if (!$errors && User::findByEmail($email, db())) {
            $errors[] = 'このメールアドレスは既に登録されています。';
        }
        if ($errors) {
            foreach ($errors as $e) {
                flash('error', $e);
            }
            redirect('register');
        }

        try {
            $user = User::create($name, $email, $password, db());
        } catch (PDOException $e) {
            flash('error', 'ユーザー登録に失敗しました。');
            redirect('register');
        }
        login_user($user);
        flash('success', '登録が完了しました。');
        redirect('');
    }
}
