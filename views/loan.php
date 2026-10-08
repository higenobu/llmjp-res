<?php $title = '貸出'; require __DIR__ . '/_header.php'; ?>
<h1>貸出</h1>
<?php if (empty($books)): ?>
<p>現在、借りられる書籍はありません。</p>
<?php else: ?>
<table>
    <tr><th>ISBN</th><th>タイトル</th><th>著者</th><th>在庫</th><th></th></tr>
    <?php foreach ($books as $b): ?>
    <tr>
        <td><?= h($b['isbn']) ?></td>
        <td><?= h($b['title']) ?></td>
        <td><?= h($b['author']) ?></td>
        <td><?= (int)$b['stock'] ?></td>
        <td>
            <form class="inline" action="<?= h(url('loan')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="book_id" value="<?= (int)$b['id'] ?>">
                <button type="submit">借りる</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/_footer.php'; ?>
