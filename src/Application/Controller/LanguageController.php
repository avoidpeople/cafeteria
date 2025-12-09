<?php

namespace App\Application\Controller;

class LanguageController
{
    public function switch(): void
    {
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
