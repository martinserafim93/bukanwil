# Coding Standards

> Coding conventions for BUKANWIL (CodeIgniter 4 + Tailwind CSS v4 + MySQL).
> Adopted from the actual codebase.

## PHP & CodeIgniter 4

- PHP 8.2+ compatibility (tested on PHP 8.3).
- Follow PSR-12 coding style and PSR-4 autoloading standards.
- Controllers live in `app/Controllers/`, extending `App\Controllers\BaseController`. Group related actions (e.g., `App\Controllers\Admin\`).
- Models live in `app/Models/`, extending `CodeIgniter\Model`. Explicitly define `$table`, `$primaryKey`, and `$allowedFields`.
- Views live in `app/Views/` organized by context (`tamu/`, `admin/`, `layouts/`). Use template inheritance (`$this->extend(...)` and `$this->section(...)`).
- Routes are explicitly defined in `app/Config/Routes.php`. Group administrative routes under the `/admin` prefix.

## Styling & Frontend

- Tailwind CSS v4 for all styling.
- Source CSS is maintained in `themes/app.css` and compiled via `@tailwindcss/cli` to `public/css/app.css`.
- Use npm scripts for asset builds: `npm run build:css` (production) and `npm run watch:css` (development).
- Use semantic HTML5 markup.
- For interactive elements, use custom modals and toast notifications rather than native browser `confirm()` or `alert()`.
- Keep JavaScript focused and lightweight; use Chart.js for dashboard charts and vanilla JavaScript for DOM interactions.

## Database & Data Access

- MySQL 8.0 / MariaDB with `utf8mb4_unicode_ci` charset.
- Authoritative database schema and seed data are maintained in `database/schema.sql`.
- Timestamps and calculations must strictly adhere to the WITA (UTC+8) timezone (`SET time_zone = '+08:00'`).
- Always use CodeIgniter Query Builder or Model methods for parameterized database queries to prevent SQL injection.
- Ensure all queries filtering or ordering by dates use proper database indexes (e.g., `idx_tamu_created_at`).

## Security & Validation

- **CSRF Protection**: CSRF is active across all POST requests. Every HTML form must include `<?= csrf_field() ?>`.
- **Output Escaping**: Always wrap user-supplied output in views with `esc()` to prevent Cross-Site Scripting (XSS).
- **Form Validation**: Validate all inputs using CodeIgniter's validation rules before processing or storing data.
- **Honeypot & Rate Limiting**: Maintain honeypot fields on public submission forms and enforce rate limiting (e.g., public submissions and login attempts).
- **Authentication & Passwords**: Passwords must always be hashed with `password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])` and verified with `password_verify()`.
- **File Upload Security**: Validate uploaded photos using finfo MIME checking (JPEG, PNG, WebP only), enforce maximum file size (1 MB), and generate random stored filenames (`getRandomName()`).

## Error Handling & Notifications

- Validate input early and redirect back with input and errors (`return redirect()->back()->withInput()->with('errors', $this->validator->getErrors())`).
- Use session flashdata (`session()->setFlashdata('sukses', ...)` / `session()->setFlashdata('error', ...)`) for user feedback messages across redirects.
- Provide clear, user-friendly error messages in Indonesian.

## Testing

The blueprint installs no test runner; testing is opt-in at the project level,
because the overlay can't know your stack. Adding unit testing is an explicit
setup task the AI can do through the normal workflow, either as a build-plan item
or with `/tests`. The setup should choose the stack-native runner, wire the
scripts or commands, add a small example test, and update the Commands section
of `AGENTS.md`.

When `AGENTS.md` declares a `Verify` command, treat it as the umbrella automated
gate. It combines only the checks this project actually has, in this order when
available: typecheck, tests, then build. The command does not enable an absent
test runner or replace focused evidence. It gives local work and optional CI one
exact command to run. `/ci` owns Verify and CI setup. `/tests` adds the real test
command to Verify when it already exists, but never creates CI only because
testing was configured.

**The opt-in switch is one signal: a `test` command in the Commands section of
`AGENTS.md`.** Declare one and **tests become a gate for logic-bearing steps**,
not an optional extra; leave it out and the loop verifies logic with the evidence
it already uses (run it, a screenshot, the build). Adding the runner is itself a
deliberate step, never a silent mid-step install. This is the single definition
of the switch; the skills and `ai-interaction.md` only point back here.

- **What to test (the scope rule):** pure logic where a wrong answer is possible -
  parsers, formatters, validators, helper functions, and data transformations. These
  have assertable inputs and outputs and real edge cases (empty, missing, malformed).
- **What not to test:** UI layouts, rendering views, or integration-level surfaces
  requiring a real browser or external service. Verify those with manual checks or
  browser evidence, not brittle unit tests.
- **The gate (when a runner is configured):** a build step that adds in-scope logic
  must ship a passing test in the same reviewable diff. The project's test command
  must be green before the step is approved, before any checkpoint commit, and
  before `/complete` merges. UI and integration-only steps are exempt.
- Run tests via the project's test command (see Commands in `AGENTS.md`), such as
  `composer test`.

## Browser Verification

For UI and integration behavior, prefer real browser evidence over reading the
code and assuming it works.

- Browser automation is separately opt-in through `/tests browser`. That setup
  reuses a compatible runner or prefers Playwright for supported projects, then
  documents the exact command as `Browser tests` in `AGENTS.md`.
- When `Browser tests` is declared, add focused coverage for stable behavioral
  done-whens when it is proportionate, and run the documented command during
  `/check`. Do not assume it proves visual fidelity, real authenticated-profile
  behavior, browser chrome, or another claim the test does not observe.
- If no Browser tests command is declared, do not add a runner silently in the
  middle of an unrelated feature. Use the available dev server, browser
  screenshots, build output, or manual evidence instead.
- Browser tests are not part of the default Verify command or CI unless the user
  separately chooses that slower gate.
- Browser evidence is especially important for flows that click, type, submit,
  navigate, download files, render complex layouts, or depend on client-side
  state.

## Code Quality

- No commented-out dead code.
- No unused variables or unused imports.
- Keep controller and model methods focused and readable.

## Comments

Write code that explains itself; comment only what the code cannot say.
Over-commenting is a common AI tell, so resist it.

- Comment the **why**, not the **what**. Delete any comment that restates the code.
- No banner/header blocks, section dividers, or step-by-step narration of obvious
  code. A file does not need a comment announcing each region.
- A comment earns its place only when it captures something the code can't: a
  non-obvious decision, a gotcha or workaround, why a value is what it is, or a
  link to a spec or issue.
- Prefer self-documenting names and small functions over explanatory comments.
- Keep doc comments minimal: a one-line purpose on an exported class or method is
  plenty; don't write PHPDoc that just repeats the signature.
- When in doubt, leave the comment out.

## Writing

- No em dashes (U+2014) in generated content: docs, comments, commit messages,
  READMEs, specs. They read as AI-generated.
- Use a hyphen for `term - description` separators; rephrase prose with commas,
  parentheses, or a colon. Avoid en dashes and the ellipsis character too.
