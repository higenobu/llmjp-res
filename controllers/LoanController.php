<?php
declare(strict_types=1);

class LoanController
{
    public function handle(): void
    {
        $user = require_login();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            render('loan', ['books' => Book::available(db())]);
            return;
        }
        verify_csrf();
        $bookId = (int)($_POST['book_id'] ?? 0);
        if ($bookId > 0 && Loan::borrow($user->getId(), $bookId, db())) {
            flash('success', '貸し出しが完了しました。');
            redirect('history');
        }
        flash('error', '貸し出しできませんでした（在庫なし、または既に借りています）。');
        redirect('loan');
    }
}
