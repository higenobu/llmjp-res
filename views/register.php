<?php $title = '会員登録'; require __DIR__ . '/_header.php'; ?>
<h1>会員登録</h1>
<form action="<?= h(url('register')) ?>" method="post">
    <?= csrf_field() ?>
    <label>名前<br><input type="text" name="name" maxlength="100" required></label>
    <label>メールアドレス<br><input type="email" name="email" maxlength="255" required></label>
    <label>パスワード（6文字以上）<br><input type="password" name="password" minlength="6" required></label>
    <button type="submit">登録</button>
</form>
<p><a href="<?= h(url('login')) ?>">既にアカウントをお持ちの方はこちら</a></p>
<?php require __DIR__ . '/_footer.php'; ?>
