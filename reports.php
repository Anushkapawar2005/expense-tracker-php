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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body{
            font-family: Arial, sans-serif;
            margin:0;
            background:#f4f6f9;
        }

        .header{
            background:#111827;
            color:white;
            padding:15px 30px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .container{
            padding:30px;
        }

        .cards{
            display:grid;
            grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
            gap:20px;
            margin-bottom:30px;
        }

        .card{
            background:white;
            padding:20px;
            border-radius:15px;
            box-shadow:0 4px 10px rgba(0,0,0,0.08);
            transition:0.3s;
        }

        .card:hover{
            transform:translateY(-5px);
        }

        .card h3{
            margin:0;
            color:#6b7280;
            font-size:14px;
        }

        .card h2{
            margin-top:10px;
            color:#111827;
        }
        /* Blue → Budget */
.card-blue{
    background: linear-gradient(135deg,#2193b0,#6dd5ed);
}

/* Red → Expense */
.card-red{
    background: linear-gradient(135deg,#ff416c,#ff4b2b);
}

/* Green → Income */
.card-green{
    background: linear-gradient(135deg,#11998e,#38ef7d);
}

/* Dark Blue → Balance */
.card-darkblue{
    background: linear-gradient(135deg,#396afc,#2948ff);
}

/* Optional Extra (if needed later) */
.card-orange{
    background: linear-gradient(135deg,#f7971e,#ffd200);
}

        .charts{
            display:grid;
            grid-template-columns: repeat(auto-fit,minmax(400px,1fr));
            gap:30px;
        }

        .chart-box{
            background:white;
            padding:20px;
            border-radius:15px;
            box-shadow:0 4px 10px rgba(0,0,0,0.08);
        }

        canvas{
            width:100% !important;
            height:350px !important;
        }

        .back-btn{
            text-decoration:none;
            color:white;
            background:#2563eb;
            padding:8px 14px;
            border-radius:8px;
            font-size:14px;
        }
    </style>
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
