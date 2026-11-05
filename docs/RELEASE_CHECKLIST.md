# Release checklist

Copy this list into the release pull request and tick every line.

## Code and tests
- [ ] CI green on MySQL, PostgreSQL, SQLite (tests, Pint, PHPStan, build)
- [ ] Translation gates green (keys, untranslated scan, Playwright FR walkthrough)
- [ ] Playwright e2e and offline scenario green
- [ ] Lighthouse: Accessibility ≥ 95, Best Practices ≥ 95, PWA pass on dashboard, students, portal
- [ ] `composer audit`, `npm audit --audit-level=high` clean

## Data safety
- [ ] Migrations reviewed: additive, reversible or documented; tested on a copy of production data
- [ ] `php artisan skuul:backup --with-files` run just before deploy; `skuul:check-backup` passes
- [ ] `php artisan skuul:rehearse-restore` passed within the last 7 days
- [ ] `php artisan skuul:release-readiness --check-backups` all PASS

## Product
- [ ] Manual scripts in `TESTING_QA.md` passed in EN and FR, desktop and phone
- [ ] Screenshots updated in `docs/screenshots/`
- [ ] User guides updated (EN/FR)
- [ ] `CHANGELOG_CAMPULY.md` entry in EN and FR

## Deploy
- [ ] Staging deployed and smoke-tested (`DEPLOYMENT.md` §9)
- [ ] Production deploy in a low-traffic window; maintenance message ready in EN/FR
- [ ] Desktop installers built, signed, checksums published (Phase 7+)
- [ ] Rollback plan written: previous image tag + database restore point
- [ ] Post-deploy: `/health`, sign-in, upload, notice, payment sandbox
