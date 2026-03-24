<?php
include 'includes/db.php';

// Get all distinct class levels from database
$classLevels = [];
$res = $conn->query("SELECT DISTINCT class_level FROM classes ORDER BY class_level");
while ($row = $res->fetch_assoc()) {
    $classLevels[] = $row['class_level'];
}

// Selected class level from GET (default first or all)
$selectedClass = $_GET['class_level'] ?? ($classLevels[0] ?? '');
$selectedClass = htmlspecialchars($selectedClass);

// Filter and sort options
$filter = $_GET['filter'] ?? 'all';      // all, top10, bottom10
$sortBy = $_GET['sort'] ?? 'average';   // average_asc, average_desc

// Helper functions for grades and divisions for F1-4 and F5-6

function getGradeAndPoint($avg, $classLevel) {
    if ($classLevel <= 4) {
        if ($avg >= 75) return ['A', 1];
        if ($avg >= 65) return ['B', 2];
        if ($avg >= 55) return ['C', 3];
        if ($avg >= 35) return ['D', 4];
        return ['F', 5];
    } else {
        if ($avg >= 80) return ['A', 1];
        if ($avg >= 70) return ['B', 2];
        if ($avg >= 60) return ['C', 3];
        if ($avg >= 50) return ['D', 4];
        if ($avg >= 40) return ['E', 5];
        if ($avg >= 35) return ['S', 6];
        return ['F', 7];
    }
}

function getDivisionAndPoints($totalPoints, $classLevel) {
    if ($classLevel <= 4) {
        $divisions = [
            "Div 1" => [7, 17],
            "Div 2" => [18, 21],
            "Div 3" => [22, 25],
            "Div 4" => [26, 33],
            "Div 0" => [34, 35],
        ];
    } else {
        $divisions = [
            "Div 1" => [3, 9],
            "Div 2" => [10, 12],
            "Div 3" => [13, 17],
            "Div 4" => [18, 19],
            "Div 0" => [20, 21],
        ];
    }
    foreach ($divisions as $div => [$min, $max]) {
        if ($totalPoints >= $min && $totalPoints <= $max) {
            return $div;
        }
    }
    return "No Division";
}

// Fetch all classes matching selected class level (all streams combined)
$classIds = [];
if ($selectedClass) {
    $stmt = $conn->prepare("SELECT class_id FROM classes WHERE class_level = ?");
    $stmt->bind_param("s", $selectedClass);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $classIds[] = $row['class_id'];
    }
    $stmt->close();
}

// Fetch all students in those classes
$students = [];
if ($classIds) {
    $placeholders = implode(',', array_fill(0, count($classIds), '?'));
    $types = str_repeat('i', count($classIds));
    $sql = "SELECT s.student_id, s.name, c.stream, c.class_level FROM students s JOIN classes c ON s.class_id = c.class_id WHERE s.class_id IN ($placeholders) ORDER BY s.name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$classIds);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

// Get all subject IDs and names for this class level (all streams combined)
$subjectIds = [];
$subjectNames = [];
if ($selectedClass) {
    // Join class_subjects and classes by class_level
    $sql = "SELECT DISTINCT sub.subject_id, sub.name FROM subjects sub 
            JOIN class_subjects cs ON cs.subject_id = sub.subject_id
            JOIN classes c ON cs.class_id = c.class_id
            WHERE c.class_level = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selectedClass);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $subjectIds[] = $row['subject_id'];
        $subjectNames[$row['subject_id']] = $row['name'];
    }
    $stmt->close();
}

// Fetch marks for all students and subjects
$marksData = [];
if ($students && $subjectIds) {
    $studentIds = array_column($students, 'student_id');

    if (!$studentIds) {
        $students = [];
    }

    if ($studentIds) {
    $studentPlaceholders = implode(',', array_fill(0, count($studentIds), '?'));
    $studentTypes = str_repeat('i', count($studentIds));

    $subjectPlaceholders = implode(',', array_fill(0, count($subjectIds), '?'));
    $subjectTypes = str_repeat('i', count($subjectIds));

    $sql = "SELECT m.student_id, m.subject_id, m.marks FROM marks m 
            WHERE m.student_id IN ($studentPlaceholders) AND m.subject_id IN ($subjectPlaceholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($studentTypes . $subjectTypes, ...$studentIds, ...$subjectIds);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $marksData[$row['student_id']][$row['subject_id']] = $row['marks'];
    }
    $stmt->close();
    }
}

// Calculate performance data for each student
$performance = [];
foreach ($students as $student) {
    $total = 0;
    $count = 0;
    $points = 0;
    $subjectPointsList = [];

    foreach ($subjectIds as $subjId) {
        $mark = $marksData[$student['student_id']][$subjId] ?? null;
        if ($mark !== null && $mark !== '') {
            $total += $mark;
            $count++;

            list($grade, $point) = getGradeAndPoint($mark, $student['class_level']);
            $points += $point;
            $subjectPointsList[] = $point;
        }
    }
    $average = $count ? round($total / $count, 2) : 0;

    list($overallGrade, $overallPoint) = getGradeAndPoint($average, $student['class_level']);

    // GPA: average of points per subject (if any)
    $gpa = $count ? round(array_sum($subjectPointsList) / $count, 2) : 0;

    $division = getDivisionAndPoints($points, $student['class_level']);

    $performance[] = [
        'id' => $student['student_id'],
        'name' => $student['name'],
        'stream' => $student['stream'],
        'class_level' => $student['class_level'],
        'marks' => array_map(function($subjId) use ($marksData, $student) {
            return $marksData[$student['student_id']][$subjId] ?? '-';
        }, $subjectIds),
        'total' => $total,
        'average' => $average,
        'grade' => $overallGrade,
        'gpa' => $gpa,
        'division' => $division,
        'points' => $points,
    ];
}

// Sorting function
function sortPerformance($data, $sortBy, $asc = true) {
    usort($data, function($a, $b) use ($sortBy, $asc) {
        if ($sortBy == 'average') {
            return $asc ? ($a['average'] <=> $b['average']) : ($b['average'] <=> $a['average']);
        } elseif ($sortBy == 'gpa') {
            return $asc ? ($a['gpa'] <=> $b['gpa']) : ($b['gpa'] <=> $a['gpa']);
        } elseif ($sortBy == 'division') {
            return $asc ? strcmp($a['division'], $b['division']) : strcmp($b['division'], $a['division']);
        }
        return 0;
    });
    return $data;
}

// Apply sorting
$ascending = isset($_GET['order']) && strtolower($_GET['order']) == 'asc';
$performance = sortPerformance($performance, $sortBy, $ascending);

// Apply filter for top10 or bottom10
if ($filter === 'top10') {
    $performance = array_slice($performance, 0, 10);
} elseif ($filter === 'bottom10') {
    $performance = array_slice($performance, -10);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Overall Performance - Class <?= htmlspecialchars($selectedClass) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .btn-group .btn {
            margin-right: 5px;
        }
        .table thead th {
            white-space: nowrap;
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-4">

    <!-- Added Buttons for Print and Filter at top -->
    <div class="mb-4 d-flex flex-wrap gap-2 align-items-center">
        <a href="?class_level=<?= urlencode($selectedClass) ?>&filter=all&sort=average&order=desc" class="btn btn-primary btn-sm">Print Full List</a>
        <a href="?class_level=<?= urlencode($selectedClass) ?>&filter=top10&sort=average&order=desc" class="btn btn-success btn-sm">Top 10 Best</a>
        <a href="?class_level=<?= urlencode($selectedClass) ?>&filter=bottom10&sort=average&order=asc" class="btn btn-danger btn-sm">10 Worst</a>
        <form action="generate_bulk_reports.php" method="POST" style="display:inline;">
            <input type="hidden" name="class_level" value="<?= htmlspecialchars($selectedClass) ?>" />
            <button type="submit" class="btn btn-warning btn-sm">Generate Bulk PDF Report</button>
        </form>
    </div>

    <h2>Overall Performance - Class <?= htmlspecialchars($selectedClass ?: 'All') ?></h2>

    <!-- Class level toggle buttons -->
    <div class="btn-group mb-3" role="group" aria-label="Class level toggles">
        <?php foreach ($classLevels as $level): ?>
            <a href="?class_level=<?= urlencode($level) ?>&filter=<?= urlencode($filter) ?>&sort=<?= urlencode($sortBy) ?>&order=<?= $ascending ? 'asc' : 'desc' ?>" 
               class="btn btn-sm <?= $selectedClass == $level ? 'btn-primary' : 'btn-outline-primary' ?>">
                Class <?= htmlspecialchars($level) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Filter and sort controls -->
    <form method="get" class="mb-3 d-flex gap-2 align-items-center flex-wrap">
        <input type="hidden" name="class_level" value="<?= htmlspecialchars($selectedClass) ?>" />
        
        <label>Filter:</label>
        <select name="filter" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>All Students</option>
            <option value="top10" <?= $filter == 'top10' ? 'selected' : '' ?>>Top 10</option>
            <option value="bottom10" <?= $filter == 'bottom10' ? 'selected' : '' ?>>Bottom 10</option>
        </select>

        <label>Sort by:</label>
        <select name="sort" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="average" <?= $sortBy == 'average' ? 'selected' : '' ?>>Average</option>
            <option value="gpa" <?= $sortBy == 'gpa' ? 'selected' : '' ?>>GPA</option>
            <option value="division" <?= $sortBy == 'division' ? 'selected' : '' ?>>Division</option>
        </select>

        <label>Order:</label>
        <select name="order" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="desc" <?= !$ascending ? 'selected' : '' ?>>Descending</option>
            <option value="asc" <?= $ascending ? 'selected' : '' ?>>Ascending</option>
        </select>
    </form>

    <div class="table-responsive">
    <table class="table table-bordered table-striped bg-white align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Stream</th>
                <?php foreach ($subjectNames as $subjId => $subjName): ?>
                    <th><?= htmlspecialchars($subjName) ?></th>
                <?php endforeach; ?>
                <th>Total</th>
                <th>Average</th>
                <th>Grade</th>
                <th>GPA</th>
                <th>Division</th>
                <th>Points</th>
                <th>Download Report</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($performance): foreach ($performance as $i => $p): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars($p['stream']) ?></td>
                    <?php foreach ($subjectIds as $subjId): 
                        $mark = $marksData[$p['id']][$subjId] ?? '-';
                    ?>
                        <td class="text-center"><?= htmlspecialchars($mark) ?></td>
                    <?php endforeach; ?>
                    <td class="text-center"><?= $p['total'] ?></td>
                    <td class="text-center"><?= $p['average'] ?></td>
                    <td class="text-center"><?= $p['grade'] ?></td>
                    <td class="text-center"><?= $p['gpa'] ?></td>
                    <td class="text-center"><?= $p['division'] ?></td>
                    <td class="text-center"><?= $p['points'] ?></td>
                    <td class="text-center">
                        <a href="download_report.php?student_id=<?= urlencode($p['id']) ?>" class="btn btn-sm btn-success" target="_blank" rel="noopener noreferrer">Download</a>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="<?= 6 + count($subjectNames) ?>" class="text-center">No students found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-3">⬅️ Back to Dashboard</a>

</div>
</body>
</html>
