<?php
declare(strict_types=1);
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/BookModel.php';

$auth->requireLogin();
$model = new BookModel(db());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = (int)($_POST['book_id'] ?? 0);
    if (!check_csrf((string)($_POST['csrf'] ?? '')) || $bookId <= 0) {
        flash_set('error', '不正なリクエストです。');
    } elseif ($model->setReturned($bookId, (int)$auth->getUserId())) {
        flash_set('success', '返却が完了しました。');
    } else {
        flash_set('error', 'この書籍は貸し出されていません。');
    }
    redirect('return.php');
}

render('return', [
    'title' => '返却',
    'books' => $model->getLoanedByUser((int)$auth->getUserId()),
]);
