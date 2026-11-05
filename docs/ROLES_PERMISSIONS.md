# Roles and permissions

Authorization = Spatie roles/permissions + 55 policy classes + organization permissions + campus roles. **Every** screen, Livewire action and API endpoint must call a policy.

## Built-in roles (demo database: 199 permissions)

| Role | Permissions in demo | Scope | Typical user |
|---|---|---|---|
| `platform-admin` | 199 (all) | Whole installation, all organizations | Campuly operator / IT lead |
| `organization-admin` | 6 + organization permissions | One organization and its campuses | Proprietor, group director |
| `admin` | 176 | One campus | Principal, secretary, school administrator |
| `teacher` | 28 | Own classes, subjects, registers, mark sheets | Teacher, class master |
| `accountant` (campus role) | 22 | Finance of one campus | Bursar, accountant |
| `librarian` (campus role) | 4 | Library | Librarian |
| `student` | 12 | Own records through the portal | Learner |
| `parent` | 14 | Own children through the portal | Parent / guardian |

Campus roles are created per school in **Roles** (`/dashboard/roles`); the accountant and librarian above are examples from the demo.

Organization permissions: read organization, manage organization, manage members, manage campuses, move students between campuses, read organization reports. Platform permissions: access all schools, access all organizations, manage platform.

## Module access matrix (target for v1)

R = read · W = create/edit · A = approve/publish · — = none · own = only own records/children

| Module | Platform/Org admin | School admin | Teacher | Accountant | Librarian | Parent | Student |
|---|---|---|---|---|---|---|---|
| Setup, years, classes | W | W | R | — | — | — | — |
| Students, parents | W | W | R (own classes) | R | R | own | own |
| Admissions | W | W | — | R | — | apply (Phase 8) | — |
| Attendance | R | W | W (own classes) | — | — | own | own |
| Gradebook, report cards | R | W, A | W (own subjects) | — | — | own | own |
| Timetable, syllabus | R | W | W (own) | — | — | own | own |
| Finance | R | R | — | W, A | — | own invoices, pay | own invoices |
| Library | R | R | R | — | W | own loans | own loans |
| Boarding | R | W | R | — | — | own | own |
| Care (incidents, support, health) | R | W (health separate permission) | W (incidents) | — | — | own (limited) | — |
| Notices, calendar | W | W | W (class notices) | W (finance notices) | — | R | R |
| Messaging (Phase 6) | W | W | W | W | W | W | W (if enabled) |
| Reports | R | W | own classes | finance | library | — | — |
| Roles, features, settings | W | W | — | — | — | — | — |

## Rules

1. Add new permissions in `database/seeders/PermissionSeeder.php` and assign them in `RoleSeeder.php`; never check role names in views — check permissions/policies.
2. API abilities mirror permissions; a token can never exceed its user.
3. Health records require their own permission even for admins.
4. Parents see only children linked to them; students only themselves; tests must prove cross-family isolation.
5. Organization admins cannot read campus data unless they also hold a campus membership (existing behaviour); keep it.
