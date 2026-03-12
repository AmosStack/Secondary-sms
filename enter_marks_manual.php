<?php
// enter_marks_manual.php

include "includes/db.php";

$class_id = $_GET['class_id'] ?? null;
$subject_id = $_GET['subject_id'] ?? null;

if (!$class_id || !$subject_id) {
    echo "<div class='alert alert-danger'>❌ Missing class or subject.</div>";
    exit;
}

// Fetch students
$students = [];
$stmt = $conn->prepare("SELECT id, name FROM students WHERE class_id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enter Marks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h2 class="mb-4">Manual Marks Entry</h2>

<form method="POST" action="save_marks.php">
    <input type="hidden" name="subject_id" value="<?= htmlspecialchars($subject_id) ?>">

    <?php foreach ($students as $student): ?>
        <div class="mb-2">
            <label><?= htmlspecialchars($student['name']) ?></label>
            <input type="hidden" name="student_ids[]" value="<?= $student['id'] ?>">
            <input type="number" name="marks[]" class="form-control" required>
        </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-success">Save Marks</button>
</form>

</body>
</html>
