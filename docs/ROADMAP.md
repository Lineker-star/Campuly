# Campuly roadmap

Durations are planning estimates for one developer working with an AI coding agent; adjust after Phase 0. Each phase ends with a demo and a tagged pre-release.

| Phase | Branch | Outcome | Gate to exit | Estimate |
|---|---|---|---|---|
| 0 Foundation | `phase-0-foundation` | Baseline recorded, CI, brand locked, MySQL+PostgreSQL+SQLite, any storage, Docker | CI matrix green on 3 databases; container runs | 1–2 weeks |
| 1 Bilingual | `phase-1-i18n` | Every word EN/FR, per-user switch, local dates/money, bilingual PDFs | 3 translation gates green; zero English in FR walkthrough | 3–4 weeks |
| 2 UI/UX | `phase-2-ui` | Design system, new shell, mobile nav, role dashboards, data tables, polish | Lighthouse a11y ≥ 95 on key pages; 20 screenshots approved | 3–4 weeks |
| 3 API | `phase-3-api` | `/api/v1`, tokens, sync pull/push, OpenAPI | API tests incl. forbidden per role; idempotency proven | 2–3 weeks |
| 4 PWA | `phase-4-pwa` | Localised manifest, service worker, install button, push | Lighthouse PWA pass; installs on Windows + Android | 1 week |
| 5 Offline | `phase-5-offline` | IndexedDB store, outbox, sync worker, offline screens, conflicts | Offline school-day Playwright scenario green | 3–4 weeks |
| 6 Payments & messaging | `phase-6-payments-messaging` | MoMo/Orange Money, SMS/WhatsApp, chat | Sandbox payment end-to-end | 2–3 weeks |
| 7 Desktop | `phase-7-desktop` | Tauri client, NativePHP campus node, signed installers | Fresh-PC offline day test passes | 3–4 weeks |
| 8 Features | `phase-8-*` | 12 new features (see MASTER_PROMPT) | Each feature: tests, docs, EN/FR | 8–12 weeks |
| 9 Release | `phase-9-release` | Security, performance, guides, v1.0.0 | `RELEASE_CHECKLIST.md` complete | 2 weeks |

**Pilot plan:** run a pilot school after Phase 2 (web only, online), add offline after Phase 5, mobile money after Phase 6. Collect feedback from one francophone and one anglophone school.

**Definition of done (any step):** code + tests + EN/FR strings + responsive check + docs updated + changelog line + CI green.
