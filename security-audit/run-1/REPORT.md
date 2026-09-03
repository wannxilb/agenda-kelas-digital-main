# Security Audit Report

## Executive Summary

The application has a generally coherent role-based structure: most sensitive routes are authenticated, controller checks often verify ownership or class context, and private attendance media is encrypted before storage. One confirmed MEDIUM issue was found in bulk onboarding: imported student and teacher accounts are created with predictable default passwords and are treated as already changed, allowing account takeover when an attacker knows or guesses NIS/NIP identifiers. One LOW account enumeration issue was also confirmed on the password-reset endpoint.

No prior audit runs were found for this repository. Additional runs are recommended because a single pass will not cover every role workflow.

## Baseline

The closest baseline is a school information/attendance system. Such systems commonly support bulk imports, but the secure baseline is to issue random one-time credentials or force password setup before the account can use the portal. This code instead uses shared constants for imported accounts.

## Findings

| Severity | Title | Description |
| --- | --- | --- |
| MEDIUM | Predictable default passwords for imported users | Imported students receive `password`, imported teachers receive `password123`, and imported accounts are not forced through first-login reset. |
| LOW | Password reset reveals whether an email exists | The public reset endpoint returns different success/error responses for registered and unregistered email addresses. |

## MEDIUM: Predictable Default Passwords For Imported Users

Files:

- `routes/web.php:88`, `routes/web.php:99`
- `app/Http/Controllers/Admin/StudentController.php:352`
- `app/Http/Controllers/Admin/TeacherController.php:238`
- `app/Imports/StudentsImport.php:115`
- `app/Imports/StudentsImport.php:408`
- `app/Imports/TeachersImport.php:120`
- `app/Http/Controllers/Auth/LoginController.php:40`

Attack scenario:

1. A school admin imports students through `POST /admin/students/import` or teachers through `POST /admin/teachers/import`.
2. A student account is created with NIS from the spreadsheet and password `password`; a teacher account is created with password `password123` and either the spreadsheet email or the generated email `<nip>@sekolah.sch.id`.
3. An unauthenticated attacker who knows or guesses the student's NIS, or the generated/imported teacher email, submits `POST /login` with the default password.
4. The login controller treats non-email login input as NIS and email-format input as email, calls `Auth::attempt`, and redirects into the student's or teacher's dashboard.

Impact:

An attacker can take over imported accounts that have not manually changed their password. For students, this exposes personal attendance, grades, schedules, and lets the attacker submit attendance/correction/early-leave actions as the student. For teachers, it exposes teacher workflows and grade/agenda actions allowed to that role.

Recommended fix:

Generate a unique random temporary password per imported account, store only the hash, deliver it out-of-band, and require rotation on first login. Do not set `password_changed_at` to `now()` for imported default credentials. Enforce `auth_force_password_change` or add an explicit `must_change_password` flag checked after authentication.

## LOW: Password Reset Email Enumeration

Files:

- `routes/web.php:34`
- `app/Http/Controllers/Auth/ForgotPasswordController.php:17`

Attack scenario:

An unauthenticated user sends `POST /forgot-password` with a candidate email. Existing accounts receive a success response; unknown accounts receive an error response. This lets the attacker build a valid-user list.

Impact:

The attacker learns which emails are registered. This is useful for targeted phishing or password guessing, but does not by itself grant account access.

Recommended fix:

Always return the same user-facing response, regardless of whether the email exists. Keep logging and broker behavior server-side.

## Hardening Notes

- `.env` exists in the working tree but is ignored by Git. Keep it out of source control and rotate local credentials if this workspace was shared.
- `trustProxies(at: '*')` trusts forwarded headers from any upstream. Restrict trusted proxies in production so audit logs and IP-based rate limits cannot be spoofed when the app is directly exposed.
- The super-admin backup/restore commands shell out to `pg_dump`/`psql`; arguments are escaped, but these routes should stay super-admin only and preferably run behind additional operational controls.

## Positive Patterns

- Login uses Laravel `RateLimiter`.
- Institution scoping is applied through a reusable model trait and global scope.
- Most role-specific controllers add explicit resource ownership or class-context checks.
- Attendance evidence is stored encrypted on the local disk and served through an authenticated controller.
- Shell arguments in database backup and restore commands are escaped.
