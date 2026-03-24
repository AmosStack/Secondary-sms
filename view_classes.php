<?php
include 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Classes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <a href="dashboard.php" class="btn btn-secondary position-fixed top-0 end-0 m-3" style="z-index: 1030;">⬅️ Back to Dashboard</a>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">📘 Registered Classes and Streams</h4>
            </div>
            <div class="card-body">
                <?php
                $query = "SELECT * FROM classes ORDER BY class_level ASC, stream ASC";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    echo "<table class='table table-striped table-hover'>";
                    echo "<thead class='table-light'>
                            <tr>
                                <th>#</th>
                                <th>Class Level</th>
                                <th>Stream</th>
                                <th>Actions</th>
                            </tr>
                          </thead>";
                    echo "<tbody>";
                    $i = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$i}</td>
                                <td>Form {$row['class_level']}</td>
                                <td>{$row['stream']}</td>
                                <td>
                                    <a class='btn btn-sm btn-outline-primary' href='edit_class.php?id={$row['class_id']}'>Edit</a>
                                    <a class='btn btn-sm btn-outline-danger' href='delete_class.php?id={$row['class_id']}' onclick=\"return confirm('Delete this class?');\">Delete</a>
                                </td>
                              </tr>";
                        $i++;
                    }
                    echo "</tbody></table>";
                } else {
                    echo "<p class='text-muted'>No classes registered yet.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
