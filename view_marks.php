<?php
include 'includes/db.php';

// Get selected class_level and sanitize
$class_level = $_GET['class_level'] ?? '';
$class_level = htmlspecialchars($class_level);

// Get selected stream or 'all'
$stream_filter = $_GET['stream'] ?? 'all';

// === Helper Functions for Grades & Divisions ===

function getGradeF1toF4($mark) {
    if ($mark >= 75) return 'A';
    if ($mark >= 65) return 'B';
    if ($mark >= 55) return 'C';
    if ($mark >= 35) return 'D';
    return 'F';
}

function getPointF1toF4($grade) {
    $points_map = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'F' => 5];
    return $points_map[$grade] ?? 0;
}

function getDivisionF1toF4($totalPoints) {
    if ($totalPoints >= 7 && $totalPoints <= 17) return 'Div 1';
    if ($totalPoints >= 18 && $totalPoints <= 21) return 'Div 2';
    if ($totalPoints >= 22 && $totalPoints <= 25) return 'Div 3';
    if ($totalPoints >= 26 && $totalPoints <= 33) return 'Div 4';
    if ($totalPoints >= 34 && $totalPoints <= 35) return 'Div 0';
    return '-';
}

function getGradeF5toF6($mark) {
    if ($mark >= 80) return 'A';
    if ($mark >= 70) return 'B';
    if ($mark >= 60) return 'C';
    if ($mark >= 50) return 'D';
    if ($mark >= 40) return 'E';
    if ($mark >= 35) return 'S';
    return 'F';
}

function getPointF5toF6($grade) {
    $points_map = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'S' => 6, 'F' => 7];
    return $points_map[$grade] ?? 0;
}

function getDivisionF5toF6($totalPoints) {
    if ($totalPoints >= 3 && $totalPoints <= 9) return 'Div 1';
    if ($totalPoints >= 10 && $totalPoints <= 12) return 'Div 2';
    if ($totalPoints >= 13 && $totalPoints <= 17) return 'Div 3';
    if ($totalPoints >= 18 && $totalPoints <= 19) return 'Div 4';
    if ($totalPoints >= 20 && $totalPoints <= 21) return 'Div 0';
    return '-';
}

// === Fetch streams for selected class level ===
$streams = [];
if ($class_level) {
    $stmt = $conn->prepare("SELECT DISTINCT stream FROM classes WHERE class_level = ?");
    $stmt->bind_param("s", $class_level);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $streams[] = $row['stream'];
    }
    $stmt->close();
}

// === Fetch subjects for selected class level and stream (only if stream selected) ===
$subjects = [];
if ($class_level && $stream_filter !== 'all') {
    $stmt = $conn->prepare("SELECT DISTINCT sub.subject_id AS subject_id, sub.name FROM subjects sub
                            JOIN class_subjects cs ON cs.subject_id = sub.subject_id
                            JOIN classes c ON cs.class_id = c.class_id
                            WHERE c.class_level = ? AND c.stream = ?");
    $stmt->bind_param("ss", $class_level, $stream_filter);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $subjects[] = $row;
    }
    $stmt->close();
}

// === Fetch class IDs for the filter ===
$class_ids = [];
if ($class_level) {
    if ($stream_filter === 'all') {
        $stmt = $conn->prepare("SELECT class_id FROM classes WHERE class_level = ?");
        $stmt->bind_param("s", $class_level);
    } else {
        $stmt = $conn->prepare("SELECT class_id FROM classes WHERE class_level = ? AND stream = ?");
        $stmt->bind_param("ss", $class_level, $stream_filter);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $class_ids[] = $row['class_id'];
    }
    $stmt->close();
}

// === Fetch students in the selected classes ===
$students = [];
if ($class_ids) {
    $placeholders = implode(',', array_fill(0, count($class_ids), '?'));
    $types = str_repeat('i', count($class_ids));
    $sql = "SELECT student_id AS student_id, name FROM students WHERE class_id IN ($placeholders) ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$class_ids);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

// === Fetch marks for these students and subjects ===
$marks_data = [];
if ($students && $subjects) {
    $student_ids = array_column($students, 'student_id');
    $subject_ids = array_column($subjects, 'subject_id');

    $student_placeholders = implode(',', array_fill(0, count($student_ids), '?'));
    $subject_placeholders = implode(',', array_fill(0, count($subject_ids), '?'));

    $types = str_repeat('i', count($student_ids) + count($subject_ids));

    $sql = "SELECT m.student_id, m.subject_id, m.marks, m.mark_id AS mark_id FROM marks m
            WHERE m.student_id IN ($student_placeholders) AND m.subject_id IN ($subject_placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$student_ids, ...$subject_ids);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $marks_data[$row['student_id']][$row['subject_id']] = $row;
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>View Marks - Class <?= htmlspecialchars($class_level ?: 'All') ?> Stream <?= htmlspecialchars($stream_filter) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<a href="dashboard.php" class="btn btn-secondary position-fixed top-0 end-0 m-3" style="z-index: 1030;">⬅️ Back to Dashboard</a>

<div class="container mt-4">
    <h2>Marks - Class <?= htmlspecialchars($class_level ?: 'All') ?> Stream <?= htmlspecialchars($stream_filter) ?></h2>

    <!-- Stream filter buttons -->
    <?php if ($class_level): ?>
    <div class="mb-3">
        <strong>Streams:</strong>
        <a href="?class_level=<?= urlencode($class_level) ?>&stream=all" class="btn btn-sm <?= $stream_filter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
        <?php foreach ($streams as $stream): ?>
            <a href="?class_level=<?= urlencode($class_level) ?>&stream=<?= urlencode($stream) ?>" class="btn btn-sm <?= $stream_filter === $stream ? 'btn-primary' : 'btn-outline-primary' ?>">
                <?= htmlspecialchars($stream) ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!$class_level): ?>
        <p class="text-warning">Please select a class level from the dashboard to view marks.</p>
    <?php elseif ($stream_filter === 'all'): ?>
        <p class="text-info">Select a stream to view subjects and marks for that stream.</p>
    <?php elseif (!$subjects): ?>
        <p class="text-danger">No subjects found for Class <?= htmlspecialchars($class_level) ?> Stream <?= htmlspecialchars($stream_filter) ?>.</p>
    <?php else: ?>
        <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <?php foreach ($subjects as $subject): ?>
                        <th><?= htmlspecialchars($subject['name']) ?></th>
                    <?php endforeach; ?>
                    <th>Total</th>
                    <th>Average</th>
                    <th>Grade</th>
                    <th>Points</th>
                    <th>Division</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students): ?>
                    <?php foreach ($students as $index => $student): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($student['name']) ?></td>
                            <?php
                                $totalMarks = 0;
                                $subjectCount = 0;
                                $totalPoints = 0;
                                foreach ($subjects as $subject) {
                                    $markEntry = $marks_data[$student['student_id']][$subject['subject_id']] ?? null;
                                    $mark = $markEntry['marks'] ?? '';
                                    $mark_id = $markEntry['mark_id'] ?? null;

                                    if ($mark !== '' && $mark !== null) {
                                        $totalMarks += $mark;
                                        $subjectCount++;

                                        if ((int)$class_level <= 4) {
                                            $grade = getGradeF1toF4($mark);
                                            $points = getPointF1toF4($grade);
                                        } else {
                                            $grade = getGradeF5toF6($mark);
                                            $points = getPointF5toF6($grade);
                                        }
                                    } else {
                                        $mark = '-';
                                        $points = 0;
                                    }
                                    $totalPoints += $points;
                                    ?>
                                    <td>
                                        <?= htmlspecialchars($mark) ?>
                                        <?php if ($mark_id): ?>
                                            <a href="edit_mark.php?id=<?= $mark_id ?>" class="btn btn-sm btn-outline-primary ms-1">Edit</a>
                                        <?php endif; ?>
                                    </td>
                                <?php
                                }

                                if ($subjectCount) {
                                    $average = $totalMarks / $subjectCount;
                                    if ((int)$class_level <= 4) {
                                        $avgGrade = getGradeF1toF4($average);
                                        $division = getDivisionF1toF4($totalPoints);
                                    } else {
                                        $avgGrade = getGradeF5toF6($average);
                                        $division = getDivisionF5toF6($totalPoints);
                                    }
                                } else {
                                    $average = '-';
                                    $avgGrade = '-';
                                    $division = '-';
                                }
                            ?>
                            <td><?= $subjectCount ? $totalMarks : '-' ?></td>
                            <td><?= $subjectCount ? round($average, 2) : '-' ?></td>
                            <td><?= htmlspecialchars($avgGrade) ?></td>
                            <td><?= $subjectCount ? $totalPoints : '-' ?></td>
                            <td><?= htmlspecialchars($division) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="<?= 5 + count($subjects) ?>" class="text-center">No students found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
