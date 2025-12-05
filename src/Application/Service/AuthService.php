<?php

namespace App\Application\Service;

use App\Domain\User;
use App\Domain\UserRepositoryInterface;
use App\Infrastructure\SessionManager;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $users,
        private SessionManager $session,
    ) {
    }

    public function login(string $username, string $password): array
    {
        $user = $this->users->findByUsername($username);
        if (!$user || !password_verify($password, $user->passwordHash)) {
            return ['success' => false, 'error' => 'Неверный логин или пароль'];
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

        if ($username === '' || $password === '' || $confirm === '' || $first === '' || $last === '' || $phone === '') {
            $errors[] = 'Заполните все поля.';
        }
        if (strlen($username) < 3) {
            $errors[] = 'Логин должен быть не короче 3 символов.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Пароли не совпадают.';
        }
        if (strlen($password) < 5) {
            $errors[] = 'Пароль должен быть не короче 5 символов.';
        }
        if ($phone !== '' && !preg_match('/^[\d\s+\-()]{6,}$/u', $phone)) {
            $errors[] = 'Введите корректный номер телефона.';
        }
        if ($this->users->usernameExists($username)) {
            $errors[] = 'Такой логин уже занят.';
        }

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

    public function requireLogin(string $message = 'Необходима авторизация', string $redirect = '/login'): void
    {
        if (!$this->session->get('user_id')) {
            $this->session->set('toast', ['message' => $message, 'type' => 'warning']);
            header("Location: $redirect");
            exit;
        }
    }
}
