# Result 1

## Prompt

Create teh following programs:
```
library_app/
├─ app/
│   ├─ config.php
│   ├─ functions.php
│   ├─ models/
│   │   ├─ User.php
│   │   ├─ Book.php
│   │   └─ Loan.php
│   └─ controllers/
│       ├─ RegisterController.php
│       ├─ BookInController.php
│       ├─ SearchController.php
│       ├─ LoanController.php
│       ├─ ReturnController.php
│       └─ HistoryController.php
├─ public/
│   ├─ index.php
│   ├─ css/
│   │   └─ style.css
│   ├─ js/
│   │   └─ main.js
│   └─ views/
│       ├─ register.php
│       ├─ book_in.php
│       ├─ search.php
│       ├─ loan.php
│       ├─ return.php
│       └─ history.php
└─ .htaccess

## Program

Below is a **complete starter‑kit** for the “Library App” you described.  
All files are written in plain PHP 7+ (no Composer) so you can copy the folder structure, drop it into any web‑root and start testing immediately.

> **What you’ll get**
> * A tiny **MVC** layout (`app/` → models / controllers / functions)
> * Simple **SQLite** database (you can swap to MySQL/PostgreSQL later)
> * Password handling with **bcrypt**
> * Basic HTML/CSS/JS front‑ends (`public/views/*.php`)
> * An **`.htaccess`** that rewrites all requests to `public/index.php`
> * Comments & TODOs where you can extend the logic

---

## 1️⃣ Folder Structure (copy‑paste)

```
library_app/
│
├─ app/
│   ├─ config.php
│   ├─ functions.php
│   ├─ models/
│   │   ├─ User.php
│   │   ├─ Book.php
│   │   └─ Loan.php
│   └─ controllers/
│       ├─ RegisterController.php
│       ├─ BookInController.php
│       ├─ SearchController.php
│       ├─ LoanController.php
│       ├─ ReturnController.php
│       └─ HistoryController.php
│
├─ public/
│   ├─ index.php
│   ├─ css/
│   │   └─ style.css
│   ├─ js/
│   │   └─ main.js
│   └─ views/
│       ├─ register.php
│       ├─ book_in.php
│       ├─ search.php
│       ├─ loan.php
│       ├─ return.php
│       └─ history.php
│
└─ .htaccess
```

---

## 2️⃣ `.htaccess` – URL rewriting  

```apacheconf
# -------------------------------------------------
# .htaccess – route everything to public/index.php
# -------------------------------------------------
RewriteEngine On

# Exclude files that should not be rewritten
RewriteRule ^(.*\.(css|js|png|jpg|jpeg|gif|ico))$ /$1 [L]
RewriteRule ^(.*\.php)$ /$1 [L]

# All other requests go to index.php
RewriteRule ^(.*)$ /index.php [L]
```

*Place this file inside the project root.*  

---

## 3️⃣ `public/index.php` – entry point & router  

```php
<?php
/**
 * public/index.php
 *
 * Very small router – reads the first segment of the URL,
 * loads the matching controller and calls its handle() method.
 */

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/functions.php';

// Autoload classes (PSR‑4 style)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . '/' . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// ------------------------------------------------------------------
// 1️⃣ Load session & basic security
// ------------------------------------------------------------------
session_start();
if (!isset($_SESSION['user_id'])) {
    // Not logged in → redirect to login page (we only have register for demo)
    header('Location: /register');
    exit;
}

// ------------------------------------------------------------------
// 2️⃣ Parse request
// ------------------------------------------------------------------
$controllerName = '';
$action         = '';

// Example URLs:
// /search          → SearchController::search()
// /loan/add        → LoanController::add()
// /history         → HistoryController::show()

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

if ($segments[0] === '') {
    $segments = ['index'];
}

// First segment = controller (lowercase)
$controllerName = ucfirst($segments[0]) . 'Controller';
$action         = $segments[1] ?? 'index';

// ------------------------------------------------------------------
// 3️⃣ Dispatch
// ------------------------------------------------------------------
if (!class_exists($controllerName)) {
    http_response_code(404);
    echo '<h1>404 – Controller not found</h1>';
    exit;
}

$controller = new $controllerName();

// Each controller must implement a `handle($action)` method.
if (!method_exists($controller, 'handle')) {
    http_response_code(500);
    echo '<h1>500 – Controller missing handle() method</h1>';
    exit;
}

try {
    $controller->handle($action);
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Error</h1>';
    echo '<pre>', htmlspecialchars($e->getMessage()), '</pre>';
}
```

> **Why this works:**  
> * The router lives in the public folder, so every request passes through it.  
> * All business logic stays inside the `app/controllers/*` classes.  

---

## 4️⃣ `app/config.php` – DB connection & constants  

```php
<?php
/**
 * app/config.php
 *
 * Central place for configuration values.
 * For simplicity we use SQLite stored in the project root.
 */

define('DB_FILE', __DIR__ . '/../data/library.db');   // <-- create this file first

/**
 * Get a PDO instance (singleton).
 * You can replace this with mysqli/PDO for another RDBMS.
 */
function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'sqlite:' . DB_FILE;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO($dsn, null, null, $options);
    }
    return $pdo;
}

/**
 * Helper to fetch a single row as object (optional).
 */
function fetchOne(string $sql, array $params = []): ?array
{
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Helper to execute non‑SELECT statements.
 */
function exec(string $sql, array $params = []): void
{
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
}

/**
 * Create tables if they do not exist yet.
 * Run once after cloning the repo (or call from a CLI script).
 */
function initSchema(): void
{
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS books (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    author TEXT NOT NULL,
    isbn TEXT UNIQUE NOT NULL,
    available INTEGER NOT NULL DEFAULT 1   -- 1 = yes, 0 = no
);

CREATE TABLE IF NOT EXISTS loans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    book_id INTEGER NOT NULL,
    borrowed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    returned_at DATETIME,
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(book_id) REFERENCES books(id)
);
SQL;

    exec($sql);
}

// Uncomment the line below the first time you run the app
// initSchema();
```

---

## 5️⃣ `app/functions.php` – small utility helpers  

```php
<?php
/**
 * app/functions.php
 *
 * Small reusable functions used across controllers.
 */

/**
 * Generate a random string (used for temporary tokens if needed)
 */
function randomString(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

/**
 * Escape output for XSS protection (simple wrapper around htmlspecialchars)
 */
function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
```

---

## 6️⃣ Models  

### 6.1 `User.php`

```php
<?php
namespace App\Models;

use PDO;
use PDOException;

/**
 * User model – handles authentication & user data.
 */
class User
{
    private int $id;
    private string $username;
    private string $email;
    private string $password;   // bcrypt hash

    /** @var PDO */
    private static PDO $db;

    /**
     * Load a user by ID.
     */
    public static function find(int $id): ?self
    {
        $row = fetchOne('SELECT * FROM users WHERE id = :id', ['id' => $id]);
        if (!$row) {
            return null;
        }
        return new self($row);
    }

    /**
     * Find a user by username.
     */
    public static function findByUsername(string $username): ?self
    {
        $row = fetchOne('SELECT * FROM users WHERE username = :u', ['u' => $username]);
        return $row ? new self($row) : null;
    }

    /**
     * Register a new user (stores bcrypt hash).
     */
    public static function register(string $username, string $email, string $plainPassword): bool
    {
        $hash = password_hash($plainPassword, PASSWORD_BCRYPT);
        if ($hash === false) {
            return false;
        }

        try {
            exec('INSERT INTO users (username,email,password) VALUES (:u,:e,:p)', [
                'u' => $username,
                'e' => $email,
                'p' => $hash,
            ]);
            return true;
        } catch (PDOException $e) {
            // Duplicate username/email will raise an error – treat as failure
            return false;
        }
    }

    /**
     * Verify password.
     */
    public function checkPassword(string $plain): bool
    {
        return password_verify($plain, $this->password);
    }

    // -----------------------------------------------------------------
    // Constructor (private fields are set via property injection)
    // -----------------------------------------------------------------
    private function __construct(array $data)
    {
        $this->id        = (int)$data['id'];
        $this->username  = $data['username'];
        $this->email     = $data['email'];
        $this->password  = $data['password'];
    }

    // Getters (setters omitted – immutable after creation)
    public function getId(): int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
}
```

### 6.2 `Book.php`

```php
<?php
namespace App\Models;

/**
 * Book model – simple CRUD over the `books` table.
 */
class Book
{
    private int $id;
    private string $title;
    private string $author;
    private string $isbn;
    private bool $available;

    private static PDO $db;

    /** Load a book by ID */
    public static function find(int $id): ?self
    {
        $row = fetchOne('SELECT * FROM books WHERE id = :id', ['id' => $id]);
        return $row ? new self($row) : null;
    }

    /** Load all books (ordered alphabetically) */
    public static function all(): array
    {
        $stmt = getDB()->query('SELECT * FROM books ORDER BY title');
        return $stmt->fetchAll();
    }

    /** Insert a new book */
    public static function add(string $title, string $author, string $isbn): bool
    {
        try {
            exec('INSERT INTO books (title,author,isbn) VALUES (:t,:a,:i)', [
                't' => $title,
                'a' => $author,
                'i' => $isbn,
            ]);
            return true;
        } catch (PDOException $e) {
            return false; // e.g. duplicate ISBN
        }
    }

    /** Mark a book as unavailable (when loaned out) */
    public static function setUnavailable(int $bookId): void
    {
        exec('UPDATE books SET available = 0 WHERE id = :id', ['id' => $bookId]);
    }

    /** Mark a book as available again */
    public static function setAvailable(int $bookId): void
    {
        exec('UPDATE books SET available = 1 WHERE id = :id', ['id' => $bookId]);
    }

    // -----------------------------------------------------------------
    // ctor + getters
    // -----------------------------------------------------------------
    private function __construct(array $data)
    {
        $this->id        = (int)$data['id'];
        $this->title     = $data['title'];
        $this->author    = $data['author'];
        $this->isbn      = $data['isbn'];
        $this->available = (bool)$data['available'];
    }

    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getAuthor(): string { return $this->author; }
    public function getIsbn(): string { return $this->isbn; }
    public function isAvailable(): bool { return $this->available; }
}
```

### 6.3 `Loan.php`

```php
<?php
namespace App\Models;

/**
 * Loan model – represents a borrowing record.
 */
class Loan
{
    private int $id;
    private int $userId;
    private int $bookId;
    private string $borrowedAt;
    private ?string $returnedAt = null;

    private static PDO $db;

    /** Load a loan by ID */
    public static function find(int $id): ?self
    {
        $row = fetchOne('SELECT * FROM loans WHERE id = :id', ['id' => $id]);
        return $row ? new self($row) : null;
    }

    /** All active (not yet returned) loans for a given user