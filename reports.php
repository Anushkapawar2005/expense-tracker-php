<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_month = date('Y-m');

// Month-wise Expenses
$month_expense_query = mysqli_query($conn, "
    SELECT DATE_FORMAT(expense_date, '%b %Y') AS month, SUM(amount) AS total
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

// Category-wise Expenses
$category_query = mysqli_query($conn, "
    SELECT c.category_name, SUM(e.amount) AS total
    FROM expenses e
    JOIN categories c ON e.category_id = c.category_id
    WHERE e.user_id='$user_id'
    GROUP BY e.category_id
");

$categories = [];
$category_totals = [];
while($row = mysqli_fetch_assoc($category_query)){
    $categories[] = $row['category_name'];
    $category_totals[] = $row['total'];
}

// Current Month Summary
$budget = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT total_budget FROM budgets
    WHERE user_id='$user_id' AND month='$current_month'
"))['total_budget'] ?? 0;

$expense = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(amount) AS total FROM expenses
    WHERE user_id='$user_id'
    AND DATE_FORMAT(expense_date,'%Y-%m')='$current_month'
"))['total'] ?? 0;

$income = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(amount) AS total FROM income
    WHERE user_id='$user_id'
    AND DATE_FORMAT(income_date,'%Y-%m')='$current_month'
"))['total'] ?? 0;

$balance = $income - $expense;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Interactive Reports Dashboard</title>
    <link rel="stylesheet" href="css/report.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
</head>
<body>

<div class="header">
    <h2>Reports Dashboard</h2>
    <a class="back-btn" href="dashboard.php">Back</a>
</div>

<div class="container">

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


</div>

    <!-- Charts -->
    <div class="charts">

        <div class="chart-box">
            <h3>Month‑wise Expenses</h3>
            <canvas id="expenseChart"></canvas>
        </div>

        <div class="chart-box">
            <h3>Category‑wise Expenses</h3>
            <canvas id="categoryChart"></canvas>
        </div>

    </div>

</div>

<script>

// Expense Line Chart
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
    },
    options:{
        responsive:true,
        plugins:{
            legend:{display:true}
        }
    }
});

// Category Doughnut Chart
new Chart(document.getElementById('categoryChart'),{
    type:'doughnut',
    data:{
        labels: <?php echo json_encode($categories); ?>,
        datasets:[{
            data: <?php echo json_encode($category_totals); ?>,
            borderWidth:1
        }]
    },
    options:{
        responsive:true,
        plugins:{
            legend:{position:'bottom'}
        }
    }
});

</script>

</body>
</html>
