<?php
include 'includes/db.php';

if (
    empty($_POST['subject_id']) ||
    empty($_POST['student_ids']) ||
    empty($_POST['marks'])
) {
    die("❌ Error: Missing subject ID, student IDs, or marks.");
}

$subject_id = intval($_POST['subject_id']);
$student_ids = $_POST['student_ids'];
$marks = $_POST['marks'];

if (count($student_ids) !== count($marks)) {
    die("❌ Error: Mismatch between students and marks.");
}

for ($i = 0; $i < count($student_ids); $i++) {
    $student_id = intval($student_ids[$i]);
    $mark = intval($marks[$i]);

    $stmt = $conn->prepare("INSERT INTO marks (student_id, subject_id, marks) 
                            VALUES (?, ?, ?) 
                            ON DUPLICATE KEY UPDATE marks = ?");
    $stmt->bind_param("iiii", $student_id, $subject_id, $mark, $mark);
    $stmt->execute();
}

echo "✅ Marks saved successfully!";
