<?php
include 'includes/db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_level = $_POST['class_level'];
    $stream = $_POST['stream'];
    $subjects = $_POST['subjects'];

    $stmt = $conn->prepare("INSERT INTO classes (class_level, stream) VALUES (?, ?)");
    $stmt->bind_param("ss", $class_level, $stream);
    $stmt->execute();
    $class_id = $stmt->insert_id;

    foreach ($subjects as $subject_id) {
        $stmt = $conn->prepare("INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $class_id, $subject_id);
        $stmt->execute();
    }

    $message = "<div class='alert alert-success mt-3'>✅ <strong>$class_level - $stream</strong> registered with selected subjects.</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Class - School System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .subject-box {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 10px 15px;
            margin-bottom: 10px;
            background-color: #fff;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">🎓 Register New Class & Stream</h3>
        <a href="dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
    </div>

    <?php echo $message; ?>

    <div class="card shadow">
        <div class="card-body">
            <h5 class="text-primary mb-3">📘 Class Details</h5>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="class_level" class="form-label">Class Level</label>
                        <select name="class_level" id="class_level" class="form-select" required>
                            <option value="">-- Select Form --</option>
                            <option value="Form 1">Form 1</option>
                            <option value="Form 2">Form 2</option>
                            <option value="Form 3">Form 3</option>
                            <option value="Form 4">Form 4</option>
                            <option value="Form 5">Form 5</option>
                            <option value="Form 6">Form 6</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="stream" class="form-label">Stream Name</label>
                        <input type="text" name="stream" id="stream" class="form-control" placeholder="e.g. A, B, Science, Arts" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Subjects</label>
                        <div class="row">
                            <?php
                            $result = $conn->query("SELECT * FROM subjects");
                            while ($row = $result->fetch_assoc()) {
                                echo "
                                <div class='col-md-6'>
                                    <div class='form-check subject-box'>
                                        <input class='form-check-input' type='checkbox' name='subjects[]' value='{$row['subject_id']}' id='subject_{$row['subject_id']}'>
                                        <label class='form-check-label' for='subject_{$row['subject_id']}'>
                                            {$row['name']}
                                        </label>
                                    </div>
                                </div>
                                ";
                            }
                            ?>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">💾 Register Class</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
