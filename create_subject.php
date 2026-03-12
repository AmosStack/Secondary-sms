<?php
include 'includes/db.php';

$alert = "";

// Handle subject creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);

    if (!empty($name)) {
        // Check if subject already exists
        $stmt = $conn->prepare("SELECT id FROM subjects WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $alert = '<div class="alert alert-warning">⚠️ Subject already exists.</div>';
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO subjects (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                $alert = '<div class="alert alert-success">✅ Subject created successfully!</div>';
            } else {
                $alert = '<div class="alert alert-danger">❌ Failed to create subject.</div>';
            }
        }
        $stmt->close();
    }
}

// Handle subject deletion
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: create_subject.php");
    exit();
}

// Fetch all subjects
$subjects = $conn->query("SELECT * FROM subjects");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Subject</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <h3 class="mb-4 text-primary">📚 Add New Subject</h3>

  <?php if ($alert) echo $alert; ?>

  <form method="POST" class="row g-3 mb-5">
    <div class="col-auto">
      <input type="text" name="name" class="form-control" placeholder="Subject Name" required>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-success">➕ Add Subject</button>
    </div>
  </form>

  <h4 class="text-secondary">📋 Existing Subjects</h4>
  <table class="table table-bordered bg-white">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>Subject Name</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($subjects && $subjects->num_rows > 0): 
        $i = 1;
        while ($row = $subjects->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td>
            <a href="create_subject.php?delete=<?= $row['id'] ?>" 
               onclick="return confirm('Are you sure you want to delete this subject?')" 
               class="btn btn-sm btn-danger">🗑️ Delete</a>
          </td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="3" class="text-center">No subjects found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <a href="dashboard.php" class="btn btn-secondary mt-3">⬅️ Back to Dashboard</a>
</div>

</body>
</html>
