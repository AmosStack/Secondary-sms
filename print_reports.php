<?php
include "includes/db.php";

// Get students for dropdown
$studentsRes = $conn->query("SELECT student_id, full_name, class_id FROM students ORDER BY full_name");

// Fetch classes to get class level and stream labels
$classMap = [];
$classRes = $conn->query("SELECT class_id, class_level, stream FROM classes");
while ($c = $classRes->fetch_assoc()) {
    $classMap[$c['class_id']] = $c;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Print Student Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 760px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0 text-primary">Print Student Report</h3>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <p class="text-muted mb-3">This page uses the exact same report template as the download button in Overall Performance.</p>
            <form method="get" action="download_report.php" target="_blank">
                <div class="mb-3">
                    <label for="student_id" class="form-label">Select Student</label>
                    <select name="student_id" id="student_id" class="form-select" required>
                        <option value="">-- Select Student --</option>
                        <?php while ($stu = $studentsRes->fetch_assoc()):
                            $cls = $classMap[$stu['class_id']] ?? ['class_level' => '', 'stream' => ''];
                            $label = $stu['full_name'] . ' (F' . $cls['class_level'] . ' - ' . $cls['stream'] . ')';
                        ?>
                            <option value="<?= (int)$stu['student_id'] ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Open Printable Report</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
