<?php

namespace App\Core;

/**
 * Authentication and Session Manager
 * Matching Class Diagram 5
 */
class Auth
{
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $lifetime = (int) ($_ENV['SESSION_LIFETIME'] ?? 120) * 60;
            $cookieName = $_ENV['SESSION_COOKIE_NAME'] ?? 'helpdesk_session';

            session_name($cookieName);
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    public static function login(string $email, string $password): bool
    {
        self::init();

        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE email = :email LIMIT 1", ['email' => $email]);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_role'] = $user['role'];

        return true;
    }

    public static function loginUsingId(int $userId): bool
    {
        self::init();

        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = :id LIMIT 1", ['id' => $userId]);

        if (!$user) {
            return false;
        }

        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_role'] = $user['role'];

        return true;
    }

    public static function logout(): void
    {
        self::init();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function check(): bool
    {
        self::init();
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        self::init();
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        self::init();
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function role(): ?string
    {
        self::init();
        return $_SESSION['user_role'] ?? null;
    }

    public static function hasRole(string|array $roles): bool
    {
        $currentRole = self::role();
        if (!$currentRole) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($currentRole, $roles, true);
        }

        return $currentRole === $roles;
    }

    public static function isAdmin(): bool
    {
        return self::hasRole('admin');
    }

    public static function isTechnician(): bool
    {
        return self::hasRole(['technician', 'admin']);
    }

    public static function isUser(): bool
    {
        return self::hasRole('user');
    }
}
