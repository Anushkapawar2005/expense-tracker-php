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
$month_expense_query = mysqli_query($conn,"
SELECT DATE_FORMAT(expense_date,'%b %Y') AS month,
SUM(amount) AS total
FROM expenses
WHERE user_id='$user_id'
GROUP BY month
ORDER BY expense_date ASC
");

$expense_months=[];
$expense_totals=[];

while($row=mysqli_fetch_assoc($month_expense_query)){
$expense_months[]=$row['month'];
$expense_totals[]=$row['total'];
}

/* ===== Category-wise Expenses ===== */

$category_query=mysqli_query($conn,"
SELECT c.category_name,
SUM(e.amount) AS total
FROM expenses e
JOIN categories c
ON e.category_id=c.category_id
WHERE e.user_id='$user_id'
GROUP BY e.category_id
");

$categories=[];
$category_totals=[];

while($row=mysqli_fetch_assoc($category_query)){
$categories[]=$row['category_name'];
$category_totals[]=$row['total'];
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

$balance=$income-$expense;


/* ===== REPORT QUERIES ===== */

/* 1 Month Wise Expense */
$monthly_expense=mysqli_query($conn,"
SELECT DATE_FORMAT(expense_date,'%M %Y') AS month,
SUM(amount) AS total
FROM expenses
WHERE user_id='$user_id'
GROUP BY month
");

/* 2 Date Wise Expense Report */

$datewise_report=mysqli_query($conn,"
SELECT expense_date,
SUM(amount) AS total
FROM expenses
WHERE user_id='$user_id'
GROUP BY expense_date
ORDER BY expense_date DESC
");

/* 3 Category Wise Expense */
$category_report=mysqli_query($conn,"
SELECT c.category_name,SUM(e.amount) AS total
FROM expenses e
JOIN categories c
ON e.category_id=c.category_id
WHERE e.user_id='$user_id'
GROUP BY c.category_name
");

/* 4 Highest Spending Category */
$highest_category=mysqli_query($conn,"
SELECT c.category_name,SUM(e.amount) AS total
FROM expenses e
JOIN categories c
ON e.category_id=c.category_id
WHERE e.user_id='$user_id'
GROUP BY c.category_name
ORDER BY total DESC
LIMIT 1
");

/* 5 Month Wise Income */
$monthly_income=mysqli_query($conn,"
SELECT DATE_FORMAT(income_date,'%M %Y') AS month,
SUM(amount) AS total
FROM income
WHERE user_id='$user_id'
GROUP BY month
");

/* 6 Budget vs Expense */
$budget_report=mysqli_query($conn,"
SELECT b.month,b.total_budget,
IFNULL(SUM(e.amount),0) AS expense
FROM budgets b
LEFT JOIN expenses e
ON DATE_FORMAT(e.expense_date,'%Y-%m')=b.month
AND b.user_id=e.user_id
WHERE b.user_id='$user_id'
GROUP BY b.month
");

/* 7 Net Balance */
$balance_report=mysqli_query($conn,"
SELECT 
DATE_FORMAT(i.income_date,'%M %Y') AS month,
SUM(i.amount) AS income,
(SELECT IFNULL(SUM(e.amount),0)
FROM expenses e
WHERE DATE_FORMAT(e.expense_date,'%Y-%m')=
DATE_FORMAT(i.income_date,'%Y-%m')
AND e.user_id='$user_id') AS expense
FROM income i
WHERE i.user_id='$user_id'
GROUP BY month
");


?>

<!DOCTYPE html>
<html>
<head>
<title>Reports Dashboard</title>

<link rel="stylesheet" href="css/header.css">
<link rel="stylesheet" href="css/footer.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
font-family:'Segoe UI',Arial;
background:#f4f6f9;
margin:0;
display:flex;
flex-direction:column;
min-height:100vh;
}

.page-content{
flex:1;
padding:30px 20px;
}

.container{
max-width:1100px;
margin:auto;
}

.page-header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:25px;
}

/* CARDS */

.cards{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:18px;
margin-bottom:30px;
}

.card{
padding:20px;
border-radius:14px;
color:white;
box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.card h3{
margin:0;
font-size:15px;
}

.card h2{
margin-top:8px;
}

.card-blue{background:#6366f1;}
.card-red{background:#ef4444;}
.card-green{background:#10b981;}
.card-darkblue{background:#1d4ed8;}

/* CHARTS */

.charts{
display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
margin-bottom:40px;
}

.chart-box{
background:white;
padding:20px;
border-radius:14px;
box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.chart-box canvas{
max-height:260px;
}

/* REPORTS */

.report-section{
margin-top:20px;
}

.report-section h2{
margin-top:35px;
}

table{
width:100%;
border-collapse:collapse;
background:white;
margin-top:10px;
margin-bottom:30px;
box-shadow:0 5px 15px rgba(0,0,0,.05);
}

th,td{
padding:10px;
border:1px solid #ddd;
text-align:center;
}

th{
background:#1f2937;
color:white;
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

<?php include "includes/header.php"; ?>

<div class="page-content">
<div class="container">

<div class="page-header">
<h2>Reports Dashboard</h2>
</div>

<!-- SUMMARY CARDS -->

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

<!-- CHARTS -->

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


<!-- REPORT TABLES -->

<div class="report-section">

<h2>1. Month Wise Expense Report</h2>
<table>
<tr><th>Month</th><th>Total Expense</th></tr>
<?php while($row=mysqli_fetch_assoc($monthly_expense)){ ?>
<tr>
<td><?php echo $row['month']; ?></td>
<td>₹ <?php echo $row['total']; ?></td>
</tr>
<?php } ?>
</table>

<h2>2. Date Wise Expense Report</h2>

<table>
<tr>
<th>Date</th>
<th>Total Expense</th>
</tr>

<?php while($row=mysqli_fetch_assoc($datewise_report)){ ?>
<tr>
<td><?php echo $row['expense_date']; ?></td>
<td>₹ <?php echo $row['total']; ?></td>
</tr>
<?php } ?>

</table>


<h2>3. Category Wise Expense Report</h2>
<table>
<tr><th>Category</th><th>Total Expense</th></tr>
<?php while($row=mysqli_fetch_assoc($category_report)){ ?>
<tr>
<td><?php echo $row['category_name']; ?></td>
<td>₹ <?php echo $row['total']; ?></td>
</tr>
<?php } ?>
</table>


<h2>4. Highest Spending Category</h2>
<table>
<tr><th>Category</th><th>Total Expense</th></tr>
<?php $row=mysqli_fetch_assoc($highest_category); ?>
<tr>
<td><?php echo $row['category_name']; ?></td>
<td>₹ <?php echo $row['total']; ?></td>
</tr>
</table>


<h2>5. Month Wise Income Report</h2>
<table>
<tr><th>Month</th><th>Total Income</th></tr>
<?php while($row=mysqli_fetch_assoc($monthly_income)){ ?>
<tr>
<td><?php echo $row['month']; ?></td>
<td>₹ <?php echo $row['total']; ?></td>
</tr>
<?php } ?>
</table>


<h2>6. Budget vs Expense Report</h2>
<table>
<tr>
<th>Month</th>
<th>Budget</th>
<th>Expense</th>
<th>Status</th>
</tr>

<?php while($row=mysqli_fetch_assoc($budget_report)){ ?>
<tr>
<td><?php echo $row['month']; ?></td>
<td>₹ <?php echo $row['total_budget']; ?></td>
<td>₹ <?php echo $row['expense']; ?></td>
<td>
<?php
if($row['expense']>$row['total_budget'])
echo "Exceeded";
else
echo "Within Budget";
?>
</td>
</tr>
<?php } ?>
</table>


<h2>7. Net Balance Report</h2>
<table>
<tr>
<th>Month</th>
<th>Income</th>
<th>Expense</th>
<th>Net Balance</th>
</tr>

<?php while($row=mysqli_fetch_assoc($balance_report)){ ?>
<tr>
<td><?php echo $row['month']; ?></td>
<td>₹ <?php echo $row['income']; ?></td>
<td>₹ <?php echo $row['expense']; ?></td>
<td>₹ <?php echo $row['income']-$row['expense']; ?></td>
</tr>
<?php } ?>
</table>






</div>

</div>
</div>

<?php include "includes/footer.php"; ?>

<script>

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

new Chart(document.getElementById('categoryChart'),{
type:'doughnut',
data:{
labels: <?php echo json_encode($categories); ?>,
datasets:[{
data: <?php echo json_encode($category_totals); ?>
}]
}
});

</script>

</body>
</html>