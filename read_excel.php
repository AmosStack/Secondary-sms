<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$inputFileName = 'students.xlsx';

try {
    $spreadsheet = IOFactory::load($inputFileName);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    foreach ($rows as $row) {
        echo implode(" | ", $row) . "<br>";
    }

} catch(Exception $e) {
    echo 'Error loading file: ' . $e->getMessage();
}
?>
