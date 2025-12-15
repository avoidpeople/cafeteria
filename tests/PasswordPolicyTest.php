<?php
declare(strict_types=1);

session_save_path(sys_get_temp_dir());

require __DIR__ . '/../autoload.php';
require __DIR__ . '/../src/helpers.php';

use App\Application\Service\PasswordValidator;

function passwordPolicyValidator(): PasswordValidator
{
    ensureSession();
    $_SESSION['locale'] = 'ru';

    return new PasswordValidator();
}

function assertPasswordPolicy(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function expectError(array $errors, string $key): void
{
    $message = translate($key);
    assertPasswordPolicy(in_array($message, $errors, true), "Expected error message for {$key}");
}

function testValidStrongPasswordPasses(): void
{
    $validator = passwordPolicyValidator();
    $password = 'ValidPass1!';

    $errors = $validator->validate($password, $password);
    assertPasswordPolicy($errors === [], 'A strong password should pass validation');
}

function testMissingUppercaseFails(): void
{
    $validator = passwordPolicyValidator();
    $password = 'lowercase1!';

    $errors = $validator->validate($password, $password);
    expectError($errors, 'auth.errors.password_uppercase');
}

function testMissingLowercaseFails(): void
{
    $validator = passwordPolicyValidator();
    $password = 'NOLOWERCASE1!';

    $errors = $validator->validate($password, $password);
    expectError($errors, 'auth.errors.password_lowercase');
}

function testMissingDigitFails(): void
{
    $validator = passwordPolicyValidator();
    $password = 'NoDigits!!A';

    $errors = $validator->validate($password, $password);
    expectError($errors, 'auth.errors.password_digit');
}

function testMissingSymbolFails(): void
{
    $validator = passwordPolicyValidator();
    $password = 'NoSymbol123aA';

    $errors = $validator->validate($password, $password);
    expectError($errors, 'auth.errors.password_symbol');
}

function testCommonPasswordRejected(): void
{
    $validator = passwordPolicyValidator();
    $password = 'Password123!';

    $errors = $validator->validate($password, $password);
    expectError($errors, 'auth.errors.password_common');
}

function testReusedPasswordRejected(): void
{
    $validator = passwordPolicyValidator();
    $currentPassword = 'Reuse1234!';
    $hash = password_hash($currentPassword, PASSWORD_DEFAULT);

    $errors = $validator->validate($currentPassword, $currentPassword, $hash);
    expectError($errors, 'auth.errors.password_reused');
}

try {
    testValidStrongPasswordPasses();
    testMissingUppercaseFails();
    testMissingLowercaseFails();
    testMissingDigitFails();
    testMissingSymbolFails();
    testCommonPasswordRejected();
    testReusedPasswordRejected();

    echo "Password policy tests passed.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Password policy tests failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
