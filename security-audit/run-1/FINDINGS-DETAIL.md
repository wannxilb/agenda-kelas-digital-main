# Findings Detail

## MEDIUM: Predictable Default Passwords For Imported Users

### Data Flow

1. `routes/web.php:88` registers `POST /admin/students/import` inside the authenticated admin route group and `feature:import_data`.
2. `app/Http/Controllers/Admin/StudentController.php:352` accepts an uploaded XLSX/XLS/CSV file and calls `Excel::import(new StudentsImport(...))` at `app/Http/Controllers/Admin/StudentController.php:368`.
3. `app/Imports/StudentsImport.php:115` creates one hash for the literal default password `password`.
4. `app/Imports/StudentsImport.php:408` creates the imported user. The spreadsheet NIS is stored at `app/Imports/StudentsImport.php:411`, the password hash is stored at `app/Imports/StudentsImport.php:416`, and `password_changed_at` is set to `now()` at `app/Imports/StudentsImport.php:417`.
5. `routes/web.php:99` registers `POST /admin/teachers/import` inside the same admin import feature group.
6. `app/Http/Controllers/Admin/TeacherController.php:238` accepts an uploaded XLSX/XLS/CSV file and calls `Excel::import(new TeachersImport, ...)` at `app/Http/Controllers/Admin/TeacherController.php:245`.
7. `app/Imports/TeachersImport.php:120` creates imported teacher users. The spreadsheet NIP is stored at `app/Imports/TeachersImport.php:121`, the email is stored at `app/Imports/TeachersImport.php:123`, the literal password `password123` is hashed at `app/Imports/TeachersImport.php:126`, and `password_changed_at` is set to `now()` at `app/Imports/TeachersImport.php:127`. If the spreadsheet email is empty, `app/Imports/TeachersImport.php:86` generates `<nip>@sekolah.sch.id`.
8. `app/Http/Controllers/Auth/LoginController.php:40` reads the submitted login identifier. `app/Http/Controllers/Auth/LoginController.php:48` to `app/Http/Controllers/Auth/LoginController.php:59` treats email-format input as `email` and non-email input as `nis`. `app/Http/Controllers/Auth/LoginController.php:68` calls `Auth::attempt`.
9. `app/Http/Controllers/Auth/LoginController.php:101` to `app/Http/Controllers/Auth/LoginController.php:108` only flashes an expiry warning when configured; it does not block default imported credentials or force a password change. The `auth_force_password_change` setting appears in settings, but no enforcement path was found.

### Exact HTTP Requests

After a student import row with NIS `12345`:

```http
POST /login HTTP/1.1
Host: target.example
Content-Type: application/x-www-form-urlencoded

email=12345&password=password
```

After a teacher import row with NIP `198765` and no explicit email:

```http
POST /login HTTP/1.1
Host: target.example
Content-Type: application/x-www-form-urlencoded

email=198765%40sekolah.sch.id&password=password123
```

### What The Attacker Gets

The attacker receives an authenticated session for the imported student or teacher account. Student accounts can view and submit student workflows. Teacher accounts can access teacher dashboard, agenda, attendance, status, and grade routes according to the assigned role and schedules.

### Baseline Handling

Comparable school portals that support bulk onboarding usually create random temporary passwords, send one-time setup links, or force password rotation at first login. Shared default passwords are especially risky in this domain because NIS/NIP-style identifiers are often known to classmates, families, administrators, printed documents, or other staff.
