<?php $title = '貸出履歴'; require __DIR__ . '/_header.php'; ?>
<h1>貸出履歴</h1>
<?php if (empty($loans)): ?>
<p>履歴がありません。</p>
<?php else: ?>
<table>
    <tr><th>ID</th><th>タイトル</th><th>著者</th><th>貸出日</th><th>返却期限</th><th>返却日</th><th>状態</th></tr>
    <?php foreach ($loans as $l): ?>
    <tr>
        <td><?= (int)$l['id'] ?></td>
        <td><?= h($l['title']) ?></td>
        <td><?= h($l['author']) ?></td>
        <td><?= h($l['loan_date']) ?></td>
        <td><?= h($l['due_date']) ?></td>
        <td><?= h($l['return_date'] ?? '-') ?></td>
        <td><?= $l['status'] === 'returned' ? '返却済' : '貸出中' ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/_footer.php'; ?>
