CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(20)  NOT NULL UNIQUE,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','member') NOT NULL DEFAULT 'member',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE books (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title          VARCHAR(255) NOT NULL,
    author         VARCHAR(255) NOT NULL,
    published_year SMALLINT NULL,
    isbn           VARCHAR(20) NULL,
    status         ENUM('available','loaned') NOT NULL DEFAULT 'available',
    INDEX (title), INDEX (author)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE loans (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    book_id     INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    loaned_at   DATETIME NOT NULL,
    returned_at DATETIME NULL,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX (user_id, returned_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
