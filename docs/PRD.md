# Campuly — Product requirements document (PRD)

Version 1.0 · November 2026 · Owner: Megaj Technologies

## 1. Vision

Give every school in Cameroon and Africa one bilingual platform to **learn, interact and excel**: run the school, teach, assess, collect fees and keep families informed — even when the internet or electricity is unreliable.

## 2. Problems

1. Schools juggle paper registers, Excel mark sheets, receipt books and WhatsApp groups; information is late and errors are frequent.
2. Most tools are English-only or French-only; Cameroon needs both, often in the same school.
3. Internet is intermittent and expensive; cloud-only tools stop working when it matters.
4. Families pay by MTN MoMo or Orange Money and expect instant receipts.
5. Official report cards (séquences, trimestres, coefficients; GCE grading) are produced by hand.

## 3. Users (personas)

| Persona | Needs |
|---|---|
| Proprietor / Principal / Director | Overview of enrolment, results, attendance, finances across campuses; decisions |
| Administrator / Secretary | Admissions, student records, classes, timetables, documents |
| Teacher / Class master | Registers, marks, syllabus progress, homework, messages with parents |
| Bursar / Accountant | Fees, invoices, payments (cash, MoMo, Orange Money, bank), receipts, reports |
| Librarian, Boarding master, Nurse/Counsellor | Their module, quickly, often on a phone |
| Parent / Guardian | Results, attendance, fees and payment, notices, talk to the school — in their language |
| Student | Timetable, results, homework, lessons, library |
| Campuly operator (SaaS) | Onboard schools, subscriptions, support, monitoring |

## 4. Scope

### 4.1 Existing modules (keep and translate)
Organizations/campuses, setup, academic structure, people, admissions, enrolment lifecycle, teaching and course offerings, assessment and report cards, exams, attendance, timetables, syllabi and lesson notes, finance and ledger, financial reports, student care, staff operations, calendar and notices, boarding, library, facilities, programmes, graduation plans, family portal, operations (backups, health, audit). Details in `AUDIT_REPORT.md`.

### 4.2 New capabilities
- Full English ⇄ French, per user, instant switch (Phase 1)
- Campuly design system and role dashboards (Phase 2)
- REST API + sync protocol (Phase 3)
- Installable PWA, push notifications (Phase 4)
- Offline-first core screens with automatic sync (Phase 5)
- MTN MoMo / Orange Money collection, SMS/WhatsApp, in-app messaging (Phase 6)
- Desktop client and offline campus node (Phase 7)
- Cameroon report cards, homework, quizzes/CBT, e-learning library, ID cards/QR, admissions portal, HR/payroll lite, transport, analytics, AI assistant, SaaS mode, data rights (Phase 8)

## 5. Non-functional requirements

| Area | Requirement |
|---|---|
| Languages | 100 % of UI, documents, emails, SMS in EN and FR; switch < 1 s |
| Responsiveness | Works from 360 px phones to 1920 px screens; touch targets ≥ 44 px |
| Performance | Main pages < 300 ms server time with 10 000 students; first load < 3 s on 3G for the PWA shell; JS bundle < 300 KB gzip for the shell |
| Offline | Attendance, marks, payments, directory, timetable, notices, library usable offline; sync within 2 min of reconnecting; zero data loss |
| Availability | 99.5 % monthly for the cloud; daily encrypted backups, RPO ≤ 24 h, RTO ≤ 4 h |
| Security | Role-based access through policies; 2FA for admins/accountants; encrypted backups and offline store; OWASP Top 10 reviewed |
| Accessibility | WCAG 2.2 AA |
| Portability | MySQL, PostgreSQL (Supabase/Neon), SQLite; local or S3-compatible storage; Docker or classic PHP hosting |
| Privacy | Data per school isolated; parent sees only own children; export and deletion on request; health data separately permissioned |

## 6. Success metrics (first school year)

- 90 % of daily registers taken in Campuly by week 4
- Report cards produced in Campuly for 100 % of classes at end of first term
- ≥ 50 % of fee payments recorded the same day; ≥ 30 % via mobile money by term 2
- ≥ 60 % of parents active on the portal or PWA monthly
- Fewer than 1 sync conflict per 1 000 offline changes needing manual resolution

## 7. Out of scope for v1

Full LMS with live video classes, national exam registration integrations, native iOS/Android store apps (the PWA and NativePHP Mobile can follow in v2).
