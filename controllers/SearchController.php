<?php
declare(strict_types=1);

class SearchController
{
    public function handle(): void
    {
        require_login();
        $keyword = trim((string)($_GET['keyword'] ?? ''));
        $books = Book::search($keyword, db());
        render('search', ['keyword' => $keyword, 'books' => $books]);
    }
}
