<?php

function ensureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function setToast(string $message, string $type = 'success'): void
{
    ensureSession();

    $_SESSION['toast'] = [
        'message' => $message,
        'type' => $type,
    ];
}

function localizationConfig(): array
{
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/../config/localization.php';
    }
    return $config;
}

function availableLocales(): array
{
    $config = localizationConfig();
    return $config['locales'] ?? [];
}

function currentLocale(): string
{
    ensureSession();
    $available = array_keys(availableLocales());
    $default = $available[0] ?? 'ru';
    if (!empty($_SESSION['locale']) && in_array($_SESSION['locale'], $available, true)) {
        return $_SESSION['locale'];
    }
    if (!empty($_COOKIE['locale']) && in_array($_COOKIE['locale'], $available, true)) {
        $_SESSION['locale'] = $_COOKIE['locale'];
        return $_COOKIE['locale'];
    }
    return $default;
}

function setLocalePreference(string $locale): void
{
    ensureSession();
    $_SESSION['locale'] = $locale;
    setcookie('locale', $locale, [
        'expires' => time() + 31536000,
        'path' => '/',
        'httponly' => false,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'samesite' => 'Lax',
    ]);
}

function translate(string $key, array $replace = [], ?string $locale = null): string
{
    $locale ??= currentLocale();
    $config = localizationConfig();
    $phrases = $config['phrases'] ?? [];
    $fallbackLocale = $config['fallback'] ?? 'ru';

    $value = $phrases[$locale][$key] ?? $phrases[$fallbackLocale][$key] ?? $key;
    foreach ($replace as $needle => $replacement) {
        $value = str_replace(':' . $needle, $replacement, $value);
    }

    return $value;
}

function translateStatus(?string $status): string
{
    if ($status === null) {
        return '';
    }
    return translate('status.' . $status);
}
