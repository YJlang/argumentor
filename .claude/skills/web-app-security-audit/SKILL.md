---
name: web-app-security-audit
description: Use when auditing a PHP/JavaScript/HTML web application for security vulnerabilities. Covers configuration, authentication, authorization, input validation, XSS, API security, HTTP headers, and dependency scanning. Produces a severity-rated audit report with actionable fixes.
metadata:
  portable: true
  compatible_with:
  - Claude
  - Codex
---

# Web Application Security Audit

Systematic security audit for PHP/JavaScript/HTML web applications. Scans 8 security layers, produces a structured report with severity-rated findings and actionable fix recommendations.

**Core Principle:** Scan everything before fixing anything. Full picture first, then targeted remediation.

**Scope:** Web application code only (PHP, JS, HTML, CSS).

## When to Use

- Before deploying a web application to production
- After implementing major features or modules
- Periodic security review (quarterly recommended)
- After discovering a vulnerability in one area (audit all areas)
- When onboarding a new project or inheriting a codebase

## Audit Workflow

### Phase 1: Discovery
1. App structure: entry points (`public/*.php`, `api/*.php`), config (`.env`, `config/`, `php.ini`, `.htaccess`), routes, middleware.
2. Auth flows: login/logout/register, sessions (`session_start`, JWT), password handling.
3. Data flows: DB queries (PDO, mysqli), external APIs (curl), uploads, output points (echo, templates).

### Phase 2: Scan (8 Layers)
- Batch 1 (independent): Configuration, HTTP Headers, Dependencies
- Batch 2 (auth-related): Auth & Sessions, Authorization
- Batch 3 (data flow): Input Validation, Output & XSS, API Security

### Phase 3: Report
Generate `docs/security-audit/YYYY-MM-DD-audit.md`, sorted by severity.

## Severity Classification

| Severity | Criteria | Example |
|----------|----------|---------|
| CRITICAL | Exploitable now, data breach risk | SQL injection, hardcoded credentials, no auth on admin |
| HIGH | Exploitable with effort, significant impact | Missing session regeneration, weak password hashing |
| MEDIUM | Requires specific conditions | Missing CSRF on non-critical form, verbose errors |
| LOW | Minor weakness, defense-in-depth | Missing security header, loose CORS |
| INFO | Best practice recommendation | Missing SRI on CDN, could add rate limiting |

## Layer 1: Configuration
```
display_errors = On                  → CRITICAL: errors shown to users
allow_url_include = On               → CRITICAL: remote file inclusion
.env in webroot                      → CRITICAL: secrets accessible
.env in .gitignore                   → must be ignored
phpinfo() calls                      → HIGH: full config exposed
hardcoded password / api_key / token → HIGH: must be in env, not in code
```

## Layer 2: Authentication & Sessions
```
session.cookie_httponly / use_strict_mode / use_only_cookies  → Must be 1
session_regenerate_id after login    → CRITICAL if missing
password_hash with PASSWORD_ARGON2ID → Required
password_verify usage                → Required (not manual comparison)
md5()/sha1() for passwords           → CRITICAL: weak hashing
Login rate limiting / lockout        → HIGH/MEDIUM if missing
random_int/random_bytes for tokens   → Required (not rand/mt_rand)
Hardcoded encryption keys            → CRITICAL: use env/vault
```

## Layer 3: Authorization & Access Control
```
$_GET['id'] without ownership check  → CRITICAL (IDOR)
Admin routes without auth middleware → CRITICAL
API endpoints without authentication → HIGH
Cross-tenant data access prevention  → CRITICAL if missing
```

## Layer 4: Input Validation
```
$_GET / $_POST without filter_var/validation → HIGH
String concatenation in SQL queries          → CRITICAL
Non-parameterized queries                    → CRITICAL
== instead of === (type juggling)            → HIGH ("0e123"=="0e456" is true)
unserialize() on user/external data          → CRITICAL (object injection/RCE)
eval() with any variable input               → CRITICAL
exec/system/shell_exec/passthru              → CRITICAL (command injection)
include/require with user-controlled path    → CRITICAL (file inclusion)
File uploads: no MIME validation / in webroot → HIGH
```

## Layer 5: Output Encoding & XSS
```
echo $_GET / echo $_POST                  → CRITICAL
echo $variable without htmlspecialchars   → HIGH (if user-sourced)
Missing ENT_QUOTES in htmlspecialchars    → MEDIUM
No CSP header                             → MEDIUM
CSP with 'unsafe-eval'                    → HIGH
```

## Layer 6: API Security
```
State-changing endpoints without CSRF token → HIGH
CSRF token not validated server-side        → CRITICAL
Login/password-reset without rate limiting  → HIGH
Stack traces / DB errors in API responses   → HIGH / CRITICAL
Access-Control-Allow-Origin: *              → HIGH
Credentials with wildcard origin            → CRITICAL
Webhook endpoints without signature verify  → CRITICAL
```

## Layer 7: HTTP Security Headers
```
Strict-Transport-Security: max-age=31536000; includeSubDomains  → HIGH if missing
Content-Security-Policy                                          → MEDIUM if missing
X-Content-Type-Options: nosniff                                  → LOW if missing
X-Frame-Options: DENY (or SAMEORIGIN)                            → MEDIUM if missing
Referrer-Policy: strict-origin-when-cross-origin                 → LOW if missing
X-Powered-By / Server version                                   → LOW if present (remove)
```

## Layer 8: Dependencies & Supply Chain
```
composer audit / npm audit advisories    → Severity from advisory
composer.lock / package-lock committed   → HIGH if missing
CDN scripts without SRI integrity attr   → MEDIUM
.env.example with real values            → HIGH
```

## Fix Workflow
1. Present summary to user (counts by severity)
2. Work through CRITICAL findings first, then HIGH, MEDIUM, LOW
3. For each finding: show location, explain risk, apply fix, verify
4. Re-run affected layer checks after fixes
5. Update report with fix status

## Anti-Patterns
- Scanning only one layer and declaring the app secure
- Fixing issues before completing the full scan (lose context)
- Rating everything as CRITICAL (desensitizes the team)
- Ignoring INFO findings (they become vulnerabilities when combined)
- Skipping the dependency audit (most common attack vector)

Acknowledgement: original skill shared by Peter Bamuhigire, techguypeter.com.
