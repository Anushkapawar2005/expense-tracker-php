<?php
session_start();
include "db_connect.php";

/* ===== Access Control ===== */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

/* ===== Month Selection ===== */
$current_month = $_POST['month'] ?? date('Y-m');

/* ===== Save Budget ===== */
if (isset($_POST['set_budget'])) {

    $total_budget = $_POST['total_budget'];

    if (empty($total_budget)) {
        $error = "Please enter budget amount.";
    } else {

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

/* ===== Fetch Budget ===== */
$budget_res = mysqli_query(
    $conn,
    "SELECT total_budget
     FROM budgets
     WHERE user_id='$user_id'
     AND month='$current_month'"
);

$budget_row = mysqli_fetch_assoc($budget_res);
$total_budget = $budget_row['total_budget'] ?? 0;

/* ===== Fetch Expenses ===== */
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
<html>
<head>
<title>Monthly Budget</title>

<!-- Shared Layout CSS -->
<link rel="stylesheet" href="css/header.css">
<link rel="stylesheet" href="css/footer.css">

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

/* Center Wrapper */

.page-content{
    flex:1;
    display:flex;
    justify-content:center;
    align-items:flex-start;
    padding:30px 15px;
}

/* Container */

.container{
    width:100%;
    max-width:900px;
}

/* Headings */

h2{
    text-align:center;
    margin-bottom:25px;
    color:#1f2937;
}

/* Cards */

.card{
    background:#fff;
    padding:20px 25px;
    border-radius:15px;
    box-shadow:0 15px 40px rgba(0,0,0,0.08);
    margin-bottom:25px;
}

/* Form Grid */

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

label{
    font-weight:600;
    font-size:14px;
    display:block;
    margin-bottom:5px;
}

input{
    width:95%;
    padding:10px;
    border-radius:8px;
    border:1px solid #d1d5db;
}

/* Button */

.btn{
    padding:12px 18px;
    background:linear-gradient(135deg,#4f46e5,#6366f1);
    color:#fff;
    border:none;
    border-radius:10px;
    cursor:pointer;
}

/* Alerts */

.alert{
    margin-top:12px;
    font-size:14px;
}

.error{ color:#dc2626; }
.success{ color:#16a34a; }

/* Summary */

.summary-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:15px;
}

.summary-card{
    padding:18px;
    border-radius:12px;
    color:#fff;
    font-weight:600;
}

.budget{ background:#6366f1; }
.expense{ background:#ef4444; }
.remaining{ background:#10b981; }

.amount{
    font-size:18px;
    margin-top:6px;
}

/* Progress */

.progress{
    height:12px;
    background:#e5e7eb;
    border-radius:10px;
    margin-top:20px;
    overflow:hidden;
}

.progress-bar{
    height:100%;
    background:#4f46e5;
}

/* Back Link */

.back-link{
    display:block;
    text-align:center;
    font-weight:600;
    text-decoration:none;
    color:#4f46e5;
}

/* Mobile */

@media(max-width:768px){

.form-grid{
    grid-template-columns:1fr;
}

.summary-grid{
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

<h2>Monthly Budget Management</h2>

<!-- Budget Form -->
<div class="card">

<form method="post">

<div class="form-grid">

<div>
<label>Select Month</label>
<input type="month"
       name="month"
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

<!-- Summary -->
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



</div>
</div>

<!-- ===== FOOTER ===== -->
<?php include "includes/footer.php"; ?>

</body>
</html>
