# Campuly security guide

Campuly holds children's records, health notes and money. Treat every change as security-relevant. (Upstream reporting policy: `../SECURITY.md`; Campuly reports go to the project owner.)

## Baseline already in the code

- Invite-only accounts (no public sign-up), 72 h invitation expiry, email-domain checks.
- Fortify/Jetstream: password rules, 2FA (TOTP), passkeys, password-change enforcement, inactive-account blocking.
- 55 policies, Spatie permissions, campus memberships, separate health-record access.
- `SendSecurityHeaders` middleware, CSRF on all forms, encrypted cookies.
- Encrypted backups (refuse to run without `BACKUP_KEY`), restore rehearsals, audit events.
- Demo mode blocks deletions.

## Rules for every change

1. Authorize in the policy, never only in the view.
2. Validate with Form Requests or Livewire rules; never trust IDs from the client — load through scoped relations (`current_school()->students()->findOrFail()`).
3. No raw SQL with user input; use bindings.
4. Escape output (`{{ }}`); `{!! !!}` only for sanitized rich text (notices already sanitized).
5. Files: validate MIME and size; store private by default; serve through signed/authorized routes.
6. Secrets only in env; never log tokens, passwords, payment payloads or health data.
7. Rate-limit login, password reset, API, webhooks, search.
8. Webhooks (payments, SMS): verify signatures, idempotency keys, replay window ≤ 5 min.
9. Offline data: encrypt IndexedDB payloads, wipe on logout/30 days; campus node encrypts its SQLite backups.
10. Multi-tenant isolation: every query scoped to the school; tests for cross-school and cross-family access.

## Production checklist

- [ ] HTTPS everywhere, HSTS, `SESSION_SECURE_COOKIE=true`
- [ ] `APP_DEBUG=false`, log viewer restricted to platform admins
- [ ] 2FA required for platform admins, school admins, accountants
- [ ] CSP header (Phase 9) allowing only self + payment provider domains
- [ ] Database user with least privileges; no public DB port
- [ ] S3 bucket private; access keys server-side only (Supabase S3 keys bypass RLS)
- [ ] `composer audit` and `npm audit --audit-level=high` clean in CI
- [ ] Daily backups to a different provider/region; monthly restore drill
- [ ] Admin actions visible in the audit log
- [ ] Compliance with Cameroon Law No. 2024/017 of 23 December 2024 on personal data protection (transition period ended 23 June 2026): privacy notice for parents and staff, consent where required, data-subject rights (access, correction, deletion), and the **prior authorisation the law requires for processing data about minors** from the Personal Data Protection Authority. Have a lawyer confirm what applies to each school and to Campuly as a service provider.

## Incident response (short)

1. Contain: revoke tokens (`personal_access_tokens`), rotate `APP_KEY` only with a re-encryption plan, rotate DB/S3/payment keys.
2. Assess: audit log, server logs, provider logs.
3. Inform the school owner within 24 h; follow legal notification duties.
4. Fix, add a test, write a post-mortem in `docs/incidents/`.
