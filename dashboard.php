<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html"); exit();
}

$user_id  = $_SESSION['user_id'];
$fullName = $_SESSION['fullname'];

try {
    $curMonth = date('Y-m');
    $attStmt  = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM attendance WHERE user_id=? AND DATE_FORMAT(date,'%Y-%m')=? GROUP BY status");
    $attStmt->execute([$user_id, $curMonth]);
    $attStats = $attStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $present = $attStats['present'] ?? 0;
    $absent  = $attStats['absent']  ?? 0;
    $late    = $attStats['late']    ?? 0;

    $leaveStmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM leave_applications WHERE user_id=? GROUP BY status");
    $leaveStmt->execute([$user_id]);
    $leaveStat      = $leaveStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $approvedLeaves = $leaveStat['Approved'] ?? 0;

    $recentLeaveStmt = $pdo->prepare("SELECT * FROM leave_applications WHERE user_id=? ORDER BY id DESC LIMIT 5");
    $recentLeaveStmt->execute([$user_id]);
    $recentLeaves = $recentLeaveStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Employee Dashboard — EMS</title>
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

.summary { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:24px; }
.stat-card { background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:16px; }
.stat-card .label { font-size:12px; color:#888; }
.stat-card .value { font-size:22px; font-weight:600; margin-top:6px; color:#2c3e50; }

.grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
.card { background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:18px; }
.card h3 { font-size:14px; font-weight:600; margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid #eee; }

table { width:100%; border-collapse:collapse; font-size:13px; }
th { text-align:left; background:#f9f9f9; padding:8px 10px; font-weight:500; color:#666; border-bottom:1px solid #eee; }
td { padding:9px 10px; border-bottom:1px solid #f0f0f0; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:#fafafa; }

.badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:500; }
.green  { background:#e8f8f0; color:#27ae60; }
.red    { background:#fdecea; color:#e74c3c; }
.yellow { background:#fef9e7; color:#f39c12; }
</style>
</head>
<body>

<nav class="topnav">
  <div class="topnav-brand">EMS Portal <span>Employee Panel</span></div>
  <div class="topnav-links">
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="apply_leave.php">Apply Leave</a>
    <a href="my_leaves.php">My Leaves</a>
    <a href="salary.php">My Salary</a>
    <a href="profile.php">My Profile</a>
  </div>
  <div class="topnav-right">
    <span class="date"><?php echo date('D, d M Y'); ?></span>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</nav>

<div class="page">
  <div class="page-header">
    <h2>Welcome, <?php echo htmlspecialchars(explode(' ', $fullName)[0]); ?> 👋</h2>
    <p>Here's your overview for <?php echo date('F Y'); ?></p>
  </div>

  <div class="summary">
    <div class="stat-card"><div class="label">Present</div><div class="value" style="color:#27ae60"><?php echo $present; ?></div></div>
    <div class="stat-card"><div class="label">Absent</div><div class="value" style="color:#e74c3c"><?php echo $absent; ?></div></div>
    <div class="stat-card"><div class="label">Late</div><div class="value" style="color:#f39c12"><?php echo $late; ?></div></div>
    <div class="stat-card"><div class="label">Leaves Approved</div><div class="value"><?php echo $approvedLeaves; ?></div></div>
  </div>

  <div class="grid">
    <div class="card">
      <h3>Recent Leave Applications</h3>
      <table>
        <thead><tr><th>Type</th><th>From</th><th>Status</th></tr></thead>
        <tbody>
          <?php if (empty($recentLeaves)): ?>
            <tr><td colspan="3" style="text-align:center;color:#aaa;padding:16px">No applications yet.</td></tr>
          <?php else: foreach ($recentLeaves as $l): $st = strtolower($l['status']); ?>
            <tr>
              <td><?php echo htmlspecialchars($l['leave_type']); ?></td>
              <td><?php echo date('d M', strtotime($l['from_date'])); ?></td>
              <td>
                <?php if ($st==='approved') echo '<span class="badge green">Approved</span>';
                      elseif ($st==='rejected') echo '<span class="badge red">Rejected</span>';
                      else echo '<span class="badge yellow">Pending</span>'; ?>
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