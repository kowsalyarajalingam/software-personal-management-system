<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html"); exit();
}

$user_id  = $_SESSION['user_id'];
$fullName = $_SESSION['fullname'];
$message  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = $_POST['leave_type'];
    $from_date  = $_POST['from_date'];
    $to_date    = $_POST['to_date'];
    $reason     = trim($_POST['reason']);
    try {
        $pdo->prepare("INSERT INTO leave_applications (user_id, leave_type, from_date, to_date, reason, status) VALUES (?,?,?,?,?,'Pending')")
            ->execute([$user_id, $leave_type, $from_date, $to_date, $reason]);
        $message = '<p class="msg-success">Leave application submitted successfully!</p>';
    } catch (PDOException $e) {
        $message = '<p class="msg-error">Error: ' . $e->getMessage() . '</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Apply Leave — EMS</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Poppins',sans-serif; background:#f4f6f9; color:#333; font-size:14px; }
a { text-decoration:none; color:inherit; }

.topnav {
  background:#2c3e50; display:flex; align-items:center;
  padding:0 24px; height:54px; position:sticky; top:0; z-index:100;
  box-shadow:0 2px 8px rgba(0,0,0,0.18);
}
.topnav-brand { color:#fff; font-size:15px; font-weight:600; margin-right:32px; white-space:nowrap; line-height:1.2; }
.topnav-brand span { font-size:11px; color:#95a5a6; display:block; font-weight:400; }
.topnav-links { display:flex; flex:1; gap:2px; }
.topnav-links a { color:#bdc3c7; padding:8px 13px; border-radius:4px; font-size:13px; white-space:nowrap; transition:background .15s; }
.topnav-links a:hover, .topnav-links a.active { background:rgba(255,255,255,0.12); color:#fff; }
.topnav-right { display:flex; align-items:center; gap:16px; font-size:13px; }
.topnav-right .logout { color:#e74c3c; font-weight:500; }
.topnav-right .logout:hover { text-decoration:underline; }

.page {
  padding: 24px;
  display: flex;
  flex-direction: column;
  align-items: center;   /* ✅ centers horizontally */
}

.page-header {
  width: 100%;
  max-width: 560px;
  margin-bottom: 20px;
}

.card {
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  padding: 24px;
  max-width: 560px;
  width: 100%;
}

.card { background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:24px; max-width:560px; }

.form-group { margin-bottom:14px; }
.form-group label { display:block; font-size:12px; color:#666; font-weight:500; margin-bottom:5px; }
.form-group input, .form-group select, .form-group textarea {
  width:100%; padding:9px 11px; border:1px solid #ccc; border-radius:4px;
  font-family:inherit; font-size:13px; color:#333; outline:none;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color:#3498db; }
.form-group textarea { height:90px; resize:vertical; }

.form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

.btn-primary { padding:10px 22px; background:#2c3e50; color:#fff; border:none; border-radius:4px; cursor:pointer; font-family:inherit; font-size:13px; font-weight:500; }
.btn-primary:hover { background:#3d5166; }

.msg-success { background:#e8f8f0; color:#27ae60; padding:10px 14px; border-radius:4px; margin-bottom:16px; font-size:13px; border-left:3px solid #27ae60; }
.msg-error   { background:#fdecea; color:#e74c3c; padding:10px 14px; border-radius:4px; margin-bottom:16px; font-size:13px; border-left:3px solid #e74c3c; }
</style>
</head>
<body>

<nav class="topnav">
  <div class="topnav-brand">EMS Portal <span>Employee Panel</span></div>
  <div class="topnav-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="apply_leave.php" class="active">Apply Leave</a>
    <a href="my_leaves.php">My Leaves</a>
    <a href="salary.php">My Salary</a>
    <a href="profile.php">My Profile</a>
  </div>
  <div class="topnav-right">
    <a href="logout.php" class="logout">Logout</a>
  </div>
</nav>

<div class="page">
  <div class="page-header"><h2>Apply for Leave</h2></div>

  <div class="card">
    <?php echo $message; ?>
    <form method="POST">
      <div class="form-group">
        <label>Leave Type</label>
        <select name="leave_type" required>
          <option value="">— Select Type —</option>
          <option>Casual Leave</option>
          <option>Sick Leave</option>
          <option>Earned Leave</option>
          <option>Emergency Leave</option>
        </select>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>From Date</label>
          <input type="date" name="from_date" required>
        </div>
        <div class="form-group">
          <label>To Date</label>
          <input type="date" name="to_date" required>
        </div>
      </div>
      <div class="form-group">
        <label>Reason</label>
        <textarea name="reason" placeholder="Brief reason for leave..." required></textarea>
      </div>
      <button type="submit" class="btn-primary">Submit Application</button>
    </form>
  </div>
</div>

</body>
</html>