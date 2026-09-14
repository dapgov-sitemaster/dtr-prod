You are acting as a **senior Laravel architect, application security engineer, QA engineer, and DevOps reviewer**.

I have an **old Laravel application** that is currently working, but I want to modernize it carefully without breaking existing business functionality.

Your goal is to perform a **complete application review, modernization, security audit, consistency cleanup, endpoint validation, testing, and production-readiness assessment**.

Do not make unnecessary architectural changes just for the sake of modernization. Preserve existing business behavior unless a change is required for security, correctness, maintainability, compatibility, or performance.

# Primary Objectives

1. Audit the entire Laravel application.
2. Identify the Laravel and PHP versions currently used.
3. Determine whether framework and dependency upgrades are required.
4. Review every route, API endpoint, controller, middleware, model, service, request validator, job, command, event, listener, and authentication flow.
5. Detect security vulnerabilities.
6. Fix inconsistent implementation patterns.
7. Improve validation, authorization, error handling, logging, and API responses.
8. Remove dead or duplicate code where safe.
9. Add or improve automated tests.
10. Verify production configuration and deployment readiness.
11. Preserve existing functionality and database compatibility unless explicitly documented otherwise.

# Important Working Rules

Before modifying anything:

- Inspect the project structure.
- Read `composer.json`.
- Read `package.json` if present.
- Inspect `.env.example`.
- Inspect `config/`.
- Inspect `routes/`.
- Inspect authentication and authorization.
- Inspect middleware.
- Inspect database migrations.
- Inspect models and relationships.
- Inspect controllers.
- Inspect Form Request classes.
- Inspect policies and gates.
- Inspect jobs, queues, commands, events, listeners, notifications, mail classes, and scheduled tasks.
- Inspect tests.
- Identify integrations with external services.

Create an initial assessment before performing major changes.

Do not blindly upgrade Laravel.

Determine:

- Current PHP version requirement
- Current Laravel version
- Current dependency versions
- Deprecated packages
- Unsupported packages
- Upgrade risks
- Breaking changes
- Database compatibility concerns

If an upgrade is appropriate, propose a safe staged migration path.

# PHASE 1 — Application Inventory

Create an application inventory including:

- Laravel version
- PHP version
- Database engine/version
- Authentication mechanism
- Session authentication
- API authentication
- OAuth/SSO integrations
- Queue system
- Cache system
- Mail system
- File storage
- Scheduled tasks
- External APIs
- Webhooks
- Payment integrations if any
- Third-party packages
- Frontend framework
- Build system
- Deployment assumptions

Map the application's major modules and business functions.

# PHASE 2 — Complete Endpoint Audit

Inspect ALL routes from:

- `routes/web.php`
- `routes/api.php`
- console routes
- custom route files
- package-defined routes where relevant

Generate a complete endpoint inventory containing:

- HTTP method
- URI
- Route name
- Controller/action
- Middleware
- Authentication required
- Authorization required
- Request validation
- Rate limiting
- CSRF applicability
- Expected response
- Potential security issue
- Test coverage

Specifically check for endpoints that accidentally allow:

- anonymous access
- privilege escalation
- unauthorized object access
- IDOR/BOLA
- mass assignment
- unrestricted file upload
- destructive GET requests
- missing authorization
- missing validation
- excessive information exposure
- unrestricted enumeration
- missing rate limits

Every endpoint that accesses a user-specific or organization-specific resource must verify that the authenticated user is authorized to access that specific resource.

Never assume authentication alone is sufficient authorization.

# PHASE 3 — Authentication and Authorization Review

Audit:

- Login
- Logout
- Registration
- Password reset
- Password confirmation
- Email verification
- Remember-me functionality
- Session handling
- Session expiration
- OAuth
- Google/Microsoft authentication if present
- Laravel Sanctum
- Laravel Passport
- JWT if present
- API tokens
- Personal access tokens

Verify:

- passwords are securely hashed
- sensitive tokens are never logged
- sessions are regenerated after authentication
- logout invalidates sessions appropriately
- account enumeration is minimized
- password reset tokens expire
- authorization is consistently enforced

Audit all:

- policies
- gates
- middleware
- role checks
- permission checks

Replace scattered role checks with policies or centralized authorization where doing so improves consistency without unnecessary redesign.

Look specifically for patterns such as:

```php
if ($user->role === 'admin')
```

being implemented inconsistently throughout controllers.

Where appropriate, centralize authorization.

# PHASE 4 — Input Validation

Review every endpoint that accepts user-controlled input.

Validation should preferably use Laravel Form Request classes for non-trivial endpoints.

Check:

- required fields
- types
- maximum lengths
- minimum lengths
- date validation
- numeric ranges
- enums
- foreign-key existence
- file extensions
- MIME types
- file size
- image dimensions where appropriate
- arrays
- nested arrays
- URL validation
- email validation
- unique constraints

Never trust:

- request IDs
- user IDs
- role IDs
- prices
- calculated totals
- organization IDs
- ownership fields
- status fields

when those should be determined server-side.

# PHASE 5 — OWASP Security Audit

Review the application against current OWASP web application risks.

Pay particular attention to:

## Broken Access Control

Check for:

- IDOR
- BOLA
- privilege escalation
- missing ownership checks
- role bypasses
- organization/department boundary bypasses

## Injection

Check for:

- SQL injection
- raw SQL
- `DB::raw`
- `whereRaw`
- `orderByRaw`
- dynamic SQL
- shell commands
- command injection
- template injection

Use parameterized queries and Laravel's query builder/Eloquent whenever possible.

## XSS

Inspect:

- Blade templates
- `{!! !!}`
- raw HTML rendering
- rich-text fields
- user comments
- descriptions
- uploaded HTML/SVG content

Sanitize untrusted HTML where required.

## CSRF

Verify state-changing web routes have proper CSRF protection.

Do not apply CSRF assumptions incorrectly to stateless APIs.

## SSRF

Inspect any functionality that accepts URLs or retrieves remote resources.

## File Uploads

Check:

- filename handling
- MIME validation
- extension validation
- executable file risks
- storage location
- public exposure
- path traversal
- overwrite risks

Use generated filenames instead of trusting user filenames where appropriate.

## Sensitive Data Exposure

Check for:

- secrets in source code
- credentials
- private keys
- API tokens
- `.env` committed to Git
- stack traces exposed publicly
- sensitive values returned by APIs
- hidden model attributes
- logs containing credentials or tokens

## Mass Assignment

Review every model's:

```php
$fillable
```

and:

```php
$guarded
```

configuration.

Never allow users to mass-assign sensitive fields such as:

- role
- permissions
- ownership
- approval status
- account balance
- administrative flags

unless specifically protected by authorization and business rules.

# PHASE 6 — Database and Eloquent Review

Inspect:

- model relationships
- foreign keys
- indexes
- unique constraints
- cascading deletes
- soft deletes
- timestamps
- casts
- accessors
- mutators
- scopes

Look for:

- N+1 queries
- unnecessary repeated queries
- missing eager loading
- incorrect relationships
- unsafe raw queries
- race conditions
- duplicate records
- missing transactions

Use database transactions for multi-step operations that must succeed or fail atomically.

Do not make destructive schema changes without documenting the reason and migration strategy.

# PHASE 7 — Controller and Architecture Cleanup

Identify:

- fat controllers
- duplicated business logic
- repeated validation
- repeated authorization
- repeated queries
- inconsistent API responses
- excessively long methods

Refactor where beneficial into:

- services
- actions
- policies
- Form Requests
- resource classes
- reusable domain logic

Do not over-engineer simple functionality.

# PHASE 8 — API Consistency

Standardize API behavior.

Successful responses should follow a predictable structure where practical, for example:

```json
{
    "success": true,
    "message": "Record retrieved successfully.",
    "data": {}
}
```

Errors should also be predictable.

Use appropriate status codes:

- `200` successful request
- `201` resource created
- `204` successful request with no content
- `400` malformed request
- `401` unauthenticated
- `403` unauthorized
- `404` not found
- `409` conflict
- `422` validation failure
- `429` rate limited
- `500` unexpected server failure

Do not expose internal exceptions or stack traces to clients.

Use Laravel API Resources where they provide value.

# PHASE 9 — Error Handling and Logging

Review global exception handling.

Ensure:

- users receive safe error messages
- developers receive useful logs
- sensitive information is excluded from logs
- API errors are consistent
- authentication failures are handled correctly
- authorization failures return `403`
- missing resources return `404`

Add contextual logging for important failures.

Do not log:

- passwords
- access tokens
- refresh tokens
- API secrets
- authorization headers
- sensitive personal information unless absolutely necessary

# PHASE 10 — Rate Limiting and Abuse Protection

Identify endpoints that require rate limiting, particularly:

- login
- registration
- password reset
- OTP
- email verification
- search
- public APIs
- file uploads
- resource creation
- expensive reports
- external API calls
- webhook endpoints

Apply reasonable Laravel rate-limit policies.

Prevent obvious brute-force and resource-exhaustion attacks.

# PHASE 11 — Security Headers and Web Configuration

Review recommendations for production HTTP headers including:

- Content-Security-Policy
- X-Content-Type-Options
- Referrer-Policy
- Permissions-Policy
- HSTS when HTTPS is fully enforced
- clickjacking protection

Determine whether these should be handled by Laravel or the reverse proxy such as Nginx.

Avoid duplicate or conflicting configuration.

# PHASE 12 — Dependency Security

Audit Composer dependencies.

Run or recommend checks equivalent to:

```bash
composer audit
```

Review:

- abandoned packages
- unsupported packages
- known vulnerabilities
- unnecessary dependencies
- incompatible versions

Also inspect frontend dependencies if present.

Run or recommend:

```bash
npm audit
```

but do not blindly apply breaking dependency updates.

# PHASE 13 — Automated Testing

Build or improve the test suite.

Prioritize Feature tests for HTTP behavior.

Every important endpoint should test at minimum:

- successful request
- unauthenticated request
- unauthorized request
- invalid input
- missing resource
- ownership restrictions
- expected database change
- expected response structure

Security-sensitive endpoints should also test privilege escalation attempts.

Example:

User A must not be able to retrieve, edit, approve, or delete User B's records unless explicitly authorized.

Add Unit tests for complex business logic where appropriate.

Use Laravel factories and seeders appropriately.

# PHASE 14 — Regression Testing

Before considering a refactor complete, verify that existing business functionality remains intact.

For each module:

1. Identify existing behavior.
2. Create tests describing that behavior.
3. Perform refactoring.
4. Run the tests.
5. Resolve regressions.

Treat existing functionality as a contract unless it is insecure or demonstrably incorrect.

# PHASE 15 — Production Configuration Audit

Review `.env.example` and production configuration requirements.

Check:

```env
APP_ENV=production
APP_DEBUG=false
```

Verify:

- secure `APP_KEY`
- HTTPS
- trusted proxy configuration
- secure cookies
- session configuration
- cache
- Redis if used
- queue worker configuration
- mail configuration
- database SSL where applicable
- log rotation
- storage permissions
- public storage configuration

Check that development tooling is not exposed in production.

# PHASE 16 — Laravel Production Optimization

Verify applicability of:

```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Detect anything preventing route or config caching.

Never use `env()` directly outside configuration files unless there is a very good reason.

# PHASE 17 — Queue and Scheduler Review

If queues are used, check:

- failed jobs
- retry configuration
- timeout
- idempotency
- duplicate processing
- queue monitoring
- worker restart deployment process

Review Laravel scheduler configuration.

Confirm the production server runs:

```bash
php artisan schedule:run
```

through cron or the appropriate scheduler mechanism.

# PHASE 18 — Performance Review

Identify:

- N+1 queries
- expensive SQL
- excessive database calls
- large result sets
- endpoints without pagination
- blocking external API requests
- unnecessary file operations
- unbounded loops
- excessive memory usage

Add pagination where appropriate.

Recommend indexes only after identifying the related query patterns.

# PHASE 19 — Code Consistency

Standardize:

- naming
- controller patterns
- service patterns
- validation
- authorization
- status enums
- API responses
- exception handling
- date/time handling
- database querying
- formatting

Use PHP enums where appropriate and supported by the project's PHP version.

Remove obvious:

- commented-out code
- debugging statements
- `dd()`
- `dump()`
- accidental console output
- unused imports
- dead classes

Do not delete questionable legacy functionality until its purpose has been investigated.

# PHASE 20 — Static Analysis and Formatting

Where compatible with the project, consider:

- Laravel Pint
- PHPStan
- Larastan

Use them incrementally.

Do not create hundreds of unrelated changes merely to satisfy formatting tools before understanding the application.

# PHASE 21 — Deployment Safety

Create a production deployment checklist that includes:

- database backup
- application backup
- dependency installation
- migration review
- maintenance mode decision
- migrations
- cache clearing
- cache rebuilding
- queue restart
- scheduler verification
- permissions
- health checks
- rollback procedure

For database changes, clearly state whether migrations are backward compatible.

# PHASE 22 — Health Checks

Add or validate a health endpoint where appropriate.

It should verify only what is safe and useful, such as:

- application responsiveness
- database availability
- cache availability if required
- queue dependency availability if required

Do not expose:

- secrets
- database credentials
- internal stack traces
- detailed infrastructure information

# PHASE 23 — Final Security Verification

Perform an adversarial review.

Attempt to identify scenarios where a malicious user could:

- access another user's record
- modify another user's record
- become an administrator
- approve their own restricted request
- manipulate ownership
- change protected status fields
- bypass validation
- upload executable content
- enumerate users
- brute-force authentication
- exploit unsafe redirects
- inject SQL
- inject HTML/JavaScript
- abuse resource-intensive endpoints
- trigger unintended jobs
- expose sensitive application data

Document and remediate each credible issue.

# Required Output

Maintain a running audit report with these severity levels:

- CRITICAL
- HIGH
- MEDIUM
- LOW
- INFORMATIONAL

For every issue provide:

**Issue:**  
Short description.

**Location:**  
File, class, function, route, or endpoint.

**Risk:**  
What could happen.

**Evidence:**  
Relevant code or behavior.

**Recommended Fix:**  
What should change.

**Status:**  
Not started / Fixed / Needs review / Accepted risk.

# Endpoint Audit Table

Maintain an endpoint matrix with:

| Method | Endpoint | Authentication | Authorization | Validation | Rate Limit | Tests | Status |
|---|---|---|---|---|---|---|---|

Do not mark an endpoint secure simply because it uses `auth` middleware.

Confirm object-level authorization.

# Change Management

Make changes in small logical batches.

For every major change:

1. Explain the problem.
2. Explain why the current implementation is risky or inconsistent.
3. Implement the smallest safe fix.
4. Add or update tests.
5. Run relevant tests.
6. Report the result.

Avoid combining unrelated changes.

# Commands to Consider

Depending on the application, inspect output from:

```bash
php artisan about
php artisan route:list
php artisan migrate:status
php artisan config:show
composer show
composer outdated
composer audit
php artisan test
```

If available:

```bash
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
```

For frontend applications:

```bash
npm audit
npm run build
```

Do not run destructive commands without first analyzing their impact.

# Definition of Done

The application should only be considered production-ready when:

- all important endpoints have been reviewed
- authentication is verified
- object-level authorization is verified
- input validation is present
- major security findings are resolved
- critical/high dependency vulnerabilities are resolved or explicitly documented
- API behavior is consistent
- production configuration is secure
- `APP_DEBUG=false`
- automated tests pass
- critical workflows have regression tests
- unauthorized-access tests pass
- database migrations have been reviewed
- production build succeeds
- queue/scheduler configuration is verified
- logging is configured safely
- health checks work
- backup and rollback procedures are documented
- no known CRITICAL findings remain
- no unexplained HIGH findings remain

# Final Deliverables

At completion, provide:

1. **Executive Summary**
2. **Current Architecture Summary**
3. **Laravel/PHP Upgrade Recommendation**
4. **Complete Endpoint Security Matrix**
5. **Security Findings**
6. **Fixed Issues**
7. **Remaining Risks**
8. **Code Consistency Improvements**
9. **Database Improvements**
10. **Performance Improvements**
11. **Automated Test Coverage**
12. **Dependency Audit**
13. **Production Configuration Review**
14. **Deployment Checklist**
15. **Rollback Plan**
16. **Recommended Future Improvements**
17. **Final Production Readiness Verdict**

The final verdict must be one of:

**NOT READY FOR PRODUCTION**

**READY WITH CONDITIONS**

**READY FOR PRODUCTION**

Never claim the application is secure or production-ready without evidence from the code, configuration, dependency audit, and test results.

Begin by inspecting the repository and producing the **initial application inventory, Laravel/PHP compatibility assessment, route inventory, and prioritized security findings** before making broad changes.