<?php

namespace App\Infrastructure\Repository;

use App\Domain\User;
use App\Domain\UserRepositoryInterface;
use SQLite3;
use SQLite3Stmt;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private SQLite3 $db)
    {
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        return $result ? $this->mapUser($result) : null;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        return $result ? $this->mapUser($result) : null;
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->prepare("SELECT 1 FROM users WHERE username = :username");
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        return (bool) $result;
    }

    public function create(User $user): User
    {
        $stmt = $this->prepare("INSERT INTO users (username, password, first_name, last_name, phone, role)
            VALUES (:username, :password, :first, :last, :phone, :role)");
        $stmt->bindValue(':username', $user->username, SQLITE3_TEXT);
        $stmt->bindValue(':password', $user->passwordHash, SQLITE3_TEXT);
        $stmt->bindValue(':first', $user->firstName, SQLITE3_TEXT);
        $stmt->bindValue(':last', $user->lastName, SQLITE3_TEXT);
        $stmt->bindValue(':phone', $user->phone, SQLITE3_TEXT);
        $stmt->bindValue(':role', $user->role, SQLITE3_TEXT);
        $stmt->execute();

        return new User(
            id: (int) $this->db->lastInsertRowID(),
            username: $user->username,
            passwordHash: $user->passwordHash,
            role: $user->role,
            firstName: $user->firstName,
            lastName: $user->lastName,
            phone: $user->phone,
        );
    }

    public function updatePassword(int $id, string $hash): void
    {
        $stmt = $this->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->bindValue(':password', $hash, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }

    private function mapUser(array $row): User
    {
        return new User(
            id: (int) $row['id'],
            username: $row['username'],
            passwordHash: $row['password'],
            role: $row['role'],
            firstName: $row['first_name'] ?? null,
            lastName: $row['last_name'] ?? null,
            phone: $row['phone'] ?? null,
        );
    }

    private function prepare(string $sql): SQLite3Stmt
    {
        return $this->db->prepare($sql);
    }
}
