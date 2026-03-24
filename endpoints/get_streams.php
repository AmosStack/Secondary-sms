


<?php
include '../includes/db.php';

$class_level = '';
if (isset($_POST['class_level'])) {
    $class_level = trim($_POST['class_level']);
} elseif (isset($_GET['class_level'])) {
    $class_level = trim($_GET['class_level']);
}

echo '<option value="">-- Select Stream --</option>';

if ($class_level !== '') {
    $stmt = $conn->prepare("SELECT DISTINCT stream FROM classes WHERE TRIM(class_level) = TRIM(?) ORDER BY stream");
    $stmt->bind_param("s", $class_level);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($row['stream']) . '">' . htmlspecialchars($row['stream']) . '</option>';
    }
}
?>

