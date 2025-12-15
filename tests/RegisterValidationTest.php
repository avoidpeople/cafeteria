<?php
declare(strict_types=1);

session_save_path(sys_get_temp_dir());

require __DIR__ . '/../autoload.php';
require __DIR__ . '/../src/helpers.php';

use App\Application\Service\AuthService;
use App\Infrastructure\Repository\UserRepository;
use App\Infrastructure\SessionManager;

function createRegisterStack(): array
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

    $session = new SessionManager();
    $userRepository = new UserRepository($db);
    $authService = new AuthService($userRepository, $session);

    ensureSession();
    $_SESSION['locale'] = 'ru';

    return [$authService, $userRepository];
}

function assertRegister(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function testValidRegistrationPasses(): void
{
    [$authService] = createRegisterStack();

    $result = $authService->register([
        'username' => 'ValidUser123',
        'password' => 'ValidPass1!',
        'confirm' => 'ValidPass1!',
        'first_name' => 'Ivan',
        'last_name' => 'Petrov',
        'phone' => '+37120000000',
    ]);

    assertRegister($result['success'] === true, 'Valid registration should succeed');
}

function testFirstNameTooLongFails(): void
{
    [$authService] = createRegisterStack();
    $longName = str_repeat('a', 51);

    $result = $authService->register([
        'username' => 'User123',
        'password' => 'ValidPass1!',
        'confirm' => 'ValidPass1!',
        'first_name' => $longName,
        'last_name' => 'Ivanov',
        'phone' => '+37120000000',
    ]);

    assertRegister($result['success'] === false, 'Registration with long first name must fail');
    assertRegister(in_array(translate('auth.errors.first_name_long'), $result['errors'] ?? [], true), 'Must include first name length error');
}

function testUsernameTooLongFails(): void
{
    [$authService] = createRegisterStack();
    $longUsername = str_repeat('a', 61);

    $result = $authService->register([
        'username' => $longUsername,
        'password' => 'ValidPass1!',
        'confirm' => 'ValidPass1!',
        'first_name' => 'Ivan',
        'last_name' => 'Petrov',
        'phone' => '+37120000000',
    ]);

    assertRegister($result['success'] === false, 'Registration with long username must fail');
    assertRegister(in_array(translate('auth.errors.username_long'), $result['errors'] ?? [], true), 'Must include username length error');
}

function testUsernameNonLatinFails(): void
{
    [$authService] = createRegisterStack();

    $result = $authService->register([
        'username' => 'тестUser',
        'password' => 'ValidPass1!',
        'confirm' => 'ValidPass1!',
        'first_name' => 'Ivan',
        'last_name' => 'Petrov',
        'phone' => '+37120000000',
    ]);

    assertRegister($result['success'] === false, 'Registration with non-latin username must fail');
    assertRegister(in_array(translate('auth.errors.username_latin'), $result['errors'] ?? [], true), 'Must include latin-only error');
}

try {
    testValidRegistrationPasses();
    testFirstNameTooLongFails();
    testUsernameTooLongFails();
    testUsernameNonLatinFails();

    echo "Register validation tests passed.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Register validation tests failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
