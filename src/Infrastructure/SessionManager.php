<?php

namespace App\Infrastructure;

class SessionManager
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            \configureSessionCookie();
            session_start();
        }
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function unset(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function flash(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
        $_SESSION['_flash_keys'][] = $key;
    }

    public function destroy(array $preserveKeys = []): void
    {
        $preserve = [];
        foreach ($preserveKeys as $key) {
            if (isset($_SESSION[$key])) {
                $preserve[$key] = $_SESSION[$key];
            }
        }
        $_SESSION = $preserve;
        session_regenerate_id(true);
    }
}
