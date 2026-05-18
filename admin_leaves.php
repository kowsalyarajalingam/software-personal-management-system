<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html"); exit();
}

$message = '';

if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $lid    = intval($_GET['id']);
    $action = $_GET['action'];
    if (in_array($action, ['approve', 'reject'])) {
        $newStatus = ($action === 'approve') ? 'Approved' : 'Rejected';
        try {
            $pdo->prepare("UPDATE leave_applications SET status=? WHERE id=?")->execute([$newStatus, $lid]);
            $message = '<p class="msg-success">Leave request ' . strtolower($newStatus) . '.</p>';
        } catch (PDOException $e) {
            $message = '<p class="msg-error">Error: ' . $e->getMessage() . '</p>';
        }
    }
}

$filter = $_GET['status'] ?? 'all';
$where  = ($filter !== 'all') ? "WHERE l.status = '" . addslashes($filter) . "'" : '';

try {
    $leaves = $pdo->query("SELECT l.*, u.fullname, u.dept_name, u.employee_id FROM leave_applications l JOIN users u ON l.user_id = u.id $where ORDER BY l.id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Leave Requests — EMS</title>
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
.page-header { margin-bottom:20px; }
.page-header h2 { font-size:17px; font-weight:600; }
.page-header p { font-size:12px; color:#888; margin-top:3px; }

.filter-tabs { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
.filter-tab { padding:5px 14px; border-radius:20px; font-size:12px; font-weight:500; border:1px solid #ccc; color:#555; }
.filter-tab:hover, .filter-tab.active { border-color:#2c3e50; background:#2c3e50; color:#fff; }

.card { background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:18px; margin-bottom:20px; }
.card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid #eee; }
.card h3 { font-size:14px; font-weight:600; }

table { width:100%; border-collapse:collapse; font-size:13px; }
th { text-align:left; padding:9px 10px; background:#f9f9f9; color:#555; font-weight:500; border-bottom:1px solid #e0e0e0; }
td { padding:9px 10px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
tr:last-child td { border-bottom:none; }

.badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:600; }
.badge-green  { background:#e8f8f0; color:#27ae60; }
.badge-red    { background:#fdecea; color:#e74c3c; }
.badge-yellow { background:#fef9e7; color:#f39c12; }

.btn { padding:5px 12px; border:none; border-radius:4px; cursor:pointer; font-family:inherit; font-size:12px; font-weight:500; }
.btn-success { background:#27ae60; color:#fff; }
.btn-success:hover { background:#219150; }
.btn-danger  { background:#e74c3c; color:#fff; }
.btn-danger:hover { background:#c0392b; }

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
    <a href="admin_attendance.php">Attendance</a>
    <a href="admin_leaves.php" class="active">Leave Requests</a>
    <a href="admin_salary.php">Salary</a>
    <a href="admin_profile.php">My Profile</a>
  </div>
  <div class="topnav-right">
    <a href="logout.php" class="logout">Logout</a>
  </div>
</nav>

<div class="page">
  <div class="page-header">
    <h2>Leave Requests</h2>
    <p>Approve or reject employee leave applications</p>
  </div>

  <?php echo $message; ?>

  <div class="filter-tabs">
    <a href="admin_leaves.php?status=all"      class="filter-tab <?php echo ($filter==='all')?'active':''; ?>">All</a>
    <a href="admin_leaves.php?status=Pending"  class="filter-tab <?php echo ($filter==='Pending')?'active':''; ?>">Pending</a>
    <a href="admin_leaves.php?status=Approved" class="filter-tab <?php echo ($filter==='Approved')?'active':''; ?>">Approved</a>
    <a href="admin_leaves.php?status=Rejected" class="filter-tab <?php echo ($filter==='Rejected')?'active':''; ?>">Rejected</a>
  </div>

  <div class="card">
    <div class="card-header"><h3>Leave Applications (<?php echo count($leaves); ?>)</h3></div>
    <table>
      <thead>
        <tr><th>#</th><th>Employee</th><th>Dept</th><th>Leave Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($leaves)): ?>
          <tr><td colspan="10" style="text-align:center;color:#aaa;padding:24px">No leave requests found.</td></tr>
        <?php else: $i=1; foreach ($leaves as $l):
          $from = new DateTime($l['from_date']);
          $to   = new DateTime($l['to_date']);
          $days = $from->diff($to)->days + 1;
          $st   = strtolower($l['status']);
        ?>
        <tr>
          <td><?php echo $i++; ?></td>
          <td><?php echo htmlspecialchars($l['fullname']); ?><br><small style="color:#888"><?php echo htmlspecialchars($l['employee_id'] ?: '—'); ?></small></td>
          <td><?php echo htmlspecialchars($l['dept_name'] ?: '—'); ?></td>
          <td><?php echo htmlspecialchars($l['leave_type']); ?></td>
          <td><?php echo date('d M Y', strtotime($l['from_date'])); ?></td>
          <td><?php echo date('d M Y', strtotime($l['to_date'])); ?></td>
          <td><?php echo $days; ?> day<?php echo $days>1?'s':''; ?></td>
          <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?php echo htmlspecialchars($l['reason']); ?>"><?php echo htmlspecialchars($l['reason'] ?: '—'); ?></td>
          <td>
            <?php if ($st==='approved') echo '<span class="badge badge-green">Approved</span>';
                  elseif ($st==='rejected') echo '<span class="badge badge-red">Rejected</span>';
                  else echo '<span class="badge badge-yellow">Pending</span>'; ?>
          </td>
          <td>
            <?php if ($st === 'pending'): ?>
            <div style="display:flex;gap:6px">
              <a href="admin_leaves.php?action=approve&id=<?php echo $l['id']; ?>&status=<?php echo $filter; ?>" class="btn btn-success">Approve</a>
              <a href="admin_leaves.php?action=reject&id=<?php echo $l['id']; ?>&status=<?php echo $filter; ?>"  class="btn btn-danger">Reject</a>
            </div>
            <?php else: ?><span style="font-size:12px;color:#aaa">Decided</span><?php endif; ?>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>