<?php
session_start();
require 'db_connection.php';

// check login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// update profile
if (isset($_POST['update_profile'])) {
    $phone  = $_POST['phone'];
    $gender = $_POST['gender'];
    $dob    = $_POST['dob'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET phone=?, gender=?, dob=? WHERE id=?");
        $stmt->execute([$phone, $gender, $dob, $user_id]);
        $message = "Profile updated successfully!";
    } catch (Exception $e) {
        $message = "Error updating profile.";
    }
}

// fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$name  = $user['fullname'];
$email = $user['email'];
$phone = $user['phone'];
$gender = $user['gender'];
$dob = $user['dob'];
$dept = $user['dept_name'];
$designation = $user['designation'];

$initials = strtoupper(substr($name, 0, 1));
?>

<!DOCTYPE html>
<html>
<head>
<title>My Profile</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;}

body {
    font-family:'Poppins',sans-serif;
    background:#f4f6f9;
}

/* Top Navbar */
.topnav {
    background:#2c3e50;
    display:flex;
    align-items:center;
    padding:0 20px;
    height:55px;
    color:white;
}
.topnav a {
    color:#bdc3c7;
    margin-right:15px;
    text-decoration:none;
    font-size:13px;
}
.topnav a:hover {
    color:white;
}

/* Page */
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
/* Profile Header */
.profile {
    display:flex;
    align-items:center;
    gap:15px;
    margin-bottom:20px;
}

.avatar {
    width:65px;
    height:65px;
    border-radius:50%;
    background:#5b6ef5;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    font-weight:bold;
}

/* Form */
label {
    font-size:13px;
    color:#555;
}

input, select {
    width:100%;
    padding:9px;
    margin-top:5px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:5px;
}

button {
    padding:10px 20px;
    background:#5b6ef5;
    border:none;
    color:white;
    border-radius:5px;
    cursor:pointer;
}

button:hover {
    background:#3f51b5;
}

/* Message */
.msg {
    margin-bottom:15px;
    color:#27ae60;
}
</style>
</head>

<body>

<!-- Navbar -->
<div class="topnav">
    <strong style="margin-right:20px;">EMS</strong>
    <a href="dashboard.php">Dashboard</a>
    <a href="apply_leave.php">Apply Leave</a>
    <a href="my_leaves.php">My Leaves</a>
    <a href="salary.php">Salary</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</div>

<!-- Page -->
<div class="page">

    <div class="card">

        <div class="profile">
            <div class="avatar"><?php echo $initials; ?></div>
            <div>
                <h2><?php echo htmlspecialchars($name); ?></h2>
                <small><?php echo htmlspecialchars($designation); ?> - <?php echo htmlspecialchars($dept); ?></small>
            </div>
        </div>

        <?php if ($message): ?>
            <p class="msg"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST">

            <label>Email</label>
            <input type="text" value="<?php echo htmlspecialchars($email); ?>" readonly>

            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>">

            <label>Gender</label>
            <select name="gender">
                <option value="">Select</option>
                <option value="Male" <?php if($gender=="Male") echo "selected"; ?>>Male</option>
                <option value="Female" <?php if($gender=="Female") echo "selected"; ?>>Female</option>
                <option value="Other" <?php if($gender=="Other") echo "selected"; ?>>Other</option>
            </select>

            <label>Date of Birth</label>
            <input type="date" name="dob" value="<?php echo $dob; ?>">

            <button type="submit" name="update_profile">Save Profile</button>

        </form>

    </div>

</div>

</body>
</html>