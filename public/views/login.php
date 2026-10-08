<?php
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<h2>ログイン</h2>
<?php if ($error) : ?>
    <p class="flash error"><?= html_escape($error) ?></p>
<?php endif; ?>
<form action="<?= BASE_URL ?>login.php" method="post">
    <label>メールアドレス
        <input type="email" name="email" required>
    </label>
    <label>パスワード
        <input type="password" name="password" required>
    </label>
    <?= csrf_field() ?>
    <button type="submit">ログイン</button>
</form>
<p><a href="<?= BASE_URL ?>register.php">会員登録はこちら</a></p>
