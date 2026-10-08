<?php
declare(strict_types=1);

class Loan
{
    /** Borrow one copy. Returns false when out of stock or the user already holds it. */
    public static function borrow(int $userId, int $bookId, PDO $pdo): bool
    {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM loans WHERE user_id = :uid AND book_id = :bid AND status = "borrowed"'
            );
            $stmt->execute(['uid' => $userId, 'bid' => $bookId]);
            if ((int)$stmt->fetchColumn() > 0 || !Book::takeOne($bookId, $pdo)) {
                $pdo->rollBack();
                return false;
            }
            $stmt = $pdo->prepare(
                'INSERT INTO loans (user_id, book_id, loan_date, due_date, status)
                 VALUES (:uid, :bid, :ld, :dd, "borrowed")'
            );
            $stmt->execute([
                'uid' => $userId,
                'bid' => $bookId,
                'ld'  => date('Y-m-d'),
                'dd'  => date('Y-m-d', strtotime('+' . LOAN_DAYS . ' days')),
            ]);
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Return a loan owned by $userId. Returns false if not found or already returned. */
    public static function giveBack(int $loanId, int $userId, PDO $pdo): bool
    {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'UPDATE loans SET return_date = :rd, status = "returned"
                 WHERE id = :id AND user_id = :uid AND status = "borrowed"'
            );
            $stmt->execute(['rd' => date('Y-m-d'), 'id' => $loanId, 'uid' => $userId]);
            if ($stmt->rowCount() !== 1) {
                $pdo->rollBack();
                return false;
            }
            $stmt = $pdo->prepare('SELECT book_id FROM loans WHERE id = :id');
            $stmt->execute(['id' => $loanId]);
            Book::giveBackOne((int)$stmt->fetchColumn(), $pdo);
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function activeByUser(int $userId, PDO $pdo): array
    {
        $stmt = $pdo->prepare(
            'SELECT l.id, b.title, b.author, b.isbn, l.loan_date, l.due_date
             FROM loans l JOIN books b ON l.book_id = b.id
             WHERE l.user_id = :uid AND l.status = "borrowed" ORDER BY l.loan_date DESC, l.id DESC'
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public static function historyByUser(int $userId, PDO $pdo): array
    {
        $stmt = $pdo->prepare(
            'SELECT l.id, b.title, b.author, b.isbn, l.loan_date, l.due_date, l.return_date, l.status
             FROM loans l JOIN books b ON l.book_id = b.id
             WHERE l.user_id = :uid ORDER BY l.loan_date DESC, l.id DESC'
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }
}
