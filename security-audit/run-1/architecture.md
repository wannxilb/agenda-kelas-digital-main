# Security Architecture Summary

Target: `C:\Users\RIDWAN\agenda-kelas-digital`

Output directory: `security-audit/run-1`

Prior runs: none found under the default audit location for this repository. Coverage should improve with additional independent runs.

## Application

Agenda Kelas Digital is a Laravel 12 web application for school class agenda, attendance, grading, reporting, and institution administration. It is comparable to school information systems and classroom attendance portals: authenticated users operate through role-scoped web routes, and most high-value data is student identity, student attendance, teacher schedules, grades, agenda entries, and media evidence.

## Stack

- PHP 8.2 and Laravel 12.
- Blade views and Vite/Tailwind frontend assets.
- Spatie Laravel Permission for roles.
- Eloquent models with institution and academic-year scopes.
- Maatwebsite Excel for XLSX/CSV import/export.
- DomPDF for PDF exports.
- Laravel session, password reset broker, queue, cache, and scheduled commands.
- Main route entry point: `routes/web.php`.
- Middleware registration: `bootstrap/app.php`.

## Trust Model

Public unauthenticated users can access login and password reset routes. Authenticated actors are separated by Spatie roles:

- `super_admin`: platform-wide institution, admin, global setting, backup, audit log operations.
- `admin`: institution-scoped school administration for students, teachers, subjects, schedules, reports, settings, and imports.
- `wakasek`: monitoring and reports, with policy checks in several controllers.
- `wali_kelas`: homeroom attendance verification and reporting for assigned class contexts.
- `teacher`: agenda, attendance, teacher status, and grade workflows for own schedules.
- `sekretaris`: class secretary agenda and attendance workflows for own class context.
- `siswa`: personal schedule, agenda, grades, daily attendance, corrections, and early leave requests.

Core enforcement appears in route middleware (`auth`, `role`, `feature`, `system.maintenance`, `operational.hours`, `agenda.location`) plus controller-level checks and Eloquent global institution scopes (`App\Traits\BelongsToInstitution`, `App\Models\Scopes\InstitutionScope`).

## Input Surfaces

Primary HTTP surfaces:

- Authentication: `/login`, `/logout`, `/forgot-password`, `/reset-password`.
- Super admin: institution/admin CRUD, global settings, test email, database backup/restore, file backup, audit logs.
- Admin: student/teacher/class/subject/schedule/room CRUD, import/export, reports, settings, daily attendance settings and corrections.
- Teacher: attendance, teacher status CRUD, grade CRUD/export, agenda CRUD/preview/report export.
- Secretary: class agenda CRUD/preview/print and attendance entry/reporting.
- Student: daily attendance check-in/check-out, photo data, location data, correction evidence upload, early leave evidence upload, schedule and grade views.
- Shared authenticated media controller for private attendance media.

File inputs:

- Excel/CSV student and teacher imports.
- Agenda attachments stored on the public disk.
- Attendance photos and evidence stored encrypted on the local disk.
- Super admin database restore upload.

Dangerous sinks reviewed:

- Shell execution in super admin backup/restore uses `escapeshellarg`.
- File storage and retrieval in agenda and private attendance media.
- Query builder raw expressions appear mostly constant.
- HTML output uses Blade escaping in normal templates; previews strip tags from descriptions.

## Baseline

Comparable school information systems commonly rely on role-scoped server-side authorization and bulk imports. Bulk onboarding often needs temporary credentials, but secure baselines use per-user random one-time passwords, activation links, or forced password rotation on first login. Predictable shared default passwords are a known class of account takeover risk in education portals because identifiers such as NIS/NIP are routinely known or guessable.
