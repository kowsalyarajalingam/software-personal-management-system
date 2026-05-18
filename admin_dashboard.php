<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html"); exit();
}

$fullName = $_SESSION['fullname'];

try {
    $totalEmployees = $pdo->query("SELECT COUNT(*) FROM users WHERE role='employee'")->fetchColumn();
    $presentToday   = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date=CURDATE() AND status='present'")->fetchColumn();
    $pendingLeaves  = $pdo->query("SELECT COUNT(*) FROM leave_applications WHERE status='Pending'")->fetchColumn();
    $totalDepts     = $pdo->query("SELECT COUNT(DISTINCT dept_name) FROM users WHERE dept_name IS NOT NULL AND dept_name != ''")->fetchColumn();
    $recentEmployees = $pdo->query("SELECT fullname, employee_id, dept_name FROM users WHERE role='employee' ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $recentLeaves    = $pdo->query("SELECT l.*, u.fullname FROM leave_applications l JOIN users u ON l.user_id=u.id ORDER BY l.id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin Dashboard — EMS</title>
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
.topnav-right { display:flex; align-items:center; gap:16px; font-size:13px; color:#95a5a6; }
.topnav-right .date { font-size:12px; }
.topnav-right .logout { color:#e74c3c; font-weight:500; }
.topnav-right .logout:hover { text-decoration:underline; }

.page { padding:24px; }
.page-header { margin-bottom:22px; }
.page-header h2 { font-size:17px; font-weight:600; }
.page-header p { font-size:12px; color:#888; margin-top:3px; }

.summary-bar { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:24px; }
.summary-item { background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:16px; }
.summary-item .label { font-size:12px; color:#888; margin-bottom:4px; }
.summary-item .value { font-size:22px; font-weight:600; color:#2c3e50; }

.grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
.card { background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:18px; }
.card h3 { font-size:14px; font-weight:600; margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
.card h3 a { font-size:12px; color:#3498db; font-weight:400; }

table { width:100%; border-collapse:collapse; font-size:13px; }
th { text-align:left; padding:8px 10px; background:#f9f9f9; color:#666; font-weight:500; border-bottom:1px solid #eee; }
td { padding:9px 10px; border-bottom:1px solid #f0f0f0; color:#444; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:#fafafa; }

.badge { display:inline-block; padding:2px 10px; border-radius:12px; font-size:11px; font-weight:500; }
.badge-green  { background:#e8f8f0; color:#27ae60; }
.badge-red    { background:#fdecea; color:#e74c3c; }
.badge-yellow { background:#fef9e7; color:#f39c12; }
</style>
</head>
<body>

<nav class="topnav">
  <div class="topnav-brand">EMS Portal <span>Admin Panel</span></div>
  <div class="topnav-links">
    <a href="admin_dashboard.php" class="active">Dashboard</a>
    <a href="manage_employees.php">Manage Employees</a>
    <a href="admin_attendance.php">Attendance</a>
    <a href="admin_leaves.php">Leave Requests</a>
    <a href="admin_salary.php">Salary</a>
    <a href="admin_profile.php">My Profile</a>
  </div>
  <div class="topnav-right">
    <span class="date"><?php echo date('D, d M Y'); ?></span>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</nav>

<div class="page">
  <div class="page-header">
    <h2>Admin Dashboard</h2>
    <p>Welcome back, <?php echo htmlspecialchars($fullName); ?></p>
  </div>

  
  <div class="grid-2">
    <div class="card">
      <h3>Recent Employees <a href="manage_employees.php">View All</a></h3>
      <table>
        <thead><tr><th>Name</th><th>Employee ID</th><th>Department</th></tr></thead>
        <tbody>
          <?php if (empty($recentEmployees)): ?>
            <tr><td colspan="3" style="text-align:center;color:#aaa;padding:16px">No employees yet.</td></tr>
          <?php else: foreach ($recentEmployees as $e): ?>
            <tr>
              <td><?php echo htmlspecialchars($e['fullname']); ?></td>
              <td><?php echo htmlspecialchars($e['employee_id'] ?: '—'); ?></td>
              <td><?php echo htmlspecialchars($e['dept_name'] ?: '—'); ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <div class="card">
      <h3>Recent Leave Requests <a href="admin_leaves.php">View All</a></h3>
      <table>
        <thead><tr><th>Employee</th><th>Type</th><th>Status</th></tr></thead>
        <tbody>
          <?php if (empty($recentLeaves)): ?>
            <tr><td colspan="3" style="text-align:center;color:#aaa;padding:16px">No leave requests.</td></tr>
          <?php else: foreach ($recentLeaves as $l): $st = strtolower($l['status']); ?>
            <tr>
              <td><?php echo htmlspecialchars($l['fullname']); ?></td>
              <td><?php echo htmlspecialchars($l['leave_type']); ?></td>
              <td>
                <?php if ($st==='approved') echo '<span class="badge badge-green">Approved</span>';
                      elseif ($st==='rejected') echo '<span class="badge badge-red">Rejected</span>';
                      else echo '<span class="badge badge-yellow">Pending</span>'; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>