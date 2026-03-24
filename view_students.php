<?php
include 'includes/db.php';

$classLevels = [];
$res = $conn->query('SELECT DISTINCT class_level FROM classes ORDER BY class_level');
while ($row = $res->fetch_assoc()) {
    $classLevels[] = $row['class_level'];
}

$class_level = $_GET['class_level'] ?? '';
$stream_filter = $_GET['stream'] ?? 'all';

$hasGender = false;
$check = $conn->query("SHOW COLUMNS FROM students LIKE 'gender'");
if ($check && $check->num_rows > 0) {
    $hasGender = true;
}

$nameColumn = 's.full_name';
$nameAlias = 'full_name';
$genderColumn = $hasGender ? ', s.gender' : '';

$streams = [];
if ($class_level !== '') {
    $stmt = $conn->prepare('SELECT DISTINCT stream FROM classes WHERE class_level = ? ORDER BY stream');
    $stmt->bind_param('s', $class_level);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $streams[] = $row['stream'];
    }
    $stmt->close();
}

$class_ids = [];
if ($class_level !== '') {
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
        $class_ids[] = (int)$row['class_id'];
    }
    $stmt->close();
}

$students = [];
if ($class_ids) {
    $placeholders = implode(',', array_fill(0, count($class_ids), '?'));
    $types = str_repeat('i', count($class_ids));
    $sql = "SELECT s.student_id, {$nameColumn} AS {$nameAlias}{$genderColumn}, c.class_level, c.stream
            FROM students s
            JOIN classes c ON s.class_id = c.class_id
            WHERE s.class_id IN ($placeholders)
            ORDER BY {$nameAlias}";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$class_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>View Students<?= $class_level !== '' ? ' - Class ' . htmlspecialchars($class_level) : '' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<a href="dashboard.php" class="btn btn-secondary position-fixed top-0 end-0 m-3" style="z-index: 1030;">Back to Dashboard</a>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Students</h2>
        <a href="register_student.php" class="btn btn-primary btn-sm">Add Student</a>
    </div>

    <?php if (!$hasGender): ?>
        <div class="alert alert-warning">
            Recommended: add <strong>gender</strong> column in students table for complete profile data.
        </div>
    <?php endif; ?>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-4">
            <select name="class_level" class="form-select" onchange="this.form.submit()">
                <option value="">-- Select Class Level --</option>
                <?php foreach ($classLevels as $level): ?>
                    <option value="<?= htmlspecialchars($level) ?>" <?= $class_level === $level ? 'selected' : '' ?>>
                        Class <?= htmlspecialchars($level) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($class_level !== ''): ?>
            <div class="col-md-4">
                <select name="stream" class="form-select" onchange="this.form.submit()">
                    <option value="all" <?= $stream_filter === 'all' ? 'selected' : '' ?>>All Streams</option>
                    <?php foreach ($streams as $stream): ?>
                        <option value="<?= htmlspecialchars($stream) ?>" <?= $stream_filter === $stream ? 'selected' : '' ?>>
                            <?= htmlspecialchars($stream) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Class Level</th>
                    <th>Stream</th>
                    <th style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students): ?>
                    <?php foreach ($students as $i => $student): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($student[$nameAlias] ?? '-') ?></td>
                            <td><?= htmlspecialchars($student['gender'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($student['class_level']) ?></td>
                            <td><?= htmlspecialchars($student['stream']) ?></td>
                            <td>
                                <a href="edit_student.php?id=<?= (int)$student['student_id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="delete_student.php?id=<?= (int)$student['student_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this student?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No students found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
