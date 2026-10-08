<?php
$books   = $books ?? [];
$keyword = $keyword ?? '';
?>
<h2>書籍検索</h2>

<form action="<?= BASE_URL ?>search.php" method="get">
    <input type="text" name="q" value="<?= html_escape($keyword) ?>" placeholder="タイトル／著者名">
    <button type="submit">検索</button>
</form>

<?php if ($keyword !== '') : ?>
    <p>「<?= html_escape($keyword) ?>」の検索結果（<?= count($books) ?>件）</p>
<?php else : ?>
    <p>新着書籍一覧（<?= count($books) ?>件）</p>
<?php endif; ?>

<ul>
    <?php foreach ($books as $b) : ?>
        <li>
            <strong><?= html_escape($b['title']) ?></strong>（<?= html_escape($b['author']) ?>）
            <?php if ($b['status'] === 'loaned') : ?>
                – 貸出中
            <?php else : ?>
                – 在庫あり
                <form action="<?= BASE_URL ?>loan.php" method="post" class="inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="book_id" value="<?= (int)$b['id'] ?>">
                    <button type="submit" class="borrow-btn">貸し出し</button>
                </form>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
