function getGradeF1toF4($mark) {
    if ($mark >= 75) return 'A';
    elseif ($mark >= 65) return 'B+';
    elseif ($mark >= 50) return 'B';
    elseif ($mark >= 40) return 'C';
    elseif ($mark >= 30) return 'D';
    else return 'F';
}

function getGradeF5toF6($mark) {
    if ($mark >= 80) return 'A';
    elseif ($mark >= 70) return 'B+';
    elseif ($mark >= 60) return 'B';
    elseif ($mark >= 50) return 'C';
    elseif ($mark >= 40) return 'D';
    else return 'F';
}

function gradeToPoint($grade, $level) {
    $map = [
        'A' => 5, 'B+' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'F' => 0
    ];
    return $map[$grade] ?? null;
}

function getOverallGrade($avg) {
    if ($avg >= 75) return 'A';
    elseif ($avg >= 65) return 'B+';
    elseif ($avg >= 50) return 'B';
    elseif ($avg >= 40) return 'C';
    elseif ($avg >= 30) return 'D';
    else return 'F';
}

function getDivision($points, $level) {
    if ($points >= 31) return "I";
    elseif ($points >= 21) return "II";
    elseif ($points >= 11) return "III";
    elseif ($points >= 1) return "IV";
    else return "0";
}
