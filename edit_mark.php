<?php
include 'includes/db.php';

$mark_id = $_GET['id'] ?? null;
$mark_id = intval($mark_id);

if (!$mark_id) {
    die("Invalid mark ID.");
}

// Fetch existing mark and related student/class info
$stmt = $conn->prepare("
    SELECT m.marks, s.student_id, s.full_name AS student_name, c.class_level, c.stream
    FROM marks m
    JOIN students s ON m.student_id = s.student_id
    JOIN classes c ON s.class_id = c.class_id
    WHERE m.mark_id = ?
");
$stmt->bind_param("i", $mark_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Mark record not found.");
}

$markData = $result->fetch_assoc();
$stmt->close();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_mark = $_POST['marks'] ?? '';
    $new_mark = trim($new_mark);

    if ($new_mark === '' || !is_numeric($new_mark) || $new_mark < 0 || $new_mark > 100) {
        $error = "Please enter a valid mark between 0 and 100.";
    } else {
        // Update mark in DB
        $stmt = $conn->prepare("UPDATE marks SET marks = ? WHERE mark_id = ?");
        $stmt->bind_param("ii", $new_mark, $mark_id);
        if ($stmt->execute()) {
            $success = "Mark updated successfully.";

            // Redirect back to view_marks.php with class and stream filter preserved
            header("Location: view_marks.php?class_level=" . urlencode($markData['class_level']) . "&stream=" . urlencode($markData['stream']));
            exit;
        } else {
            $error = "Failed to update mark. Try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Mark for <?= htmlspecialchars($markData['student_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container mt-4">
    <h2>Edit Mark for <?= htmlspecialchars($markData['student_name']) ?></h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="marks" class="form-label">Marks (0-100)</label>
            <input
                type="number"
                id="marks"
                name="marks"
                class="form-control"
                min="0"
                max="100"
                value="<?= htmlspecialchars($markData['marks']) ?>"
                required
            />
        </div>

        <button type="submit" class="btn btn-primary">Update Mark</button>
        <a href="view_marks.php?class_level=<?= urlencode($markData['class_level']) ?>&stream=<?= urlencode($markData['stream']) ?>" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

</body>
</html>
