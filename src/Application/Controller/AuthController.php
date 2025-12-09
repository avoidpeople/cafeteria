<?php

namespace App\Application\Controller;

use App\Application\Service\AuthService;
use App\Infrastructure\SessionManager;
use App\Infrastructure\ViewRenderer;
use function setToast;
use function translate;

class AuthController
{
    public function __construct(
        private AuthService $authService,
        private ViewRenderer $view,
        private SessionManager $session
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

        return $this->view->render('auth/login', compact('error', 'usernameValue'));
    }

    public function login(): void
    {
        if ($this->session->get('user_id')) {
            header('Location: /');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $result = $this->authService->login($username, $password);
        if ($result['success']) {
            setToast(translate('auth.toast.login', ['name' => $result['display_name']]));
            header('Location: /');
            exit;
        }

        $this->session->set('login_error', $result['error'] ?? translate('auth.errors.invalid_credentials'));
        $this->session->set('login_username', $username);
        header('Location: /login');
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

        return $this->view->render('auth/register', compact('errors', 'inputs'));
    }

    public function register(): void
    {
        if ($this->session->get('user_id')) {
            header('Location: /');
            exit;
        }

        $result = $this->authService->register($_POST);
        if ($result['success']) {
            setToast(translate('auth.toast.register', ['name' => $result['display_name']]));
            header('Location: /');
            exit;
        }

        $this->session->set('register_errors', $result['errors'] ?? [translate('auth.errors.register_failed')]);
        $this->session->set('register_inputs', [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
        ]);
        header('Location: /register');
        exit;
    }

    public function logout(): void
    {
        $this->authService->logout();
        setToast(translate('auth.toast.logout'), 'info');
        header('Location: /login');
        exit;
    }
}
