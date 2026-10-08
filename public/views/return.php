<?php $books = $books ?? []; ?>
<h2>返却</h2>
<?php if (empty($books)) : ?>
    <p>返却する書籍はありません。</p>
<?php else : ?>
    <table>
        <tr><th>タイトル</th><th>著者</th><th>ISBN</th><th>貸出日</th><th></th></tr>
        <?php foreach ($books as $b) : ?>
            <tr>
                <td><?= html_escape($b['title']) ?></td>
                <td><?= html_escape($b['author']) ?></td>
                <td><?= html_escape($b['isbn']) ?></td>
                <td><?= html_escape(date('Y-m-d', strtotime($b['loaned_at']))) ?></td>
                <td>
                    <form action="<?= BASE_URL ?>return.php" method="post" class="inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="book_id" value="<?= (int)$b['id'] ?>">
                        <button type="submit">返却</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
