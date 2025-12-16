# Doctor Gorilka

## Auth
- Login is **email-only** (no username).
- Registration requires **email verification** (`/verify-email?token=...`).
- Password reset is available via `/forgot-password` and `/reset-password?token=...`.

### Configuration (.env or environment)
- `APP_ENV` — set `local` to enable `/debug/mail-test`.
- `BASE_URL` — used to generate absolute links in emails (local example: `http://localhost:8000`).

#### SendPulse SMTP
- `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `SMTP_SECURE` (`tls` for STARTTLS).
- `MAIL_FROM_EMAIL` (e.g. `no-reply@doctor-gorilka.id.lv`)
- `MAIL_FROM_NAME` (e.g. `Doctor Gorilka`)

#### Rate limits
- `LOGIN_RATE_LIMIT_ATTEMPTS` — max attempts in the window (default: `5`).
- `LOGIN_RATE_LIMIT_WINDOW` — window length in seconds (default: `60`).
- `LOGIN_RATE_LIMIT_LOCKOUT` — lock duration in seconds after exceeding limit (default: `60`).
- `ACTION_RATE_LIMIT_ATTEMPTS` — max attempts for registration/resend/forgot (default: `3`).
- `ACTION_RATE_LIMIT_WINDOW` — action window length in seconds (default: `3600`).
- `ACTION_RATE_LIMIT_LOCKOUT` — action lock duration in seconds (default: `3600`).

### Tests
Run:
- `php tests/LoginRateLimiterTest.php`
- `php tests/RegisterValidationTest.php`
# email_cafe
