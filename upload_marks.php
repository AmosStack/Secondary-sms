<?php
include "includes/db.php";
require 'vendor/autoload.php'; // PhpSpreadsheet must be installed via Composer

use PhpOffice\PhpSpreadsheet\IOFactory;

function canLoadSpreadsheetFile(string $originalName): array {
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $isZipBased = in_array($ext, ['xlsx', 'xlsm', 'xltx', 'xltm', 'ods'], true);

    if ($isZipBased && !class_exists('ZipArchive')) {
        return [
            false,
            'ZIP support is missing. Enable the PHP zip extension in XAMPP php.ini (extension=zip) and restart Apache.'
        ];
    }

    return [true, ''];
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid request method. Please use the form to upload the file.");
}

// Get posted data
$class_level = $_POST['class_level'] ?? null;
$class_id = $_POST['class_id'] ?? null;
$subject_id = $_POST['subject_id'] ?? null;

if (!$class_id || !$subject_id || !isset($_FILES['marks_file'])) {
    echo "<script>alert('❌ Missing class, subject, or file.'); history.back();</script>";
    exit;
}

$file = $_FILES['marks_file']['tmp_name'];
$originalFileName = $_FILES['marks_file']['name'] ?? '';

[$canLoad, $dependencyError] = canLoadSpreadsheetFile($originalFileName);
if (!$canLoad) {
    echo "<script>alert('❌ Upload failed: {$dependencyError}'); history.back();</script>";
    exit;
}

try {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();

    // Read column headers
    $headers = array_map('trim', $data[0]);
    $nameIndex = array_search("Name", $headers);

    // Get subject name
    $subjectNameRes = $conn->query("SELECT name FROM subjects WHERE subject_id = $subject_id");
    if (!$subjectNameRes || $subjectNameRes->num_rows == 0) {
        echo "<script>alert('❌ Invalid subject ID.'); history.back();</script>";
        exit;
    }
    $subjectName = $subjectNameRes->fetch_assoc()['name'];

    $markIndex = array_search($subjectName, $headers);

    if ($nameIndex === false || $markIndex === false) {
        echo "<script>alert('❌ Excel must have columns \"Name\" and \"$subjectName\".'); history.back();</script>";
        exit;
    }

    $inserted = 0;

    for ($i = 1; $i < count($data); $i++) {
        $studentName = trim($data[$i][$nameIndex]);
        $marks = trim($data[$i][$markIndex]);

        if ($studentName === "" || $marks === "") continue;

        // Find student by name and class
        $stmt = $conn->prepare("SELECT student_id FROM students WHERE name = ? AND class_id = ?");
        $stmt->bind_param("si", $studentName, $class_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($student = $result->fetch_assoc()) {
            $student_id = $student['student_id'];

            // Insert mark
            $insert = $conn->prepare("INSERT INTO marks (student_id, subject_id, marks, date_recorded) VALUES (?, ?, ?, NOW())");
            $insert->bind_param("iii", $student_id, $subject_id, $marks);
            if ($insert->execute()) $inserted++;
        }
    }

    echo "<script>alert('✅ $inserted marks uploaded successfully.'); window.location.href='enter_marks.php';</script>";
} catch (Exception $e) {
    echo "<script>alert('❌ Upload failed: " . $e->getMessage() . "'); history.back();</script>";
}
?>