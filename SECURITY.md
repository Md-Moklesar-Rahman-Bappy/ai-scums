# Security Policy

**Project:** AI-Powered Integrated Educational Management System (IEMS)
**Last Updated:** 2026-08-27

---

## Supported Versions

We provide security fixes for the following release lines:

| Version | Supported | Notes |
|---------|-----------|-------|
| Laravel 11.x (main) | ✅ Yes | Current actively maintained line |
| Prior major versions | ❌ No | Upgrade to the current line |

Only the latest patch release of the supported line receives security updates.
(Legacy note: the former `1.x` line is no longer supported.)

## Reporting a Vulnerability

**Please do NOT open a public GitHub issue for security vulnerabilities.**

Report privately to the security contact:

- **Name:** Md Moklesar Rahman
- **Email:** md.moklasarrahmanbappy@gmail.com
- **Phone:** +8801965031371
- **Subject:** `AI SCUMS Security Vulnerability Report`

Include:
1. Description of the vulnerability and impact.
2. Steps to reproduce (PoC if possible).
3. Affected version(s) and environment.
4. Suggested remediation (optional).

### What to expect
- **Acknowledgement** within 72 hours.
- **Triage & severity assessment** within 7 days.
- **Coordinated disclosure**: a fix and CVE/advisory will be prepared before public
  disclosure. We will credit reporters (with consent).
- We ask that you refrain from disclosing the issue publicly until a fix is released.

## Disclosure Policy
1. Reports are handled confidentially by the security owner above.
2. We validate and triage, assigning a severity (Critical/High/Medium/Low) per the
   OWASP risk rating.
3. Fixes are developed in a private branch; a CVE may be requested for significant issues.
4. The reporter is credited (unless anonymity is requested) upon coordinated public
   disclosure.
5. **Embargo:** public disclosure occurs only after a patched release is available,
   typically within 90 days of confirmation.

## Coordinated Disclosure & Safe Harbour
We support responsible disclosure. Good-faith security research conducted without
compromising user data or service availability will not result in legal action.

## Known Hardening Items (pre-production)

These are tracked and, where applicable, resolved in the audit documents in the
repository root (`AUTH_AUDIT_REPORT.md`, `RBAC_SECURITY_REPORT.md`,
`TENANT_SECURITY_REPORT.md`, etc.):
- Remove/secure seeded default credentials before production. ✅ Resolved — demo
  accounts are now environment-gated (see `PRODUCTION_SEEDER_POLICY.md`).
- Add rate limiting / account lockout to auth endpoints. ✅ Resolved —
  `RATE_LIMITING_DOCUMENTATION.md`.
- Tighten `User.$fillable` (remove `is_super_admin`, `institution_id`, `is_active`). ⚠️
  Partially mitigated: these are never mass-assigned from requests; recommend
  removing from `$fillable` in a future refactor.
- Force tenant assignment in the `TenantScoped` scope; make the tenant scope
  fail-closed. ✅ Tenant scope verified; see `TENANT_SECURITY_REPORT.md`.
- Add explicit `institution_id` filtering in every AI tool. ✅ Verified; tools are
  tenant-scoped via the global scope.
- Remove end-user external LLM provider selection (PII egress). ⚠️ Recommend
  restricting provider selection to administrators.
- Enable `SESSION_SECURE_COOKIE` / `SESSION_ENCRYPT` in production. ✅ Documented in
  `SESSION_SECURITY_REPORT.md` (set in production env).

## Security Best Practices for Deployers
- Set `APP_DEBUG=false`, a strong unique `APP_KEY`, `SESSION_SECURE_COOKIE=true`,
  `SESSION_ENCRYPT=true`.
- Use the offline `MockProvider` or a self-hosted `Local` provider in
  sensitive/regulated environments.
- Restrict cloud provider selection to administrators; minimize PII sent to
  third-party LLMs; ensure DPAs/consent for FERPA/GDPR.
- Define retention & access control for `ai_audit_logs` (contains queries/
  responses/PII).

## Security Hardening Highlights (this release)
- Email verification enforced on all modules (`verified` middleware).
- Login brute-force protection (5 attempts / 60s, IP+user, configurable).
- Strong password policy (12+ chars, mixed case, number, symbol, uncompromised).
- Centralised, tamper-evident audit logging of all auth/tenant/rbac events.
- Production-safe seeders (no demo accounts in prod).
- Tenant isolation via mandatory global scope + policies.
- Parent/student scoped access (no cross-student/family data leakage).
