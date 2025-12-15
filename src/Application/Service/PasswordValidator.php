<?php

namespace App\Application\Service;

use function translate;

class PasswordValidator
{
    /**
     * List of weak substrings that must not appear in the password.
     */
    private const WEAK_PASSWORDS = [
        'password',
        '123456',
        '12345678',
        'qwerty',
        'admin',
        'letmein',
    ];

    /**
     * Validate a password against the policy.
     *
     * @param string      $password     Password to validate.
     * @param string|null $confirmation Optional confirmation for equality check.
     * @param string|null $currentHash  Optional current hash to prevent reuse.
     *
     * @return string[] List of translated error messages.
     */
    public function validate(string $password, ?string $confirmation = null, ?string $currentHash = null): array
    {
        $errors = [];
        $length = mb_strlen($password, 'UTF-8');

        if ($password === '') {
            $errors[] = translate('auth.errors.password_required');
            return $errors;
        }

        if ($confirmation !== null && $password !== $confirmation) {
            $errors[] = translate('auth.errors.password_mismatch');
        }

        if ($length < 10) {
            $errors[] = translate('auth.errors.password_min');
        }
        if (!preg_match('/\\p{Lu}/u', $password)) {
            $errors[] = translate('auth.errors.password_uppercase');
        }
        if (!preg_match('/\\p{Ll}/u', $password)) {
            $errors[] = translate('auth.errors.password_lowercase');
        }
        if (!preg_match('/\\p{N}/u', $password)) {
            $errors[] = translate('auth.errors.password_digit');
        }
        if (!preg_match('/[^\\p{L}\\p{N}\\s]/u', $password)) {
            $errors[] = translate('auth.errors.password_symbol');
        }
        if (preg_match('/\\s/u', $password)) {
            $errors[] = translate('auth.errors.password_spaces');
        }

        $lowerPassword = mb_strtolower($password, 'UTF-8');
        foreach (self::WEAK_PASSWORDS as $weak) {
            if (mb_strpos($lowerPassword, $weak, 0, 'UTF-8') !== false) {
                $errors[] = translate('auth.errors.password_common');
                break;
            }
        }

        if ($currentHash && password_verify($password, $currentHash)) {
            $errors[] = translate('auth.errors.password_reused');
        }

        return $errors;
    }
}
