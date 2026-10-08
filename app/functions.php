<?php
declare(strict_types=1);

function h(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function hash_password(string $raw): string
{
    return password_hash($raw, PASSWORD_BCRYPT);
}

function verify_password(string $raw, string $hash): bool
{
    return password_verify($raw, $hash);
}

function is_valid_email(string $email): bool
{
    return strlen($email) <= 255 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ---- CSRF ----
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Invalid CSRF token.');
    }
}

// ---- flash messages ----
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function pull_flash(): array
{
    $msgs = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $msgs;
}

// ---- auth ----
function getCurrentUser(): ?User
{
    static $cache = [];
    $id = (int)($_SESSION['user_id'] ?? 0);
    if ($id <= 0) {
        return null;
    }
    return $cache[$id] ??= User::findById($id, db());
}

function require_login(): User
{
    $user = getCurrentUser();
    if (!$user) {
        redirect('login');
    }
    return $user;
}

function login_user(User $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user->getId();
}

function render(string $view, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    require APP_ROOT . '/views/' . $view . '.php';
}
