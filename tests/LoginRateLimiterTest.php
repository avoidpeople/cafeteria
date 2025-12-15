<?php
declare(strict_types=1);

session_save_path(sys_get_temp_dir());

require __DIR__ . '/../autoload.php';
require __DIR__ . '/../src/helpers.php';

use App\Application\Service\AuthService;
use App\Application\Service\LoginRateLimiter;
use App\Infrastructure\Repository\UserRepository;
use App\Infrastructure\SessionManager;

function createLoginTestStack(): array
{
    $db = new SQLite3(':memory:');
    $db->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        first_name TEXT,
        last_name TEXT,
        phone TEXT,
        role TEXT DEFAULT 'user'
    )");
    $db->exec("CREATE TABLE login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        rate_key TEXT NOT NULL,
        username TEXT,
        ip TEXT,
        attempted_at INTEGER NOT NULL,
        successful INTEGER DEFAULT 0,
        blocked_until INTEGER
    )");
    $db->exec("CREATE INDEX idx_login_attempts_key_time ON login_attempts(rate_key, attempted_at)");

    $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
    $stmt->bindValue(':username', 'demo', SQLITE3_TEXT);
    $stmt->bindValue(':password', password_hash('secret123', PASSWORD_DEFAULT), SQLITE3_TEXT);
    $stmt->bindValue(':role', 'user', SQLITE3_TEXT);
    $stmt->execute();

    $session = new SessionManager();
    $userRepository = new UserRepository($db);
    $clockTime = 1_700_000_000;
    $clock = function () use (&$clockTime): int {
        return $clockTime;
    };

    $limiter = new LoginRateLimiter($db, $clock, [
        'max_attempts' => 5,
        'decay_seconds' => 60,
        'lock_seconds' => 60,
    ]);

    $authService = new AuthService($userRepository, $session);

    $advanceClock = function (int $seconds) use (&$clockTime): void {
        $clockTime += $seconds;
    };

    return [$authService, $limiter, $session, $advanceClock];
}

function assertLoginTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function testSuccessfulLoginAllowed(): void
{
    [$authService, $limiter] = createLoginTestStack();
    $ip = '127.0.0.1';
    $state = $limiter->check('demo', $ip);
    assertLoginTrue($state['blocked'] === false, 'Fresh login must not be blocked');

    $result = $authService->login('demo', 'secret123');
    $limiter->hitSuccess('demo', $ip);

    assertLoginTrue($result['success'] === true, 'Valid credentials should succeed');
    $state = $limiter->check('demo', $ip);
    assertLoginTrue($state['blocked'] === false, 'Successful login must reset block state');
}

function testExceedingRateLimitBlocks(): void
{
    [$authService, $limiter] = createLoginTestStack();
    $ip = '10.0.0.1';
    $blocked = false;

    for ($i = 0; $i < 5; $i++) {
        $state = $limiter->check('demo', $ip);
        assertLoginTrue($state['blocked'] === false, 'Attempt ' . ($i + 1) . ' should not be blocked before limit');

        $result = $authService->login('demo', 'wrong-pass');
        assertLoginTrue($result['success'] === false, 'Invalid password must fail');

        $state = $limiter->hitFailure('demo', $ip);
        $blocked = $state['blocked'];
    }

    assertLoginTrue($blocked === true, 'Fifth failed attempt should trigger block');
    $state = $limiter->check('demo', $ip);
    assertLoginTrue($state['blocked'] === true, 'Blocked state must persist after hitting the limit');
}

function testLockExpiresAfterCooldown(): void
{
    [$authService, $limiter, , $advanceClock] = createLoginTestStack();
    $ip = '172.16.0.5';

    for ($i = 0; $i < 5; $i++) {
        $authService->login('demo', 'wrong');
        $limiter->hitFailure('demo', $ip);
    }

    $state = $limiter->check('demo', $ip);
    assertLoginTrue($state['blocked'] === true, 'Lock should be active after repeated failures');

    $advanceClock(61);
    $state = $limiter->check('demo', $ip);
    assertLoginTrue($state['blocked'] === false, 'Lock must expire after cooldown window');

    $result = $authService->login('demo', 'secret123');
    $limiter->hitSuccess('demo', $ip);
    assertLoginTrue($result['success'] === true, 'Login should succeed once block expires');
}

try {
    testSuccessfulLoginAllowed();
    testExceedingRateLimitBlocks();
    testLockExpiresAfterCooldown();
    echo "Login rate limiter tests passed.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Login rate limiter tests failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
