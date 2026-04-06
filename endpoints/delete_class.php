<?php
include 'includes/db.php';

$classId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($classId > 0) {
    // Remove class-subject mapping first to satisfy FK constraints.
    $stmt = $conn->prepare('DELETE FROM class_subjects WHERE class_id = ?');
    $stmt->bind_param('i', $classId);
    $stmt->execute();
    $stmt->close();

    // Delete class only if no student references remain.
    $stmt = $conn->prepare('DELETE FROM classes WHERE class_id = ?');
    $stmt->bind_param('i', $classId);
    $stmt->execute();
    $stmt->close();
}

header('Location: view_classes.php');
exit;
