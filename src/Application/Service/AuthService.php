<?php

namespace App\Application\Service;

use App\Domain\User;
use App\Domain\UserRepositoryInterface;
use App\Infrastructure\SessionManager;
use function translate;

class AuthService
{
    private const MAX_USERNAME_LENGTH = 20;
    private const MAX_FIRST_NAME_LENGTH = 20;
    private const MAX_LAST_NAME_LENGTH = 20;

    public function __construct(
        private UserRepositoryInterface $users,
        private SessionManager $session,
        private ?PasswordValidator $passwordValidator = null,
    ) {
        $this->passwordValidator ??= new PasswordValidator();
    }

    public function login(string $username, string $password): array
    {
        $user = $this->users->findByUsername($username);
        if (!$user || !password_verify($password, $user->passwordHash)) {
            return ['success' => false, 'error' => translate('auth.errors.invalid_credentials')];
        }

        $this->session->set('user_id', $user->id);
        $this->session->set('role', $user->role);
        $this->session->set('username', $user->username);
        $this->session->set('first_name', $user->firstName);
        $this->session->set('last_name', $user->lastName);
        $this->session->set('phone', $user->phone);

        return ['success' => true, 'display_name' => $user->displayName()];
    }

    public function register(array $data): array
    {
        $errors = [];
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');
        $confirm = trim($data['confirm'] ?? '');
        $first = trim($data['first_name'] ?? '');
        $last = trim($data['last_name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $usernameLength = mb_strlen($username, 'UTF-8');
        $firstLength = mb_strlen($first, 'UTF-8');
        $lastLength = mb_strlen($last, 'UTF-8');

        if ($username === '' || $password === '' || $confirm === '' || $first === '' || $last === '' || $phone === '') {
            $errors[] = translate('auth.errors.fields_required');
        }
        if ($confirm === '') {
            $errors[] = translate('auth.errors.password_confirm_required');
        }
        if ($usernameLength < 3) {
            $errors[] = translate('auth.errors.username_short');
        }
        if ($usernameLength > self::MAX_USERNAME_LENGTH) {
            $errors[] = translate('auth.errors.username_long');
        }
        if (!preg_match('/^[A-Za-z0-9]+$/', $username)) {
            $errors[] = translate('auth.errors.username_latin');
        }
        if ($firstLength > self::MAX_FIRST_NAME_LENGTH) {
            $errors[] = translate('auth.errors.first_name_long');
        }
        if ($lastLength > self::MAX_LAST_NAME_LENGTH) {
            $errors[] = translate('auth.errors.last_name_long');
        }
        if (mb_strlen($phone, 'UTF-8') > 30) {
            $errors[] = translate('auth.errors.phone_long');
        }
        if ($phone !== '' && !preg_match('/^[\d\s+\-()]{6,}$/u', $phone)) {
            $errors[] = translate('auth.errors.phone_invalid');
        }
        if ($this->users->usernameExists($username)) {
            $errors[] = translate('auth.errors.username_taken');
        }

        $confirmation = $confirm === '' ? null : $confirm;
        $errors = array_merge($errors, $this->passwordValidator->validate($password, $confirmation));

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $user = new User(0, $username, $hash, 'user', $first, $last, $phone);
        $created = $this->users->create($user);
        $this->session->set('user_id', $created->id);
        $this->session->set('role', $created->role);
        $this->session->set('username', $created->username);
        $this->session->set('first_name', $created->firstName);
        $this->session->set('last_name', $created->lastName);
        $this->session->set('phone', $created->phone);

        return ['success' => true, 'display_name' => $created->displayName()];
    }

    public function logout(): void
    {
        $this->session->destroy(['theme']);
    }

    public function requireLogin(?string $message = null, string $redirect = '/login'): void
    {
        if (!$this->session->get('user_id')) {
            $this->session->set('toast', ['message' => $message ?? translate('auth.require.default'), 'type' => 'warning']);
            header("Location: $redirect");
            exit;
        }
    }
}
