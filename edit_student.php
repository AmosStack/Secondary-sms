<?php
include 'includes/db.php';

$studentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($studentId <= 0) {
    die('Student ID is missing.');
}

$hasFullName = false;
$hasGender = false;
$check = $conn->query("SHOW COLUMNS FROM students LIKE 'full_name'");
if ($check && $check->num_rows > 0) {
    $hasFullName = true;
}
$check = $conn->query("SHOW COLUMNS FROM students LIKE 'gender'");
if ($check && $check->num_rows > 0) {
    $hasGender = true;
}

$nameColumn = $hasFullName ? 'full_name' : 'name';

$stmt = $conn->prepare("SELECT student_id, {$nameColumn} AS full_name, class_id" . ($hasGender ? ", gender" : "") . " FROM students WHERE student_id = ?");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$studentRes = $stmt->get_result();
$student = $studentRes->fetch_assoc();
$stmt->close();

if (!$student) {
    die('Student not found.');
}

$classResult = $conn->query("SELECT class_id, class_level, stream FROM classes ORDER BY class_level, stream");
$classes = [];
while ($row = $classResult->fetch_assoc()) {
    $classes[] = $row;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $classId = (int)($_POST['class_id'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');

    if ($fullName === '' || $classId <= 0 || ($hasGender && $gender === '')) {
        $message = '<div class="alert alert-danger">Please fill all required fields.</div>';
    } else {
        if ($hasGender) {
            $stmt = $conn->prepare("UPDATE students SET {$nameColumn} = ?, gender = ?, class_id = ? WHERE student_id = ?");
            $stmt->bind_param('ssii', $fullName, $gender, $classId, $studentId);
        } else {
            $stmt = $conn->prepare("UPDATE students SET {$nameColumn} = ?, class_id = ? WHERE student_id = ?");
            $stmt->bind_param('sii', $fullName, $classId, $studentId);
        }

        if ($stmt->execute()) {
            header('Location: view_students.php');
            exit;
        }

        $message = '<div class="alert alert-danger">Failed to update student.</div>';
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 720px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Student</h4>
        <a href="view_students.php" class="btn btn-secondary">Back</a>
    </div>

    <?= $message ?>

    <?php if (!$hasGender): ?>
        <div class="alert alert-warning">Gender column is not yet in database. Run the SQL migration provided to enable gender editing.</div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($student['full_name'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Class & Stream</label>
                    <select name="class_id" class="form-select" required>
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= (int)$class['class_id'] ?>" <?= (int)$class['class_id'] === (int)$student['class_id'] ? 'selected' : '' ?>>
                                Form <?= htmlspecialchars($class['class_level']) ?> - <?= htmlspecialchars($class['stream']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select" <?= $hasGender ? 'required' : 'disabled' ?>>
                        <option value="">-- Select Gender --</option>
                        <option value="Male" <?= (($student['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= (($student['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
