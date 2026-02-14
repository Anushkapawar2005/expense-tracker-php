<?php
session_start();
include "db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$current_date = date('Y-m-d');
$current_month = date('Y-m');

// --- Expenses ---
$res_today_expense = mysqli_query($conn, "SELECT SUM(amount) as total_today FROM expenses WHERE user_id='$user_id' AND expense_date='$current_date'");
$row_today = mysqli_fetch_assoc($res_today_expense);
$total_today_expense = $row_today['total_today'] ?? 0;

$res_month_expense = mysqli_query($conn, "SELECT SUM(amount) as month_expense FROM expenses WHERE user_id='$user_id' AND DATE_FORMAT(expense_date, '%Y-%m')='$current_month'");
$row_month_expense = mysqli_fetch_assoc($res_month_expense);
$month_expense = $row_month_expense['month_expense'] ?? 0;

$res_total_expense = mysqli_query($conn, "SELECT SUM(amount) as total_expense FROM expenses WHERE user_id='$user_id'");
$row_total_expense = mysqli_fetch_assoc($res_total_expense);
$total_expense = $row_total_expense['total_expense'] ?? 0;

// --- Budget ---
$res_budget = mysqli_query($conn, "SELECT total_budget FROM budgets WHERE user_id='$user_id' AND month='$current_month'");
$row_budget = mysqli_fetch_assoc($res_budget);
$total_budget = $row_budget['total_budget'] ?? 0;
$remaining_budget = $total_budget - $month_expense;

// --- Income ---
$res_month_income = mysqli_query($conn, "SELECT SUM(amount) as month_income FROM income WHERE user_id='$user_id' AND DATE_FORMAT(income_date,'%Y-%m')='$current_month'");
$row_month_income = mysqli_fetch_assoc($res_month_income);
$month_income = $row_month_income['month_income'] ?? 0;

$res_total_income = mysqli_query($conn, "SELECT SUM(amount) as total_income FROM income WHERE user_id='$user_id'");
$row_total_income = mysqli_fetch_assoc($res_total_income);
$total_income = $row_total_income['total_income'] ?? 0;

// --- Net Balance (Current Month) ---
$net_balance = $month_income - $month_expense;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/header.css">
<link rel="stylesheet" href="css/footer.css">
<link rel="stylesheet" href="css/dashboard.css">


</head>
<body>
    <?php include "includes/header.php"; ?>


<div class="welcome">
    <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
    <p>Select an action below:</p>
</div>

<div class="nav">
    <a href="add_expense.php">Add Expense</a>
    <a href="view_expense.php">View Expenses</a>
    <a href="add_income.php">Add Income</a>
    <a href="view_income.php">View Income</a>
    <a href="budget.php">Budget</a>
    <a href="reports.php">Reports</a>
    <a href="logout.php">Logout</a>
</div>

<div class="summary">
    <h3>Financial Overview</h3>

    <!-- Top Row : 4 Cards -->
    <div class="cards cards-top">
        <div class="card danger">
            <h4>Today’s Expenses</h4>
            <p>₹ <?php echo number_format($total_today_expense, 2); ?></p>
            <span>Daily spending snapshot</span>
        </div>

        <div class="card warning">
            <h4>Monthly Expenses</h4>
            <p>₹ <?php echo number_format($month_expense, 2); ?></p>
            <span>Current month outflow</span>
        </div>

        <div class="card success">
            <h4>Monthly Income</h4>
            <p>₹ <?php echo number_format($month_income, 2); ?></p>
            <span>Income received this month</span>
        </div>

        <div class="card primary">
            <h4>Net Balance</h4>
            <p>₹ <?php echo number_format($net_balance, 2); ?></p>
            <span>Income − Expenses</span>
        </div>
    </div>

    <!-- Bottom Row : 3 Cards -->
    <div class="cards cards-bottom">
        <div class="card info">
            <h4>Monthly Budget</h4>
            <p>₹ <?php echo number_format($total_budget, 2); ?></p>
            <span>Allocated budget</span>
        </div>

        <div class="card neutral">
            <h4>Remaining Budget</h4>
            <p>₹ <?php echo number_format($remaining_budget, 2); ?></p>
            <span>Available to spend</span>
        </div>

        <div class="card dark">
            <h4>Total Income</h4>
            <p>₹ <?php echo number_format($total_income, 2); ?></p>
            <span>Lifetime earnings</span>
        </div>
    </div>
    <div class="charts">

    <div class="chart-box">
        <h3>Income vs Expense</h3>
        <canvas id="barChart"></canvas>
    </div>

    <div class="chart-box">
        <h3>Expense Distribution</h3>
        <canvas id="pieChart"></canvas>
    </div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

const barCtx = document.getElementById('barChart');

new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: ['Income','Expense'],
        datasets: [{
            data: [
                <?php echo $month_income; ?>,
                <?php echo $month_expense; ?>
            ],
            backgroundColor:['#2a9d8f','#e63946']
        }]
    }
});

</script>

<script>

const pieCtx = document.getElementById('pieChart');

new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: ['Spent', 'Remaining'],
        datasets: [{
            
            data: [
                <?php echo $month_expense; ?>,
                <?php echo $remaining_budget; ?>
            ],
            
            backgroundColor: [
                '#e63946',
                '#2a9d8f'
            ]
            
        }]
    },
    options:{
    responsive:true,
   
}

});

</script>

<?php include "includes/footer.php"; ?>

</body>
</html>
