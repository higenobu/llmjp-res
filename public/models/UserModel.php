<?php
declare(strict_types=1);

/**
 * users table access
 */
class UserModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, email, password_hash, role FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, email, role FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function exists(string $email, string $username): bool {
        $stmt = $this->pdo->prepare(
            'SELECT id FROM users WHERE email = :email OR username = :username LIMIT 1'
        );
        $stmt->execute(['email' => $email, 'username' => $username]);
        return (bool)$stmt->fetch();
    }

    public function create(string $username, string $email, string $passwordHash, string $role = 'member'): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, email, password_hash, role) VALUES (:u, :e, :p, :r)'
        );
        $stmt->execute(['u' => $username, 'e' => $email, 'p' => $passwordHash, 'r' => $role]);
        return (int)$this->pdo->lastInsertId();
    }
}
