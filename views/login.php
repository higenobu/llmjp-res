<?php $title = 'ログイン'; require __DIR__ . '/_header.php'; ?>
<h1>ログイン</h1>
<form action="<?= h(url('login')) ?>" method="post">
    <?= csrf_field() ?>
    <label>メールアドレス<br><input type="email" name="email" required></label>
    <label>パスワード<br><input type="password" name="password" required></label>
    <button type="submit">ログイン</button>
</form>
<p><a href="<?= h(url('register')) ?>">アカウントをお持ちでない方はこちら</a></p>
<?php require __DIR__ . '/_footer.php'; ?>
