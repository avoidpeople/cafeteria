<?php

namespace App\Domain;

class User
{
    public function __construct(
        public int $id,
        public string $username,
        public string $passwordHash,
        public string $role,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $phone = null,
    ) {}

    public function displayName(): string
    {
        $name = trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
        return $name !== '' ? $name : $this->username;
    }
}
