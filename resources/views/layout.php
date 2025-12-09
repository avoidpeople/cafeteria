<?php
/** @var string $content */
/** @var string $title */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'Doctor Gorilka') ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script>
        (function() {
            const getCookie = (name) => document.cookie.split('; ').find(row => row.startsWith(name + '='))?.split('=')[1];
            const storedTheme = localStorage.getItem('theme') || getCookie('theme') || 'dark';
            document.documentElement.setAttribute('data-bs-theme', storedTheme);
        })();
    </script>
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    <?= $content ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/notifications.js" defer></script>
    <script src="/assets/js/admin_pending.js" defer></script>
    <script>
    (function() {
        const themeToggle = document.getElementById('themeToggle');
        const applyTheme = (theme) => {
            document.documentElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            document.cookie = `theme=${theme};path=/;max-age=31536000`;
            if (themeToggle) {
                themeToggle.innerText = theme === 'light' ? '🌙' : '☀️';
            }
        };
        applyTheme(localStorage.getItem('theme') || document.cookie.split('; ').find(row => row.startsWith('theme='))?.split('=')[1] || document.documentElement.getAttribute('data-bs-theme') || 'dark');
        themeToggle?.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-bs-theme');
            const next = current === 'light' ? 'dark' : 'light';
            applyTheme(next);
        });
    })();
    </script>
    <?php include __DIR__ . '/partials/toasts.php'; ?>
</body>
</html>
