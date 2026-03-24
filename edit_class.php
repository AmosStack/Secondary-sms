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

// Fetch all subjects and current class subject mappings
$subjects = [];
$subjectResult = $conn->query('SELECT subject_id, name FROM subjects ORDER BY name');
while ($row = $subjectResult->fetch_assoc()) {
    $subjects[] = $row;
}

$selectedSubjectIds = [];
$stmt = $conn->prepare('SELECT subject_id FROM class_subjects WHERE class_id = ?');
$stmt->bind_param('i', $classId);
$stmt->execute();
$subjectMapResult = $stmt->get_result();
while ($row = $subjectMapResult->fetch_assoc()) {
    $selectedSubjectIds[] = (int)$row['subject_id'];
}
$stmt->close();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classLevel = trim($_POST['class_level'] ?? '');
    $stream = trim($_POST['stream'] ?? '');
    $subjectsInput = $_POST['subjects'] ?? [];
    $subjectsInput = array_values(array_unique(array_map('intval', (array)$subjectsInput)));

    if ($classLevel === '' || $stream === '') {
        $message = '<div class="alert alert-danger">Class level and stream are required.</div>';
    } else {
        $conn->begin_transaction();

        $stmt = $conn->prepare('UPDATE classes SET class_level = ?, stream = ? WHERE class_id = ?');
        $stmt->bind_param('ssi', $classLevel, $stream, $classId);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $stmt = $conn->prepare('DELETE FROM class_subjects WHERE class_id = ?');
            $stmt->bind_param('i', $classId);
            $ok = $stmt->execute();
            $stmt->close();
        }

        if ($ok && $subjectsInput) {
            $stmt = $conn->prepare('INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)');
            foreach ($subjectsInput as $subjectId) {
                $stmt->bind_param('ii', $classId, $subjectId);
                if (!$stmt->execute()) {
                    $ok = false;
                    break;
                }
            }
            $stmt->close();
        }

        if ($ok) {
            $conn->commit();
            header('Location: view_classes.php');
            exit;
        } else {
            $conn->rollback();
            $message = '<div class="alert alert-danger">Failed to update class and subjects.</div>';
        }
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
                <div class="mb-3">
                    <label class="form-label">Subjects</label>
                    <div class="row g-2">
                        <?php foreach ($subjects as $subject): ?>
                            <div class="col-md-6">
                                <div class="form-check border rounded p-2">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="subjects[]"
                                        value="<?= (int)$subject['subject_id'] ?>"
                                        id="subject_<?= (int)$subject['subject_id'] ?>"
                                        <?= in_array((int)$subject['subject_id'], $selectedSubjectIds, true) ? 'checked' : '' ?>
                                    >
                                    <label class="form-check-label" for="subject_<?= (int)$subject['subject_id'] ?>">
                                        <?= htmlspecialchars($subject['name']) ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
