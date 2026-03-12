<?php
include 'includes/db.php';
require_once('tcpdf/tcpdf.php');

$class_level = $_POST['class_level'] ?? '';
$stream_filter = $_POST['stream'] ?? 'all';

// Fetch all students in class & stream, fetch subjects and marks same as your marks page

// For demo, assume $students array is fetched

// Initialize TCPDF to create a multi-page PDF
$pdf = new TCPDF();
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetFont('times', '', 12);

foreach ($students as $student) {
    $pdf->AddPage();

    // Generate report content for $student
    // You can refactor your individual report generation code here

    $html = "<h2>Student: " . htmlspecialchars($student['name']) . "</h2>";
    // ... add rest of the report html (subjects, marks, grade, etc.)

    $pdf->writeHTML($html, true, false, true, false, '');
}

// Output combined PDF to browser
$pdf->Output('class_reports_' . $class_level . '_' . $stream_filter . '.pdf', 'D');
