<?php
session_start();
include('includes/db.php'); // This sets up $conn

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $plainPassword = trim($_POST['password'] ?? '');

    if ($username === '' || $email === '' || $plainPassword === '') {
        $_SESSION['error'] = "All fields are required.";
        header("Location: login");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header("Location: login");
        exit;
    }

    $password = password_hash($plainPassword, PASSWORD_DEFAULT);

    $checkSql = "SELECT admin_id FROM admin WHERE email = ?";
    $checkStmt = $conn->prepare($checkSql);

    if ($checkStmt) {
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult && $checkResult->num_rows > 0) {
            $_SESSION['error'] = "An account with this email already exists.";
            $checkStmt->close();
            header("Location: login");
            exit;
        }

        $checkStmt->close();
    }

    $sql = "INSERT INTO admin (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sss", $username, $email, $password);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Registration successful. Please login.";
            header("Location: login");
            exit;
        } else {
            $_SESSION['error'] = "Registration failed. Please try again.";
            header("Location: login");
            exit;
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "System error while creating account.";
        header("Location: login");
        exit;
    }
}

?>
