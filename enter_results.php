<?php
include 'includes/db.php'; 
require 'vendor/autoload.php'; // PhpSpreadsheet must be installed via Composer

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get posted data
    $class_level = $_POST['class_level'] ?? null;
    $class_id = $_POST['class_id'] ?? null;
    $subject_id = $_POST['subject_id'] ?? null;

    if (!$class_id || !$subject_id || !isset($_FILES['marks_file'])) {
        echo "<script>alert('❌ Missing class, subject, or file.'); history.back();</script>";
        exit;
    }

    $file = $_FILES['marks_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        // Read column headers
        $headers = array_map('trim', $data[0]);
        $nameIndex = array_search("Name", $headers);

        // Get subject name
        $subjectNameRes = $conn->query("SELECT name FROM subjects WHERE id = $subject_id");
        if (!$subjectNameRes || $subjectNameRes->num_rows == 0) {
            echo "<script>alert('❌ Invalid subject ID.'); history.back();</script>";
            exit;
        }
        $subjectName = $subjectNameRes->fetch_assoc()['name'];

        $markIndex = array_search($subjectName, $headers);

        if ($nameIndex === false || $markIndex === false) {
            echo "<script>alert('❌ Excel must have columns \"Name\" and \"$subjectName\".'); history.back();</script>";
            exit;
        }

        $inserted = 0;

        for ($i = 1; $i < count($data); $i++) {
            $studentName = trim($data[$i][$nameIndex]);
            $marks = trim($data[$i][$markIndex]);

            if ($studentName === "" || $marks === "") continue;

            // Find student by name and class
            $stmt = $conn->prepare("SELECT id FROM students WHERE name = ? AND class_id = ?");
            $stmt->bind_param("si", $studentName, $class_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($student = $result->fetch_assoc()) {
                $student_id = $student['id'];

                // Insert or update mark due to unique(student_id, subject_id)
                $insert = $conn->prepare("INSERT INTO marks (student_id, subject_id, marks, date_recorded) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE marks = VALUES(marks), date_recorded = NOW()");
                $insert->bind_param("iii", $student_id, $subject_id, $marks);
                if ($insert->execute()) $inserted++;
            }
        }

        echo "<script>alert('✅ $inserted marks uploaded successfully.'); window.location.href='enter_results.php';</script>";
        exit;
    } catch (Exception $e) {
        echo "<script>alert('❌ Upload failed: " . $e->getMessage() . "'); history.back();</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Enter Marks</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

</head>
<body>

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="text-primary">🎓 Student Marks Entry Portal</h3>
      <a href="dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
  </div>

  <div class="card shadow">
      <div class="card-body">
          <div class="row">
              <!-- Left Column: Manual Entry -->
              <div class="col-md-6 border-end">
                  <h5 class="text-primary">📘 Manual Entry</h5>
                  <form method="GET" action="enter_marks_manual.php">
                      <!-- Class Level Dropdown -->
                      <div class="mb-3">
                          <label for="class_manual" class="form-label">Select Class Level:</label>
                          <select name="class_level" id="class_manual" class="form-select" required>
                              <option value="">--Select Level--</option>
                              <?php
                              $levels = $conn->query("SELECT DISTINCT class_level FROM classes ORDER BY class_level");
                              while ($row = $levels->fetch_assoc()) {
                                  echo "<option value='{$row['class_level']}'>{$row['class_level']}</option>";
                              }
                              ?>
                          </select>
                      </div>

                      <!-- Stream Dropdown -->
                      <div class="mb-3">
                          <label for="stream_manual" class="form-label">Select Stream:</label>
                          <select name="class_id" id="stream_manual" class="form-select" required>
                              <option value="">--Select Stream--</option>
                          </select>
                      </div>

                      <!-- Subject Dropdown -->
                      <div class="mb-3">
                          <label for="subject_id_manual" class="form-label">Select Subject:</label>
                          <select name="subject_id" id="subject_id_manual" class="form-select" required>
                              <option value="">--Select Subject--</option>
                          </select>
                      </div>

                      <button type="submit" class="btn btn-primary w-100">➡️ Enter Marks Manually</button>
                  </form>
              </div>

              <!-- Right Column: Upload Excel -->
              <div class="col-md-6">
                  <h5 class="text-success">📂 Upload via Excel</h5>
                  <form action="" method="POST" enctype="multipart/form-data">
                      <!-- Class Level Dropdown -->
                      <div class="mb-3">
                          <label for="class_upload" class="form-label">Select Class Level:</label>
                          <select name="class_level" id="class_upload" class="form-select" required>
                              <option value="">--Select Level--</option>
                              <?php
                              $levels->data_seek(0); // Reset pointer
                              while ($row = $levels->fetch_assoc()) {
                                  echo "<option value='{$row['class_level']}'>{$row['class_level']}</option>";
                              }
                              ?>
                          </select>
                      </div>

                      <!-- Stream Dropdown -->
                      <div class="mb-3">
                          <label for="stream_upload" class="form-label">Select Stream:</label>
                          <select name="class_id" id="stream_upload" class="form-select" required>
                              <option value="">--Select Stream--</option>
                          </select>
                      </div>

                      <!-- Subject Dropdown -->
                      <div class="mb-3">
                          <label for="subject_id_upload" class="form-label">Select Subject:</label>
                          <select name="subject_id" id="subject_id_upload" class="form-select" required>
                              <option value="">--Select Subject--</option>
                          </select>
                      </div>

                      <!-- Upload Field -->
                      <div class="mb-3">
                          <label for="marks_file" class="form-label">Upload File:</label>
                          <input type="file" name="marks_file" id="marks_file" class="form-control" accept=".csv,.xlsx,.xls" required>
                      </div>

                      <button type="submit" class="btn btn-success w-100">📤 Upload Marks</button>
                  </form>

                  <div class="mt-3 alert alert-info">
                      <strong>Note:</strong> File must be named <code>marks</code> and contain student names with subject marks (e.g., <code>Name</code>, <code>Mathematics</code>).
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>

<!-- AJAX Script -->
<script>
function loadStreams(level, streamSelectId, subjectSelectId) {
    if (!level) return;
    fetch('get_streams_by_level.php?level=' + encodeURIComponent(level))
        .then(r => r.text())
        .then(data => {
            document.getElementById(streamSelectId).innerHTML = data;
            document.getElementById(subjectSelectId).innerHTML = '<option value="">--Select Subject--</option>';
        });
}

function loadSubjects(classId, subjectSelectId) {
    if (!classId) return;
    fetch('get_subjects.php?class_id=' + encodeURIComponent(classId))
        .then(r => r.text())
        .then(data => {
            document.getElementById(subjectSelectId).innerHTML = data;
        });
}

document.getElementById('class_manual').addEventListener('change', function () {
    loadStreams(this.value, 'stream_manual', 'subject_id_manual');
});
document.getElementById('stream_manual').addEventListener('change', function () {
    loadSubjects(this.value, 'subject_id_manual');
});
document.getElementById('class_upload').addEventListener('change', function () {
    loadStreams(this.value, 'stream_upload', 'subject_id_upload');
});
document.getElementById('stream_upload').addEventListener('change', function () {
    loadSubjects(this.value, 'subject_id_upload');
});
</script>

</body>
</html>
