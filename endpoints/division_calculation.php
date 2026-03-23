<?php
function getGradeF1toF4($avg) {
    if ($avg >= 75) return "A";
    if ($avg >= 65) return "B";
    if ($avg >= 55) return "C";
    if ($avg >= 35) return "D";
    return "F";
}

function getGradeF5toF6($avg) {
    if ($avg >= 80) return "A";
    if ($avg >= 70) return "B";
    if ($avg >= 60) return "C";
    if ($avg >= 50) return "D";
    if ($avg >= 40) return "E";
    if ($avg >= 35) return "S";
    return "F";
}

// Grade comment map
$gradeComments = [
    'A' => 'Excellent',
    'B' => 'Very Good',
    'C' => 'Good',
    'D' => 'Fair',
    'E' => 'Poor',
    'S' => 'Very Poor',
    'F' => 'Fail'
];

function getDivision($form, $subjectAverages) {
    global $gradeComments;

    // Division boundaries
    $divisions_f1_f4 = [
        "Div 1" => [7, 17],
        "Div 2" => [18, 21],
        "Div 3" => [22, 25],
        "Div 4" => [26, 33],
        "Div 0" => [34, 35]
    ];

    $divisions_f5_f6 = [
        "Div 1" => [3, 9],
        "Div 2" => [10, 12],
        "Div 3" => [13, 17],
        "Div 4" => [18, 19],
        "Div 0" => [20, 21]
    ];

    if ($form < 1 || $form > 6 || !is_array($subjectAverages) || count($subjectAverages) === 0) {
        return "Invalid class";
    }

    $subjectGrades = [];
    $divisionPoints = [];

    if ($form <= 4) {
        $gradePoints = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'F' => 5];
        $divisions = $divisions_f1_f4;
        $numSubjects = 7;

        foreach ($subjectAverages as $subject => $avg) {
            $grade = getGradeF1toF4($avg);
            $point = $gradePoints[$grade] ?? null;

            if ($point === null) return "Invalid grade for $subject";

            $subjectGrades[$subject] = [
                'average' => $avg,
                'grade' => $grade,
                'comment' => $gradeComments[$grade] ?? '-',
                'point' => $point
            ];

            $divisionPoints[] = $point;
        }
    } else {
        $gradePoints = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'S' => 6, 'F' => 7];
        $divisions = $divisions_f5_f6;
        $numSubjects = 3;

        foreach ($subjectAverages as $subject => $avg) {
            $grade = getGradeF5toF6($avg);
            $point = $gradePoints[$grade] ?? null;

            if ($point === null) return "Invalid grade for $subject";

            $subjectGrades[$subject] = [
                'average' => $avg,
                'grade' => $grade,
                'comment' => $gradeComments[$grade] ?? '-',
                'point' => $point
            ];

            // ✅ Exclude General Studies from division calculation
            if (strtolower(trim($subject)) !== 'general studies') {
                $divisionPoints[] = $point;
            }
        }
    }

    // Ensure enough subjects for division
    if (count($divisionPoints) < $numSubjects) {
        return "Not enough valid subjects for division (need at least $numSubjects)";
    }

    // Calculate division
    sort($divisionPoints);
    $totalPoints = array_sum(array_slice($divisionPoints, 0, $numSubjects));
    $divisionLabel = "No division";

    foreach ($divisions as $div => [$min, $max]) {
        if ($totalPoints >= $min && $totalPoints <= $max) {
            $divisionLabel = "$div (Total Points: $totalPoints)";
            break;
        }
    }

    // 🖥️ Display Subject Table (All Subjects)
    echo "<h3>Subject Results:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse'>";
    echo "<tr><th>Subject</th><th>Average</th><th>Grade</th><th>Comment</th></tr>";
    foreach ($subjectGrades as $subject => $data) {
        echo "<tr>
            <td>$subject</td>
            <td>{$data['average']}</td>
            <td>{$data['grade']}</td>
            <td>{$data['comment']}</td>
        </tr>";
    }
    echo "</table><br>";

    return $divisionLabel;
}

// 🔹 Sample test
$form6 = [
    'Physics' => 83,
    'Chemistry' => 74,
    'Advanced Math' => 65,
    'General Studies' => 99  // included in display, excluded in division
];

$form3 = [
    'Math' => 82,
    'English' => 72,
    'Science' => 69,
    'Geography' => 55,
    'Civics' => 46,
    'History' => 78,
    'Kiswahili' => 64,
    'ICT' => 36
];

echo "<h2>Form 6 Result:</h2>";
echo getDivision(6, $form6);

echo "<hr>";

echo "<h2>Form 3 Result:</h2>";
echo getDivision(3, $form3);
?>
