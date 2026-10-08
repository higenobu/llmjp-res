<?php
$username = $auth->getUsername();
$role     = $auth->getRole();
?>
<h2>ようこそ、<?= html_escape($username) ?> さん！</h2>
<p>あなたの権限: <strong><?= html_escape($role) ?></strong></p>

<ul>
    <li><a href="<?= BASE_URL ?>search.php">書籍検索</a></li>
    <li><a href="<?= BASE_URL ?>loan.php">貸し出し</a></li>
    <li><a href="<?= BASE_URL ?>return.php">返却</a></li>
    <li><a href="<?= BASE_URL ?>history.php">貸出履歴</a></li>
</ul>

<?php if ($role === 'admin') : ?>
    <hr>
    <p>※ 管理者専用メニュー（将来的に追加予定）</p>
<?php endif; ?>
