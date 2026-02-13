<?php
session_start();
include "db_connect.php";

// Access Control
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Month Selection (Editable)
$current_month = $_POST['month'] ?? date('Y-m');

// Handle Budget Submission
if (isset($_POST['set_budget'])) {

    $total_budget = $_POST['total_budget'];

    if (empty($total_budget)) {
        $error = "Please enter budget amount.";
    } else {

        // Check Existing Budget
        $check = mysqli_query(
            $conn,
            "SELECT * FROM budgets
             WHERE user_id='$user_id'
             AND month='$current_month'"
        );

        if (mysqli_num_rows($check) > 0) {

            $sql = "UPDATE budgets
                    SET total_budget='$total_budget'
                    WHERE user_id='$user_id'
                    AND month='$current_month'";

        } else {

            $sql = "INSERT INTO budgets
                    (user_id, month, total_budget, created_at)
                    VALUES
                    ('$user_id','$current_month','$total_budget',CURDATE())";
        }

        if (mysqli_query($conn,$sql)) {
            $success = "Monthly budget saved successfully.";
        } else {
            $error = "Failed to save budget.";
        }
    }
}

// Fetch Budget
$budget_res = mysqli_query(
    $conn,
    "SELECT total_budget
     FROM budgets
     WHERE user_id='$user_id'
     AND month='$current_month'"
);

$budget_row = mysqli_fetch_assoc($budget_res);
$total_budget = $budget_row['total_budget'] ?? 0;

// Fetch Expenses
$expense_res = mysqli_query(
    $conn,
    "SELECT SUM(amount) AS total_expense
     FROM expenses
     WHERE user_id='$user_id'
     AND DATE_FORMAT(expense_date,'%Y-%m')='$current_month'"
);

$expense_row = mysqli_fetch_assoc($expense_res);
$total_expense = $expense_row['total_expense'] ?? 0;

$remaining = $total_budget - $total_expense;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Monthly Budget</title>

<link rel="stylesheet" href="css/budget.css">

</head>

<body>

<div class="container">

<h2>Monthly Budget Management</h2>

<!-- Budget Form -->
<div class="card">

<form method="post">

<div class="form-grid">

<div>
<label>Select Month</label>
<input type="month" name="month"
       value="<?php echo $current_month; ?>">
</div>

<div>
<label>Total Budget (₹)</label>
<input type="number"
       name="total_budget"
       step="0.01"
       value="<?php echo $total_budget; ?>">
</div>

</div>

<br>

<button type="submit"
        name="set_budget"
        class="btn">
        Save Budget
</button>

</form>

<?php if($error): ?>
<div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if($success): ?>
<div class="alert success"><?php echo $success; ?></div>
<?php endif; ?>

</div>

<!-- Budget Summary -->
<div class="card">

<h3>Budget Summary — <?php echo $current_month; ?></h3>

<div class="summary-grid">

<div class="summary-card budget">
    Total Budget
    <div class="amount">
        ₹ <?php echo number_format($total_budget,2); ?>
    </div>
</div>

<div class="summary-card expense">
    Total Expenses
    <div class="amount">
        ₹ <?php echo number_format($total_expense,2); ?>
    </div>
</div>

<div class="summary-card remaining">
    Remaining
    <div class="amount">
        ₹ <?php echo number_format($remaining,2); ?>
    </div>
</div>

</div>

<!-- Utilization Progress -->
<?php
$percent = ($total_budget>0)
 ? ($total_expense/$total_budget)*100
 : 0;
?>

<div class="progress">
<div class="progress-bar"
     style="width:<?php echo min($percent,100); ?>%">
</div>
</div>

</div>

<a class="back-link"
   href="dashboard.php">
   ← Back to Dashboard
</a>

</div>

</body>
</html>
