<?php
$error = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);
?>
<h2>会員登録</h2>
<?php if ($error) : ?>
    <p class="flash error"><?= html_escape($error) ?></p>
<?php endif; ?>
<form action="<?= BASE_URL ?>register.php" method="post">
    <label>ユーザー名
        <input type="text" name="username" required minlength="3" maxlength="20">
    </label>
    <label>メールアドレス
        <input type="email" name="email" required>
    </label>
    <label>パスワード
        <input type="password" name="password" required minlength="6">
    </label>
    <?= csrf_field() ?>
    <button type="submit">登録する</button>
</form>
<p><a href="<?= BASE_URL ?>login.php">ログインはこちら</a></p>
