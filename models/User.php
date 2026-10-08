<?php
declare(strict_types=1);

class User
{
    public function __construct(
        private int $id,
        private string $name,
        private string $email,
        private string $passwordHash
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }

    private static function fromRow(?array $row): ?self
    {
        return $row ? new self((int)$row['id'], $row['name'], $row['email'], $row['password_hash']) : null;
    }

    public static function findById(int $id, PDO $pdo): ?self
    {
        $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return self::fromRow($stmt->fetch() ?: null);
    }

    public static function findByEmail(string $email, PDO $pdo): ?self
    {
        $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        return self::fromRow($stmt->fetch() ?: null);
    }

    public static function create(string $name, string $email, string $password, PDO $pdo): self
    {
        $hash = hash_password($password);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :hash)');
        $stmt->execute(['name' => $name, 'email' => $email, 'hash' => $hash]);
        return new self((int)$pdo->lastInsertId(), $name, $email, $hash);
    }
}
