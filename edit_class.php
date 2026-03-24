<?php
include 'includes/db.php';

$classId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($classId <= 0) {
    die('Class ID is missing.');
}

$stmt = $conn->prepare('SELECT class_id, class_level, stream FROM classes WHERE class_id = ?');
$stmt->bind_param('i', $classId);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$class) {
    die('Class not found.');
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classLevel = trim($_POST['class_level'] ?? '');
    $stream = trim($_POST['stream'] ?? '');

    if ($classLevel === '' || $stream === '') {
        $message = '<div class="alert alert-danger">Class level and stream are required.</div>';
    } else {
        $stmt = $conn->prepare('UPDATE classes SET class_level = ?, stream = ? WHERE class_id = ?');
        $stmt->bind_param('ssi', $classLevel, $stream, $classId);
        if ($stmt->execute()) {
            header('Location: view_classes.php');
            exit;
        }
        $message = '<div class="alert alert-danger">Failed to update class.</div>';
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Class</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 680px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Class</h4>
        <a href="view_classes.php" class="btn btn-secondary">Back</a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Class Level</label>
                    <input type="text" class="form-control" name="class_level" required value="<?= htmlspecialchars($class['class_level']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Stream</label>
                    <input type="text" class="form-control" name="stream" required value="<?= htmlspecialchars($class['stream']) ?>">
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
