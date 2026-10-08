<?php
declare(strict_types=1);
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/BookModel.php';

$auth->requireLogin();

render('history', [
    'title'   => '貸出履歴',
    'history' => (new BookModel(db()))->getHistory((int)$auth->getUserId()),
]);
