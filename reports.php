<?php
session_start();
include "db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Current month
$current_month = date('Y-m');

// --- 1. Month-wise Expenses ---
$month_expense_query = mysqli_query($conn, "
    SELECT DATE_FORMAT(expense_date, '%Y-%m') AS month, SUM(amount) AS total
    FROM expenses
    WHERE user_id='$user_id'
    GROUP BY month
    ORDER BY month DESC
");

// --- 2. Category-wise Expenses ---
$category_expense_query = mysqli_query($conn, "
    SELECT c.category_name, SUM(e.amount) AS total
    FROM expenses e
    JOIN categories c ON e.category_id = c.category_id
    WHERE e.user_id='$user_id'
    GROUP BY e.category_id
");

// --- 3. Highest Spending Category ---
$highest_category_query = mysqli_query($conn, "
    SELECT c.category_name, SUM(e.amount) AS total
    FROM expenses e
    JOIN categories c ON e.category_id = c.category_id
    WHERE e.user_id='$user_id'
    GROUP BY e.category_id
    ORDER BY total DESC
    LIMIT 1
");
$top_category = mysqli_fetch_assoc($highest_category_query);

// --- 4. Month-wise Income ---
$month_income_query = mysqli_query($conn, "
    SELECT DATE_FORMAT(income_date, '%Y-%m') AS month, SUM(amount) AS total
    FROM income
    WHERE user_id='$user_id'
    GROUP BY month
    ORDER BY month DESC
");

// --- 5. Budget vs Expense & Net Balance (Current Month) ---
$budget_query = mysqli_query($conn, "
    SELECT total_budget
    FROM budgets
    WHERE user_id='$user_id' AND month='$current_month'
");
$budget_row = mysqli_fetch_assoc($budget_query);
$total_budget = $budget_row['total_budget'] ?? 0;

$expense_query = mysqli_query($conn, "
    SELECT SUM(amount) AS total
    FROM expenses
    WHERE user_id='$user_id' AND DATE_FORMAT(expense_date, '%Y-%m')='$current_month'
");
$expense_row = mysqli_fetch_assoc($expense_query);
$total_expense = $expense_row['total'] ?? 0;

$income_query = mysqli_query($conn, "
    SELECT SUM(amount) AS total
    FROM income
    WHERE user_id='$user_id' AND DATE_FORMAT(income_date, '%Y-%m')='$current_month'
");
$income_row = mysqli_fetch_assoc($income_query);
$total_income = $income_row['total'] ?? 0;

$net_balance = $total_income - $total_expense;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Expense & Income Reports</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2, h3 { color: #333; }
        table { border-collapse: collapse; width: 60%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #007bff; color: white; }
        a { text-decoration: none; color: #007bff; }
    </style>
</head>
<body>

<h2>Reports Dashboard</h2>
<a href="dashboard.php">Back to Dashboard</a>
<hr>

<h3>1. Month-wise Expenses</h3>
<table>
<tr><th>Month</th><th>Total Expense (₹)</th></tr>
<?php while($row = mysqli_fetch_assoc($month_expense_query)) { ?>
<tr>
    <td><?php echo $row['month']; ?></td>
    <td><?php echo number_format($row['total'], 2); ?></td>
</tr>
<?php } ?>
</table>

<h3>2. Category-wise Expenses</h3>
<table>
<tr><th>Category</th><th>Total (₹)</th></tr>
<?php while($row = mysqli_fetch_assoc($category_expense_query)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
    <td><?php echo number_format($row['total'], 2); ?></td>
</tr>
<?php } ?>
</table>

<h3>3. Highest Spending Category</h3>
<p>
    Category: <b><?php echo htmlspecialchars($top_category['category_name'] ?? 'N/A'); ?></b><br>
    Amount: ₹<?php echo number_format($top_category['total'] ?? 0, 2); ?>
</p>

<h3>4. Month-wise Income</h3>
<table>
<tr><th>Month</th><th>Total Income (₹)</th></tr>
<?php while($row = mysqli_fetch_assoc($month_income_query)) { ?>
<tr>
    <td><?php echo $row['month']; ?></td>
    <td><?php echo number_format($row['total'], 2); ?></td>
</tr>
<?php } ?>
</table>

<h3>5. Budget vs Expense & Net Balance (Current Month)</h3>
<table>
<tr><th>Metric</th><th>Amount (₹)</th></tr>
<tr><td>Total Budget</td><td><?php echo number_format($total_budget, 2); ?></td></tr>
<tr><td>Total Expense</td><td><?php echo number_format($total_expense, 2); ?></td></tr>
<tr><td>Total Income</td><td><?php echo number_format($total_income, 2); ?></td></tr>
<tr><td><strong>Net Balance</strong></td><td><strong><?php echo number_format($net_balance, 2); ?></strong></td></tr>
</table>

</body>
</html>
