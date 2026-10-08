<?php $title = 'マイページ'; require __DIR__ . '/_header.php'; ?>
<h1>ようこそ、<?= h($user->getName()) ?> さん！</h1>
<ul>
    <li><a href="<?= h(url('search')) ?>">書籍検索</a></li>
    <li><a href="<?= h(url('book_in')) ?>">書籍の入庫</a></li>
    <li><a href="<?= h(url('loan')) ?>">貸出</a></li>
    <li><a href="<?= h(url('return')) ?>">返却</a></li>
    <li><a href="<?= h(url('history')) ?>">貸出履歴</a></li>
</ul>
<?php require __DIR__ . '/_footer.php'; ?>
