# Kendat Integrated Services - Enterprise PHP Web Application

An enterprise-grade, high-performance web platform for **Kendat Integrated Services** offering custom software engineering, AI copilot solutions, cloud infrastructure, and business automation.

---

## Technical Architecture & Stack

- **Core**: Native PHP 8.1+ (Strict types enabled)
- **Database**: MySQL / MariaDB (PDO Prepared Statements, Foreign Keys, Schema Migrations)
- **Frontend**: HTML5, Vanilla CSS3 (Custom Design System, Fluid Typography, Micro-animations), Vanilla JS ES6+
- **Security**: Cryptographic CSRF Tokens, Database-backed & File-fallback Brute-force Rate Limiting (`rate_limits`), MIME Hardened Upload Engine, `finfo_file` verification, Strict Session Security, HTTP Security Headers, Content Security Policy (CSP), Admin Audit Trail (`admin/audit-logs.php`), Strict SMTP TLS Peer Verification.

---

## Environment Setup & Configuration

1. **Clone/Copy Project Files** to your local web root (e.g. `c:/xampp/htdocs/KendatTech` or `/var/www/html`).
2. **Environment Variables**:
   Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
   Update `.env` with your actual database and SMTP configuration:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://localhost/KendatTech

   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=kendat_integrated_services
   DB_USER=root
   DB_PASS=your_secure_db_password

   MAIL_MAILER=smtp
   MAIL_HOST=mail.kisprojectslab.com
   MAIL_PORT=465
   MAIL_USERNAME=hello@kisprojectslab.com
   MAIL_PASSWORD=your_secure_smtp_password
   MAIL_ENCRYPTION=ssl
   ```

---

## Database Setup & Versioned Migrations

Run database migrations via the command line engine:
```bash
php database/migrator.php
```

Or seed initial database structure with the CLI setup tool:
```bash
php setup.php
```
*Note: `setup.php` and `migrate_firebase.php` are strictly restricted to CLI execution (`PHP_SAPI === 'cli'`) for security.*

---

## Security Features & Hardening

- **Secrets Management**: No hardcoded DB or SMTP credentials in source code. All secrets loaded dynamically via `includes/env.php`.
- **CSRF Protection**: All mutating HTML forms (`POST`) require valid `_csrf_token` checked via `require_csrf_token()`.
- **Destructive Action Safety**: All delete, update, and status toggle actions operate exclusively over `POST` with CSRF validation.
- **File Upload Security**: Strict MIME type checking (`finfo_file`), extension allowlists, SVG XSS sanitization, random hex filenames (`bin2hex(random_bytes(16))`), and restrictive directory permissions (`0755`/`0644`).
- **Session Security**: `HttpOnly`, `SameSite=Lax`, `Strict Mode`, automatic `session_regenerate_id(true)` upon authentication, and 30-minute idle session timeout.
- **SMTP TLS Security**: Enforces `verify_peer` and `verify_peer_name` by default to prevent MITM attacks over email stream sockets.
- **Rate Limiting**: Integrated database-backed `RateLimiter` with automatic TTL cleanup and file fallback locks out brute force attempts.
- **Audit Logging & Governance**: Structured security events (`ADMIN_PROJECT_CREATED`, `ADMIN_MESSAGE_DELETED`, etc.) automatically logged to `audit_logs` table and viewable via `admin/audit-logs.php`.

---

## Running Automated Tests

Execute the comprehensive automated test suite locally:
```bash
php tests/run_tests.php
```
The test runner validates:
1. Complete PHP syntax lint (`php -l`) across all `.php` files.
2. Environment & loader configuration.
3. Database connectivity & migration tables (`schema_migrations`, `audit_logs`, `rate_limits`).
4. Cryptographic CSRF token generation and validation.
5. Rate limiter hit/clear/rejection logic.
6. Hardened file upload handling.
7. Session authentication guards, CLI tooling enforcement (`setup.php`, `migrate_firebase.php`), and CSRF mutation policies.

---

## Responsive Design & Breakpoints

- **Desktop** (`>= 1025px`): Full navigation, multi-column grids, fixed persistent admin sidebar.
- **Tablet** (`481px - 1024px`): 2-column layout, compact navigation, scalable typography.
- **Mobile** (`<= 480px`): 1-column layout, off-canvas navigation drawers for public and admin, touch targets >= 44px, horizontally scrollable `.table-responsive` tables, input zoom prevention.

---

## Production Deployment Checklist

- [ ] Ensure `.env` is present and `.env.example` contains no secrets.
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
- [ ] Verify file permissions on `storage/` and `assets/uploads/` (`0755` for directories, `0644` for files).
- [ ] Run `php database/migrator.php` to ensure all migrations are applied.
- [ ] Run `php tests/run_tests.php` to confirm 100% test pass status.
- [ ] Verify HTTPS is active for HSTS and Secure cookie flags.
