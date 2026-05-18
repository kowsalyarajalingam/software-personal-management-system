<?php
session_start();
require 'db_connection.php';

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html");
    exit();
}

$message = '';

// Fetch employees
$employees = $pdo->query("SELECT id, fullname FROM users WHERE role='employee' ORDER BY fullname")->fetchAll(PDO::FETCH_ASSOC);

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid   = $_POST['user_id'];
    $basic = $_POST['basic_salary'];
    $hra   = $_POST['hra'];
    $ta    = $_POST['ta'];
    $ded   = $_POST['deductions'];
    $month = $_POST['month'];

    $net = $basic + $hra + $ta - $ded;

    try {
        $stmt = $pdo->prepare("INSERT INTO salaries 
            (user_id, salary_month, basic_salary, hra, transport_allowance, deductions, net_salary, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Paid')");
        $stmt->execute([$uid, $month, $basic, $hra, $ta, $ded, $net]);

        $message = "<p class='msg-success'>Salary saved successfully!</p>";
    } catch (PDOException $e) {
        $message = "<p class='msg-error'>Error occurred!</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Salary Management</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family:'Poppins',sans-serif;
    background:#f4f6f9;
}

/* ===== TOP NAV (same as attendance) ===== */
.topnav {
  background:#2c3e50;
  display:flex;
  align-items:center;
  padding:0 24px;
  height:54px;
  position:sticky;
  top:0;
  z-index:100;
}

.topnav-brand {
  color:#fff;
  font-size:15px;
  font-weight:600;
  margin-right:32px;
}
.topnav-brand span {
  font-size:11px;
  color:#95a5a6;
  display:block;
}

.topnav-links {
  display:flex;
  flex:1;
}
.topnav-links a {
  color:#bdc3c7;
  padding:8px 13px;
  font-size:13px;
  border-radius:4px;
}
.topnav-links a:hover,
.topnav-links a.active {
  background:rgba(255,255,255,0.12);
  color:#fff;
}

.topnav-right .logout {
  color:#e74c3c;
}

/* ===== PAGE ===== */
.page {
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:calc(100vh - 54px);
}

/* ===== CARD ===== */
.container {
    width:420px;
    background:#fff;
    padding:22px;
    border-radius:6px;
    border:1px solid #e0e0e0;
}

h2 { margin-bottom:12px; font-size:16px; }

/* ===== FORM ===== */
label {
    display:block;
    margin-top:10px;
    font-size:12px;
    color:#666;
}

input, select {
    width:100%;
    padding:8px;
    margin-top:4px;
    border:1px solid #ccc;
    border-radius:4px;
}

button {
    width:100%;
    margin-top:15px;
    padding:10px;
    background:#2c3e50;
    color:white;
    border:none;
    border-radius:4px;
    cursor:pointer;
}
button:hover { background:#1a252f; }

/* ===== MESSAGES ===== */
.msg-success {
    background:#e8f8f0;
    color:#27ae60;
    padding:10px;
    margin-bottom:10px;
    border-radius:4px;
}

.msg-error {
    background:#fdecea;
    color:#e74c3c;
    padding:10px;
    margin-bottom:10px;
    border-radius:4px;
}
</style>
</head>

<body>

<!-- ===== TOP NAVBAR ===== -->
<nav class="topnav">
  <div class="topnav-brand">EMS Portal <span>Admin Panel</span></div>

  <div class="topnav-links">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_employees.php">Employees</a>
    <a href="admin_attendance.php">Attendance</a>
    <a href="admin_leaves.php">Leave Requests</a>
    <a href="admin_salary.php" class="active">Salary</a>
    <a href="admin_profile.php">My Profile</a>
  </div>

  <div class="topnav-right">
    <a href="logout.php" class="logout">Logout</a>
  </div>
</nav>

<!-- ===== PAGE CONTENT ===== -->
<div class="page">
<div class="container">

    <h2>Process Salary</h2>

    <?php echo $message; ?>

    <form method="POST">
        
        <label>Select Employee</label>
        <select name="user_id" required>
            <option value="">Select</option>
            <?php foreach ($employees as $emp): ?>
                <option value="<?php echo $emp['id']; ?>">
                    <?php echo htmlspecialchars($emp['fullname']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Month</label>
        <input type="month" name="month" required>

        <label>Basic Salary</label>
        <input type="number" name="basic_salary" required>

        <label>HRA</label>
        <input type="number" name="hra" value="0">

        <label>Transport Allowance</label>
        <input type="number" name="ta" value="0">

        <label>Deductions</label>
        <input type="number" name="deductions" value="0">

        <button type="submit">Save Salary</button>
    </form>

</div>
</div>

</body>
</html>