<?php
spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\\' => __DIR__ . '/src/',
        'Carbon\\' => __DIR__ . '/src/Carbon/',
    ];

    foreach ($prefixes as $prefix => $dir) {
        if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $dir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    }
});
