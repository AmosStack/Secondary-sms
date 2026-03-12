<?php
include('includes/db.php');

if (!isset($_GET['id'])) {
    die("Student ID is missing.");
}

$id = intval($_GET['id']);

// Get student data
$student_sql = "SELECT * FROM students WHERE id = $id";
$student_result = $conn->query($student_sql);
if ($student_result->num_rows === 0) {
    die("Student not found.");
}
$student = $student_result->fetch_assoc();

// Get classes for dropdown
$class_sql = "SELECT * FROM classes GROUP BY class_level";
$class_result = $conn->query($class_sql);

// Get current stream of student
$current_class = $conn->query("SELECT * FROM classes WHERE id = " . $student['class_id'])->fetch_assoc();

// Handle form update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $gender = $_POST["gender"];
    $stream_id = intval($_POST["stream_id"]);

    if ($name && $gender && $stream_id) {
        $update_sql = "UPDATE students SET name=?, gender=?, class_id=? WHERE id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssii", $name, $gender, $stream_id, $id);

        if ($stmt->execute()) {
            $success = "✅ Student updated successfully!";
            $student['name'] = $name;
            $student['gender'] = $gender;
            $student['class_id'] = $stream_id;
        } else {
            $error = "❌ Update failed.";
        }
    } else {
        $error = "❗ All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4 text-primary">✏️ Edit Student</h2>
        <a href="view_students.php" class="btn btn-secondary mb-3">← Back</a>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Student Name</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($student['name']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select" required>
                    <option value="Male" <?= $student['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $student['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Class Level</label>
                <select name="class_level" id="class_level" class="form-select" required>
                    <option value="">-- Select Class Level --</option>
                    <?php while ($row = $class_result->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['class_level']) ?>"
                            <?= $current_class['class_level'] == $row['class_level'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['class_level']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Stream</label>
                <select name="stream_id" id="stream" class="form-select" required>
                    <!-- Stream options loaded by JS -->
                    <option value="<?= $current_class['id'] ?>"><?= $current_class['stream'] ?></option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">💾 Update</button>
        </form>
    </div>

    <script>
    $(document).ready(function(){
        $('#class_level').change(function(){
            var level = $(this).val();
            if (level !== "") {
                $.ajax({
                    url: "get_stream.php",
                    method: "POST",
                    data: { class_level: level },
                    success: function(data){
                        $('#stream').html(data);
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
