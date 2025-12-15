<?php

namespace App\Application\Controller;

use App\Application\Service\AuthService;
use App\Application\Service\CartService;
use App\Application\Service\LoginRateLimiter;
use App\Infrastructure\SessionManager;
use App\Infrastructure\ViewRenderer;
use function setToast;
use function translate;
use function verify_csrf;

class AuthController
{
    public function __construct(
        private AuthService $authService,
        private ViewRenderer $view,
        private SessionManager $session,
        private LoginRateLimiter $loginRateLimiter,
        private ?CartService $cartService = null
    ) {
    }

    public function showLogin(): string
    {
        if ($this->session->get('user_id')) {
            header('Location: /');
            exit;
        }
        $error = $this->session->get('login_error');
        $usernameValue = $this->session->get('login_username', '');
        $this->session->unset('login_error');
        $this->session->unset('login_username');
        $next = $this->rememberNext($_GET['next'] ?? $this->session->get('login_next', ''));

        return $this->view->render('auth/login', compact('error', 'usernameValue', 'next'));
    }

    public function login(): void
    {
        if ($this->session->get('user_id')) {
            header('Location: /');
            exit;
        }

        if (!verify_csrf()) {
            setToast(translate('common.csrf_failed'), 'warning');
            header('Location: /login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $next = $this->rememberNext($_POST['next'] ?? $this->session->get('login_next', ''));
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $block = $this->loginRateLimiter->check($username, $ip);
        if ($block['blocked']) {
            $this->session->set('login_error', translate('auth.errors.too_many_attempts', ['seconds' => (string) max(1, $block['retry_after'])]));
            $this->session->set('login_username', $username);
            $redirect = '/login' . ($next ? '?next=' . urlencode($next) : '');
            $this->logSecurity(sprintf('Rate limit hit for user "%s" from %s (retry in %ds)', $this->maskUsername($username), $ip, (int) $block['retry_after']));
            header('Location: ' . $redirect);
            exit;
        }

        $result = $this->authService->login($username, $password);
        if ($result['success']) {
            $this->loginRateLimiter->hitSuccess($username, $ip);
            $this->cartService?->mergeUserCart((int)$this->session->get('user_id', 0));
            $this->session->unset('login_next');
            setToast(translate('auth.toast.login', ['name' => $result['display_name']]));
            header('Location: ' . ($next ?: '/'));
            exit;
        }

        $limitState = $this->loginRateLimiter->hitFailure($username, $ip);
        $errorMessage = $result['error'] ?? translate('auth.errors.invalid_credentials');
        if ($limitState['blocked']) {
            $errorMessage = translate('auth.errors.too_many_attempts', ['seconds' => (string) max(1, $limitState['retry_after'])]);
        }

        $this->session->set('login_error', $errorMessage);
        $this->session->set('login_username', $username);
        $redirect = '/login' . ($next ? '?next=' . urlencode($next) : '');
        $this->logSecurity(sprintf(
            'Failed login for user "%s" from %s%s',
            $this->maskUsername($username),
            $ip,
            $limitState['blocked'] ? ' (rate limited)' : ''
        ));
        header('Location: ' . $redirect);
        exit;
    }

    public function showRegister(): string
    {
        if ($this->session->get('user_id')) {
            header('Location: /');
            exit;
        }

        $errors = $this->session->get('register_errors', []);
        $inputs = $this->session->get('register_inputs', ['first_name' => '', 'last_name' => '', 'username' => '', 'phone' => '']);
        $this->session->unset('register_errors');
        $this->session->unset('register_inputs');
        $next = $this->rememberNext($_GET['next'] ?? $this->session->get('login_next', ''));

        return $this->view->render('auth/register', compact('errors', 'inputs', 'next'));
    }

    public function register(): void
    {
        if ($this->session->get('user_id')) {
            header('Location: /');
            exit;
        }

        if (!verify_csrf()) {
            setToast(translate('common.csrf_failed'), 'warning');
            header('Location: /register');
            exit;
        }

        $next = $this->rememberNext($_POST['next'] ?? $this->session->get('login_next', ''));
        $result = $this->authService->register($_POST);
        if ($result['success']) {
            $this->cartService?->mergeUserCart((int)$this->session->get('user_id', 0));
            $this->session->unset('login_next');
            setToast(translate('auth.toast.register', ['name' => $result['display_name']]));
            header('Location: ' . ($next ?: '/'));
            exit;
        }

        $this->session->set('register_errors', $result['errors'] ?? [translate('auth.errors.register_failed')]);
        $this->session->set('register_inputs', [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
        ]);
        $redirect = '/register' . ($next ? '?next=' . urlencode($next) : '');
        header('Location: ' . $redirect);
        exit;
    }

    public function logout(): void
    {
        $this->authService->logout();
        setToast(translate('auth.toast.logout'), 'info');
        header('Location: /login');
        exit;
    }

    private function rememberNext(?string $next): string
    {
        $normalized = $this->normalizeNext($next);
        if ($normalized !== '') {
            $this->session->set('login_next', $normalized);
        } else {
            $this->session->unset('login_next');
        }
        return $normalized;
    }

    private function normalizeNext(?string $next): string
    {
        $next = trim((string)$next);
        if ($next === '' || !str_starts_with($next, '/')) {
            return '';
        }
        $host = parse_url($next, PHP_URL_HOST);
        if ($host) {
            return '';
        }
        return $next;
    }

    private function logSecurity(string $message): void
    {
        error_log('[security] ' . $message);
    }

    private function maskUsername(string $username): string
    {
        $trimmed = trim($username);
        if ($trimmed === '') {
            return '(empty)';
        }
        if (strlen($trimmed) <= 2) {
            return $trimmed[0] . '*';
        }

        return substr($trimmed, 0, 1) . str_repeat('*', max(strlen($trimmed) - 2, 1)) . substr($trimmed, -1);
    }
}
