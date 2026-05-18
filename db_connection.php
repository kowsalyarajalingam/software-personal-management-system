<?php
$host = 'localhost';
$dbname = 'personnel_management';
$username = 'root';
$password = ''; 
$port = 8889;

try {
    // CORRECTED LINE 9: Port moved inside the connection string
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>