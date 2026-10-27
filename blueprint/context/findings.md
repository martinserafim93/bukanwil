# Findings

> **Generated file.** The findings ledger: review findings raised by `/audit`
> against the work in progress, each with a durable ID, severity (P0-P3), and
> status. `/implement` marks repaired findings `fixed`, a later `/audit` pass
> moves them to `closed`, and `/complete` refuses to land while any P0 or P1
> finding is `open` or `fixed`, then archives resolved findings with the work
> and resets this file.

_Findings recorded during security audit on 2026-10-27._

### F-01 [P2] fixed - Default superadmin password in initial database schema

**File:** database/schema.sql:118-124
**Found:** 2026-10-27 by /audit (scope: full; lens: security)
**Why it matters:** The initial database migration/schema seeds a default superadmin user with a known password (`Admin@2026`). There is currently no check or warning banner in the admin interface prompting the administrator to change this default credential, leaving installations vulnerable if left unchanged.
**Suggested fix:** Add a detection banner in `app/Views/admin/dashboard.php` or `app/Controllers/Admin/Dashboard.php` that warns when the superadmin account is still using the initial default password hash, advising an immediate password update.
**Resolution:** Implemented default password detection in `app/Controllers/Admin/Dashboard.php` and rendered an alert banner in `app/Views/admin/dashboard.php` with direct link to profile edit page. Verified live via browser and automated test.

### F-02 [P3] fixed - Direct script execution not disabled in public uploads directory

**File:** public/uploads/
**Found:** 2026-10-27 by /audit (scope: full; lens: security)
**Why it matters:** Although `app/Controllers/Tamu.php` properly verifies MIME types using `finfo`, forces safe extensions (`.jpg`, `.png`, `.webp`), and uses random 32-hex filenames, the directory `public/uploads/` lacks an Apache `.htaccess` rule to disable script interpretation (`php_flag engine off` or `<FilesMatch "\.(php|phtml|php5|php7|phar)$"> Order Deny,Allow Deny from all </FilesMatch>`). In defense-in-depth terms, direct execution protection prevents any potential future upload bypass from executing server-side code.
**Suggested fix:** Create a `.htaccess` file inside `public/uploads/` containing directives to turn off PHP engine execution and restrict file serving to allowed image extensions.
**Resolution:** Created `public/uploads/.htaccess` with directives to turn off PHP engine (`php_flag engine off`), block script and executable file types (`Require all denied`), and only allow safe media files (`Require all granted`). Added blank `index.html` files. Verified script access returns HTTP 403 Forbidden while valid images return HTTP 200 OK.

### F-03 [P3] fixed - Admin logout action accessible via GET request without CSRF protection

**File:** app/Config/Routes.php:13
**Found:** 2026-10-27 by /audit (scope: full; lens: security)
**Why it matters:** The admin logout endpoint is registered as `GET /admin/logout`. An attacker could embed `<img src="http://bukanwil.test/admin/logout">` on a third-party webpage or email, causing an authenticated administrator's session to be terminated unexpectedly (Login/Logout CSRF).
**Suggested fix:** Switch the logout route to `POST /admin/logout` and submit via a form with CSRF token, updating the admin layout modal accordingly.
**Resolution:** Updated `app/Config/Routes.php` to define `POST /admin/logout` (with GET redirecting harmlessly). Updated `app/Controllers/Admin/Auth.php` to enforce POST method before destroying session. Updated `app/Views/layouts/admin.php` with POST form, CSRF token, and JavaScript modal submission. Verified live via browser subagent and automated tests.

### F-04 [P3] fixed - CSRF token randomization is disabled against side-channel compression attacks

**File:** app/Config/Security.php:27
**Found:** 2026-10-27 by /audit (scope: full; lens: security)
**Why it matters:** In `app/Config/Security.php`, `$tokenRandomize` is set to `false`. When deployed with HTTPS and HTTP compression (gzip/deflate/brotli), static tokens in responses could potentially be extracted via BREACH-style side-channel compression attacks.
**Suggested fix:** Set `public bool $tokenRandomize = true;` in `app/Config/Security.php` or configure it via `.env`.
**Resolution:** Enabled `$tokenRandomize = true;` in `app/Config/Security.php`. Verified end-to-end token generation, randomized 64-char token output in forms, and automated verification of CSRF requests.


