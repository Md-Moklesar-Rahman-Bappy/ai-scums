# Contributing to AI SCUMS

Thank you for your interest in contributing! This document describes how to work with the project effectively.

## Code Standards

- **PHP:** PSR-12, `declare(strict_types=1)` at the top of every PHP file, typed properties/returns where possible.
- **Architecture:** follow Controllers → Services → Repositories → Models. Keep controllers thin; put business logic in Services; authorization in Policies + `authorizeResource`.
- **Validation:** always use Form Requests (`app/Http/Requests`). Never validate in controllers.
- **Security:** never expose `institution_id`, `is_super_admin`, `is_active` in mass assignment; tenant is owned by `TenantScoped`.
- **Docs:** update `docs/` when changing architecture/AI/API; keep `CHANGELOG.md` current.

## Branch Strategy

- `main` — stable, protected.
- `develop` — integration branch.
- Feature branches: `feature/<short-description>`, e.g. `feature/tenant-scoped-binding`.
- Bug branches: `fix/<short-description>`.
- Hotfix: `hotfix/<version>`.

Open PRs against `develop` (or `main` for small fixes).

## Pull Requests

- Reference the related issue/audit finding.
- Ensure `php artisan test` passes (and add tests for new behavior).
- Keep PRs focused; one concern per PR.
- Describe the change, motivation, and verification steps.
- CI must be green before review.

## Commit Messages

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <subject>

<body> (optional)
<footer> (optional)
```

Types: `feat`, `fix`, `docs`, `refactor`, `test`, `chore`, `security`.
Example: `security(auth): throttle login and harden User fillable`.

## Issue Templates

When filing issues, include:
- **Environment:** PHP/Laravel/DB versions.
- **Steps to reproduce.**
- **Expected vs actual behavior.**
- **Security issues:** email the security contact in `SECURITY.md` (do **not** open a public issue).

## Code of Conduct

This project adheres to the [Contributor Covenant](CODE_OF_CONDUCT.md). Be respectful and constructive.
