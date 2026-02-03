<?php
session_start();
include "db_connect.php";

// Access control
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Current month (YYYY-MM)
$current_month = date('Y-m');

// Handle budget submission
if (isset($_POST['set_budget'])) {
    $total_budget = $_POST['total_budget'];

    if (empty($total_budget)) {
        $error = "Please enter budget amount.";
    } else {
        // Check if budget already exists for this month
        $check = mysqli_query($conn, "SELECT * FROM budgets WHERE user_id='$user_id' AND month='$current_month'");

        if (mysqli_num_rows($check) > 0) {
            // Update budget
            $sql = "UPDATE budgets 
                    SET total_budget='$total_budget' 
                    WHERE user_id='$user_id' AND month='$current_month'";
        } else {
            // Insert budget
            $sql = "INSERT INTO budgets (user_id, month, total_budget, created_at)
                    VALUES ('$user_id', '$current_month', '$total_budget', CURDATE())";
        }

        if (mysqli_query($conn, $sql)) {
            $success = "Monthly budget saved successfully.";
        } else {
            $error = "Failed to save budget.";
        }
    }
}

// Fetch budget
$budget_res = mysqli_query($conn, "SELECT total_budget FROM budgets WHERE user_id='$user_id' AND month='$current_month'");
$budget_row = mysqli_fetch_assoc($budget_res);
$total_budget = $budget_row['total_budget'] ?? 0;

// Fetch total expenses for current month
$expense_res = mysqli_query($conn, "
    SELECT SUM(amount) AS total_expense 
    FROM expenses 
    WHERE user_id='$user_id' 
    AND DATE_FORMAT(expense_date, '%Y-%m')='$current_month'
");
$expense_row = mysqli_fetch_assoc($expense_res);
$total_expense = $expense_row['total_expense'] ?? 0;

$remaining = $total_budget - $total_expense;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Monthly Budget</title>
</head>
<body>

<h2>Set Monthly Budget</h2>

<form method="post">
    <label>Month:</label><br>
    <input type="text" value="<?php echo $current_month; ?>" disabled><br><br>

    <label>Total Budget (₹):</label><br>
    <input type="number" name="total_budget" step="0.01" value="<?php echo $total_budget; ?>"><br><br>

    <button type="submit" name="set_budget">Save Budget</button>
</form>

<p style="color:red;"><?php echo $error; ?></p>
<p style="color:green;"><?php echo $success; ?></p>

<hr>

<h3>Budget Summary</h3>
<p>Total Budget: ₹<?php echo number_format($total_budget, 2); ?></p>
<p>Total Expenses: ₹<?php echo number_format($total_expense, 2); ?></p>
<p>Remaining Amount: ₹<?php echo number_format($remaining, 2); ?></p>

<br>
<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>
