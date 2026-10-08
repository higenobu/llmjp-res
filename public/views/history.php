<?php $history = $history ?? []; ?>
<h2>貸出履歴</h2>
<?php if (empty($history)) : ?>
    <p>まだ貸出履歴はありません。</p>
<?php else : ?>
    <table>
        <tr><th>タイトル</th><th>著者</th><th>ISBN</th><th>貸出日</th><th>返却日</th></tr>
        <?php foreach ($history as $row) : ?>
            <tr>
                <td><?= html_escape($row['title']) ?></td>
                <td><?= html_escape($row['author']) ?></td>
                <td><?= html_escape($row['isbn']) ?></td>
                <td><?= html_escape(date('Y-m-d', strtotime($row['loaned_at']))) ?></td>
                <td><?= $row['returned_at'] ? html_escape(date('Y-m-d', strtotime($row['returned_at']))) : '貸出中' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
