<?php
include 'includes/db.php';


$alert = "";
$edit_id = null;
$edit_name = '';

// Handle edit load
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT name FROM subjects WHERE subject_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $edit_name = $row['name'];
    } else {
        $edit_id = null;
    }
    $stmt->close();
}

// Handle subject update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
    $edit_id = intval($_POST['edit_id']);
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE subjects SET name = ? WHERE subject_id = ?");
        $stmt->bind_param("si", $name, $edit_id);
        if ($stmt->execute()) {
            $alert = '<div class="alert alert-success">✅ Subject updated successfully!</div>';
        } else {
            $alert = '<div class="alert alert-danger">❌ Failed to update subject.</div>';
        }
        $stmt->close();
        $edit_id = null;
        $edit_name = '';
    }
}

// Handle subject creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name']) && !isset($_POST['edit_id'])) {
    $name = trim($_POST['name']);

    if (!empty($name)) {
        // Check if subject already exists
        $stmt = $conn->prepare("SELECT subject_id FROM subjects WHERE name = ?");
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

<a href="dashboard.php" class="btn btn-secondary position-fixed top-0 end-0 m-3" style="z-index: 1030;">⬅️ Back to Dashboard</a>

<div class="container mt-5">
  <div class="mb-3">
    <h3 class="text-primary\"><?= $edit_id ? '✏️ Edit Subject' : '📚 Add New Subject' ?></h3>
  </div>

  <?php if ($alert) echo $alert; ?>

  <div class="card shadow mb-4">
    <div class="card-body">
      <h5 class="text-primary mb-3">📘 Subject Form</h5>
      <form method="POST" class="row g-3 mb-0">
        <?php if ($edit_id): ?>
          <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
        <?php endif; ?>
        <div class="col-md-8">
          <input type="text" name="name" class="form-control" placeholder="Subject Name" value="<?= htmlspecialchars($edit_name) ?>" required>
        </div>
        <div class="col-md-4">
          <button type="submit" class="btn btn-primary w-100"><?= $edit_id ? '💾 Update Subject' : '➕ Add Subject' ?></button>
        </div>
      </form>
      <?php if ($edit_id): ?>
        <a href="create_subject.php" class="btn btn-outline-secondary mt-3">Cancel Edit</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="card shadow">
    <div class="card-body">
      <h5 class="text-success mb-3">📂 Existing Subjects</h5>
      <div class="table-responsive">
        <table class="table table-bordered bg-white mb-0">
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
                  <a href="create_subject.php?edit=<?= $row['subject_id'] ?>" class="btn btn-sm btn-primary">✏️ Edit</a>
                  <a href="create_subject.php?delete=<?= $row['subject_id'] ?>" 
                     onclick="return confirm('Are you sure you want to delete this subject?')" 
                     class="btn btn-sm btn-danger">🗑️ Delete</a>
                </td>
              </tr>
            <?php endwhile; else: ?>
              <tr><td colspan="3" class="text-center">No subjects found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

</body>
</html>
