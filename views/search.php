<?php $title = '書籍検索'; require __DIR__ . '/_header.php'; ?>
<h1>書籍検索</h1>
<form action="<?= h(url('search')) ?>" method="get">
    <input type="text" name="keyword" value="<?= h($keyword) ?>" placeholder="タイトル／著者／ISBN">
    <button type="submit">検索</button>
</form>
<?php if (empty($books)): ?>
<p>該当する書籍はありません。</p>
<?php else: ?>
<table>
    <tr><th>ISBN</th><th>タイトル</th><th>著者</th><th>在庫</th></tr>
    <?php foreach ($books as $b): ?>
    <tr>
        <td><?= h($b['isbn']) ?></td>
        <td><?= h($b['title']) ?></td>
        <td><?= h($b['author']) ?></td>
        <td><?= (int)$b['stock'] ?> / <?= (int)$b['total'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/_footer.php'; ?>
