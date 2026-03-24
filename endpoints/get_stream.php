<?php
include('../includes/db.php');

if (isset($_POST['class_level'])) {
    $class_level = $_POST['class_level'];
    $sql = "SELECT * FROM classes WHERE class_level = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $class_level);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<option value="">-- Select Stream --</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['class_id'] . '">' . htmlspecialchars($row['stream']) . '</option>';
    }
}
?>
