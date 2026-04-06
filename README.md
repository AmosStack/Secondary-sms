# Secondary School Management System

Mafiga is a PHP + MySQL/MariaDB school results system for managing classes, subjects, students, marks, and report generation.

## Current Version Summary

This version includes:
- centralized grade/point/division logic in `endpoints/division_calculation.php`
- endpoint handlers moved under the `endpoints/` folder
- student naming standardized to `full_name` (with compatibility fallback in some flows)
- student `gender` support in registration
- `print_reports.php` aligned to use the same report output path as `overall_performance.php` download action (via `download_report.php`)

## Core Features

- Admin authentication 
- Subject management (create, edit, delete)
- Class + stream registration and subject assignment
- Student registration:
   - manual entry
   - Excel upload (PhpSpreadsheet)
- Marks entry:
   - manual per class/subject
   - Excel upload
- Performance views:
   - detailed marks view with grade and division
   - overall class performance rankings
- PDF report generation (TCPDF)

## Tech Stack

- PHP (mysqli, procedural style)
- MySQL / MariaDB
- Bootstrap 5
- JavaScript (fetch-based dependent dropdown loading)
- Composer packages:
   - `phpoffice/phpspreadsheet`
   - `tecnickcom/tcpdf`

## Key Pages

- `index.php` - landing page
- `login.php`, `register.php`, `logout.php` - authentication
- `dashboard.php` - main admin hub
- `create_subject.php` - subject CRUD
- `register_class.php` - class/stream + subject assignment
- `register_student.php` - manual + Excel student registration
- `enter_results.php` - marks entry portal (manual + Excel)
- `enter_marks_manual.php` - manual marks entry form
- `view_students.php` - students listing
- `view_marks.php` - marks, averages, grades, divisions
- `overall_performance.php` - class-level performance ranking
- `print_reports.php` - student selector that opens `download_report.php`
- `download_report.php` - student report PDF generator

## Endpoints

All primary AJAX/save endpoints are under `endpoints/`:
- `endpoints/get_stream.php`
- `endpoints/get_streams.php`
- `endpoints/get_streams_by_level.php`
- `endpoints/get_subjects.php`
- `endpoints/get_stream_subjects.php`
- `endpoints/save_marks.php`
- `endpoints/division_calculation.php`

## Grading and Division

Grading and division rules are centralized in `endpoints/division_calculation.php` and reused by reporting and performance pages.

Main helper functions include:
- `normalizeFormLevel(...)`
- `getGradeByForm(...)`
- `getGradePointByForm(...)`
- `calculateDivisionResult(...)`
- `getGradeAndCommentByForm(...)`

## Requirements

- XAMPP (Apache + MySQL) or equivalent PHP + MySQL stack
- PHP 8.0+
- Composer
- MySQL user with create/import permissions

## Installation (Windows + XAMPP)

1. Put the project in:
    - `c:/xampp/htdocs/mafiga`

2. Install dependencies:

```bash
cd c:/xampp/htdocs/mafiga
composer install
```

3. Create database and import schema:
    - Create database `mafiga`
    - Import `mafiga2.sql` (recommended for this version)

4. Configure DB credentials in `includes/db.php`.

5. Start Apache + MySQL from XAMPP Control Panel.

6. Open:
    - `http://localhost/mafiga/`

## Schema Notes (Important)

This codebase expects student names in `students.full_name` in many pages.

If your DB still uses `students.name`, run migration SQL:

```sql
ALTER TABLE students ADD COLUMN full_name VARCHAR(150) NULL AFTER student_id;
UPDATE students SET full_name = name WHERE (full_name IS NULL OR full_name = '') AND name IS NOT NULL;
ALTER TABLE students MODIFY full_name VARCHAR(150) NOT NULL;
```

If gender is missing, add it:

```sql
ALTER TABLE students ADD COLUMN gender ENUM('Male','Female') NOT NULL DEFAULT 'Male' AFTER full_name;
```

Optional cleanup after confirming all flows work with `full_name`:

```sql
ALTER TABLE students DROP COLUMN name;
```

## Excel Upload Format

### Student Upload (`register_student.php`)
- Required columns: `Name`, `Class`, `Stream`
- Optional column: `Gender`

### Marks Upload (`enter_results.php`)
- Required column: `Name`
- Required subject column: exact subject name (for example `Mathematics`)

## Report Flow

- `overall_performance.php` provides per-student download actions.
- `print_reports.php` now only selects a student and forwards to `download_report.php`.
- `download_report.php` is the shared PDF renderer for both flows.

## Troubleshooting

- `Class "ZipArchive" not found` during Excel upload:
   - Enable ZIP extension in active `php.ini`:
      - `extension=zip`
   - Restart Apache.

- Composer/autoload issues:
   - Run `composer install` from project root.

- Database errors like unknown column:
   - Confirm schema matches this version (`mafiga2.sql` + migrations above).

- No marks imported:
   - Ensure Excel headers match exactly and student names align with DB records.

## License

No license file is currently included. Add a `LICENSE` file before distribution.
