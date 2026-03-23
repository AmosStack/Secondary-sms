<?php
include 'includes/db.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$msg = "";

// Handle Excel Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $inserted = 0;
        $skipped = 0;

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // skip header

            $name = trim($row[0]);
            $class_level = trim($row[1]);
            $stream = trim($row[2]);

            if ($name === '' || $class_level === '' || $stream === '') {
                $skipped++;
                continue;
            }

            $stmt = $conn->prepare("SELECT id FROM classes WHERE class_level = ? AND stream = ?");
            $stmt->bind_param("ss", $class_level, $stream);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0) {
                $class_row = $res->fetch_assoc();
                $class_id = (int) $class_row['id'];

                $check = $conn->prepare("SELECT id FROM students WHERE name = ? AND class_id = ?");
                $check->bind_param("si", $name, $class_id);
                $check->execute();
                $check_res = $check->get_result();

                if ($check_res->num_rows == 0) {
                    $insert = $conn->prepare("INSERT INTO students (name, class_id) VALUES (?, ?)");
                    $insert->bind_param("si", $name, $class_id);
                    $insert->execute();
                    $inserted++;
                } else {
                    $skipped++;
                }
            } else {
                $skipped++;
            }
        }

        $msg .= "<div class='alert alert-info'>📥 $inserted students added. ⚠️ $skipped skipped.</div>";
    } catch (Exception $e) {
        $msg .= "<div class='alert alert-danger'>❌ Upload failed: " . $e->getMessage() . "</div>";
    }
}

// Handle Manual Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_manual'])) {
    $name = trim($_POST['name']);
    $class_level = $_POST['class_level'];
    $stream = $_POST['stream'];

    if (!empty($name) && !empty($class_level) && !empty($stream)) {
        $stmt = $conn->prepare("SELECT class_id FROM classes WHERE class_level = ? AND stream = ?");
        $stmt->bind_param("ss", $class_level, $stream);
        $stmt->execute();
        $stmt->bind_result($class_id);

        if ($stmt->fetch()) {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO students (name, class_id) VALUES (?, ?)");
            $stmt->bind_param("si", $name, $class_id);
            if ($stmt->execute()) {
                $msg .= '<div class="alert alert-success">✅ Student registered successfully!</div>';
            } else {
                $msg .= '<div class="alert alert-danger">❌ Failed to register student.</div>';
            }
        } else {
            $msg .= '<div class="alert alert-danger">❌ Class and stream combination not found.</div>';
        }
        $stmt->close();
    } else {
        $msg .= '<div class="alert alert-warning">⚠️ All fields are required.</div>';
    }
}

// Fetch distinct class levels
$levels = $conn->query("SELECT DISTINCT class_level FROM classes ORDER BY class_level");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">🧑‍🎓 Student Registration Portal</h3>
        <a href="dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
    </div>
    <?= $msg ?>

    <div class="card shadow">
        <div class="card-body">
            <div class="row">
        <!-- Manual Registration -->
        <div class="col-md-6 border-end">
                    <h5 class="text-primary">📘 Manual Entry</h5>
                    <form method="POST" class="row g-3">
                        <input type="hidden" name="register_manual" value="1">
                        <div class="col-md-12">
                            <label class="form-label">Student Name:</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Select Class Level:</label>
                            <select name="class_level" id="class_level" class="form-select" required>
                                <option value="">--Select Level--</option>
                                <?php while ($row = $levels->fetch_assoc()): ?>
                                    <option value="<?= $row['class_level'] ?>"><?= $row['class_level'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Select Stream:</label>
                            <select name="stream" id="stream" class="form-select" required>
                                <option value="">--Select Stream--</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary w-100">➡️ Register Student</button>
                        </div>
                    </form>
                </div>

        <!-- Excel Upload -->
        <div class="col-md-6">
                    <h5 class="text-success">📂 Upload via Excel</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Upload File:</label>
                            <input type="file" name="excel_file" accept=".xlsx" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">📤 Upload Students</button>
                    </form>

                    <div class="mt-3 alert alert-info">
                        <strong>Note:</strong> File must include columns <code>Name</code>, <code>Class</code>, and <code>Stream</code>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    var classLevelSelect = document.getElementById('class_level');
    var streamSelect = document.getElementById('stream');

    classLevelSelect.addEventListener('change', function () {
        var classLevel = this.value;

        if (!classLevel) {
            streamSelect.innerHTML = '<option value="">--Select Stream--</option>';
            return;
        }

        fetch('get_streams.php?class_level=' + encodeURIComponent(classLevel), {
            method: 'GET'
        })
            .then(function (response) { return response.text(); })
            .then(function (html) {
                streamSelect.innerHTML = html;
            })
            .catch(function () {
                streamSelect.innerHTML = '<option value="">--Select Stream--</option>';
            });
    });
});
</script>

</body>
</html>
