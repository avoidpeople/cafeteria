<?php

namespace App\Domain;

interface UserRepositoryInterface
{
    public function findByUsername(string $username): ?User;
    public function findById(int $id): ?User;
    public function usernameExists(string $username): bool;
    public function create(User $user): User;
    public function updatePassword(int $id, string $hash): void;
}
