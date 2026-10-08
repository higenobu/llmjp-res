<?php $books = $books ?? []; ?>
<h2>貸し出し</h2>
<?php if (empty($books)) : ?>
    <p>貸し出し可能な書籍はありません。</p>
<?php else : ?>
    <table>
        <tr><th>タイトル</th><th>著者</th><th>ISBN</th><th></th></tr>
        <?php foreach ($books as $b) : ?>
            <tr>
                <td><?= html_escape($b['title']) ?></td>
                <td><?= html_escape($b['author']) ?></td>
                <td><?= html_escape($b['isbn']) ?></td>
                <td>
                    <form action="<?= BASE_URL ?>loan.php" method="post" class="inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="book_id" value="<?= (int)$b['id'] ?>">
                        <button type="submit" class="borrow-btn">貸し出し</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
