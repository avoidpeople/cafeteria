<?php

namespace App\Application\Service;

use SQLite3;

/**
 * Simple login rate limiter backed by SQLite to throttle brute-force attempts.
 */
class LoginRateLimiter
{
    private SQLite3 $db;
    /** @var callable|null */
    private $clock;
    private array $config;

    public function __construct(SQLite3 $db, ?callable $clock = null, ?array $config = null)
    {
        $this->db = $db;
        $this->clock = $clock;

        $defaults = [
            'max_attempts' => (int) (getenv('LOGIN_RATE_LIMIT_ATTEMPTS') ?: 5),
            'decay_seconds' => (int) (getenv('LOGIN_RATE_LIMIT_WINDOW') ?: 60),
            'lock_seconds' => (int) (getenv('LOGIN_RATE_LIMIT_LOCKOUT') ?: 60),
        ];

        $this->config = array_merge($defaults, is_array($config) ? $config : []);
        $this->config['max_attempts'] = max(1, (int) $this->config['max_attempts']);
        $this->config['decay_seconds'] = max(1, (int) $this->config['decay_seconds']);
        $this->config['lock_seconds'] = max(1, (int) $this->config['lock_seconds']);
    }

    /**
     * Check if the given IP (across any username) is currently blocked.
     *
     * @return array{blocked: bool, retry_after: int}
     */
    public function check(string $username, string $ip): array
    {
        $key = $this->key($ip);
        $now = $this->now();

        $this->purgeExpired($key, $now);

        $stmt = $this->db->prepare('SELECT MAX(blocked_until) AS blocked_until FROM login_attempts WHERE rate_key = :key');
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC) ?: [];
        $blockedUntil = (int) ($row['blocked_until'] ?? 0);

        if ($blockedUntil > $now) {
            return ['blocked' => true, 'retry_after' => $blockedUntil - $now];
        }

        return ['blocked' => false, 'retry_after' => 0];
    }

    /**
     * Record a failed login attempt; returns block state after increment.
     *
     * @return array{blocked: bool, retry_after: int}
     */
    public function hitFailure(string $username, string $ip): array
    {
        $key = $this->key($ip);
        $now = $this->now();
        $this->purgeExpired($key, $now);

        $attemptId = $this->insertAttempt($key, $username, $ip, false, null, $now);

        $recentCount = $this->countRecentAttempts($key, $now);
        if ($recentCount >= $this->config['max_attempts']) {
            $blockedUntil = $now + $this->config['lock_seconds'];
            $this->markBlocked($attemptId, $blockedUntil);

            return ['blocked' => true, 'retry_after' => $blockedUntil - $now];
        }

        return ['blocked' => false, 'retry_after' => 0];
    }

    /**
     * Record successful login and clear throttling history for this key.
     */
    public function hitSuccess(string $username, string $ip): void
    {
        $key = $this->key($ip);
        $this->deleteAttempts($key);
    }

    public function clearAttempts(string $username, string $ip): void
    {
        $key = $this->key($ip);
        $this->deleteAttempts($key);
    }

    private function deleteAttempts(string $key): void
    {
        $stmt = $this->db->prepare('DELETE FROM login_attempts WHERE rate_key = :key');
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $stmt->execute();
    }

    private function insertAttempt(string $key, string $username, string $ip, bool $success, ?int $blockedUntil, int $timestamp): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO login_attempts (rate_key, username, ip, attempted_at, successful, blocked_until)
             VALUES (:key, :username, :ip, :attempted_at, :successful, :blocked_until)'
        );
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
        $stmt->bindValue(':attempted_at', $timestamp, SQLITE3_INTEGER);
        $stmt->bindValue(':successful', $success ? 1 : 0, SQLITE3_INTEGER);
        if ($blockedUntil !== null) {
            $stmt->bindValue(':blocked_until', $blockedUntil, SQLITE3_INTEGER);
        } else {
            $stmt->bindValue(':blocked_until', null, SQLITE3_NULL);
        }
        $stmt->execute();

        return (int) $this->db->lastInsertRowID();
    }

    private function markBlocked(int $attemptId, int $blockedUntil): void
    {
        $stmt = $this->db->prepare('UPDATE login_attempts SET blocked_until = :blocked_until WHERE id = :id');
        $stmt->bindValue(':blocked_until', $blockedUntil, SQLITE3_INTEGER);
        $stmt->bindValue(':id', $attemptId, SQLITE3_INTEGER);
        $stmt->execute();
    }

    private function countRecentAttempts(string $key, int $now): int
    {
        $cutoff = $now - $this->config['decay_seconds'];
        $stmt = $this->db->prepare('SELECT COUNT(*) AS attempts FROM login_attempts WHERE rate_key = :key AND attempted_at >= :cutoff');
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $stmt->bindValue(':cutoff', $cutoff, SQLITE3_INTEGER);
        $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        return (int) ($row['attempts'] ?? 0);
    }

    private function purgeExpired(string $key, int $now): void
    {
        $ttl = max($this->config['decay_seconds'], $this->config['lock_seconds']);
        $cutoff = $now - $ttl;
        $stmt = $this->db->prepare('DELETE FROM login_attempts WHERE rate_key = :key AND attempted_at < :cutoff');
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $stmt->bindValue(':cutoff', $cutoff, SQLITE3_INTEGER);
        $stmt->execute();
    }

    private function key(string $ip): string
    {
        $cleanIp = trim($ip) ?: 'unknown';

        return $cleanIp;
    }

    private function now(): int
    {
        return $this->clock ? (int) call_user_func($this->clock) : time();
    }
}
