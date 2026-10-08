<?php
declare(strict_types=1);
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/BookModel.php';

$auth->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = (int)($_POST['book_id'] ?? 0);
    if (!check_csrf((string)($_POST['csrf'] ?? '')) || $bookId <= 0) {
        flash_set('error', '不正なリクエストです。');
    } elseif ((new BookModel(db()))->setOnLoan($bookId, (int)$auth->getUserId())) {
        flash_set('success', '貸し出しが完了しました。');
    } else {
        flash_set('error', 'その書籍は現在貸し出されています。');
    }
    redirect('loan.php');
}

render('loan', [
    'title' => '貸し出し',
    'books' => (new BookModel(db()))->getAvailable(),
]);
