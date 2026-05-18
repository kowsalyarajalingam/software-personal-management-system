<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html"); exit();
}

$user_id  = $_SESSION['user_id'];
$fullName = $_SESSION['fullname'];

$stmt = $pdo->prepare("SELECT * FROM leave_applications WHERE user_id=? ORDER BY id DESC");
$stmt->execute([$user_id]);
$leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Leaves — EMS</title>
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
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; }
.page-header h2 { font-size:17px; font-weight:600; }

.btn-primary { padding:8px 16px; background:#2c3e50; color:#fff; border:none; border-radius:4px; cursor:pointer; font-family:inherit; font-size:13px; font-weight:500; display:inline-block; }
.btn-primary:hover { background:#3d5166; }

.card { background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:18px; }

table { width:100%; border-collapse:collapse; font-size:13px; }
th { text-align:left; padding:9px 10px; background:#f9f9f9; color:#555; font-weight:500; border-bottom:1px solid #e0e0e0; }
td { padding:9px 10px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:#fafafa; }

.badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:600; }
.badge-approved { background:#e8f8f0; color:#27ae60; }
.badge-rejected { background:#fdecea; color:#e74c3c; }
.badge-pending  { background:#fef9e7; color:#f39c12; }
</style>
</head>
<body>

<nav class="topnav">
  <div class="topnav-brand">EMS Portal <span>Employee Panel</span></div>
  <div class="topnav-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="apply_leave.php">Apply Leave</a>
    <a href="my_leaves.php" class="active">My Leaves</a>
    <a href="salary.php">My Salary</a>
    <a href="profile.php">My Profile</a>
  </div>
  <div class="topnav-right">
    <a href="logout.php" class="logout">Logout</a>
  </div>
</nav>

<div class="page">
  <div class="page-header">
    <h2>My Leave Applications</h2>
    <a href="apply_leave.php" class="btn-primary">+ Apply Leave</a>
  </div>

  <div class="card">
    <table>
      <thead>
        <tr><th>Leave Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php if (empty($leaves)): ?>
          <tr><td colspan="6" style="text-align:center;color:#aaa;padding:24px">No leave records found.</td></tr>
        <?php else: foreach ($leaves as $l):
          $from = new DateTime($l['from_date']);
          $to   = new DateTime($l['to_date']);
          $days = $from->diff($to)->days + 1;
          $st   = strtolower($l['status']);
        ?>
          <tr>
            <td><?php echo htmlspecialchars($l['leave_type']); ?></td>
            <td><?php echo date('d M Y', strtotime($l['from_date'])); ?></td>
            <td><?php echo date('d M Y', strtotime($l['to_date'])); ?></td>
            <td><?php echo $days; ?> day<?php echo $days>1?'s':''; ?></td>
            <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo htmlspecialchars($l['reason'] ?: '—'); ?></td>
            <td>
              <?php if ($st==='approved') echo '<span class="badge badge-approved">Approved</span>';
                    elseif ($st==='rejected') echo '<span class="badge badge-rejected">Rejected</span>';
                    else echo '<span class="badge badge-pending">Pending</span>'; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>