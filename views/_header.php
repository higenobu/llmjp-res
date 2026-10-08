<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($title ?? '図書貸出') ?></title>
<link rel="stylesheet" href="<?= h(url('css/style.css')) ?>">
</head>
<body>
<?php if (getCurrentUser()): ?>
<nav>
    <a href="<?= h(url('')) ?>">ホーム</a>
    <a href="<?= h(url('search')) ?>">書籍検索</a>
    <a href="<?= h(url('book_in')) ?>">入庫</a>
    <a href="<?= h(url('loan')) ?>">貸出</a>
    <a href="<?= h(url('return')) ?>">返却</a>
    <a href="<?= h(url('history')) ?>">貸出履歴</a>
    <form class="inline" action="<?= h(url('logout')) ?>" method="post"><?= csrf_field() ?><button type="submit">ログアウト</button></form>
</nav>
<?php endif; ?>
<?php foreach (pull_flash() as $m): ?>
<p class="msg <?= h($m['type']) ?>"><?= h($m['message']) ?></p>
<?php endforeach; ?>
