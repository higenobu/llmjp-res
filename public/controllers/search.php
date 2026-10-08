<?php
declare(strict_types=1);
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/BookModel.php';

$auth->requireLogin();

$keyword = trim((string)($_GET['q'] ?? ''));
$model   = new BookModel(db());
$books   = $keyword !== '' ? $model->search($keyword, 0, 100) : $model->getAll(0, 100);

render('search', [
    'title'   => '書籍検索',
    'books'   => $books,
    'keyword' => $keyword,
    'userId'  => $auth->getUserId(),
]);
