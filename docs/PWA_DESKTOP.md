# PWA, desktop client and campus node

## 1. PWA (Phases 4–5)

Already in the code: `public/manifest.webmanifest` (name, colours, icons, maskable icons, shortcuts), icons in `public/img/brand/`, `<x-partials.brand-head />` linking the manifest and theme colours.

To build:
1. Localised manifest served by a controller (name/description/shortcuts in EN/FR).
2. Service worker with `vite-plugin-pwa` (Workbox, `injectManifest`): precache shell, fonts and brand icons; network-first for pages and API with offline fallback; per-user cache names cleared on logout.
3. Offline page `resources/views/offline.blade.php` with the Campuly logo, bilingual.
4. Install button (`beforeinstallprompt`), iOS instructions, hide when installed (`display-mode: standalone`).
5. Update toast when a new service worker is waiting.
6. Web Push with VAPID keys (`VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`).

Install test matrix: Chrome and Edge on Windows 10/11, Chrome on Android 10+, Safari on iOS 16.4+ (Add to Home Screen), Chrome on macOS and Linux.

## 2. Desktop client — Tauri 2 (Phase 7, Mode 1)

- Folder `desktop/client/`. Loads the PWA from the configured server URL; the service worker and IndexedDB give offline mode.
- First run: "Server address" screen (default `https://app.campuly.com`), stored in app config.
- Native extras: tray icon, native notifications, silent printing for receipts/report cards, auto-updater (GitHub Releases), deep links `campuly://`.
- Builds: Windows `.msi` and `.exe` (NSIS), macOS `.dmg`, Linux `.AppImage` / `.deb`. Icons generated from `public/img/brand/icon-1024.png` (`tauri icon`).
- Size target < 15 MB installer.

## 3. Campus node — NativePHP Desktop (Phase 7, Mode 2)

- Runs the **whole Laravel app** locally with a bundled PHP binary and SQLite, inside an Electron window.
- Prerequisite: all migrations and queries work on SQLite (STEP 0.4).
- First run wizard: connect to cloud → sign in → choose school → initial pull → ready.
- Background job every 2 minutes: push outbox, pull changes (same protocol as the PWA; the node is one device).
- LAN mode (optional): serve on `0.0.0.0:8000` so other school computers use the node in a browser during outages; firewall prompt explained in the wizard.
- Local encrypted backups daily to a chosen folder or USB drive.
- Package versions: NativePHP Desktop v2.x declares support for Laravel 10–13 on Packagist (v2.4.0, Oct 2026); its documentation support table lists Laravel 11–12 — confirm with the installed version before starting.

## 4. Choosing per school

| Situation | Recommendation |
|---|---|
| Good internet, staff use phones/laptops | PWA only |
| Bursar/secretary on one office PC, unstable internet | Desktop client |
| Rural school, internet down for days, several PCs | Campus node + LAN mode |

## 5. Signing and distribution

Windows: code-signing certificate or Azure Trusted Signing (avoid SmartScreen warnings). macOS: Developer ID + notarisation. Releases built by GitHub Actions on tags `v*`; checksums published.
