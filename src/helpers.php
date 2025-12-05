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
