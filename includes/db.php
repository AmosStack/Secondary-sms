<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "mafiga";

mysqli_report(MYSQLI_REPORT_OFF);

try {
    $conn = new mysqli($host, $user, $password, $dbname);
} catch (mysqli_sql_exception $e) {
    error_log("Database connection exception: " . $e->getMessage());
    http_response_code(500);
    die("Database service is unavailable. Start MySQL in XAMPP and try again.");
}

if ($conn->connect_error) {
    error_log("Database connection error: " . $conn->connect_error);
    http_response_code(500);
    die("Database service is unavailable. Start MySQL in XAMPP and try again.");
}

$conn->set_charset("utf8mb4");
?>
