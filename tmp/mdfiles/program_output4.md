# Result 1

## Prompt

本の貸し出しと返却のWEBアプリをPHPで作成、ユーザの登録、本の入庫、本の検索、貸出、返却、ユーザ単位で本の貸出履歴表示
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

## Program

以下では、  
**「本の貸し出しと返却」** を行うシンプルな Web アプリを **PHP（Laravel は使わない）** で実装する手順と、主要ファイルのサンプルコード・設計ポイントをまとめました。  

---

## 1️⃣ 全体像と技術スタック

| 項目 | 内容 |
|------|------|
| 言語 | PHP 8.2+ （型宣言・union type が利用可能） |
| フレームワーク | なし（純粋な PHP + PSR‑4 オートローディング） |
| DB | MySQL / MariaDB（PDO で接続） |
| セッション管理 | PHP 標準 `session_start()` + `hash_hmac` による CSRF トークン |
| パスワードハッシュ | `password_hash()/password_verify()`（bcrypt） |
| エラーハンドリング | `try/catch` と共通 `functions.php` の `die_error()` |
| ルーティング | `public/index.php` で簡易 MVC パターン |
| ディレクトリ構成 | 質問文にある通り（PSR‑4 に合わせて `src/` 配下に置くことも可） |

> **※ 本番環境では `php-fpm` + Nginx + HTTPS を想定し、`.htaccess` でディレクトリ保護や URL 書き換えを行います。**

---

## 2️⃣ データベース設計

### 2.1 テーブル定義（MySQL）

```sql
-- users : ユーザ情報
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARBINARY(60) NOT NULL,      -- bcrypt hash
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- books : 蔵書情報
CREATE TABLE books (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(150),
    isbn CHAR(13) UNIQUE,
    status ENUM('available','loaned') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- loans : 貸出履歴
CREATE TABLE loans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    book_id BIGINT UNSIGNED NOT NULL,
    loaned_at DATETIME NOT NULL,
    returned_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    UNIQUE KEY uq_active_loan (user_id, book_id, returned_at)
);
```

* **`loans.status` は `books.status` と同期させますが、`loans` 行が残っていれば「貸し出し中」と判定できます。**  
* 必要なら `books.category`, `books.price` 等のカラムを追加してください。

---

## 3️⃣ ディレクトリ構成と自動ロード

```
project/
├─ public/
│  ├─ index.php            ← ルーティング入口
│  ├─ css/
│  ├─ js/
│  └─ .htaccess
├─ src/
│  ├─ config/
│  │   └─ db.php
│  ├─ functions.php
│  ├─ models/
│  │   ├─ User.php
│  │   ├─ Book.php
│  │   └─ Loan.php
│  ├─ controllers/
│  │   ├─ RegisterController.php
│  │   ├─ BookInController.php
│  │   ├─ SearchController.php
│  │   ├─ LoanController.php
│  │   ├─ ReturnController.php
│  │   └─ HistoryController.php
│  └─ views/
│      ├─ register.php
│      ├─ book_in.php
│      ├─ search.php
│      ├─ loan.php
│      ├─ return.php
│      └─ history.php
└─ composer.json   ← autoload-psr4 用（任意）
```

### 3.1 `composer.json`（自動ロード例）

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

インストール後:

```bash
composer dump-autoload
```

### 3.2 `public/index.php`（簡易ルーティング）

```php
<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/config/db.php';
require __DIR__ . '/../src/functions.php';

// セッション開始
session_start();

// CSRF トークンが無ければ生成
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// ヘルパー関数
function csrf_token(): string { return $_SESSION['csrf']; }

// ---------- ルーティング ----------
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');               // "/register" → "register"

// デフォルトはホーム（ログイン済みかどうかで分岐）
$action = 'home';
$params  = [];

switch ($uri) {
    case 'register':
        $action = 'RegisterController::index';
        break;
    case 'book_in':
        $action = 'BookInController::index';
        break;
    case 'search':
        $action = 'SearchController::index';
        break;
    case 'loan':
        $action = 'LoanController::index';
        break;
    case 'return':
        $action = 'ReturnController::index';
        break;
    case 'history':
        $action = 'HistoryController::index';
        break;
    default:
        // ログインチェック
        if (!isset($_SESSION['user_id'])) {
            $action = 'RegisterController::loginRedirect';
        } else {
            $action = 'home';
        }
        break;
}

// 実行
call_user_func(explode('::', $action), $params);
```

> **ポイント**  
> * `call_user_func` の第2引数は配列で渡すので、必要に応じて `$params` をコントローラ側で受け取ります。  
> * 認証が必要なページは `home` アクションでリダイレクトさせます。（後述の認証ヘルパー参照）

---

## 4️⃣ 共通関数 (`src/functions.php`)

```php
<?php
declare(strict_types=1);

/**
 * DB 接続オブジェクト取得
 */
function getPDO(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . (getenv('DB_HOST') ?: '127.0.0.1')
                . ';dbname=' . (getenv('DB_NAME') ?: 'library')
                . ';charset=utf8mb4';
        $pdo = new PDO($dsn,
                       (getenv('DB_USER') ?: 'root'),
                       (get