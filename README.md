# Doctor Gorilka

## Login security
- Rate limit: 5 attempts per minute per IP (all usernames). After exceeding the limit, that IP is locked for 60 seconds.
- User-facing message: generic `Неверные учетные данные` on failures; after throttling `Слишком много попыток входа. Попробуйте ещё раз через :seconds сек.` is shown.
- Suspicious login attempts are logged to PHP error log with `[security]` prefix.

### Configuration (.env or environment)
- `LOGIN_RATE_LIMIT_ATTEMPTS` — max attempts in the window (default: `5`).
- `LOGIN_RATE_LIMIT_WINDOW` — window length in seconds (default: `60`).
- `LOGIN_RATE_LIMIT_LOCKOUT` — lock duration in seconds after exceeding limit (default: `60`).

### Tests
Run `php tests/LoginRateLimiterTest.php` to cover successful login, limit exceed, and cooldown restore.
