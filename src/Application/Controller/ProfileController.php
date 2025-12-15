<?php

namespace App\Application\Controller;

use App\Application\Service\AuthService;
use App\Application\Service\OrderService;
use App\Application\Service\PasswordValidator;
use App\Domain\UserRepositoryInterface;
use App\Infrastructure\SessionManager;
use App\Infrastructure\ViewRenderer;
use function setToast;
use function translate;
use function verify_csrf;

class ProfileController
{
    public function __construct(
        private AuthService $authService,
        private UserRepositoryInterface $users,
        private OrderService $orders,
        private ViewRenderer $view,
        private SessionManager $session,
        private ?PasswordValidator $passwordValidator = null,
    ) {
        $this->passwordValidator ??= new PasswordValidator();
    }

    public function index(): string
    {
        $this->authService->requireLogin(translate('auth.require.profile'), '/login');
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
            'title' => 'Doctor Gorilka — ' . translate('profile.title'),
            'user' => $user,
            'ordersCount' => $stats['orders'] ?? 0,
            'totalSpent' => $stats['total'] ?? 0,
            'passwordErrors' => $passwordErrors,
            'passwordSuccess' => $passwordSuccess,
        ]);
    }

    public function updatePassword(): void
    {
        $this->authService->requireLogin(translate('auth.require.password'), '/login');
        $userId = $this->session->get('user_id');
        $user = $this->users->findById($userId);
        if (!$user) {
            $this->authService->logout();
            header('Location: /login');
            exit;
        }

        if (!verify_csrf()) {
            setToast(translate('common.csrf_failed'), 'warning');
            header('Location: /profile');
            exit;
        }

        $current = trim($_POST['current_password'] ?? '');
        $new = trim($_POST['new_password'] ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');
        $errors = [];

        if ($current === '') {
            $errors[] = translate('profile.password.error_current_required');
        }
        if ($confirm === '') {
            $errors[] = translate('auth.errors.password_confirm_required');
        }

        $confirmation = $confirm === '' ? null : $confirm;
        $errors = array_merge($errors, $this->passwordValidator->validate($new, $confirmation, $user->passwordHash));

        if (!$errors && !password_verify($current, $user->passwordHash)) {
            $errors[] = translate('profile.password.error_current');
        }

        if ($errors) {
            $this->session->set('profile_password_errors', $errors);
            header('Location: /profile');
            exit;
        }

        $hash = password_hash($new, PASSWORD_DEFAULT);
        $this->users->updatePassword($user->id, $hash);
        $this->session->set('profile_password_success', translate('profile.password.success'));
        header('Location: /profile');
        exit;
    }
}
