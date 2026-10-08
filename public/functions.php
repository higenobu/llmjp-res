<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/* PDO connection (created lazily, shared) */
if (!function_exists('db')) {
    function db(): PDO {
        static $pdo = null;
        if ($pdo === null) {
            try {
                $pdo = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                error_log('DB Connection Error: ' . $e->getMessage());
                http_response_code(500);
                exit('サーバーエラーが発生しました。管理者へお問い合わせください。');
            }
        }
        return $pdo;
    }
}

/* HTML escape */
if (!function_exists('html_escape')) {
    function html_escape(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/* Password hashing */
if (!function_exists('hash_password')) {
    function hash_password(string $plain): string {
        return password_hash($plain, PASSWORD_DEFAULT);
    }
}

if (!function_exists('verify_password')) {
    function verify_password(string $plain, string $hash): bool {
        return password_verify($plain, $hash);
    }
}

/* Mail (simple) */
if (!function_exists('send_mail')) {
    function send_mail(string $to, string $subject, string $body): bool {
        $headers  = 'From: ' . ADMIN_EMAIL . "\r\n";
        $headers .= 'Reply-To: ' . ADMIN_EMAIL . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
    }
}

/* CSRF */
if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }
}

if (!function_exists('check_csrf')) {
    function check_csrf(string $token): bool {
        return hash_equals($_SESSION['csrf'] ?? '', $token);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="csrf" value="' . html_escape(csrf_token()) . '">';
    }
}

/* Flash messages */
if (!function_exists('flash_set')) {
    function flash_set(string $type, string $message): void {
        $_SESSION['flash'][$type] = $message;
    }
}

if (!function_exists('flash_get')) {
    function flash_get(): array {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }
}

/* Redirect (path relative to BASE_URL) */
if (!function_exists('redirect')) {
    function redirect(string $path): never {
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}

/* Render views/$view.php inside views/template.php */
if (!function_exists('render')) {
    function render(string $view, array $data = []): void {
        global $auth;
        $title = $data['title'] ?? APP_NAME;
        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/views/' . $view . '.php';
        $content = ob_get_clean();
        require __DIR__ . '/views/template.php';
    }
}
