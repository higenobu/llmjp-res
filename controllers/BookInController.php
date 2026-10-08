<?php
declare(strict_types=1);

class BookInController
{
    public function handle(): void
    {
        require_login();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            render('book_in');
            return;
        }
        verify_csrf();
        $title = trim((string)($_POST['title'] ?? ''));
        $author = trim((string)($_POST['author'] ?? ''));
        $isbn = trim((string)($_POST['isbn'] ?? ''));
        $qty = (int)($_POST['quantity'] ?? 0);

        $errors = [];
        if ($title === '' || mb_strlen($title) > 255) {
            $errors[] = 'タイトルは1〜255文字で入力してください。';
        }
        if ($author === '' || mb_strlen($author) > 255) {
            $errors[] = '著者名は1〜255文字で入力してください。';
        }
        if ($isbn === '' || strlen($isbn) > 20) {
            $errors[] = 'ISBN は1〜20文字で入力してください。';
        }
        if ($qty < 1) {
            $errors[] = '入庫数は1以上で入力してください。';
        }
        if ($errors) {
            foreach ($errors as $e) {
                flash('error', $e);
            }
            redirect('book_in');
        }

        Book::receive($title, $author, $isbn, $qty, db());
        flash('success', '書籍を入庫しました。');
        redirect('book_in');
    }
}
