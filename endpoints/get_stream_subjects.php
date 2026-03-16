<?php
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id'])) {
  $class_id = $_POST['class_id'];
  $stmt = $conn->prepare("SELECT stream FROM classes WHERE class_id = ?");
  $stmt->bind_param("i", $class_id);
  $stmt->execute();
  $stmt->bind_result($stream);
  $stmt->fetch();
  $stmt->close();

  // Get subjects
  $subjects = [];
  $stmt = $conn->prepare("SELECT s.subject_id, s.name FROM subjects s JOIN class_subjects cs ON cs.subject_id = s.subject_id WHERE cs.class_id = ?");
  $stmt->bind_param("i", $class_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while($row = $result->fetch_assoc()) {
    $subjects[] = $row;
  }

  echo json_encode(['stream' => $stream, 'subjects' => $subjects]);
}
?>
