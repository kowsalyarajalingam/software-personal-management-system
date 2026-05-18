<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html"); exit();
}

$message = '';
$selectedDate = $_GET['date'] ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    $date = $_POST['att_date'];
    try {
        foreach ($_POST['attendance'] as $uid => $status) {
            $chk = $pdo->prepare("SELECT id FROM attendance WHERE user_id=? AND date=?");
            $chk->execute([$uid, $date]);
            if ($chk->rowCount() > 0) {
                $pdo->prepare("UPDATE attendance SET status=? WHERE user_id=? AND date=?")->execute([$status, $uid, $date]);
            } else {
                $pdo->prepare("INSERT INTO attendance (user_id, date, status) VALUES (?,?,?)")->execute([$uid, $date, $status]);
            }
        }
        $message = '<p class="msg-success">Attendance saved for ' . date('d M Y', strtotime($date)) . '.</p>';
    } catch (PDOException $e) {
        $message = '<p class="msg-error">Error: ' . $e->getMessage() . '</p>';
    }
}

$employees = $pdo->query("SELECT id, fullname, employee_id, dept_name FROM users WHERE role='employee' ORDER BY fullname")->fetchAll(PDO::FETCH_ASSOC);

$attMap = [];
$attStmt = $pdo->prepare("SELECT user_id, status FROM attendance WHERE date=?");
$attStmt->execute([$selectedDate]);
foreach ($attStmt->fetchAll(PDO::FETCH_ASSOC) as $a) {
    $attMap[$a['user_id']] = $a['status'];
}

$present = count(array_filter($attMap, fn($s) => $s === 'present'));
$absent  = count(array_filter($attMap, fn($s) => $s === 'absent'));
$late    = count(array_filter($attMap, fn($s) => $s === 'late'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Attendance — EMS</title>
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
.topnav-right .logout { color:#e74c3c; font-weight:500; }
.topnav-right .logout:hover { text-decoration:underline; }

.page { padding:24px; }
.page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:22px; flex-wrap:wrap; gap:12px; }
.page-header h2 { font-size:17px; font-weight:600; }
.page-header p { font-size:12px; color:#888; margin-top:3px; }

.summary-bar { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
.summary-item { background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:14px; }
.summary-item .label { font-size:12px; color:#888; margin-bottom:4px; }
.summary-item .value { font-size:20px; font-weight:600; color:#2c3e50; }

.card { background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:18px; margin-bottom:20px; }
.card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid #eee; }
.card h3 { font-size:14px; font-weight:600; }

table { width:100%; border-collapse:collapse; font-size:13px; }
th { text-align:left; padding:9px 10px; background:#f9f9f9; color:#555; font-weight:500; border-bottom:1px solid #e0e0e0; }
td { padding:9px 10px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
tr:last-child td { border-bottom:none; }

.att-options { display:flex; gap:8px; }
.att-options label { display:flex; align-items:center; gap:4px; font-size:13px; cursor:pointer; }
.att-options input[type=radio] { cursor:pointer; }

.btn { padding:7px 14px; border:none; border-radius:4px; cursor:pointer; font-family:inherit; font-size:13px; font-weight:500; }
.btn-primary { background:#2c3e50; color:#fff; }
.btn-sm { padding:5px 12px; font-size:12px; }
.btn-outline { background:#fff; border:1px solid #ccc; color:#555; }
.btn-outline:hover { background:#f0f0f0; }

.date-form { display:flex; gap:8px; align-items:center; }
.date-form input[type=date] { padding:6px 10px; border:1px solid #ccc; border-radius:4px; font-family:inherit; font-size:13px; }

.msg-success { background:#e8f8f0; color:#27ae60; padding:10px 14px; border-radius:4px; margin-bottom:14px; font-size:13px; border-left:3px solid #27ae60; }
.msg-error   { background:#fdecea; color:#e74c3c; padding:10px 14px; border-radius:4px; margin-bottom:14px; font-size:13px; border-left:3px solid #e74c3c; }
</style>
</head>
<body>

<nav class="topnav">
  <div class="topnav-brand">EMS Portal <span>Admin Panel</span></div>
  <div class="topnav-links">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_employees.php">Employees</a>
    <a href="admin_attendance.php" class="active">Attendance</a>
    <a href="admin_leaves.php">Leave Requests</a>
    <a href="admin_salary.php">Salary</a>
    <a href="admin_profile.php">My Profile</a>
  </div>
  <div class="topnav-right">
    <a href="logout.php" class="logout">Logout</a>
  </div>
</nav>

<div class="page">
  <div class="page-header">
    <div><h2>Attendance</h2><p>Mark daily attendance for <?php echo date('d F Y', strtotime($selectedDate)); ?></p></div>
    <form method="GET" class="date-form">
      <input type="date" name="date" value="<?php echo $selectedDate; ?>">
      <button type="submit" class="btn btn-outline btn-sm">View</button>
    </form>
  </div>

  <?php echo $message; ?>

  <div class="summary-bar">
    <div class="summary-item"><div class="label">Total Employees</div><div class="value"><?php echo count($employees); ?></div></div>
    <div class="summary-item"><div class="label">Present</div><div class="value" style="color:#27ae60"><?php echo $present; ?></div></div>
    <div class="summary-item"><div class="label">Absent</div><div class="value" style="color:#e74c3c"><?php echo $absent; ?></div></div>
    <div class="summary-item"><div class="label">Late</div><div class="value" style="color:#f39c12"><?php echo $late; ?></div></div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3>Attendance — <?php echo date('d F Y', strtotime($selectedDate)); ?></h3>
      <div style="display:flex;gap:8px">
        <button type="button" class="btn btn-outline btn-sm" onclick="markAll('present')">Mark All Present</button>
        <button type="submit" form="attForm" class="btn btn-primary btn-sm">Save Attendance</button>
      </div>
    </div>

    <form method="POST" id="attForm">
      <input type="hidden" name="att_date" value="<?php echo $selectedDate; ?>">
      <table>
        <thead>
          <tr><th>#</th><th>Name</th><th>Employee ID</th><th>Department</th><th>Attendance</th></tr>
        </thead>
        <tbody>
          <?php if (empty($employees)): ?>
          <tr><td colspan="5" style="text-align:center;color:#aaa;padding:24px">No employees found.</td></tr>
          <?php else: $i=1; foreach ($employees as $e): $cur = $attMap[$e['id']] ?? ''; ?>
          <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo htmlspecialchars($e['fullname']); ?></td>
            <td><?php echo htmlspecialchars($e['employee_id'] ?: '—'); ?></td>
            <td><?php echo htmlspecialchars($e['dept_name'] ?: '—'); ?></td>
            <td>
              <div class="att-options">
                <label><input type="radio" name="attendance[<?php echo $e['id']; ?>]" value="present" <?php if($cur==='present') echo 'checked'; ?>> Present</label>
                <label><input type="radio" name="attendance[<?php echo $e['id']; ?>]" value="absent"  <?php if($cur==='absent')  echo 'checked'; ?>> Absent</label>
                <label><input type="radio" name="attendance[<?php echo $e['id']; ?>]" value="late"    <?php if($cur==='late')    echo 'checked'; ?>> Late</label>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
      <div style="text-align:right;margin-top:14px">
        <button type="submit" class="btn btn-primary">Save Attendance</button>
      </div>
    </form>
  </div>
</div>

<script>
function markAll(status) {
  document.querySelectorAll('input[type=radio][value=' + status + ']').forEach(r => r.checked = true);
}
</script>
</body>
</html>