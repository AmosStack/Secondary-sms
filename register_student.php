<?php
include 'includes/db.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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
            $gender = trim($row[1]);
            $class_level = trim($row[2]);
            $stream = trim($row[3]);

            $stmt = $conn->prepare("SELECT id FROM classes WHERE class_level = ? AND stream = ?");
            $stmt->bind_param("ss", $class_level, $stream);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0) {
                $class_row = $res->fetch_assoc();
                $class_id = $class_row['id'];

                $check = $conn->prepare("SELECT id FROM students WHERE name = ? AND class_id = ?");
                $check->bind_param("si", $name, $class_id);
                $check->execute();
                $check_res = $check->get_result();

                if ($check_res->num_rows == 0) {
                    $insert = $conn->prepare("INSERT INTO students (name, gender, class_id) VALUES (?, ?, ?)");
                    $insert->bind_param("ssi", $name, $gender, $class_id);
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
    $gender = $_POST['gender'];
    $class_level = $_POST['class_level'];
    $stream = $_POST['stream'];

    if (!empty($name) && !empty($gender) && !empty($class_level) && !empty($stream)) {
        $stmt = $conn->prepare("SELECT id FROM classes WHERE class_level = ? AND stream = ?");
        $stmt->bind_param("ss", $class_level, $stream);
        $stmt->execute();
        $stmt->bind_result($class_id);

        if ($stmt->fetch()) {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO students (name, gender, class_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $name, $gender, $class_id);
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
    <title>Register Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
    .card {
        border-radius: 12px;
    }
    label {
        font-weight: 500;
    }
    </style>

</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4">🧑‍🎓 Register Students</h2>
    <?= $msg ?>

    <div class="row">
        <!-- Manual Registration -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">✍️ Manual Registration</h5>
                    <form method="POST" class="row g-3">
                        <input type="hidden" name="register_manual" value="1">
                        <div class="col-md-12">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label>Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="">-- Select --</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Class Level</label>
                            <select name="class_level" id="class_level" class="form-select" required>
                                <option value="">-- Select Class --</option>
                                <?php while ($row = $levels->fetch_assoc()): ?>
                                    <option value="<?= $row['class_level'] ?>"><?= $row['class_level'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Stream</label>
                            <select name="stream" id="stream" class="form-select" required>
                                <option value="">-- Select Stream --</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-success w-100 mt-2">Register</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Excel Upload -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">📥 Bulk Upload via Excel</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Choose Excel File (.xlsx)</label>
                            <input type="file" name="excel_file" accept=".xlsx" class="form-control" required>
                            <div class="form-text">Expected columns: <b>Name</b>, <b>Gender</b>, <b>Class</b>, <b>Stream</b></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload & Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-3">⬅️ Back to Dashboard</a>
</div>


<script>
$(document).ready(function(){
    $('#class_level').change(function(){
        var classLevel = $(this).val();
        if (classLevel !== '') {
            $.ajax({
                url: 'get_streams.php',
                type: 'POST',
                data: {class_level: classLevel},
                success: function(response){
                    $('#stream').html(response);
                }
            });
        } else {
            $('#stream').html('<option value="">-- Select Stream --</option>');
        }
    });
});
</script>

</body>
</html>
