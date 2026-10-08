# Result 1

## Prompt

以下の構成からプログラムを生成してください。：Prompt
本の貸し出しと返却のWEBアプリをPHPで作成、ユーザの登録、本の入庫、本の検索、貸出、返却、ユーザ単位で本の貸出履歴表示
Program
1. データベース設計（MySQL）

テーブル名
カラム
型・制約
users
id
INT PK AUTO_INCREMENT


name
VARCHAR(100) NOT NULL


email
VARCHAR(255) UNIQUE NOT NULL


password_hash
CHAR(60) NOT NULL


created_at
DATETIME DEFAULT CURRENT_TIMESTAMP
books
id
INT PK AUTO_INCREMENT


title
VARCHAR(255) NOT NULL


author
VARCHAR(255) NOT NULL


isbn
VARCHAR(20) UNIQUE NOT NULL


stock
INT UNSIGNED NOT NULL DEFAULT 0


total
INT UNSIGNED NOT NULL DEFAULT 0
loans
id
INT PK AUTO_INCREMENT


user_id
INT NOT NULL (FK → users.id)


book_id
INT NOT NULL (FK → books.id)


loan_date
DATE NOT NULL


due_date
DATE NOT NULL


return_date
DATE NULL


status
ENUM('borrowed','returned') NOT NULL DEFAULT 'borrowed'


created_at
DATETIME DEFAULT CURRENT_TIMESTAMP


インデックス

users.email, books.isbn, loans.user_id, loans.book_id にそれぞれインデックスを付与。



2. フォルダー構成例

/app

│   config.php          // DB接続・基本設定

│   functions.php       // 共通関数（ハッシュ化、エラーハンドリング等）

│

/models

│   User.php

│   Book.php

│   Loan.php

│

/controllers

│   RegisterController.php

│   BookInController.php

│   SearchController.php

│   LoanController.php

│   ReturnController.php

│   HistoryController.php

│

/views

│   register.php

│   book_in.php

│   search.php

│   loan.php

│   return.php

│   history.php

│

/public

│   index.php           // ルーティング入口

│   css/

│   js/

│

/.htaccess



3. 基本的な config.php（PDO使用）

<?php

$pdo = new PDO(

    'mysql:host=localhost;dbname=library;charset=utf8mb4',

    'db_user',

    'db_password',

    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]

);

?>



4. コントローラ実装例
4‑1. ユーザー登録 (RegisterController.php)
<?php

require '../models/User.php';

require '../functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name   = trim($_POST['name']);

    $email  = strtolower(trim($_POST['email']));

    $pass   = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL))

## Program

**1. データベース設計（SQL）**

```sql
-- データベース作成
CREATE DATABASE IF NOT EXISTS library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library;

-- ユーザテーブル
CREATE TABLE users (
    id               INT PRIMARY KEY AUTO_INCREMENT,
    name             VARCHAR(100) NOT NULL,
    email            VARCHAR(255) NOT NULL UNIQUE,
    password_hash    CHAR(60) NOT NULL,
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 書籍テーブル
CREATE TABLE books (
    id        INT PRIMARY KEY AUTO_INCREMENT,
    title     VARCHAR(255) NOT NULL,
    author    VARCHAR(255) NOT NULL,
    isbn      VARCHAR(20) NOT NULL UNIQUE,
    stock     INT UNSIGNED NOT NULL DEFAULT 0,
    total     INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 貸出テーブル
CREATE TABLE loans (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    user_id       INT NOT NULL,
    book_id       INT NOT NULL,
    loan_date     DATE NOT NULL,
    due_date      DATE NOT NULL,
    return_date   DATE NULL,
    status        ENUM('borrowed','returned') NOT NULL DEFAULT 'borrowed',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE RESTRICT,
    INDEX idx_user_id (user_id),
    INDEX idx_book_id (book_id)
) ENGINE=InnoDB;

-- インデックス（上記FKで自動作成済みだが明示的に追加）
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_books_isbn ON books(isbn);
```

---

**2. フォルダー構成例**

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
```

---

**3. `app/config.