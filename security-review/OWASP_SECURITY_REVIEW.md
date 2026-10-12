# OWASP Security Code Review — ERP Lighter Media Group

Review date: 12 October 2026  
Scope: current working tree in `D:\laragon\www\erp_lmg`  
Method: architecture and trust-boundary inventory, route enumeration, source-to-sink tracing, authorization review, secret and dangerous-sink search, file-storage review, Composer/NPM advisory audit, and safe PoC validation.

## Executive summary

The review confirmed six security findings: two Critical, three High, and one Medium. The most urgent problem is that 30 business API routes are reachable without authentication, including read and write operations over CRM, payroll, invoices, audit trails, users, clients, and task data. Separately, any authenticated user can reach user-management endpoints and create an `isAdmin=true` account. These issues should be fixed before the ERP is exposed outside a trusted development network.

## Threat model

- Actors: unauthenticated internet/LAN attacker, ordinary authenticated employee, division manager, ERP administrator, connected OAuth providers, n8n/WAHA services.
- High-value assets: employee identity and roles, CRM/customer PII and chat history, payroll and invoice data, client contracts/documents, social-media OAuth tokens, stored account passwords, audit trails.
- Trust boundaries: browser to Laravel web routes, unauthenticated clients to `routes/api.php`, Laravel to PostgreSQL/Supabase, public webroot to storage, Laravel to n8n/WAHA/social APIs, uploaded files to document parsers.
- Primary invariants: all business data requires authentication; admin state changes require admin authorization; division/client access is enforced server-side; secrets never ship in source; private files cannot be fetched through public storage; untrusted files are parsed only by patched libraries.

## Findings

### SEC-01 — Critical — Business APIs allow unauthenticated reads and writes

- **Location:** `routes/api.php:10-16`, `routes/api.php:83-121`; representative sinks: `app/Http/Controllers/ProdukController.php:818-1108`, `app/Http/Controllers/CRMController.php:141-4000`, `app/Http/Controllers/UserController.php:77`, `app/Http/Controllers/PayrollController.php:306`, `app/Http/Controllers/DashboardController.php:391`.
- **Issue:** 30 ERP business endpoints have only Laravel's stateless `api` middleware. They do not require `auth:sanctum`, `web` + `auth`, a signed webhook, or an API credential. Controller methods also do not perform authentication locally.
- **Exploit path:** unauthenticated request → public `/api/*` route → validation only → direct model query/update/delete. Examples include `GET /api/users/json`, `GET /api/crm/human`, `GET /api/get-data-payroll`, `POST|PUT|DELETE /api/produk/*`, and CRM checklist/follow-up mutation endpoints.
- **Impact:** disclosure of user emails/roles, customer names/phones/social/chat history, payroll, invoices, and audit logs; unauthorised creation, modification, or deletion of clients and CRM records; manipulation of follow-up/checklist state.
- **Remediation:** deny by default. Put all internal API routes inside `Route::middleware(['web','auth'])` when called by the session-authenticated ERP, or use `auth:sanctum` for token clients. Add per-route role, division, client, and object-level policies. Keep only explicitly documented signed webhooks public.
- **Test:** feature-test every API without credentials (expect 401), with an unrelated division user (expect 403), and with an authorised user (expect success). Include state-changing endpoints.
- **PoC requirements:** unauthenticated network access only; safe mode performs no requests; `--execute` performs read-only GETs and suppresses response bodies. See `poc_unauthenticated_business_api.py`.

### SEC-02 — Critical — Any authenticated user can create or promote an administrator

- **Location:** `routes/web.php:107-109`; `app/Http/Controllers/UserController.php:220-348`, especially the `divisi_id=lmg|idme` decision and `isAdmin => $isLmg` assignment.
- **Issue:** user-management routes are inside the global `auth` group but lack `EnsureUserIsAdmin`. `UserController::store()` and `update()` contain no admin authorization and trust an authenticated request to select `lmg`/`idme`, which sets `isAdmin=true`.
- **Exploit path:** ordinary employee login → valid CSRF token → POST `/manajemen_user` with `divisi_id=lmg` and `jabatan=ceo` → new admin account; alternatively update an existing account through the resource route.
- **Impact:** full vertical privilege escalation, including Data Center credential access, user locking, role changes, and access to all divisions/clients.
- **Remediation:** apply `EnsureUserIsAdmin` to the complete `manajemen_user` resource and lock route; additionally call a policy/`authorize()` in every controller action. Do not derive administrator status from request-controlled division values. Prevent self-promotion and protect the last-admin invariant.
- **Test:** log in as a non-admin and assert 403 for index/store/update/destroy/toggleLock; assert `isAdmin` remains unchanged after crafted requests.
- **PoC requirements:** ordinary authenticated test user, session cookie, CSRF token, isolated test database. Dry-run is default; `--execute` is explicitly state-changing and includes cleanup instructions. See `poc_authenticated_admin_privilege_escalation.py`.

### SEC-03 — High — WAHA API credential is hardcoded in source

- **Location:** `app/Http/Controllers/CRMController.php:2444-2445`, used at `app/Http/Controllers/CRMController.php:3744-3745` as `X-Api-Key`.
- **Issue:** a live-looking service credential is committed as a PHP constant instead of being loaded from protected server configuration.
- **Exploit path:** access to repository, deployment artifact, backup, server-side error/source disclosure, or developer workstation → extract constant → authenticate directly to WAHA.
- **Impact:** unauthorised access to WhatsApp sessions/messages or service actions permitted by that key; persistence even after application access is revoked.
- **Remediation:** rotate the key immediately, then load the replacement from `.env` through `config/services.php`. Never log it. Add secret scanning to CI and repository history cleanup where appropriate.
- **Test:** repository-wide secret scanner finds no WAHA credential; application fails closed when the environment key is absent; outbound tests assert the configured header without exposing its value.
- **PoC requirements:** read-only repository access. The PoC only detects declaration and use and intentionally suppresses the key value. See `poc_hardcoded_waha_api_key.py`.

### SEC-04 — High — Protected client documents are stored on a public web disk

- **Location:** `config/filesystems.php:41-45,77`; `app/Http/Controllers/PDFController.php:268-289,304-350,357-401`; similar default contract creation at `app/Http/Controllers/ProdukController.php:890-915`.
- **Issue:** controller preview routes call access checks, but files are saved under the `public` disk, which is symlinked to `/storage`. A direct `/storage/<location>` request bypasses controller authorization entirely.
- **Exploit path:** attacker obtains a stored path from a database leak, page/source/log, shared link, browser history, or predictable uploaded filename → unauthenticated GET `/storage/pdf/<client>/<division>/<file>` → web server returns the file.
- **Impact:** disclosure of contracts, scopes of work, handover documents, operational notes, and uploaded client PDFs. UUID filenames reduce guessing but do not provide authorization.
- **Remediation:** migrate client documents and versions to the private `local` disk (or private object storage); serve them only through an authorised controller or short-lived signed URL. Use random server-generated names for uploads and set `Content-Disposition` safely.
- **Test:** direct `/storage/...` access returns 404; authorised route returns the document; unrelated user receives 403; migrated legacy files remain accessible only through the controller.
- **PoC requirements:** a known `file_pdf.location`; no account needed. Dry-run by default; execute reads at most five bytes to confirm `%PDF-`. See `poc_public_client_document.py`.

### SEC-05 — High — Known-vulnerable PHP dependencies parse user uploads

- **Location:** `composer.lock` (`phpoffice/phpspreadsheet` 1.30.0, `maatwebsite/excel` 3.1.67, Laravel 12.41.1, Dompdf 3.1.4, and other affected packages); reachable upload sink at `app/Http/Controllers/SosialCreatorController.php:182-190`.
- **Issue:** `composer audit --locked` reports 68 advisories across 20 packages: 2 Critical, 22 High, 33 Medium, 10 Low, and 1 unclassified. Critical spreadsheet advisories include CVE-2026-34084 and CVE-2026-45034 affecting PhpSpreadsheet 1.30.0. The application accepts `xlsx`, `xls`, and `csv` uploads and passes them to `Excel::import()`.
- **Exploit path:** authenticated user able to reach `/import_creator` → crafted spreadsheet upload → vulnerable parser. Other affected runtime surfaces include PDF rendering, HTTP clients, email handling, Markdown parsing, and framework components.
- **Impact:** depending on the advisory and parser path, SSRF/RCE or resource-exhaustion DoS; the exact impact of each of the 68 advisories varies. Development-only PHPUnit/PsySH advisories matter if those packages are present on production systems.
- **Remediation:** update dependencies and lockfile to patched releases, prioritising Laravel, PhpSpreadsheet/Laravel Excel, Guzzle/PSR-7, Symfony, Dompdf, CommonMark, and phpseclib. Deploy production with `composer install --no-dev`. Until patched, restrict import to a trusted admin role, apply small size limits, and parse in an isolated worker with network/filesystem restrictions.
- **Test:** `composer audit --locked` returns zero applicable runtime advisories; malicious/oversized workbook regression fixtures are rejected without outbound requests or excessive CPU/memory.
- **PoC requirements:** local read-only access to `composer.lock` and controller source. It does not create or parse a malicious workbook. See `poc_vulnerable_spreadsheet_dependencies.py`.

### SEC-06 — Medium — Login has no brute-force throttling

- **Location:** `app/Http/Controllers/LoginController.php:17-49`; guest route at `routes/web.php:434-436`.
- **Issue:** the login action validates credentials with `Auth::attempt()` but never checks or increments a Laravel `RateLimiter`, and the route has no `throttle` middleware.
- **Exploit path:** unauthenticated attacker repeatedly submits passwords for a known or guessed email without receiving 429/lockout/backoff.
- **Impact:** password spraying and credential stuffing against ERP accounts, including privileged accounts. The generic failure message limits enumeration but does not stop guessing.
- **Remediation:** add per-email+IP throttling, progressive delays, logging/alerting, and a reasonable lockout policy. Retain generic errors. MFA/OTP should be additive, not a replacement for password throttling.
- **Test:** after the configured threshold, further invalid attempts receive 429; a successful login clears the relevant counter; different accounts/IP behavior matches policy.
- **PoC requirements:** unauthenticated access and a reserved `.invalid` address by default. Dry-run is default; execution is capped at ten attempts. See `poc_login_bruteforce_no_rate_limit.py`.

## Open questions / assumptions

- The current `.env` is configured as `APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=http://192.168.14.99:8000`, with no explicit secure session cookie and session encryption disabled. This is acceptable only for isolated local development. If this configuration is deployed to users, it becomes a separate high-risk deployment finding (debug information leakage and session exposure over HTTP).
- Reverse-proxy/WAF, web-server deny rules, VPN restrictions, and production filesystem topology were not available. They may reduce exposure but must not replace application authorization.
- The WAHA key was not used against the service and is not reproduced in this report. Treat it as compromised because it exists in source.
- Live exploit payloads for dependency advisories were intentionally not executed.
- OAuth state validation, encrypted OAuth tokens, Data Center encryption, current-password reauthentication for secret reveal, session regeneration on login/logout, and private account/master-folder storage were reviewed as positive controls.

## Coverage

- Reviewed: all registered routes and middleware groups; authentication/session flow; admin/user management; division/client middleware; CRM APIs and WAHA history integration; Data Center; OAuth controllers/models; finance/payroll/audit endpoints; file upload/storage and rich-text sanitisation; spreadsheet import; outbound n8n/HTTP integrations; raw SQL/dynamic property patterns; Blade raw HTML and browser HTML sinks; cryptographic storage; error/config defaults.
- Automated checks: `npm audit --omit=dev` reported zero vulnerabilities across 293 dependencies. `composer audit --locked` reported the advisory totals above.
- Not dynamically tested: production host, database contents, WAHA/n8n/social APIs, Cloudflare/web-server headers, object-storage ACLs, email delivery, or destructive API operations.
