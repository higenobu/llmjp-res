<?php
declare(strict_types=1);

class Book
{
    public static function findById(int $id, PDO $pdo): ?array
    {
        $stmt = $pdo->prepare('SELECT id, title, author, isbn, stock, total FROM books WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByIsbn(string $isbn, PDO $pdo): ?array
    {
        $stmt = $pdo->prepare('SELECT id, title, author, isbn, stock, total FROM books WHERE isbn = :isbn');
        $stmt->execute(['isbn' => $isbn]);
        return $stmt->fetch() ?: null;
    }

    /** Add $qty copies; creates the book if the ISBN is new. */
    public static function receive(string $title, string $author, string $isbn, int $qty, PDO $pdo): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO books (title, author, isbn, stock, total) VALUES (:title, :author, :isbn, :qty, :qty2)
             ON DUPLICATE KEY UPDATE stock = stock + VALUES(stock), total = total + VALUES(total)'
        );
        $stmt->execute(['title' => $title, 'author' => $author, 'isbn' => $isbn, 'qty' => $qty, 'qty2' => $qty]);
    }

    public static function search(string $keyword, PDO $pdo): array
    {
        $escaped = addcslashes($keyword, '\\%_');
        $stmt = $pdo->prepare(
            'SELECT id, title, author, isbn, stock, total FROM books
             WHERE title LIKE :kw1 OR author LIKE :kw2 OR isbn LIKE :kw3 ORDER BY title'
        );
        $like = '%' . $escaped . '%';
        $stmt->execute(['kw1' => $like, 'kw2' => $like, 'kw3' => $like]);
        return $stmt->fetchAll();
    }

    public static function available(PDO $pdo): array
    {
        return $pdo->query('SELECT id, title, author, isbn, stock, total FROM books WHERE stock > 0 ORDER BY title')
            ->fetchAll();
    }

    /** Atomically take one copy from stock. */
    public static function takeOne(int $id, PDO $pdo): bool
    {
        $stmt = $pdo->prepare('UPDATE books SET stock = stock - 1 WHERE id = :id AND stock > 0');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public static function giveBackOne(int $id, PDO $pdo): void
    {
        $stmt = $pdo->prepare('UPDATE books SET stock = stock + 1 WHERE id = :id AND stock < total');
        $stmt->execute(['id' => $id]);
    }
}
