<?php

if (!function_exists('getGradeCommentsMap')) {
    function getGradeCommentsMap(): array {
        return [
            'A' => 'Excellent',
            'B' => 'Very Good',
            'C' => 'Good',
            'D' => 'Fair',
            'E' => 'Poor',
            'S' => 'Very Poor',
            'F' => 'Fail'
        ];
    }
}

if (!function_exists('getSwahiliGradeCommentsMap')) {
    function getSwahiliGradeCommentsMap(): array {
        return [
            'A' => 'UFAULU MZURI',
            'B' => 'VIZURI',
            'C' => 'WASTANI',
            'D' => 'HAFIFU',
            'E' => 'HAFIFU',
            'S' => 'HAFIFU',
            'F' => 'AMEFELI'
        ];
    }
}

if (!function_exists('getGradeF1toF4')) {
    function getGradeF1toF4($avg): string {
        if ($avg >= 75) return 'A';
        if ($avg >= 65) return 'B';
        if ($avg >= 55) return 'C';
        if ($avg >= 35) return 'D';
        return 'F';
    }
}

if (!function_exists('getGradeF5toF6')) {
    function getGradeF5toF6($avg): string {
        if ($avg >= 80) return 'A';
        if ($avg >= 70) return 'B';
        if ($avg >= 60) return 'C';
        if ($avg >= 50) return 'D';
        if ($avg >= 40) return 'E';
        if ($avg >= 35) return 'S';
        return 'F';
    }
}

if (!function_exists('getGradeByForm')) {
    function getGradeByForm(int $form, $avg): string {
        return $form <= 4 ? getGradeF1toF4($avg) : getGradeF5toF6($avg);
    }
}

if (!function_exists('getGradeAndCommentByForm')) {
    function getGradeAndCommentByForm(int $form, $avg, string $language = 'en'): array {
        $grade = getGradeByForm($form, $avg);
        $comments = strtolower($language) === 'sw'
            ? getSwahiliGradeCommentsMap()
            : getGradeCommentsMap();

        return [$grade, $comments[$grade] ?? '-'];
    }
}

if (!function_exists('getGradePointByForm')) {
    function getGradePointByForm(int $form, string $grade): ?int {
        $gradePoints = $form <= 4
            ? ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'F' => 5]
            : ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'S' => 6, 'F' => 7];

        return $gradePoints[$grade] ?? null;
    }
}

if (!function_exists('getDivisionRangesByForm')) {
    function getDivisionRangesByForm(int $form): array {
        return $form <= 4
            ? [
                'Div 1' => [7, 17],
                'Div 2' => [18, 21],
                'Div 3' => [22, 25],
                'Div 4' => [26, 33],
                'Div 0' => [34, 35]
            ]
            : [
                'Div 1' => [3, 9],
                'Div 2' => [10, 12],
                'Div 3' => [13, 17],
                'Div 4' => [18, 19],
                'Div 0' => [20, 21]
            ];
    }
}

if (!function_exists('getDivisionLabelByPoints')) {
    function getDivisionLabelByPoints(int $form, int $totalPoints): string {
        foreach (getDivisionRangesByForm($form) as $division => $range) {
            if ($totalPoints >= $range[0] && $totalPoints <= $range[1]) {
                return $division;
            }
        }

        return 'No division';
    }
}

if (!function_exists('calculateDivisionResult')) {
    function calculateDivisionResult(int $form, array $subjectAverages): array {
        if ($form < 1 || $form > 6 || count($subjectAverages) === 0) {
            return [
                'valid' => false,
                'error' => 'Invalid class or no subjects provided',
                'subject_grades' => [],
                'division_points' => [],
                'used_points' => [],
                'required_subjects' => $form <= 4 ? 7 : 3,
                'total_points' => 0,
                'division' => 'No division'
            ];
        }

        $gradeComments = getGradeCommentsMap();
        $subjectGrades = [];
        $divisionPoints = [];
        $requiredSubjects = $form <= 4 ? 7 : 3;

        foreach ($subjectAverages as $subject => $avg) {
            $grade = getGradeByForm($form, $avg);
            $point = getGradePointByForm($form, $grade);

            $subjectGrades[$subject] = [
                'average' => $avg,
                'grade' => $grade,
                'comment' => $gradeComments[$grade] ?? '-',
                'point' => $point
            ];

            if ($point === null) {
                continue;
            }

            if ($form >= 5 && strtolower(trim((string)$subject)) === 'general studies') {
                continue;
            }

            $divisionPoints[] = $point;
        }

        if (count($divisionPoints) < $requiredSubjects) {
            return [
                'valid' => false,
                'error' => "Not enough valid subjects for division (need at least {$requiredSubjects})",
                'subject_grades' => $subjectGrades,
                'division_points' => $divisionPoints,
                'used_points' => [],
                'required_subjects' => $requiredSubjects,
                'total_points' => 0,
                'division' => 'No division'
            ];
        }

        sort($divisionPoints);
        $usedPoints = array_slice($divisionPoints, 0, $requiredSubjects);
        $totalPoints = array_sum($usedPoints);
        $division = getDivisionLabelByPoints($form, $totalPoints);

        return [
            'valid' => true,
            'error' => null,
            'subject_grades' => $subjectGrades,
            'division_points' => $divisionPoints,
            'used_points' => $usedPoints,
            'required_subjects' => $requiredSubjects,
            'total_points' => $totalPoints,
            'division' => $division
        ];
    }
}

if (!function_exists('getDivision')) {
    function getDivision($form, $subjectAverages): string {
        if (!is_array($subjectAverages)) {
            return 'Invalid class';
        }

        $result = calculateDivisionResult((int)$form, $subjectAverages);
        if (!$result['valid']) {
            return $result['error'] ?? 'No division';
        }

        return $result['division'] . ' (Total Points: ' . $result['total_points'] . ')';
    }
}

