<?php

return [
    'login_max_attempts' => (int) (getenv('LOGIN_RATE_LIMIT_ATTEMPTS') ?: 5),
    'login_decay_seconds' => (int) (getenv('LOGIN_RATE_LIMIT_WINDOW') ?: 60),
    'login_lock_seconds' => (int) (getenv('LOGIN_RATE_LIMIT_LOCKOUT') ?: 60),
];
