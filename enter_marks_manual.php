<?php
// enter_marks_manual.php

include "includes/db.php";

$class_id = $_REQUEST['class_id'] ?? null;
$subject_id = $_REQUEST['subject_id'] ?? null;

if (!$class_id || !$subject_id) {
    echo "<div class='alert alert-danger'>❌ Missing class or subject.</div>";
    exit;
}

// Fetch students
$students = [];
$stmt = $conn->prepare("SELECT student_id, full_name FROM students WHERE class_id = ? ORDER BY full_name");
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
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">📝 Manual Marks Entry</h3>
        <a href="dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <h5 class="text-primary mb-3">📘 Enter Marks Per Student</h5>

            <form method="POST" action="endpoints/save_marks.php">
                <input type="hidden" name="subject_id" value="<?= htmlspecialchars($subject_id) ?>">

                <?php foreach ($students as $student): ?>
                    <div class="mb-3">
                        <label class="form-label"><?= htmlspecialchars($student['full_name']) ?></label>
                        <input type="hidden" name="student_ids[]" value="<?= $student['student_id'] ?>">
                        <input type="number" name="marks[]" class="form-control" required>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-primary w-100">💾 Save Marks</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
