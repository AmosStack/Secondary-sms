<?php
// Optional: session/authentication check here
include 'includes/db.php';

// Fetch distinct class levels for sidebar dropdowns
$class_levels_res = $conn->query("SELECT DISTINCT class_level FROM classes ORDER BY class_level");
$class_levels = [];
while ($row = $class_levels_res->fetch_assoc()) {
    $class_levels[] = $row['class_level'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Mafiga School Management Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .sidebar {
      height: 100vh;
      background-color: #45d6e9ff;
      color: white;
      padding: 20px;
    }
    .sidebar a {
      color: white;
      text-decoration: none;
      display: block;
      padding: 10px 0;
      border-bottom: 1px solid #495057;
    }
    .sidebar a:hover {
      background-color: #495057;
      border-radius: 5px;
    }
    .dashboard-card {
      border-radius: 16px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      transition: 0.3s;
      padding: 25px;
      height: 100%;
      background-color: white;
    }
    .dashboard-card:hover {
      transform: scale(1.02);
    }
    /* For collapse toggle arrow */
    .sidebar a.d-flex {
      cursor: pointer;
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2 sidebar">
      <h4 class="text-white mb-4">📘 Mafiga SMS</h4>
      <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
      <a href="view_classes.php"><i class="fas fa-chalkboard-teacher"></i> View Classes</a>
      <a href="register_class.php"><i class="fas fa-building"></i> Register Class</a>
      <a href="register_student.php"><i class="fas fa-user-graduate"></i> Register Students</a>
      <a href="create_subject.php"><i class="fas fa-book"></i> Create Subjects</a>
      <a href="enter_results.php"><i class="fas fa-pen"></i> Enter Results</a>

      <!-- Students Dropdown -->
      <a class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#studentsDropdown" role="button" aria-expanded="false" aria-controls="studentsDropdown">
        <span><i class="fas fa-users"></i> Students</span>
        <i class="fas fa-chevron-down"></i>
      </a>
      <div class="collapse ms-3" id="studentsDropdown">
        <a href="view_students.php" class="d-block my-1">View All Students</a>
        <?php foreach ($class_levels as $level): ?>
          <a href="view_students.php?class_level=<?= urlencode($level) ?>" class="d-block my-1">Class <?= htmlspecialchars($level) ?></a>
        <?php endforeach; ?>
      </div>

      <!-- Marks Dropdown -->
      <a class="d-flex justify-content-between align-items-center mt-3" data-bs-toggle="collapse" href="#marksDropdown" role="button" aria-expanded="false" aria-controls="marksDropdown">
        <span><i class="fas fa-table"></i> Marks</span>
        <i class="fas fa-chevron-down"></i>
      </a>
      <div class="collapse ms-3" id="marksDropdown">
        <a href="view_marks.php" class="d-block my-1">View All Marks</a>
        <?php foreach ($class_levels as $level): ?>
          <a href="view_marks.php?class_level=<?= urlencode($level) ?>" class="d-block my-1">Class <?= htmlspecialchars($level) ?></a>
        <?php endforeach; ?>
      </div>

      <a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a>
      <a href="overall_performance.php"><i class="fas fa-trophy"></i> Performance</a>
      <a href="print_reports.php"><i class="fas fa-print"></i> Print Reports</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="col-md-9 col-lg-10 py-4">
      <h2 class="mb-4 text-primary text-center">📊 Mafiga School Management Dashboard</h2>

      <div class="row g-4">
        <div class="col-md-4">
          <a href="register_class.php" class="text-dark">
            <div class="dashboard-card border-primary">
              <h5>🏫 Register Class & Subjects</h5>
              <p>Create classes and assign subjects.</p>
            </div>
          </a>
        </div>

        <div class="col-md-4">
          <a href="register_student.php" class="text-dark">
            <div class="dashboard-card border-success">
              <h5>👨‍🎓 Register Students</h5>
              <p>Add students and assign them to classes.</p>
            </div>
          </a>
        </div>

        <div class="col-md-4">
          <a href="create_subject.php" class="text-dark">
            <div class="dashboard-card border-info">
              <h5>📚 Create Subjects</h5>
              <p>Add subjects to the system.</p>
            </div>
          </a>
        </div>

        <div class="col-md-4">
          <a href="enter_results.php" class="text-dark">
            <div class="dashboard-card border-warning">
              <h5>📝 Enter Results</h5>
              <p>Input student marks per subject.</p>
            </div>
          </a>
        </div>

        <div class="col-md-4">
          <a href="view_students.php" class="text-dark">
            <div class="dashboard-card border-dark">
              <h5>📋 View Students</h5>
              <p>List of all students by class and stream.</p>
            </div>
          </a>
        </div>

        <div class="col-md-4">
          <a href="view_marks.php" class="text-dark">
            <div class="dashboard-card border-secondary">
              <h5>📊 View Marks</h5>
              <p>Check subject-wise results & grades.</p>
            </div>
          </a>
        </div>

        <div class="col-md-4">
          <a href="analytics.php" class="text-dark">
            <div class="dashboard-card border-success">
              <h5>📈 Analytics</h5>
              <p>Visual insights of student performance.</p>
            </div>
          </a>
        </div>

        <div class="col-md-4">
          <a href="overall_performance.php" class="text-dark">
            <div class="dashboard-card border-primary">
              <h5>🏆 Overall Performance</h5>
              <p>Ranked student results from best to last.</p>
            </div>
          </a>
        </div>

        <div class="col-md-4">
          <a href="print_reports.php" class="text-dark" target="_blank">
            <div class="dashboard-card border-danger">
              <h5>🖨️ Print Reports</h5>
              <p>Generate and print academic reports.</p>
            </div>
          </a>
        </div>

      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
