<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id  = $_SESSION['user_id'];
$fullName = $_SESSION['fullname'];

$selectedMonth = $_GET['month'] ?? date('Y-m');

try {

    // Salary list
    $stmt = $pdo->prepare("SELECT * FROM salaries WHERE user_id=? ORDER BY salary_month DESC");
    $stmt->execute([$user_id]);
    $salaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Selected salary
    $curSal = null;
    foreach ($salaries as $s) {
        if ($s['salary_month'] === $selectedMonth) {
            $curSal = $s;
            break;
        }
    }
    if (!$curSal && !empty($salaries)) {
        $curSal = $salaries[0];
    }

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
<title>My Salary</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body{
    font-family: Arial;
    background:#f4f6f9;
    margin:0;
}

.container{
    margin-left:220px;
    padding:20px;
}

.sidebar{
    width:200px;
    height:100vh;
    background:#2c3e50;
    position:fixed;
    color:white;
    padding:15px;
}

.sidebar a{
    display:block;
    color:#ccc;
    padding:10px;
    text-decoration:none;
}

.sidebar a:hover{
    background:#34495e;
    color:white;
}

.card{
    background:white;
    padding:15px;
    border-radius:6px;
    margin-bottom:15px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}

.row{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.box{
    flex:1;
    background:#fff;
    padding:15px;
    border-radius:6px;
    text-align:center;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}

h2,h3{margin:0}

.badge{
    padding:4px 10px;
    border-radius:12px;
    font-size:12px;
}

.paid{background:#d4edda;color:#155724;}
.pending{background:#fff3cd;color:#856404;}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

th,td{
    padding:10px;
    border-bottom:1px solid #eee;
    font-size:13px;
}

th{
    background:#f1f1f1;
    text-align:left;
}
</style>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar">
    <h3>EMS</h3>
    <a href="dashboard.php">Dashboard</a>
    <a href="apply_leave.php">Apply Leave</a>
    <a href="my_leaves.php">My Leaves</a>
    <a href="salary.php">My Salary</a>
    <a href="profile.php">Profile</a>
</div>

<div class="container">

<h2>Welcome, <?php echo htmlspecialchars($fullName); ?></h2>

<!-- Salary Summary -->
<?php if ($curSal): ?>

<div class="row">

    <div class="box">
        <h3>Basic</h3>
        <p>₹<?php echo $curSal['basic_salary']; ?></p>
    </div>

    <div class="box">
        <h3>HRA</h3>
        <p>₹<?php echo $curSal['hra'] ?? 0; ?></p>
    </div>

    <div class="box">
        <h3>Transport</h3>
        <p>₹<?php echo $curSal['transport_allowance'] ?? 0; ?></p>
    </div>

    <div class="box">
        <h3>Net Salary</h3>
        <p><b>₹<?php echo $curSal['net_salary']; ?></b></p>
    </div>

</div>

<div class="card">
    <h3>Salary Details (<?php echo $selectedMonth; ?>)</h3>
    <p>
        Deductions: ₹<?php echo $curSal['deductions']; ?> <br>
        Status:
        <?php if($curSal['status']=='paid'): ?>
            <span class="badge paid">Paid</span>
        <?php else: ?>
            <span class="badge pending">Pending</span>
        <?php endif; ?>
    </p>
</div>

<?php else: ?>
<div class="card">
    No salary record found.
</div>
<?php endif; ?>

<!-- History -->
<div class="card">
    <h3>Salary History</h3>

    <table>
        <tr>
            <th>Month</th>
            <th>Basic</th>
            <th>Net</th>
            <th>Status</th>
        </tr>

        <?php foreach($salaries as $s): ?>
        <tr>
            <td><?php echo $s['salary_month']; ?></td>
            <td>₹<?php echo $s['basic_salary']; ?></td>
            <td>₹<?php echo $s['net_salary']; ?></td>
            <td>
                <?php if($s['status']=='paid'): ?>
                    <span class="badge paid">Paid</span>
                <?php else: ?>
                    <span class="badge pending">Pending</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>

    </table>
</div>

</div>
</body>
</html>