<?php
include 'includes/db.php';

// Get selected class_level from GET param (optional)
$class_level = $_GET['class_level'] ?? '';
$class_level = htmlspecialchars($class_level);

// Get streams of this class_level
$streams = [];
if ($class_level) {
    $stmt = $conn->prepare("SELECT DISTINCT stream FROM classes WHERE class_level = ?");
    $stmt->bind_param("s", $class_level);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $streams[] = $row['stream'];
    }
    $stmt->close();
}

// Get selected stream from GET param (optional)
$stream_filter = $_GET['stream'] ?? 'all';

// Fetch classes matching the filters to get class_ids
$class_ids = [];
if ($class_level) {
    if ($stream_filter == 'all') {
        $stmt = $conn->prepare("SELECT class_id FROM classes WHERE class_level = ?");
        $stmt->bind_param("s", $class_level);
    } else {
        $stmt = $conn->prepare("SELECT class_id FROM classes WHERE class_level = ? AND stream = ?");
        $stmt->bind_param("ss", $class_level, $stream_filter);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $class_ids[] = $row['class_id'];
    }
    $stmt->close();
}

// Fetch students in those class_ids
$students = [];
if ($class_ids) {
    $placeholders = implode(',', array_fill(0, count($class_ids), '?'));
    $types = str_repeat('i', count($class_ids));
    $sql = "SELECT s.student_id, s.name, c.class_level, c.stream FROM students s JOIN classes c ON s.class_id = c.class_id WHERE s.class_id IN ($placeholders) ORDER BY s.name";
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
    <title>View Students - Class <?= htmlspecialchars($class_level) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<a href="dashboard.php" class="btn btn-secondary position-fixed top-0 end-0 m-3" style="z-index: 1030;">⬅️ Back to Dashboard</a>

<div class="container mt-4">
    <h2>Students in Class <?= htmlspecialchars($class_level ?: 'All') ?></h2>

    <?php if ($class_level): ?>
        <div class="mb-3">
            <strong>Streams:</strong>
            <a href="?class_level=<?= urlencode($class_level) ?>&stream=all" class="btn btn-sm <?= $stream_filter == 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
            <?php foreach ($streams as $stream): ?>
                <a href="?class_level=<?= urlencode($class_level) ?>&stream=<?= urlencode($stream) ?>" class="btn btn-sm <?= $stream_filter == $stream ? 'btn-primary' : 'btn-outline-primary' ?>">
                    <?= htmlspecialchars($stream) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <table class="table table-bordered table-striped bg-white">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Class Level</th>
                <th>Stream</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($students): ?>
                <?php foreach ($students as $i => $student): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($student['name']) ?></td>
                        <td><?= htmlspecialchars($student['class_level']) ?></td>
                        <td><?= htmlspecialchars($student['stream']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">No students found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>
