<?php $title = '返却'; require __DIR__ . '/_header.php'; ?>
<h1>返却</h1>
<?php if (empty($loans)): ?>
<p>現在、借りている本はありません。</p>
<?php else: ?>
<table>
    <tr><th>ISBN</th><th>タイトル</th><th>貸出日</th><th>返却期限</th><th></th></tr>
    <?php foreach ($loans as $l): ?>
    <tr>
        <td><?= h($l['isbn']) ?></td>
        <td><?= h($l['title']) ?></td>
        <td><?= h($l['loan_date']) ?></td>
        <td><?= h($l['due_date']) ?></td>
        <td>
            <form class="inline" action="<?= h(url('return')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="loan_id" value="<?= (int)$l['id'] ?>">
                <button type="submit">返却</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/_footer.php'; ?>
