<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/models/UserModel.php';

class AuthService {
    private static ?AuthService $instance = null;
    private UserModel $users;

    public function __construct(PDO $pdo) {
        $this->users = new UserModel($pdo);
    }

    public static function instance(): AuthService {
        return self::$instance ??= new AuthService(db());
    }

    public function login(string $email, string $password): bool {
        $user = $this->users->findByEmail($email);
        if (!$user || !verify_password($password, $user['password_hash'])) {
            $_SESSION['login_error'] = 'メールアドレスまたはパスワードが正しくありません。';
            return false;
        }
        $this->startSession($user);
        return true;
    }

    public function register(string $username, string $email, string $password): bool {
        if ($this->users->exists($email, $username)) {
            $_SESSION['register_error'] = 'このメールアドレスまたはユーザー名はすでに使用されています。';
            return false;
        }

        try {
            $id = $this->users->create($username, $email, hash_password($password));
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') { // duplicate key (race condition)
                $_SESSION['register_error'] = 'このメールアドレスまたはユーザー名はすでに使用されています。';
                return false;
            }
            throw $e;
        }

        $subject = '【' . APP_NAME . '】会員登録完了のお知らせ';
        $body = "こんにちは {$username} さん、\n\n"
              . "このたびは『" . APP_NAME . "』にご登録いただきありがとうございます。\n"
              . 'ご不明な点がございましたら、' . ADMIN_EMAIL . " までご連絡ください。\n";
        send_mail($email, $subject, $body);

        $user = $this->users->findById($id);
        $this->startSession($user);
        return true;
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public function isLoggedIn(): bool {
        return !empty($_SESSION['user_id']);
    }

    public function getUserId(): ?int {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public function getUsername(): ?string {
        return $_SESSION['username'] ?? null;
    }

    public function getRole(): string {
        return $_SESSION['role'] ?? 'guest';
    }

    /** Redirect to the login page unless logged in */
    public function requireLogin(): void {
        if (!$this->isLoggedIn()) {
            redirect('login.php');
        }
    }

    private function startSession(array $user): void {
        session_regenerate_id(true);
        $_SESSION['user_id']       = (int)$user['id'];
        $_SESSION['username']      = $user['username'];
        $_SESSION['email']         = $user['email'];
        $_SESSION['role']          = $user['role'];
        $_SESSION['csrf']          = bin2hex(random_bytes(32));
        $_SESSION['last_activity'] = time();
    }
}

/* Available on every page as $auth */
$auth = AuthService::instance();
