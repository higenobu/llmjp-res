<?php
declare(strict_types=1);

/**
 * books / loans table access
 */
class BookModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /** All books (paged) */
    public function getAll(int $offset = 0, int $limit = 20): array {
        $stmt = $this->pdo->prepare(
            'SELECT id, title, author, published_year, isbn, status
             FROM books ORDER BY published_year DESC, title ASC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Keyword search on title / author */
    public function search(string $keyword, int $offset = 0, int $limit = 20): array {
        $kw = '%' . addcslashes($keyword, '\\%_') . '%';
        $stmt = $this->pdo->prepare(
            'SELECT id, title, author, published_year, isbn, status
             FROM books
             WHERE title LIKE :kw1 OR author LIKE :kw2
             ORDER BY title ASC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':kw1', $kw);
        $stmt->bindValue(':kw2', $kw);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $bookId): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT id, title, author, published_year, isbn, status FROM books WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $bookId]);
        return $stmt->fetch() ?: null;
    }

    /** Books currently available for loan */
    public function getAvailable(int $limit = 100): array {
        $stmt = $this->pdo->prepare(
            "SELECT id, title, author, published_year, isbn, status
             FROM books WHERE status = 'available' ORDER BY title ASC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Books the user currently has on loan */
    public function getLoanedByUser(int $userId): array {
        $stmt = $this->pdo->prepare(
            'SELECT b.id, b.title, b.author, b.isbn, l.loaned_at
             FROM loans l JOIN books b ON l.book_id = b.id
             WHERE l.user_id = :uid AND l.returned_at IS NULL
             ORDER BY l.loaned_at DESC'
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    /** Full loan history of the user */
    public function getHistory(int $userId): array {
        $stmt = $this->pdo->prepare(
            'SELECT b.title, b.author, b.isbn, l.loaned_at, l.returned_at
             FROM loans l JOIN books b ON l.book_id = b.id
             WHERE l.user_id = :uid
             ORDER BY l.loaned_at DESC'
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    /** Loan a book. Returns false if it is not available. */
    public function setOnLoan(int $bookId, int $userId): bool {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE books SET status = 'loaned' WHERE id = :id AND status = 'available'"
            );
            $stmt->execute(['id' => $bookId]);
            if ($stmt->rowCount() !== 1) {
                $this->pdo->rollBack();
                return false;
            }
            $stmt = $this->pdo->prepare(
                'INSERT INTO loans (book_id, user_id, loaned_at) VALUES (:bid, :uid, NOW())'
            );
            $stmt->execute(['bid' => $bookId, 'uid' => $userId]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /** Return a book the user has on loan. Returns false if there is no such loan. */
    public function setReturned(int $bookId, int $userId): bool {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE loans SET returned_at = NOW()
                 WHERE book_id = :bid AND user_id = :uid AND returned_at IS NULL'
            );
            $stmt->execute(['bid' => $bookId, 'uid' => $userId]);
            if ($stmt->rowCount() < 1) {
                $this->pdo->rollBack();
                return false;
            }
            $stmt = $this->pdo->prepare("UPDATE books SET status = 'available' WHERE id = :id");
            $stmt->execute(['id' => $bookId]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
