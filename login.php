<?php
session_start();
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    try {
        $stmt = $pdo->prepare("SELECT id, fullname, password, role FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password']) && $user['role'] === $role) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role']     = $user['role'];

            // Role-based redirect
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            header("Location: index.html?error=Invalid+email%2C+password%2C+or+role.");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: index.html?error=Database+error+occurred.");
        exit();
    }
}
?>