<?php

namespace App\Application\Controller;

use App\Application\Service\AuthService;
use App\Application\Service\OrderService;
use App\Domain\UserRepositoryInterface;
use App\Infrastructure\SessionManager;
use App\Infrastructure\ViewRenderer;

class ProfileController
{
    public function __construct(
        private AuthService $authService,
        private UserRepositoryInterface $users,
        private OrderService $orders,
        private ViewRenderer $view,
        private SessionManager $session
    ) {
    }

    public function index(): string
    {
        $this->authService->requireLogin('Войдите или зарегистрируйтесь, чтобы просматривать профиль', '/login');
        $userId = $this->session->get('user_id');
        $user = $this->users->findById($userId);
        if (!$user) {
            $this->authService->logout();
            header('Location: /login');
            exit;
        }

        $stats = $this->orders->userStats($userId);
        $passwordErrors = $this->session->get('profile_password_errors', []);
        $passwordSuccess = $this->session->get('profile_password_success');
        $this->session->unset('profile_password_errors');
        $this->session->unset('profile_password_success');

        return $this->view->render('profile/index', [
            'title' => 'Doctor Gorilka — Профиль пользователя',
            'user' => $user,
            'ordersCount' => $stats['orders'] ?? 0,
            'totalSpent' => $stats['total'] ?? 0,
            'passwordErrors' => $passwordErrors,
            'passwordSuccess' => $passwordSuccess,
        ]);
    }

    public function updatePassword(): void
    {
        $this->authService->requireLogin('Войдите или зарегистрируйтесь, чтобы изменить пароль', '/login');
        $userId = $this->session->get('user_id');
        $user = $this->users->findById($userId);
        if (!$user) {
            $this->authService->logout();
            header('Location: /login');
            exit;
        }

        $current = trim($_POST['current_password'] ?? '');
        $new = trim($_POST['new_password'] ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');
        $errors = [];

        if ($current === '' || $new === '' || $confirm === '') {
            $errors[] = 'Заполните все поля формы смены пароля.';
        }
        if (strlen($new) < 5) {
            $errors[] = 'Новый пароль должен быть не короче 5 символов.';
        }
        if ($new !== $confirm) {
            $errors[] = 'Новый пароль и подтверждение не совпадают.';
        }
        if (!$errors && !password_verify($current, $user->passwordHash)) {
            $errors[] = 'Текущий пароль введён неверно.';
        }

        if ($errors) {
            $this->session->set('profile_password_errors', $errors);
            header('Location: /profile');
            exit;
        }

        $hash = password_hash($new, PASSWORD_DEFAULT);
        $this->users->updatePassword($user->id, $hash);
        $this->session->set('profile_password_success', 'Пароль успешно обновлён.');
        header('Location: /profile');
        exit;
    }
}
