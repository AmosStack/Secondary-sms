# Mafiga School Management System

A PHP + MySQL school result management system for managing classes, subjects, students, marks entry, and performance reports.

## Overview

This project provides an admin-facing dashboard to:
- register classes/streams and assign subjects
- register students (manual and Excel upload)
- enter marks (manual and Excel upload)
- view marks with grade, points, and division calculations
- review overall class performance
- print student reports

## Tech Stack

- PHP (procedural)
- MySQL / MariaDB
- Bootstrap 5
- jQuery
- Composer dependencies:
  - phpoffice/phpspreadsheet
  - tecnickcom/tcpdf

## Main Modules

- `index.php`: Landing page
- `login.php`, `register.php`, `logout.php`: Admin authentication
- `dashboard.php`: Main navigation hub
- `register_class.php`: Create class + stream and assign subjects
- `create_subject.php`: Manage subjects
- `register_student.php`: Student registration (manual + Excel)
- `enter_results.php`: Marks entry portal (manual + Excel)
- `save_marks.php`: Save/update marks per subject
- `view_students.php`: Student listing by class and stream
- `view_marks.php`: Marks listing with totals, grades, points, and division
- `overall_performance.php`: Ranked class performance
- `print_reports.php`: Printable report card view
- `download_report.php`: PDF report download path (TCPDF-based)

Supporting API/endpoints:
- `get_stream.php`
- `get_streams.php`
- `get_streams_by_level.php`
- `get_subjects.php`
- `endpoints/get_stream_subjects.php`

## Prerequisites

- XAMPP (Apache + MySQL)
- PHP 8.0+
- Composer
- MySQL user with permission to create/import database

## Installation (XAMPP, Windows)

1. Place project in XAMPP web root:
   - `c:/xampp/htdocs/mafiga`

2. Install PHP dependencies:

```bash
cd c:/xampp/htdocs/mafiga
composer install
```

3. Create/import database:
   - Create database named `mafiga`
   - Import `mafiga.sql`

4. Configure database credentials in `includes/db.php`:

```php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "mafiga";
```

5. Start Apache and MySQL in XAMPP Control Panel.

6. Open in browser:
   - `http://localhost/mafiga/`

## First-Time Usage

1. Create an admin account:
   - Use `register.php` directly, or use the sign-up panel in `login.php` (action mapping may require adjustment in some setups).

2. Login as admin.

3. Create subjects in `create_subject.php`.

4. Register class and stream in `register_class.php`, selecting subjects for each class-stream.

5. Register students in `register_student.php`:
   - manual form, or
   - Excel upload with columns: `Name`, `Gender`, `Class`, `Stream`

6. Enter marks in `enter_results.php`:
   - manual path (per class/subject), or
   - Excel upload path with columns including:
     - `Name`
     - the exact subject name column (e.g. `Mathematics`)

7. Review results:
   - `view_marks.php`
   - `overall_performance.php`
   - `print_reports.php`

## Grading and Division Logic

The system includes separate grading scales for:
- Forms 1-4
- Forms 5-6

Grade boundaries, points mapping, and division calculation are implemented in page-level helpers (for example in `view_marks.php`, `overall_performance.php`, and `print_reports.php`).

## Current Implementation Notes

The repository contains some schema/code mismatches. If you are setting this up from scratch, review these items early:

1. `mafiga.sql` does not include an `admin` table, but authentication expects it.
2. `mafiga.sql` students table does not include `gender`, but student pages use `students.gender`.
3. Several files reference columns/tables not present in `mafiga.sql`:
   - `analytics.php` references `results` table and `mark` field shape.
   - `print_reports.php` filters marks by `m.term` and `m.year`.
   - `download_report.php` expects `test` and `exam` fields and currently has an SQL syntax issue in one query.
   - `endpoints/get_stream_subjects.php` expects `subjects.class_id` and `subjects.subject_name`, while current schema uses `class_subjects` and `subjects.name`.
4. `enter_marks_manual.php` reads `class_id` and `subject_id` from GET, while `enter_results.php` manual form submits POST.
5. Some redirects point to `enter_marks.php`, but the project currently uses `enter_results.php`.

If needed, align schema and code paths before production use.

## Recommended Development Workflow

1. Normalize the schema and naming (class level format, subject fields, marks fields).
2. Centralize grade/division helper functions in one shared file.
3. Add session/auth guards to all admin pages.
4. Add migrations/seed scripts for repeatable setup.
5. Add validation and error logging instead of inline alerts/die calls.

## Troubleshooting

- Composer autoload errors:
  - run `composer install`
- Database connection errors:
  - verify `includes/db.php` credentials and ensure MySQL is running
- Blank page / PHP warnings:
  - enable error reporting in development
- Mark upload inserts no rows:
  - verify Excel headers exactly match required names and student names match DB records

## License

No license file is currently defined in the project root. Add a `LICENSE` file before public distribution.
