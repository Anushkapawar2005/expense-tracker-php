<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_month = date('Y-m');

/* ===== Month-wise Expenses ===== */
$month_expense_query = mysqli_query($conn, "
    SELECT DATE_FORMAT(expense_date, '%b %Y') AS month,
           SUM(amount) AS total
    FROM expenses
    WHERE user_id='$user_id'
    GROUP BY month
    ORDER BY expense_date ASC
");

$expense_months = [];
$expense_totals = [];

while($row = mysqli_fetch_assoc($month_expense_query)){
    $expense_months[] = $row['month'];
    $expense_totals[] = $row['total'];
}

/* ===== Category-wise Expenses ===== */
$category_query = mysqli_query($conn, "
    SELECT c.category_name,
           SUM(e.amount) AS total
    FROM expenses e
    JOIN categories c
    ON e.category_id = c.category_id
    WHERE e.user_id='$user_id'
    GROUP BY e.category_id
");

$categories = [];
$category_totals = [];

while($row = mysqli_fetch_assoc($category_query)){
    $categories[] = $row['category_name'];
    $category_totals[] = $row['total'];
}

/* ===== Current Month Summary ===== */

$budget = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT total_budget FROM budgets
    WHERE user_id='$user_id'
    AND month='$current_month'
"))['total_budget'] ?? 0;

$expense = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT SUM(amount) AS total FROM expenses
    WHERE user_id='$user_id'
    AND DATE_FORMAT(expense_date,'%Y-%m')='$current_month'
"))['total'] ?? 0;

$income = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT SUM(amount) AS total FROM income
    WHERE user_id='$user_id'
    AND DATE_FORMAT(income_date,'%Y-%m')='$current_month'
"))['total'] ?? 0;

$balance = $income - $expense;
?>

<!DOCTYPE html>
<html>
<head>
<title>Reports Dashboard</title>

<!-- Shared CSS -->
<link rel="stylesheet" href="css/header.css">
<link rel="stylesheet" href="css/footer.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

/* ===== PAGE LAYOUT ===== */

body{
    font-family:'Segoe UI', Arial, sans-serif;
    background:#f4f6f9;
    margin:0;

    display:flex;
    flex-direction:column;
    min-height:100vh;
}

/* Wrapper */

.page-content{
    flex:1;
    padding:30px 20px;
}

/* Container */

.container{
    max-width:1100px;
    margin:auto;
}

/* Header Row */

.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.page-header h2{
    margin:0;
}

/* Back Button */

.back-btn{
    text-decoration:none;
    padding:8px 14px;
    background:#4f46e5;
    color:#fff;
    border-radius:8px;
    font-size:14px;
}

/* ===== SUMMARY CARDS ===== */

.cards{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin-bottom:30px;
}

.card{
    padding:20px;
    border-radius:14px;
    color:#fff;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.card h3{
    margin:0;
    font-size:15px;
    opacity:.9;
}

.card h2{
    margin-top:8px;
}

/* Card Colors */

.card-blue{ background:#6366f1; }
.card-red{ background:#ef4444; }
.card-green{ background:#10b981; }
.card-darkblue{ background:#1d4ed8; }

/* ===== CHARTS ===== */

.charts{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.chart-box{
    background:#fff;
    padding:20px;
    border-radius:14px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.chart-box h3{
    margin-bottom:15px;
}

/* Responsive */

@media(max-width:900px){

.cards{
    grid-template-columns:1fr 1fr;
}

.charts{
    grid-template-columns:1fr;
}

}

</style>
</head>

<body>

<!-- ===== HEADER ===== -->
<?php include "includes/header.php"; ?>

<!-- ===== PAGE CONTENT ===== -->
<div class="page-content">

<div class="container">

<!-- Title Row -->
<div class="page-header">
<h2>Reports Dashboard</h2>
</div>

<!-- Summary Cards -->
<div class="cards">

<div class="card card-blue">
<h3>Total Budget</h3>
<h2>₹ <?php echo number_format($budget,2); ?></h2>
</div>

<div class="card card-red">
<h3>Total Expense</h3>
<h2>₹ <?php echo number_format($expense,2); ?></h2>
</div>

<div class="card card-green">
<h3>Total Income</h3>
<h2>₹ <?php echo number_format($income,2); ?></h2>
</div>

<div class="card card-darkblue">
<h3>Net Balance</h3>
<h2>₹ <?php echo number_format($balance,2); ?></h2>
</div>

</div>

<!-- Charts -->
<div class="charts">

<div class="chart-box">
<h3>Month-wise Expenses</h3>
<canvas id="expenseChart"></canvas>
</div>

<div class="chart-box">
<h3>Category-wise Expenses</h3>
<canvas id="categoryChart"></canvas>
</div>

</div>

</div>
</div>

<!-- ===== FOOTER ===== -->
<?php include "includes/footer.php"; ?>

<script>

/* Expense Line Chart */
new Chart(document.getElementById('expenseChart'),{
type:'line',
data:{
labels: <?php echo json_encode($expense_months); ?>,
datasets:[{
label:'Expenses',
data: <?php echo json_encode($expense_totals); ?>,
borderWidth:3,
fill:true,
tension:0.4
}]
}
});

/* Category Doughnut */
new Chart(document.getElementById('categoryChart'),{
type:'doughnut',
data:{
labels: <?php echo json_encode($categories); ?>,
datasets:[{
data: <?php echo json_encode($category_totals); ?>,
borderWidth:1
}]
}
});

</script>

</body>
</html>
