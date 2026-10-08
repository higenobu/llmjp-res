<?php
/**
 * Common layout. Variables provided by render():
 *   $title   page title
 *   $content rendered view HTML
 *   $auth    AuthService
 */
$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title) ?> - <?= html_escape(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body>
<header>
    <h1><a href="<?= BASE_URL ?>">📚 <?= html_escape(APP_NAME) ?></a></h1>
</header>

<nav>
    <?php if ($auth->isLoggedIn()) : ?>
        <ul>
            <li><a href="<?= BASE_URL ?>search.php">書籍検索</a></li>
            <li><a href="<?= BASE_URL ?>loan.php">貸し出し</a></li>
            <li><a href="<?= BASE_URL ?>return.php">返却</a></li>
            <li><a href="<?= BASE_URL ?>history.php">貸出履歴</a></li>
            <li>
                <form action="<?= BASE_URL ?>logout.php" method="post" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="link">ログアウト</button>
                </form>
            </li>
        </ul>
    <?php else : ?>
        <ul>
            <li><a href="<?= BASE_URL ?>login.php">ログイン</a></li>
            <li><a href="<?= BASE_URL ?>register.php">会員登録</a></li>
        </ul>
    <?php endif; ?>
</nav>

<main>
    <?php foreach ($flash as $type => $message) : ?>
        <p class="flash <?= $type === 'error' ? 'error' : 'success' ?>"><?= html_escape($message) ?></p>
    <?php endforeach; ?>
    <?= $content ?>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> <?= html_escape(APP_NAME) ?>. All rights reserved.</p>
</footer>
<script src="<?= BASE_URL ?>js/main.js"></script>
</body>
</html>
