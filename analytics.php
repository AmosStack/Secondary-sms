<?php
include "includes/db.php"; // Database connection

// Grade and remark logic
function getGradeAndRemark($form, $mark) {
    if ($mark === null) return ['-', '-'];
    if ($form <= 4) {
        if ($mark >= 75) return ['A', 'Excellent'];
        if ($mark >= 65) return ['B', 'Very Good'];
        if ($mark >= 55) return ['C', 'Good'];
        if ($mark >= 35) return ['D', 'Poor'];
        return ['F', 'Fail'];
    } else {
        if ($mark >= 80) return ['A', 'Excellent'];
        if ($mark >= 70) return ['B', 'Very Good'];
        if ($mark >= 60) return ['C', 'Good'];
        if ($mark >= 50) return ['D', 'Fair'];
        if ($mark >= 40) return ['E', 'Poor'];
        if ($mark >= 35) return ['S', 'Very Poor'];
        return ['F', 'Fail'];
    }
}

// Filter logic
$term = $_GET['term'] ?? '';
$year = $_GET['year'] ?? '';
$conditions = [];
if ($year !== '' && ctype_digit((string)$year)) {
    $conditions[] = "YEAR(m.date_recorded) = " . (int)$year;
}
if ($term !== '' && ctype_digit((string)$term)) {
    $termNum = (int)$term;
    if ($termNum >= 1 && $termNum <= 3) {
        $startMonth = (($termNum - 1) * 4) + 1;
        $endMonth = $startMonth + 3;
        $conditions[] = "MONTH(m.date_recorded) BETWEEN $startMonth AND $endMonth";
    }
}
$whereClause = $conditions ? ("WHERE " . implode(" AND ", $conditions)) : "";

// Subject-wise Analysis
$subjects = [];
$subQ = $conn->query("SELECT sub.name AS subject, AVG(m.marks) AS avg_mark, COUNT(DISTINCT m.student_id) AS total
                       FROM marks m
                       JOIN subjects sub ON m.subject_id = sub.id
                       $whereClause
                       GROUP BY sub.id, sub.name
                       ORDER BY sub.name");
while ($row = $subQ->fetch_assoc()) {
    $form = 5;
    $gradeData = getGradeAndRemark($form, $row['avg_mark']);
    $subjects[] = [
        'subject' => $row['subject'],
        'avg' => round($row['avg_mark'], 2),
        'gpa' => '-',
        'grade' => $gradeData[0],
        'remark' => $gradeData[1],
        'students' => $row['total']
    ];
}

// Class-wise Analysis
$classes = [];
$classQ = $conn->query("SELECT CONCAT('Form ', c.class_level, ' - ', c.stream) AS class, c.class_level, AVG(m.marks) AS avg_mark
                         FROM marks m
                         JOIN students s ON m.student_id = s.id
                         JOIN classes c ON s.class_id = c.id
                         $whereClause
                         GROUP BY c.id, c.class_level, c.stream
                         ORDER BY c.class_level, c.stream");
while ($row = $classQ->fetch_assoc()) {
    $form = (int)$row['class_level'];
    $gradeData = getGradeAndRemark($form, $row['avg_mark']);
    $classes[] = [
        'class' => $row['class'],
        'avg' => round($row['avg_mark'], 2),
        'gpa' => '-',
        'grade' => $gradeData[0],
        'remark' => $gradeData[1]
    ];
}

$schoolQ = $conn->query("SELECT AVG(m.marks) AS avg_mark FROM marks m $whereClause");
$schoolAvg = $schoolQ->fetch_assoc()['avg_mark'];
$overallGrade = getGradeAndRemark(5, $schoolAvg);
?>

<!DOCTYPE html>
<html>
<head>
    <title>School Performance Analysis</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #f0f0f0; }
        form { margin-bottom: 20px; }
    </style>
</head>
<body>

<h2>Filter by Term and Year</h2>
<form method="get">
    Term: <input type="text" name="term" value="<?= htmlspecialchars($term) ?>">
    Year: <input type="text" name="year" value="<?= htmlspecialchars($year) ?>">
    <button type="submit">Filter</button>
    <button onclick="window.print(); return false;">Export PDF</button>
</form>

<h2>Subject-wise Performance</h2>
<table>
    <tr><th>Subject</th><th>Average</th><th>GPA</th><th>Grade</th><th>Remark</th><th>Students</th></tr>
    <?php foreach ($subjects as $s): ?>
    <tr>
        <td><?= $s['subject'] ?></td>
        <td><?= $s['avg'] ?></td>
        <td><?= $s['gpa'] ?></td>
        <td><?= $s['grade'] ?></td>
        <td><?= $s['remark'] ?></td>
        <td><?= $s['students'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>Class-wise Performance</h2>
<table>
    <tr><th>Class</th><th>Average</th><th>GPA</th><th>Grade</th><th>Remark</th></tr>
    <?php foreach ($classes as $c): ?>
    <tr>
        <td><?= $c['class'] ?></td>
        <td><?= $c['avg'] ?></td>
        <td><?= $c['gpa'] ?></td>
        <td><?= $c['grade'] ?></td>
        <td><?= $c['remark'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>Overall School Performance</h2>
<table>
    <tr><th>Avg Mark</th><th>Grade</th><th>Remark</th></tr>
    <tr>
        <td><?= $schoolAvg !== null ? round($schoolAvg, 2) : '-' ?></td>
        <td><?= $overallGrade[0] ?></td>
        <td><?= $overallGrade[1] ?></td>
    </tr>
</table>

<canvas id="subjectChart" width="400" height="150"></canvas>
<script>
const ctx = document.getElementById('subjectChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($subjects, 'subject')) ?>,
        datasets: [{
            label: 'Average Marks',
            data: <?= json_encode(array_column($subjects, 'avg')) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true, max: 100 }
        }
    }
});
</script>

</body>
</html>
