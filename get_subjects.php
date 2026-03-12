<?php
include 'includes/db.php';

if (isset($_GET['class_id'])) {
    $class_id = intval($_GET['class_id']);

    $stmt = $conn->prepare("SELECT s.id, s.name 
                            FROM subjects s
                            JOIN class_subjects cs ON cs.subject_id = s.id
                            WHERE cs.class_id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<option value=''>--Select Subject--</option>";
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['id']}'>{$row['name']}</option>";
    }
    $stmt->close();
}
?>
