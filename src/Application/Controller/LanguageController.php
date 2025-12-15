<?php

namespace App\Application\Controller;

use function setToast;
use function translate;
use function verify_csrf;

class LanguageController
{
    public function switch(): void
    {
        if (!verify_csrf()) {
            setToast(translate('common.csrf_failed'), 'warning');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }

        $locales = availableLocales();
        $requested = $_POST['lang'] ?? null;
        if ($requested && isset($locales[$requested])) {
            setLocalePreference($requested);
            setToast(translate('nav.language_switched', ['lang' => $locales[$requested]['label']]));
        } else {
            setToast(translate('nav.language_not_available'), 'danger');
        }

        $redirect = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $redirect);
        exit;
    }
}
