<?php
include 'includes/db.php';
require_once('tcpdf/tcpdf.php');
require_once('endpoints/division_calculation.php');

$class_level = $_POST['class_level'] ?? '';
$stream_filter = $_POST['stream'] ?? 'all';

if ($class_level === '') {
    header('Location: overall_performance.php');
    exit;
}

// Resolve class IDs for selected class level and optional stream.
$classIds = [];
if ($stream_filter === 'all') {
    $stmt = $conn->prepare('SELECT class_id FROM classes WHERE class_level = ?');
    $stmt->bind_param('s', $class_level);
} else {
    $stmt = $conn->prepare('SELECT class_id FROM classes WHERE class_level = ? AND stream = ?');
    $stmt->bind_param('ss', $class_level, $stream_filter);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $classIds[] = (int)$row['class_id'];
}
$stmt->close();

$students = [];
$subjectNames = [];
$marksByStudent = [];

if ($classIds) {
    $classPlaceholders = implode(',', array_fill(0, count($classIds), '?'));
    $classTypes = str_repeat('i', count($classIds));

    // Students in selected classes.
    $sqlStudents = "SELECT s.student_id, s.name, c.stream, c.class_level
                    FROM students s
                    JOIN classes c ON s.class_id = c.class_id
                    WHERE s.class_id IN ($classPlaceholders)
                    ORDER BY c.stream, s.name";
    $stmt = $conn->prepare($sqlStudents);
    $stmt->bind_param($classTypes, ...$classIds);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();

    // Subjects for selected class level.
    $stmt = $conn->prepare("SELECT DISTINCT sub.subject_id, sub.name
                            FROM subjects sub
                            JOIN class_subjects cs ON cs.subject_id = sub.subject_id
                            JOIN classes c ON cs.class_id = c.class_id
                            WHERE c.class_level = ?
                            ORDER BY sub.name");
    $stmt->bind_param('s', $class_level);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $subjectNames[(int)$row['subject_id']] = $row['name'];
    }
    $stmt->close();

    if ($students && $subjectNames) {
        $studentIds = array_map('intval', array_column($students, 'student_id'));
        $subjectIds = array_keys($subjectNames);

        $studentPlaceholders = implode(',', array_fill(0, count($studentIds), '?'));
        $subjectPlaceholders = implode(',', array_fill(0, count($subjectIds), '?'));
        $types = str_repeat('i', count($studentIds) + count($subjectIds));

        $sqlMarks = "SELECT m.student_id, m.subject_id, m.marks
                     FROM marks m
                     WHERE m.student_id IN ($studentPlaceholders)
                     AND m.subject_id IN ($subjectPlaceholders)";
        $stmt = $conn->prepare($sqlMarks);
        $stmt->bind_param($types, ...$studentIds, ...$subjectIds);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $marksByStudent[(int)$row['student_id']][(int)$row['subject_id']] = (float)$row['marks'];
        }
        $stmt->close();
    }
}

if (!$students) {
    header('Location: overall_performance.php?class_level=' . urlencode($class_level));
    exit;
}

$pdf = new TCPDF();
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetFont('times', '', 12);

foreach ($students as $student) {
    $pdf->AddPage();

    $studentId = (int)$student['student_id'];
    $form = (int)$student['class_level'];
    $studentMarks = $marksByStudent[$studentId] ?? [];

    $rowsHtml = '';
    $totalMarks = 0;
    $subjectCount = 0;
    $subjectAverages = [];

    foreach ($subjectNames as $subjectId => $subjectName) {
        $mark = $studentMarks[$subjectId] ?? null;

        if ($mark === null) {
            $rowsHtml .= '<tr><td>' . htmlspecialchars($subjectName) . '</td><td>-</td><td>-</td><td>-</td></tr>';
            continue;
        }

        $totalMarks += $mark;
        $subjectCount++;
        $subjectAverages[$subjectName] = $mark;
        $grade = getGradeByForm($form, $mark);
        $remark = getGradeCommentsMap()[$grade] ?? '-';

        $rowsHtml .= '<tr><td>' . htmlspecialchars($subjectName) . '</td><td>' . $mark . '</td><td>' . $grade . '</td><td>' . htmlspecialchars($remark) . '</td></tr>';
    }

    $average = $subjectCount ? round($totalMarks / $subjectCount, 2) : 0;
    $overallGrade = getGradeByForm($form, $average);
    $divisionResult = calculateDivisionResult($form, $subjectAverages);
    $division = $divisionResult['division'];
    $points = $divisionResult['valid'] ? $divisionResult['total_points'] : '-';

    $html = '
        <h2>Student Report</h2>
        <p><strong>Name:</strong> ' . htmlspecialchars($student['name']) . '</p>
        <p><strong>Class:</strong> ' . htmlspecialchars((string)$student['class_level']) . ' | <strong>Stream:</strong> ' . htmlspecialchars((string)$student['stream']) . '</p>
        <table border="1" cellpadding="5">
            <tr><th>Subject</th><th>Mark</th><th>Grade</th><th>Remark</th></tr>' .
            $rowsHtml .
        '</table>
        <br>
        <p><strong>Total:</strong> ' . $totalMarks . '</p>
        <p><strong>Average:</strong> ' . $average . '</p>
        <p><strong>Overall Grade:</strong> ' . $overallGrade . '</p>
        <p><strong>Division:</strong> ' . htmlspecialchars($division) . '</p>
        <p><strong>Points:</strong> ' . $points . '</p>
    ';

    $pdf->writeHTML($html, true, false, true, false, '');
}

if (ob_get_length()) {
    ob_end_clean();
}

$pdf->Output('class_reports_' . $class_level . '_' . $stream_filter . '.pdf', 'D');
