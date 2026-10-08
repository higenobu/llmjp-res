<?php $title = '書籍の入庫'; require __DIR__ . '/_header.php'; ?>
<h1>書籍の入庫</h1>
<form action="<?= h(url('book_in')) ?>" method="post">
    <?= csrf_field() ?>
    <label>タイトル<br><input type="text" name="title" maxlength="255" required></label>
    <label>著者<br><input type="text" name="author" maxlength="255" required></label>
    <label>ISBN<br><input type="text" name="isbn" maxlength="20" required></label>
    <label>入庫数<br><input type="number" name="quantity" min="1" value="1" required></label>
    <button type="submit">入庫</button>
</form>
<p>既存の ISBN を入力すると在庫が加算されます。</p>
<?php require __DIR__ . '/_footer.php'; ?>
