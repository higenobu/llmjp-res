# Result 1

## Prompt

create following programs
book-rent-management

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

Below is a **complete starter‑kit** for a tiny “Book‑Rent Management” web‑app written in plain PHP 7+ / HTML / CSS / JavaScript.  
All files are placed under the `public/` folder exactly as you described; the `.htaccess` file protects the directory and enables rewrites (useful if you later add routes).

> **What you’ll get**
> * A simple SQLite/MySQL database schema (`users`, `books`, `loans`).  
> * All PHP scripts (including a reusable `header.php`/`footer.php`) that handle **registration**, **search**, **borrow**, **return**, **loan‑history** and a home page.  
> * Minimal CSS (`style.css`) and a small JavaScript helper (`main.js`) for client‑side validation / live search.  
> * An `.htaccess` file that disables directory listings and forces HTTPS (if you enable it later).  

Feel free to copy the whole tree into your server, adjust the DB credentials, and start customizing!

---

## 1️⃣ Directory layout

```
book-rent-management/
├─ public/
│  ├─ index.php
│  ├─ css/
│  │   └─ style.css
│  ├─ js/
│  │   └─ main.js
│  └─ views/
│      ├─ header.php
│      ├─ footer.php
│      ├─ register.php
│      ├─ book_in.php
│      ├─ search.php
│      ├─ loan.php
│      ├─ return.php
│      └─ history.php
└─ .htaccess
```

*(I added `header.php` & `footer.php` – they make the rest of the pages much cleaner.)*

---

## 2️⃣ Database – quick start

### 2.1 Create the DB (SQLite example)

```bash
$ sqlite3 library.db <<'SQL'
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL
);

CREATE TABLE books (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    author TEXT NOT NULL,
    isbn TEXT UNIQUE,
    available BOOLEAN NOT NULL DEFAULT 1
);

CREATE TABLE loans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    book_id INTEGER NOT NULL,
    borrowed_at DATETIME NOT NULL,
    returned_at DATETIME,
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(book_id) REFERENCES books(id)
);
SQL
```

If you prefer MySQL/MariaDB, just replace the connection string in `config.php`.

### 2.2 Config file (`public/config.php`)

```php
<?php
// ---------------------------------------------------
// config.php – put this somewhere outside the webroot
// ---------------------------------------------------
$DB_DSN = 'sqlite:library.db';          // change for MySQL:
// $DB_DSN = 'mysql:host=localhost;dbname=library;charset=utf8mb4';
$DB_USER = '';
$DB_PASS = '';

// Simple password hash function (bcrypt)
function hash_password(string $plain): string {
    return password_hash($plain, PASSWORD_BCRYPT);
}

// Verify password
function check_password(string $plain, string $hash): bool {
    return password_verify($plain, $hash);
}
?>
```

Add `require_once __DIR__.'/config.php';` at the top of any script that needs DB access.

---

## 3️⃣ Core PHP helpers (`public/views/header.php` & `footer.php`)

### `header.php`

```php
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Book Rental Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <h1>📚 書籍レンタル管理システム</h1>
    <nav>
        <a href="index.php">ホーム</a>
        <a href="search.php">検索</a>
        <a href="book_in.php">借りる</a>
        <a href="loan.php">貸出履歴</a>
        <a href="return.php">返却</a>
        <a href="history.php">自分の履歴</a>
    </nav>
    <?php
    // Show login/logout depending on session
    session_start();
    if (isset($_SESSION['user_id'])) {
        echo '<p>ようこそ、'.$_SESSION['name'].'さん！</p>';
        echo '<p><a href="logout.php">ログアウト</a></p>';
    } else {
        echo '<p><a href="register.php">新規登録</a> | <a href="login.php">ログイン</a></p>';
    }
    ?>
</header>

<main>
```

### `footer.php`

```php
</main>

<footer>
    <small>© 2025 Book Rental Demo – All rights reserved.</small>
</footer>

<script src="js/main.js"></script>
</body>
</html>
```

---

## 4️⃣ Individual page templates

Below each file contains **full source code** ready to drop into its location.

### 4.1 `public/index.php` – Home page

```php
<?php
require_once __DIR__.'/views/header.php';
?>

<h2>ようこそ！</h2>
<p>このサンプルは「本のレンタル」管理アプリです。</p>

<ul>
    <li><a href="search.php">本を検索</a></li>
    <li><a href="book_in.php">本を借りる</a></li>
    <li><a href="loan.php">貸出履歴を見る</a></li>
</ul>

<?php
require_once __DIR__.'/views/footer.php';
?>
```

---

### 4.2 `public/register.php` – User registration

```php
<?php
require_once __DIR__.'/config.php';
session_start();

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Process POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $pass    = $_POST['password'];
    $cpass   = $_POST['confirm'];

    // Very light validation
    if (!$name || !$email || !$pass || !$cpass) {
        $error = '全項目を入力してください。';
    } elseif ($pass !== $cpass) {
        $error = 'パスワードが一致しません。';
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $error = 'メールアドレスの形式が正しくありません。';
    } else {
        $pwdHash = hash_password($pass);
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash) VALUES (?,?,?)');
        $stmt->execute([$name,$email,$pwdHash]);
        $_SESSION['name'] = $name;
        $_SESSION['user_id'] = $stmt->lastInsertId();
        header('Location: index.php');
        exit;
    }
}

// Connect to DB
$pdo = new PDO($DB_DSN, $DB_USER, $DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

?>
<?php require_once __DIR__.'/views/header.php'; ?>
<h2>会員登録</h2>

<?php if(isset($error)): ?>
<p style="color:red;"><?=htmlspecialchars($error)?></p>
<?php endif; ?>

<form method="post" action="">
    <label>名前:<br><input type="text" name="name" required></label><br><br>
    <label>メール:<br><input type="email" name="email" required></label><br><br>
    <label>パスワード:<br><input type="password" name="password" required></label><br><br>
    <label>確認:<br><input type="password" name="confirm" required></label><br><br>
    <button type="submit">登録</button>
</form>

<?php require_once __DIR__.'/views/footer.php'; ?>
```

---

### 4.3 `public/login.php` – Login (tiny but useful)

```php
<?php
require_once __DIR__.'/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    $stmt = $pdo->prepare('SELECT id,name,password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && check_password($pass,$row['password_hash'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['name']    = $row['name'];
        header('Location: index.php');
        exit;
    }
    $error = 'メールアドレスまたはパスワードが違います。';
}

$pdo = new PDO($DB_DSN,$DB_USER,$DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

?>
<?php require_once __DIR__.'/views/header.php'; ?>
<h2>ログイン</h2>

<?php if(isset($error)): ?>
<p style="color:red;"><?=htmlspecialchars($error)?></p>
<?php endif; ?>

<form method="post" action="">
    <label>メール:<br><input type="email" name="email" required></label><br><br>
    <label>パスワード:<br><input type="password" name="password" required></label><br><br>
    <button type="submit">ログイン</button>
</form>

<?php require_once __DIR__.'/views/footer.php'; ?>
```

---

### 4.4 `public/logout.php` – End session

```php
<?php
session_start();
session_destroy();
header('Location: index.php');
exit;
?>
```

---

### 4.5 `public/search.php` – Search books (AJAX + PHP)

#### 4.5.1 HTML (GET)

```php
<?php
require_once __DIR__.'/config.php';
$pdo = new PDO($DB_DSN,$DB_USER,$DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
?>
<?php require_once __DIR__.'/views/header.php'; ?>
<h2>本の検索</h2>

<input type="text" id="q" placeholder="タイトル・著者・ISBN を入力…" autocomplete="off">
<div id="results"></div>

<?php require_once __DIR__.'/views/footer.php'; ?>
```

#### 4.5.2 JavaScript (`public/js/main.js` – part of it)

Add the following snippet **inside** `main.js` (or create a separate file `search.js` and load it).

```javascript
// ---------- search.js ----------
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('q');
    const results = document.getElementById('results');

    input.addEventListener('input', async e => {
        const term = e.target.value.trim();
        if (!term) { results.innerHTML = ''; return; }

        try {
            const resp = await fetch(`search.php?q=${encodeURIComponent(term)}`);
            const data = await resp.json();
            results.innerHTML = renderResults(data);
        } catch (err) {
            console.error(err);
            results.innerHTML = '<p>エラーが起きました。</p>';
        }
    });
});

function renderResults(arr) {
    if (arr.length === 0) return '<p>該当なし。</p>';
    return arr.map(b => `
        <div class="book-item">
            <strong>${b.title}</strong> — ${b.author}
            <br>ISBN: ${b.isbn||'-'}
            <br>在庫: <span class="${b.available?'ok':'ng'}">
                ${b.available?'あり':'なし'}
            </span>
            <br><button class="borrow-btn" data-id="${b.id}">借りる</button>
        </div>`).join('');
}
```

#### 4.5.3 PHP endpoint (`search.php`)

```php
<?php
require_once __DIR__.'/config.php';

// Simple JSON API – returns an array of books matching the query
$q = isset($_GET['q']) ? $_GET['q'] : '';
$sql = "SELECT id,title,author,isbn,available FROM books WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ? LIMIT 20";
$like = "%$q%";

$stmt = $pdo->prepare($sql);
$stmt->execute([$like,$like,$like]);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($books, JSON_UNESCAPED_UNICODE);
exit;
?>
```

---

### 4.6 `public/book_in.php` – Borrow a book (POST)

```php
<?php
require_once __DIR__.'/config.php';
session_start();

// Must be logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle borrowing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = intval($_POST['book_id']);

    // Check availability
    $check = $pdo->prepare('SELECT available FROM books WHERE id = ?');
    $check->execute([$bookId]);
    $book = $check->fetch(PDO::FETCH_ASSOC);
    if (!$book) {
        die('指定された本は存在しません。');
    }