<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html"); exit();
}

$user_id       = $_SESSION['user_id'];
$fullName      = $_SESSION['fullname'];
$selectedMonth = $_GET['month'] ?? date('Y-m');

try {
    $stmt = $pdo->prepare("SELECT * FROM salaries WHERE user_id=? ORDER BY salary_month DESC");
    $stmt->execute([$user_id]);
    $salaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $curSal = null;
    foreach ($salaries as $s) {
        if ($s['salary_month'] === $selectedMonth) { $curSal = $s; break; }
    }
    if (!$curSal && !empty($salaries)) $curSal = $salaries[0];

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Salary — EMS</title>
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

.page { padding:24px; }
.page-header { margin-bottom:22px; }
.page-header h2 { font-size:17px; font-weight:600; }

.salary-cards { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
.salary-card { background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:16px; text-align:center; }
.salary-card .s-label { font-size:12px; color:#888; margin-bottom:6px; }
.salary-card .s-value { font-size:20px; font-weight:600; color:#2c3e50; }

.card { background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:18px; margin-bottom:18px; }
.card h3 { font-size:14px; font-weight:600; margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid #eee; }

.detail-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f5f5f5; font-size:13px; }
.detail-row:last-child { border-bottom:none; }
.detail-row .dk { color:#888; }
.detail-row .dv { font-weight:500; }

table { width:100%; border-collapse:collapse; font-size:13px; }
th { text-align:left; padding:9px 10px; background:#f9f9f9; color:#555; font-weight:500; border-bottom:1px solid #e0e0e0; }
td { padding:9px 10px; border-bottom:1px solid #f0f0f0; }
tr:last-child td { border-bottom:none; }

.badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:500; }
.badge-paid    { background:#e8f8f0; color:#27ae60; }
.badge-pending { background:#fef9e7; color:#f39c12; }
</style>
</head>
<body>

<nav class="topnav">
  <div class="topnav-brand">EMS Portal <span>Employee Panel</span></div>
  <div class="topnav-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="apply_leave.php">Apply Leave</a>
    <a href="my_leaves.php">My Leaves</a>
    <a href="salary.php" class="active">My Salary</a>
    <a href="profile.php">My Profile</a>
  </div>
  <div class="topnav-right">
    <a href="logout.php" class="logout">Logout</a>
  </div>
</nav>

<div class="page">
  <div class="page-header"><h2>My Salary</h2></div>

  <?php if ($curSal): ?>
  <div class="card">
    <h3>Salary Details — <?php echo $curSal['salary_month']; ?></h3>
    <div class="detail-row"><span class="dk">Basic Salary</span><span class="dv">₹<?php echo number_format($curSal['basic_salary'],2); ?></span></div>
    <div class="detail-row"><span class="dk">HRA</span><span class="dv">₹<?php echo number_format($curSal['hra']??0,2); ?></span></div>
    <div class="detail-row"><span class="dk">Transport Allowance</span><span class="dv">₹<?php echo number_format($curSal['transport_allowance']??0,2); ?></span></div>
    <div class="detail-row"><span class="dk">Deductions</span><span class="dv" style="color:#e74c3c">- ₹<?php echo number_format($curSal['deductions'],2); ?></span></div>
    <div class="detail-row" style="font-weight:600"><span class="dk">Net Salary</span><span class="dv" style="color:#27ae60">₹<?php echo number_format($curSal['net_salary'],2); ?></span></div>
    <div class="detail-row"><span class="dk">Status</span>
      <span>
        <?php if(strtolower($curSal['status'])==='paid'): ?>
          <span class="badge badge-paid">Paid</span>
        <?php else: ?>
          <span class="badge badge-pending">Pending</span>
        <?php endif; ?>
      </span>
    </div>
  </div>

  <?php else: ?>
    <div class="card" style="color:#aaa;text-align:center;padding:32px">No salary record found for <?php echo $selectedMonth; ?>.</div>
  <?php endif; ?>

  <div class="card">
    <h3>Salary History</h3>
    <table>
      <thead><tr><th>Month</th><th>Basic</th><th>HRA</th><th>Transport</th><th>Deductions</th><th>Net Salary</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (empty($salaries)): ?>
          <tr><td colspan="7" style="text-align:center;color:#aaa;padding:20px">No salary history.</td></tr>
        <?php else: foreach ($salaries as $s): ?>
          <tr>
            <td><?php echo $s['salary_month']; ?></td>
            <td>₹<?php echo number_format($s['basic_salary'],2); ?></td>
            <td>₹<?php echo number_format($s['hra']??0,2); ?></td>
            <td>₹<?php echo number_format($s['transport_allowance']??0,2); ?></td>
            <td>₹<?php echo number_format($s['deductions'],2); ?></td>
            <td><b>₹<?php echo number_format($s['net_salary'],2); ?></b></td>
            <td>
              <?php if(strtolower($s['status'])==='paid'): ?>
                <span class="badge badge-paid">Paid</span>
              <?php else: ?>
                <span class="badge badge-pending">Pending</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>