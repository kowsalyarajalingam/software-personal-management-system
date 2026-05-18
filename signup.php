<?php
session_start();
require 'db_connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    try {
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $checkStmt->execute([':email' => $email]);

        if ($checkStmt->rowCount() > 0) {
            header("Location: signup.html?error=Email is already registered.");
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insertStmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (:fn, :em, :pw, :rl)");
        $insertStmt->execute([
            ':fn' => $fullname,
            ':em' => $email,
            ':pw' => $hashed_password,
            ':rl' => $role
        ]);

        header("Location: index.html?success=Account created! Please log in.");
        exit();

    } catch(PDOException $e) {
        header("Location: signup.html?error=Database error occurred.");
        exit();
    }
}
?>