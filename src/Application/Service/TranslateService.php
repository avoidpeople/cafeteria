<?php

namespace App\Application\Service;

class TranslateService
{
    private string $providerUrl;

    public function __construct(?string $providerUrl = null)
    {
        $this->providerUrl = $providerUrl ?: 'https://translate.googleapis.com/translate_a/single';
    }

    public function translate(?string $text, string $locale): ?string
    {
        if (!is_string($text)) {
            return null;
        }

        $value = trim($text);
        if ($value === '') {
            return null;
        }

        $translation = $this->requestTranslation($value, $locale);
        if ($translation === null || trim($translation) === '') {
            return $value;
        }

        return $translation;
    }

    private function requestTranslation(string $text, string $locale): ?string
    {
        $query = http_build_query([
            'client' => 'gtx',
            'sl' => 'auto',
            'tl' => $locale,
            'dt' => 't',
            'q' => $text,
        ]);
        $url = $this->providerUrl . '?' . $query;

        $response = $this->fetch($url);
        if ($response === null) {
            return null;
        }

        $payload = json_decode($response, true);
        if (!is_array($payload) || empty($payload[0])) {
            return null;
        }

        $translated = '';
        foreach ($payload[0] as $chunk) {
            if (isset($chunk[0])) {
                $translated .= $chunk[0];
            }
        }

        return trim($translated) !== '' ? $translated : null;
    }

    private function fetch(string $url): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $result = curl_exec($ch);
            curl_close($ch);
            return is_string($result) ? $result : null;
        }

        $context = stream_context_create([
            'http' => ['timeout' => 5],
            'https' => ['timeout' => 5],
        ]);
        $result = @file_get_contents($url, false, $context);
        return is_string($result) ? $result : null;
    }
}
