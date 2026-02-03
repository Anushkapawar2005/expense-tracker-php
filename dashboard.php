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
    <style>
       /* =====================
   Global Reset & Base
===================== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f4f6f9;
    color: #2c3e50;
    padding: 30px;
}

/* =====================
   Welcome Section
===================== */
.welcome {
    background: linear-gradient(135deg, #1d3557, #457b9d);
    color: #fff;
    padding: 25px 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
}

.welcome h2 {
    font-size: 26px;
    font-weight: 600;
    margin-bottom: 8px;
}

.welcome p {
    font-size: 15px;
    opacity: 0.9;
}

/* =====================
   Navigation Buttons
===================== */
.nav {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 35px;
}

.nav a {
    background: #ffffff;
    color: #1d3557;
    padding: 12px 20px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 500;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.nav a:hover {
    background: #1d3557;
    color: #ffffff;
    transform: translateY(-3px);
    box-shadow: 0 10px 22px rgba(29, 53, 87, 0.35);
}
/* ===== Summary Section ===== */
.summary {
    margin-top: 30px;
}

.summary h3 {
    font-size: 22px;
    margin-bottom: 20px;
    color: #1d3557;
    border-left: 5px solid #457b9d;
    padding-left: 12px;
}

/* ===== Card Layout ===== */
.cards {
    display: grid;
    gap: 20px;
    margin-bottom: 25px;
}

.cards-top {
    grid-template-columns: repeat(4, 1fr);
}

.cards-bottom {
    grid-template-columns: repeat(3, 1fr);
}

/* ===== Card Base ===== */
.card {
    background: #ffffff;
    padding: 22px;
    border-radius: 14px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    width: 6px;
    height: 100%;
}

.card h4 {
    font-size: 14px;
    text-transform: uppercase;
    color: #555;
    margin-bottom: 8px;
}

.card p {
    font-size: 24px;
    font-weight: 700;
    color: #1d3557;
    margin-bottom: 5px;
}

.card span {
    font-size: 13px;
    color: #777;
}

/* ===== Hover Interaction ===== */
.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 35px rgba(0, 0, 0, 0.15);
}

/* ===== Card Color Codes ===== */
.primary::before { background: #1d3557; }
.success::before { background: #2a9d8f; }
.warning::before { background: #f4a261; }
.danger::before  { background: #e63946; }
.info::before    { background: #457b9d; }
.neutral::before { background: #6c757d; }
.dark::before    { background: #343a40; }

/* ===== Responsive ===== */
@media (max-width: 992px) {
    .cards-top {
        grid-template-columns: repeat(2, 1fr);
    }

    .cards-bottom {
        grid-template-columns: repeat(1, 1fr);
    }
}

    </style>
</head>
<body>

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
</div>


</body>
</html>
