


<?php
include 'includes/db.php';

if (isset($_POST['class_level'])) {
    $class_level = $_POST['class_level'];

    $stmt = $conn->prepare("SELECT DISTINCT stream FROM classes WHERE class_level = ?");
    $stmt->bind_param("s", $class_level);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<option value="">-- Select Stream --</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($row['stream']) . '">' . htmlspecialchars($row['stream']) . '</option>';
    }
}
?>

