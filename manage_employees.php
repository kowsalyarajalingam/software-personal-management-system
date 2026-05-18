<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html"); exit();
}

$message = '';

// Delete employee
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $pdo->prepare("DELETE FROM users WHERE id=? AND role='employee'")->execute([$_GET['delete']]);
        $message = '<p class="msg-success">Employee deleted successfully.</p>';
    } catch (PDOException $e) {
        $message = '<p class="msg-error">Error: ' . $e->getMessage() . '</p>';
    }
}

// Add employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    $fn   = trim($_POST['fullname']);
    $em   = trim($_POST['email']);
    $pw   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $eid  = trim($_POST['employee_id']);
    $dept = trim($_POST['dept_name']);
    $desig= trim($_POST['designation']);
    $sal  = floatval($_POST['basic_salary']);
    $join = $_POST['join_date'];

    try {
        $pdo->prepare("INSERT INTO users (fullname,email,password,role,employee_id,dept_name,designation,basic_salary,join_date) VALUES (?,?,?,'employee',?,?,?,?,?)")
            ->execute([$fn,$em,$pw,$eid,$dept,$desig,$sal,$join]);
        $message = '<p class="msg-success">Employee added successfully.</p>';
    } catch (PDOException $e) {
        $message = '<p class="msg-error">Error: ' . $e->getMessage() . '</p>';
    }
}

// Update employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_employee'])) {
    $uid   = intval($_POST['edit_id']);
    $fn    = trim($_POST['edit_fullname']);
    $em    = trim($_POST['edit_email']);
    $eid   = trim($_POST['edit_employee_id']);
    $dept  = trim($_POST['edit_dept_name']);
    $desig = trim($_POST['edit_designation']);
    $sal   = floatval($_POST['edit_basic_salary']);
    $join  = $_POST['edit_join_date'];
    $phone = trim($_POST['edit_phone']);
    $gender= $_POST['edit_gender'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET fullname=?, email=?, employee_id=?, dept_name=?, designation=?, basic_salary=?, join_date=?, phone=?, gender=? WHERE id=? AND role='employee'");
        $stmt->execute([$fn, $em, $eid, $dept, $desig, $sal, $join, $phone, $gender, $uid]);

        // Update password only if a new one is provided
        if (!empty($_POST['edit_password'])) {
            $newPw = password_hash($_POST['edit_password'], PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$newPw, $uid]);
        }

        $message = '<p class="msg-success">Employee details updated successfully.</p>';
    } catch (PDOException $e) {
        $message = '<p class="msg-error">Error: ' . $e->getMessage() . '</p>';
    }
}

$employees = $pdo->query("SELECT * FROM users WHERE role='employee' ORDER BY fullname")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Manage Employees — EMS</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Poppins',sans-serif; background:#f4f6f9; color:#333; font-size:14px; }
a { text-decoration:none; color:inherit; }
ul { list-style:none; }

.layout { display:flex; min-height:100vh; }
.sidebar { width:220px; background:#2c3e50; color:#ecf0f1; display:flex; flex-direction:column; position:fixed; height:100vh; }
.sidebar-title { padding:20px 18px; font-size:16px; font-weight:600; border-bottom:1px solid rgba(255,255,255,0.1); }
.sidebar-title span { font-size:12px; display:block; color:#bdc3c7; font-weight:400; margin-top:2px; }
.sidebar nav { padding:10px 0; flex:1; }
.sidebar nav a { display:block; padding:10px 18px; color:#bdc3c7; font-size:13.5px; transition:background .2s; }
.sidebar nav a:hover, .sidebar nav a.active { background:rgba(255,255,255,0.1); color:#fff; }
.sidebar nav .nav-section { font-size:10px; color:#7f8c8d; text-transform:uppercase; letter-spacing:1px; padding:14px 18px 4px; }
.sidebar-bottom { padding:14px 18px; border-top:1px solid rgba(255,255,255,0.1); }
.sidebar-bottom a { color:#e74c3c; font-size:13px; }

.main { margin-left:220px; flex:1; }
.topbar { background:#fff; border-bottom:1px solid #ddd; padding:14px 24px; display:flex; align-items:center; justify-content:space-between; }
.topbar h2 { font-size:16px; font-weight:600; }
.topbar p { font-size:12px; color:#888; margin-top:2px; }
.topbar-right { font-size:12px; color:#888; }
.page { padding:24px; }

.card { background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:18px; margin-bottom:20px; }
.card h3 { font-size:14px; font-weight:600; margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid #eee; }

.form-row { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:12px; }
.form-group { display:flex; flex-direction:column; gap:4px; }
.form-group label { font-size:12px; color:#666; font-weight:500; }
.form-group input, .form-group select {
  padding:8px 10px; border:1px solid #ccc; border-radius:4px;
  font-family:inherit; font-size:13px; color:#333; outline:none;
}
.form-group input:focus, .form-group select:focus { border-color:#3498db; }

.btn { padding:8px 18px; border:none; border-radius:4px; cursor:pointer; font-family:inherit; font-size:13px; font-weight:500; }
.btn-primary { background:#2c3e50; color:#fff; }
.btn-primary:hover { background:#3d5166; }
.btn-danger  { background:#e74c3c; color:#fff; font-size:12px; padding:4px 10px; }
.btn-danger:hover { background:#c0392b; }
.btn-warning { background:#f39c12; color:#fff; font-size:12px; padding:4px 10px; border:none; border-radius:4px; cursor:pointer; font-family:inherit; font-weight:500; }
.btn-warning:hover { background:#d68910; }

table { width:100%; border-collapse:collapse; font-size:13px; }
th { text-align:left; padding:9px 10px; background:#f9f9f9; color:#555; font-weight:500; border-bottom:1px solid #e0e0e0; }
td { padding:9px 10px; border-bottom:1px solid #f0f0f0; color:#444; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:#fafafa; }

.msg-success { background:#e8f8f0; color:#27ae60; padding:10px 14px; border-radius:4px; margin-bottom:14px; font-size:13px; border-left:3px solid #27ae60; }
.msg-error   { background:#fdecea; color:#e74c3c; padding:10px 14px; border-radius:4px; margin-bottom:14px; font-size:13px; border-left:3px solid #e74c3c; }

/* ── MODAL ── */
.modal-overlay {
  display:none;
  position:fixed; inset:0;
  background:rgba(0,0,0,0.45);
  z-index:1000;
  align-items:center;
  justify-content:center;
}
.modal-overlay.open { display:flex; }

.modal {
  background:#fff;
  border-radius:8px;
  width:720px;
  max-width:95vw;
  max-height:90vh;
  overflow-y:auto;
  box-shadow:0 8px 32px rgba(0,0,0,0.18);
  animation:slideIn .2s ease;
}

@keyframes slideIn {
  from { transform:translateY(-20px); opacity:0; }
  to   { transform:translateY(0);     opacity:1; }
}

.modal-header {
  display:flex; justify-content:space-between; align-items:center;
  padding:16px 20px;
  border-bottom:1px solid #eee;
}
.modal-header h3 { font-size:15px; font-weight:600; color:#2c3e50; }
.modal-close {
  background:none; border:none; font-size:20px; cursor:pointer;
  color:#888; line-height:1; padding:0 4px;
}
.modal-close:hover { color:#333; }

.modal-body { padding:20px; }
.modal-footer {
  padding:14px 20px;
  border-top:1px solid #eee;
  display:flex; justify-content:flex-end; gap:10px;
}

.modal .form-row { grid-template-columns:repeat(2,1fr); }
.modal .form-group input,
.modal .form-group select {
  padding:8px 10px;
}

.section-label {
  font-size:11px; font-weight:600; color:#3498db;
  text-transform:uppercase; letter-spacing:.8px;
  margin:14px 0 8px; padding-bottom:4px;
  border-bottom:1px solid #e8f4fd;
}
</style>
</head>
<body>
<div class="layout">

  <div class="sidebar">
    <div class="sidebar-title">EMS Portal<span>Admin Panel</span></div>
    <nav>
      <div class="nav-section">Main</div>
      <a href="admin_dashboard.php">Dashboard</a>
      <div class="nav-section">Employees</div>
      <a href="manage_employees.php" class="active">Manage Employees</a>
      <div class="nav-section">Attendance & Leave</div>
      <a href="admin_attendance.php">Attendance</a>
      <a href="admin_leaves.php">Leave Requests</a>
      <div class="nav-section">Payroll</div>
      <a href="admin_salary.php">Salary Management</a>
      <div class="nav-section">Account</div>
      <a href="admin_profile.php">My Profile</a>
    </nav>
    <div class="sidebar-bottom"><a href="logout.php">Logout</a></div>
  </div>

  <div class="main">
    <div class="topbar">
      <div><h2>Manage Employees</h2><p>Add, update, or remove employees</p></div>
      <div class="topbar-right"><?php echo date('D, d M Y'); ?></div>
    </div>

    <div class="page">
      <?php echo $message; ?>

      <!-- Add Employee Form -->
      <div class="card">
        <h3>Add New Employee</h3>
        <form method="POST">
          <div class="form-row">
            <div class="form-group">
              <label>Full Name *</label>
              <input type="text" name="fullname" placeholder="John Doe" required>
            </div>
            <div class="form-group">
              <label>Email *</label>
              <input type="email" name="email" placeholder="john@example.com" required>
            </div>
            <div class="form-group">
              <label>Password *</label>
              <input type="password" name="password" placeholder="Min 6 chars" required minlength="6">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Employee ID</label>
              <input type="text" name="employee_id" placeholder="EMP001">
            </div>
            <div class="form-group">
              <label>Department</label>
              <input type="text" name="dept_name" placeholder="Engineering">
            </div>
            <div class="form-group">
              <label>Designation</label>
              <input type="text" name="designation" placeholder="Developer">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Basic Salary (₹)</label>
              <input type="number" name="basic_salary" placeholder="30000" step="0.01">
            </div>
            <div class="form-group">
              <label>Join Date</label>
              <input type="date" name="join_date">
            </div>
          </div>
          <button type="submit" name="add_employee" class="btn btn-primary">Add Employee</button>
        </form>
      </div>

      <!-- Employee List -->
      <div class="card">
        <h3>All Employees (<?php echo count($employees); ?>)</h3>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Employee ID</th>
              <th>Department</th>
              <th>Designation</th>
              <th>Basic Salary</th>
              <th>Join Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($employees)): ?>
            <tr><td colspan="9" style="text-align:center;color:#aaa;padding:24px">No employees found.</td></tr>
            <?php else: ?>
            <?php $i = 1; foreach ($employees as $e): ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo htmlspecialchars($e['fullname']); ?></td>
              <td><?php echo htmlspecialchars($e['email']); ?></td>
              <td><?php echo htmlspecialchars($e['employee_id'] ?: '—'); ?></td>
              <td><?php echo htmlspecialchars($e['dept_name'] ?: '—'); ?></td>
              <td><?php echo htmlspecialchars($e['designation'] ?: '—'); ?></td>
              <td><?php echo $e['basic_salary'] ? '₹'.number_format($e['basic_salary'],2) : '—'; ?></td>
              <td><?php echo $e['join_date'] ? date('d M Y', strtotime($e['join_date'])) : '—'; ?></td>
              <td>
                <div style="display:flex;gap:6px">
                  <!-- Edit button triggers modal, passing all employee data -->
                  <button class="btn-warning"
                    onclick="openEdit(
                      <?php echo $e['id']; ?>,
                      <?php echo json_encode($e['fullname']); ?>,
                      <?php echo json_encode($e['email']); ?>,
                      <?php echo json_encode($e['employee_id'] ?? ''); ?>,
                      <?php echo json_encode($e['dept_name'] ?? ''); ?>,
                      <?php echo json_encode($e['designation'] ?? ''); ?>,
                      <?php echo json_encode($e['basic_salary'] ?? ''); ?>,
                      <?php echo json_encode($e['join_date'] ?? ''); ?>,
                      <?php echo json_encode($e['phone'] ?? ''); ?>,
                      <?php echo json_encode($e['gender'] ?? ''); ?>
                    )">Edit</button>
                  <a href="manage_employees.php?delete=<?php echo $e['id']; ?>"
                     class="btn btn-danger"
                     onclick="return confirm('Delete this employee?')">Delete</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

<!-- ── EDIT EMPLOYEE MODAL ── -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Edit Employee Details</h3>
      <button class="modal-close" onclick="closeEdit()">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="edit_id" id="edit_id">

      <div class="modal-body">

        <div class="section-label">Account Info</div>
        <div class="form-row">
          <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="edit_fullname" id="edit_fullname" required>
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input type="email" name="edit_email" id="edit_email" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>New Password <span style="color:#aaa;font-size:11px">(leave blank to keep current)</span></label>
            <input type="password" name="edit_password" placeholder="Enter new password" minlength="6">
          </div>
          <div class="form-group">
            <label>Gender</label>
            <select name="edit_gender" id="edit_gender">
              <option value="">Select</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>

        <div class="section-label">Job Details</div>
        <div class="form-row">
          <div class="form-group">
            <label>Employee ID</label>
            <input type="text" name="edit_employee_id" id="edit_employee_id" placeholder="EMP001">
          </div>
          <div class="form-group">
            <label>Department</label>
            <input type="text" name="edit_dept_name" id="edit_dept_name" placeholder="Engineering">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Designation</label>
            <input type="text" name="edit_designation" id="edit_designation" placeholder="Developer">
          </div>
          <div class="form-group">
            <label>Join Date</label>
            <input type="date" name="edit_join_date" id="edit_join_date">
          </div>
        </div>

        <div class="section-label">Contact & Salary</div>
        <div class="form-row">
          <div class="form-group">
            <label>Phone</label>
            <input type="text" name="edit_phone" id="edit_phone" placeholder="+91 98765 43210">
          </div>
          <div class="form-group">
            <label>Basic Salary (₹)</label>
            <input type="number" name="edit_basic_salary" id="edit_basic_salary" step="0.01" placeholder="30000">
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn" style="background:#eee;color:#555" onclick="closeEdit()">Cancel</button>
        <button type="submit" name="update_employee" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(id, name, email, empId, dept, desig, salary, joinDate, phone, gender) {
  document.getElementById('edit_id').value          = id;
  document.getElementById('edit_fullname').value    = name;
  document.getElementById('edit_email').value       = email;
  document.getElementById('edit_employee_id').value = empId;
  document.getElementById('edit_dept_name').value   = dept;
  document.getElementById('edit_designation').value = desig;
  document.getElementById('edit_basic_salary').value= salary;
  document.getElementById('edit_join_date').value   = joinDate;
  document.getElementById('edit_phone').value       = phone;

  const genderSelect = document.getElementById('edit_gender');
  for (let opt of genderSelect.options) {
    opt.selected = (opt.value === gender);
  }

  document.getElementById('editModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeEdit() {
  document.getElementById('editModal').classList.remove('open');
  document.body.style.overflow = '';
}

// Close on backdrop click
document.getElementById('editModal').addEventListener('click', function(e) {
  if (e.target === this) closeEdit();
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeEdit();
});
</script>
</body>
</html>