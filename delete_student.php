<?php
$conn = new mysqli("localhost", "root", "", "mafiga");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = intval($_GET['id']);
$conn->query("DELETE FROM students WHERE id = $id");

header("Location: view_students.php");
exit;
