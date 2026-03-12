<?php
include "includes/db.php";

// Grade functions and division logic (simplified for demo)
function getGradeF1toF4($avg) {
    if ($avg >= 75) return "A";
    if ($avg >= 65) return "B";
    if ($avg >= 55) return "C";
    if ($avg >= 35) return "D";
    return "F";
}

function getGradeF5toF6($avg) {
    if ($avg >= 80) return "A";
    if ($avg >= 70) return "B";
    if ($avg >= 60) return "C";
    if ($avg >= 50) return "D";
    if ($avg >= 40) return "E";
    if ($avg >= 35) return "S";
    return "F";
}

$gradePointsF1toF4 = ['A'=>1,'B'=>2,'C'=>3,'D'=>4,'F'=>5];
$gradePointsF5toF6 = ['A'=>1,'B'=>2,'C'=>3,'D'=>4,'E'=>5,'S'=>6,'F'=>7];

$gradeComments = [
    'A' => 'Excellent',
    'B' => 'Very Good',
    'C' => 'Good',
    'D' => 'Fair',
    'E' => 'Poor',
    'S' => 'Very Poor',
    'F' => 'Fail'
];

// Get students for dropdown
$studentsRes = $conn->query("SELECT id, name, class_id FROM students ORDER BY name");

// Fetch classes to get class level and stream
$classMap = [];
$classRes = $conn->query("SELECT id, class_level, stream FROM classes");
while($c = $classRes->fetch_assoc()) {
    $classMap[$c['id']] = $c;
}

$selectedStudentId = $_GET['student_id'] ?? null;
$term = $_GET['term'] ?? '';
$year = $_GET['year'] ?? '';

$reportData = null;

if ($selectedStudentId) {
    // Fetch student info
    $stmt = $conn->prepare("SELECT s.name, c.class_level, c.stream FROM students s JOIN classes c ON s.class_id = c.id WHERE s.id=?");
    $stmt->bind_param("i", $selectedStudentId);
    $stmt->execute();
    $stmt->bind_result($studentName, $classLevel, $stream);
    $stmt->fetch();
    $stmt->close();

    // Fetch marks for this student filtered by term and year if provided
    $marksQuery = "SELECT sub.name AS subject, m.marks 
                   FROM marks m 
                   JOIN subjects sub ON m.subject_id = sub.id
                   WHERE m.student_id = ? ";
    $params = [$selectedStudentId];
    $types = "i";

    if ($term) {
        $marksQuery .= " AND m.term = ? ";
        $params[] = $term;
        $types .= "s";
    }
    if ($year) {
        $marksQuery .= " AND m.year = ? ";
        $params[] = $year;
        $types .= "s";
    }
    $marksQuery .= " ORDER BY sub.name";

    $stmt = $conn->prepare($marksQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $marks = [];
    while ($row = $result->fetch_assoc()) {
        $marks[$row['subject']] = $row['marks'];
    }
    $stmt->close();

    // Calculate grades, points, division
    $subjectGrades = [];
    $divisionPoints = [];
    $totalMarks = 0;
    $countSubjects = 0;

    // Use appropriate grading based on class level
    $form = (int)$classLevel;
    $gradePoints = $form <= 4 ? $gradePointsF1toF4 : $gradePointsF5toF6;

    foreach ($marks as $subject => $mark) {
        $totalMarks += $mark;
        $countSubjects++;

        $grade = $form <=4 ? getGradeF1toF4($mark) : getGradeF5toF6($mark);
        $point = $gradePoints[$grade] ?? null;
        $comment = $gradeComments[$grade] ?? '-';

        $subjectGrades[$subject] = ['mark'=>$mark,'grade'=>$grade,'comment'=>$comment,'point'=>$point];

        // Exclude General Studies from division points for F5-F6
        if (!($form >=5 && strtolower($subject) === 'general studies')) {
            if ($point !== null) $divisionPoints[] = $point;
        }
    }

    $average = $countSubjects ? round($totalMarks / $countSubjects, 2) : 0;
    $gpa = 0;
    if (count($divisionPoints) > 0) {
        sort($divisionPoints);
        $subjectsCount = $form <=4 ? 7 : 3;
        $usePoints = array_slice($divisionPoints, 0, $subjectsCount);
        $gpa = round(array_sum($usePoints)/count($usePoints), 2);
    }

    // Determine division based on GPA points
    $divisionRangesF1toF4 = [
        "Div 1" => [7, 17],
        "Div 2" => [18, 21],
        "Div 3" => [22, 25],
        "Div 4" => [26, 33],
        "Div 0" => [34, 35]
    ];

    $divisionRangesF5toF6 = [
        "Div 1" => [3, 9],
        "Div 2" => [10, 12],
        "Div 3" => [13, 17],
        "Div 4" => [18, 19],
        "Div 0" => [20, 21]
    ];

    $divisionLabel = "No division";
    $totalPoints = array_sum($divisionPoints);

    $ranges = $form <=4 ? $divisionRangesF1toF4 : $divisionRangesF5toF6;
    foreach ($ranges as $div => [$min, $max]) {
        if ($totalPoints >= $min && $totalPoints <= $max) {
            $divisionLabel = $div;
            break;
        }
    }

    $reportData = [
        'studentName' => $studentName,
        'classLevel' => $classLevel,
        'stream' => $stream,
        'term' => $term,
        'year' => $year,
        'subjectGrades' => $subjectGrades,
        'totalMarks' => $totalMarks,
        'average' => $average,
        'gpa' => $gpa,
        'division' => $divisionLabel,
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Report Card</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .report { max-width: 700px; margin: auto; border: 1px solid #333; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px;}
        th, td { border: 1px solid #333; padding: 8px; text-align: center; }
        th { background: #444; color: #fff; }
        .header { text-align: center; }
        .print-btn { margin: 20px 0; text-align: center; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="max-width: 700px; margin:auto;">
    <h2>Generate Student Report Card</h2>
    <form method="get">
        <label for="student_id">Select Student:</label>
        <select name="student_id" id="student_id" required>
            <option value="">-- Select Student --</option>
            <?php while ($stu = $studentsRes->fetch_assoc()): 
                $selected = ($stu['id'] == $selectedStudentId) ? "selected" : "";
                $cls = $classMap[$stu['class_id']] ?? ['class_level'=>'','stream'=>''];
                ?>
                <option value="<?= $stu['id'] ?>" <?= $selected ?>><?= htmlspecialchars($stu['name'] . " (F".$cls['class_level']." - ".$cls['stream'].")") ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="term">Term (optional):</label>
        <input type="text" name="term" id="term" value="<?= htmlspecialchars($term) ?>"><br><br>

        <label for="year">Year (optional):</label>
        <input type="text" name="year" id="year" value="<?= htmlspecialchars($year) ?>"><br><br>

        <button type="submit">View Report</button>
    </form>
</div>

<?php if ($reportData): ?>
    <div class="report">
        <div class="header">
            <h1>School Name / Logo</h1>
            <h2>Student Report Card</h2>
            <p><strong>Student:</strong> <?= htmlspecialchars($reportData['studentName']) ?></p>
            <p><strong>Class:</strong> F<?= $reportData['classLevel'] ?> - <?= htmlspecialchars($reportData['stream']) ?></p>
            <p><strong>Term:</strong> <?= htmlspecialchars($reportData['term'] ?: 'N/A') ?> | <strong>Year:</strong> <?= htmlspecialchars($reportData['year'] ?: 'N/A') ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Mark</th>
                    <th>Grade</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportData['subjectGrades'] as $subject => $data): ?>
                    <tr>
                        <td><?= htmlspecialchars($subject) ?></td>
                        <td><?= $data['mark'] ?? '-' ?></td>
                        <td><?= $data['grade'] ?? '-' ?></td>
                        <td><?= $data['comment'] ?? '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th><?= $reportData['totalMarks'] ?></th>
                    <th>GPA</th>
                    <th><?= $reportData['gpa'] ?></th>
                </tr>
                <tr>
                    <th colspan="3">Average Mark</th>
                    <th><?= $reportData['average'] ?></th>
                </tr>
                <tr>
                    <th colspan="3">Division</th>
                    <th><?= $reportData['division'] ?></th>
                </tr>
            </tfoot>
        </table>

        <div class="print-btn">
            <button onclick="window.print()">Print Report</button>
        </div>
    </div>
<?php endif; ?>

</body>
</html>
