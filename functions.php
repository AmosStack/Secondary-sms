<?php
require_once __DIR__ . '/endpoints/division_calculation.php';

if (!function_exists('gradeToPoint')) {
    function gradeToPoint($grade, $level) {
        return getGradePointByForm((int)$level, (string)$grade);
    }
}

if (!function_exists('getOverallGrade')) {
    function getOverallGrade($avg) {
        return getGradeByForm(4, $avg);
    }
}
