# Offline-first design and sync protocol

Applies to the PWA (Phase 5), the desktop client and the campus node (Phase 7). One protocol, one set of conflict rules.

## 1. What works offline

| Screen | Offline mode |
|---|---|
| Attendance register (daily, per lesson) | Read + write |
| Mark entry (mark sheets) | Read + write |
| Record payment + provisional receipt | Write (append-only) |
| Library check-out / return | Write |
| Boarding roll call | Write |
| Student, staff, parent directory and profiles | Read |
| Timetable, personal week | Read |
| Notices, calendar | Read |
| Family portal: results, attendance, timetable, balance | Read |
| Everything else | "Available online only" panel (translated) |

Campus node (Phase 7): **all** screens work offline because the full app runs locally.

## 2. Client storage (browser and Tauri)

- IndexedDB via Dexie: `resources/js/offline/db.js`.
- Tables mirror server entities limited to the user's scope (school, period, own classes / own children).
- Sensitive fields encrypted with AES-GCM (WebCrypto); key derived at login, held in memory only; data wiped on logout or after 30 days without a successful sync.
- Storage quota check; warn at 80 %.

## 3. Server requirements (Phase 3)

Each syncable table: `uuid` (ULID, unique), `updated_at` (indexed), `deleted_at` (tombstone), `version` (int, +1 per update, trait `Syncable`). Table `sync_mutations` (`client_mutation_id` unique, user, device, entity, status, response, timestamps) for idempotency. Table `sync_devices` (device id, user, platform, last pull cursor, last push at).

## 4. Pull

`GET /api/v1/sync/pull?since=<cursor>&scopes=attendance,marks,directory,…`

Response:
```json
{
  "data": { "students": [ {…} ], "attendance_records": [ {…} ] },
  "deleted": { "students": ["01J…"], "notices": [] },
  "meta": { "cursor": "opaque-string", "has_more": true, "server_time": "2026-11-05T08:00:00+01:00" }
}
```
Client loops until `has_more=false`, then stores the cursor.

## 5. Push

`POST /api/v1/sync/push`
```json
{
  "device_id": "01J…",
  "mutations": [
    { "client_mutation_id": "01J…", "entity": "attendance_record", "uuid": "01J…", "op": "upsert",
      "base_version": 3, "payload": { "student": "01J…", "date": "2026-11-05", "lesson": null, "status": "absent" },
      "occurred_at": "2026-11-05T07:45:12+01:00" }
  ]
}
```
Each mutation runs through the normal Action class and policy. Response per item: `applied` (+ new version), `conflict` (+ server copy), or `rejected` (+ translated reason). Re-sending the same `client_mutation_id` returns the stored result (idempotent).

## 6. Conflict rules

| Entity | Rule |
|---|---|
| Attendance record | Last writer wins per (student, date, lesson) using `occurred_at`; every change kept in `attendance_changes` |
| Mark (grade entry) | Field-level: if server `version` ≠ `base_version` and the value differs → `conflict`; teacher resolves in a side-by-side dialog |
| Payment | Append-only; never overwritten; possible duplicate (same student, amount, channel, reference, date) flagged for the bursar |
| Provisional receipt | Number `<DEVICE>-<YEAR>-<#####>` offline, official number assigned on sync and shown on reprint |
| Library loan | First applied wins; second gets `rejected: copy already on loan` |
| Structure (years, classes, fees, users, roles) | Server only; read-only offline |

## 7. Sync worker behaviour

Triggers: app start, `online` event, every 2 minutes while online, Background Sync. Order: push first, then pull. Back-off: 5 s, 15 s, 1 min, 5 min (max). Header indicator: green online · amber syncing · grey offline · red conflicts. Drawer "Pending changes (n)" lists outbox items and conflicts.

## 8. Tests

Unit tests for the Syncable trait and conflict resolver; API tests for pull/push/idempotency/scoping/tombstones; Playwright offline school-day scenario (see `TESTING_QA.md`).
