# eDTR Modernization and Production Readiness Audit

Last updated: 2026-08-27

## Executive summary

The application is a Laravel 11 attendance and DTR system built with Livewire 3 and Filament 3. The framework itself is not legacy, so a framework upgrade is not the immediate priority. The first review found exposed credentials, weak authentication lifecycle handling, missing login throttles, and object-level authorization gaps around employee reports and uploaded MOV documents. The current verdict is **NOT READY FOR PRODUCTION** until credentials are rotated and the unresolved access-control findings are fixed and tested.

## Initial application inventory

| Area | Current implementation |
|---|---|
| PHP | `^8.2`; local CLI 8.2.12 |
| Laravel | 11.47.0 |
| Database | MySQL; database version still to be confirmed |
| Web authentication | Laravel session guard with custom Livewire login/reset screens |
| API authentication | Laravel Sanctum personal access tokens |
| Authorization | Role, center, and database-defined permission middleware; no policies |
| Admin UI | Filament 3.2.113 at `/superadmin` |
| Frontend | Livewire 3.7.4, Alpine through Filament, Tailwind 3, Vite 5 |
| Session | Database, 120-minute lifetime; secure-cookie setting environment-dependent |
| Cache | Database |
| Queue | Database; attendance writes and mail are queued |
| Mail | SMTP |
| Storage | Local plus custom Azure Blob HTTP/SAS integration |
| External APIs | Azure Blob Storage and Azure Maps reverse geocoding |
| Scheduler | Only the default hourly `inspire` command |
| Health check | Laravel `/up` liveness endpoint |
| Tests | Two placeholder tests; no business/security coverage at assessment start |
| Deployment | No committed deployment procedure, worker configuration, or rollback checklist |

### Major business modules

- Employee attendance capture, time entries, work-from-home, leave/flexible-schedule requests, profiles, QR codes, and DTR reports.
- Admin coordinator scheduling, employee time-entry review, official time, and department reports.
- HR employee master list, events, official-time evaluation, reports, and time-entry review.
- DAPCC-specific scheduling and report behavior.
- Filament super-admin CRUD for users, departments, events, MOVs, official times, permissions, and time entries.
- Mobile/scanner APIs for login, QR lookup, attendance capture, location, and daily time entries.

## Laravel/PHP compatibility assessment

Laravel 11 and PHP 8.2 are compatible and currently boot successfully. A blind framework upgrade is not recommended during this security pass. First stabilize authorization and tests, update locked patch/minor dependencies where audits permit, then evaluate Laravel 12 as a separate staged project with an isolated branch, upgrade guide review, database snapshot, and regression suite.

## Prioritized findings

### CRITICAL-001 — Credentials committed in the environment template

**Issue:** Real-looking database credentials, an application key, Azure SAS tokens, and a commented mail credential are present in `.env.example`.

**Location:** `.env.example`

**Risk:** Repository readers may access the database or blob storage, forge encrypted application data if the key was used elsewhere, or access mail infrastructure.

**Evidence:** Non-placeholder credential and signed-token values are committed in the tracked template.

**Recommended Fix:** Replace all values with placeholders, remove the application key, scan Git history, and rotate every exposed credential at its provider.

**Status:** In progress; source cleanup can be performed here, but provider-side rotation needs an operator.

### CRITICAL-002 — Hard-coded Azure Maps subscription key

**Issue:** A Maps subscription key is embedded in controller source.

**Location:** `AttendanceController::mvpool_time_entry()` and `AttendanceController::location()`

**Risk:** Anyone with source access can consume the subscription and incur cost or exhaust quota.

**Evidence:** The full key is concatenated into two outbound URLs.

**Recommended Fix:** Move it to service configuration, rotate it, validate coordinates, and set outbound HTTP timeouts.

**Status:** In progress.

### HIGH-001 — Blob storage credential disclosed by login API

**Issue:** One login branch returns an Azure SAS token and storage endpoint to the client.

**Location:** `AuthController::login()`

**Risk:** A user can bypass application authorization and access every blob permitted by the shared SAS token.

**Evidence:** The JSON response includes `sas_token` for two hard-coded email accounts.

**Recommended Fix:** Never return shared storage credentials; proxy authorized files or issue narrowly scoped, short-lived delegated URLs.

**Status:** In progress.

### HIGH-002 — Missing object-level authorization on reports, QR codes, and MOVs

**Issue:** Authentication/role middleware is present, but controllers do not prove that the requested employee, department, QR card, or MOV belongs to the caller's authorized scope.

**Location:** `DtrReportController`, `QrCodeController`, `ShowMovController`; routes containing `{hris_number}`, `{employee}`, `{department}`, or `{mov}`.

**Risk:** IDOR can expose attendance, identity, signature, report, or uploaded-document data across departments.

**Evidence:** Route parameters are queried or route-bound and rendered without an ownership/department policy check. The employee PDF route is available to every authenticated user.

**Recommended Fix:** Add policies or explicit scoped queries and cover employee/admin/HR boundary cases with feature tests.

**Status:** Needs review; business scope rules must be preserved while policies are introduced.

### HIGH-003 — Password reset tokens are plaintext, reusable, and not expiry-checked

**Issue:** Custom reset logic stores raw UUID tokens, checks only existence, does not enforce the configured 60-minute expiry, and does not delete a token after reset.

**Location:** `ForgotPassword` and `ResetPassword` Livewire components.

**Risk:** A leaked database or old reset URL can reset an account indefinitely and repeatedly.

**Evidence:** Direct inserts/lookups into `password_reset_tokens`; successful reset only updates the password.

**Recommended Fix:** Use Laravel's password broker, which hashes, throttles, expires, and consumes tokens.

**Status:** In progress.

### HIGH-004 — Public login endpoints have no rate limiting

**Issue:** Three API login endpoints and the Livewire login action lack explicit brute-force protection.

**Location:** `routes/api.php`, `Login::login()`

**Risk:** Automated credential stuffing and password guessing.

**Evidence:** No throttle middleware or component-level limiter is applied.

**Recommended Fix:** Add per-identity/IP throttles, consistent 422 validation, and 401 authentication responses.

**Status:** In progress.

### HIGH-005 — API tokens are unscoped and do not expire

**Issue:** Issued Sanctum tokens have no abilities or expiration, and token existence is partly trusted from a client-supplied string.

**Location:** `AuthController`; `config/sanctum.php`

**Risk:** A stolen token retains broad attendance and location access indefinitely.

**Evidence:** `createToken()` is called without abilities/expiry and global expiration is `null`.

**Recommended Fix:** Define scanner/MV-pool abilities, enforce them on routes, revoke superseded tokens, and set a documented expiration policy.

**Status:** Needs review for mobile-client compatibility.

### MEDIUM-001 — Login and logout session lifecycle is incomplete

**Issue:** Login does not explicitly regenerate the session; logout is a state-changing GET and does not invalidate/regenerate the session token.

**Location:** `Login::login()` and `/logout` in `routes/web.php`.

**Risk:** Session fixation and logout CSRF/incomplete session termination.

**Recommended Fix:** Regenerate after authentication; use POST logout, invalidate the session, and regenerate the CSRF token.

**Status:** In progress.

### MEDIUM-002 — Account enumeration in password recovery

**Issue:** The forgot-password form states whether an email exists.

**Location:** `ForgotPassword::submit()`

**Risk:** Anonymous users can enumerate staff accounts.

**Recommended Fix:** Always show the same accepted response and rely on broker throttling.

**Status:** In progress.

### MEDIUM-003 — Insufficient input validation and unsafe exception logging

**Issue:** QR payloads, coordinates, report ranges, and several API login inputs have weak or absent type/range validation. Attendance exceptions log raw exception messages.

**Location:** API controllers and report controllers.

**Risk:** Resource abuse, malformed requests, accidental sensitive log content, and unstable responses.

**Recommended Fix:** Add bounded validation/Form Requests, safe contextual logs, and consistent status codes.

**Status:** In progress for authentication/location endpoints; report validation remains.

### MEDIUM-004 — Production HTTP hardening is undocumented

**Issue:** No application or proxy policy is documented for CSP, frame protection, MIME sniffing, referrer policy, HSTS, or permissions policy.

**Location:** HTTP middleware/deployment configuration.

**Risk:** Reduced browser-side defense in depth.

**Recommended Fix:** Add safe baseline headers in Laravel and document that HSTS belongs at the TLS reverse proxy.

**Status:** Not started.

### MEDIUM-005 — Known default passwords

**Issue:** Employee creation and development seeding use predictable passwords, while enforcement checks only one of those defaults.

**Location:** HR master-list components, `CheckDefaultPassword`, and `DatabaseSeeder`.

**Risk:** Accounts can be accessed with known credentials, and some are not forced to change them.

**Recommended Fix:** Generate one-time random passwords or activation links and enforce reset consistently. Never use development seed credentials in production.

**Status:** Needs workflow review.

### LOW-001 — Dead/debug code and unreachable statements

**Issue:** Controllers and routes contain large commented blocks, debug remnants, and unreachable response code.

**Location:** Routes and API/report controllers.

**Risk:** Maintenance errors and obscured security behavior.

**Recommended Fix:** Remove only after regression tests establish current behavior.

**Status:** Not started.

## Endpoint security matrix

This matrix covers every application-defined route. Filament resource routes are grouped because each resource exposes the same authenticated index/create/edit pattern and all are additionally gated by `User::canAccessPanel()` to `superadmin`.

| Method | Endpoint(s) | Authentication | Authorization | Validation / rate limit | Tests | Status |
|---|---|---|---|---|---|---|
| ANY | `/` | Public | N/A | N/A | Placeholder test expects wrong status | Redirect only |
| GET | `/sign-in` | Guest | N/A | Component validation; no throttle initially | None | Fix planned |
| GET | `/forgot-password` | Guest | N/A | Email validation; custom 60s check | None | Fix planned |
| GET | `/reset-password/{token}` | Guest | Token only | Weak custom token validation | None | Fix planned |
| GET | `/logout` | Session | Any user | State-changing GET | None | Fix planned |
| GET | `/home`, `/auth/change-default-password` | Session | Default-password middleware on home only | Livewire | None | Needs review |
| GET | `/calendar/test` | Session | None | None | None | Remove/secure |
| GET | `/profile/pdf/{employee}/qr-code`, `/bulk/qr-code` | Session | None | None | None | HIGH IDOR |
| GET | `/employee/*` page routes | Session | Caller expected | Livewire rules vary | None | Needs component audit |
| GET | `/employee/{hris_number}/dtr-report` | Session | None | Query parameters unvalidated | None | HIGH IDOR |
| GET | `/admin/*` | Session | Admin role + PASIG center | Object scope incomplete | None | Needs policy tests |
| GET | `/hr-admin/*` | Session | HR role + PASIG + permission | Object scope incomplete | None | Needs policy tests |
| GET | `/dapcc/admin/*` | Session | Admin role + DAPCC center | Object scope incomplete | None | Needs policy tests |
| GET | `/dapcc/hr-admin/*` | Session | HR role + DAPCC center | Object scope incomplete | None | Needs policy tests |
| POST | `/api/v1/login/new` | Public | GSD/special email/superadmin logic | Email/password; no throttle initially | None | Fix planned |
| POST | `/api/mvpool/login` | Public | Valid account only | Weak email rule; no throttle initially | None | Fix planned |
| POST | `/api/v1/mvpool/login` | Public | Valid account only | Manual checks; trusts token-state input | None | Fix planned |
| POST | `/api/get_info` | Sanctum | Any token | Encrypted HRIS; no explicit ability | None | Needs review |
| POST | `/api/time_entry`, `/api/v1/time_entry/capture` | Sanctum | Any token | Encrypted HRIS; no explicit ability | None | Needs review |
| POST | `/api/v1/attendance/time-capture` | Sanctum | Any token | Encrypted HRIS; center check | None | Needs review |
| POST | `/api/v1/attendance/dapcc/time-capture` | Sanctum | Any token | Encrypted HRIS; center check | None | Needs review |
| POST | `/api/v1/mvpool/time_entry/new` | Sanctum | Current token owner | Coordinates only `required` initially | None | Fix planned |
| POST | `/api/v1/mvpool/location` | Sanctum | Any token | No initial coordinate validation | None | Fix planned |
| GET | `/api/v1/mvpool/time_entries` | Sanctum | Current token owner | Date fixed to today | None | Needs tests |
| GET/POST | `/livewire/*` | Route/component dependent | Component dependent | CSRF on updates; component rules vary | None | Component audit ongoing |
| GET/POST | `/superadmin` auth/dashboard | Filament session | `superadmin` via panel access | Filament defaults | None | Needs tests |
| GET/POST | `/superadmin/departments*` | Filament session | Superadmin panel | Filament resource rules | None | Needs tests |
| GET/POST | `/superadmin/events*` | Filament session | Superadmin panel | Filament resource rules | None | Needs tests |
| GET/POST | `/superadmin/movs*` | Filament session | Superadmin panel | Filament resource rules | None | Needs tests |
| GET/POST | `/superadmin/official-times*` | Filament session | Superadmin panel | Filament resource rules | None | Needs tests |
| GET/POST | `/superadmin/permissions*` | Filament session | Superadmin panel | Filament resource rules | None | Needs tests |
| GET/POST | `/superadmin/time-entries*` | Filament session | Superadmin panel | Filament resource rules | None | Needs tests |
| GET/POST | `/superadmin/users*` | Filament session | Superadmin panel | Filament resource rules | None | Needs tests |
| GET | `/up` | Public | N/A | Framework liveness only | None | Safe minimal response |

## Remaining review scope

- Complete Livewire action-by-action validation and authorization trace.
- Confirm object-scope rules with domain owners before policy enforcement.
- Audit migrations for indexes/constraints using real query patterns.
- Run Composer/npm security and outdated checks.
- Run production cache/build checks and document queue/worker operations.
- Add feature tests for authentication, reset, API throttling, object access, and critical workflows.

## Production readiness verdict

**NOT READY FOR PRODUCTION**

This verdict remains until exposed credentials are rotated, HIGH access-control/authentication findings are resolved or explicitly accepted, and security-sensitive regression tests pass.
