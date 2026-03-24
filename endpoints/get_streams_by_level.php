<?php
include '../includes/db.php';

if (isset($_GET['level'])) {
    $level = $conn->real_escape_string($_GET['level']);
    $result = $conn->query("SELECT class_id, stream FROM classes WHERE class_level = '$level' ORDER BY stream");

    echo "<option value=''>--Select Stream--</option>";
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['class_id']}'>{$row['stream']}</option>";
    }
}
?>
