<?php
session_start();
include "db_connect.php";

/* Auth Check */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

/* Add Income Logic */
if(isset($_POST['add_income'])){

    $amount       = $_POST['amount'];
    $source       = $_POST['source'];
    $income_date  = $_POST['income_date'];
    $description  = $_POST['description'];

    if(empty($amount) || empty($source) || empty($income_date)){
        $error = "Amount, Source, and Date are required.";
    }
    else{

        $stmt = $conn->prepare("
            INSERT INTO income
            (user_id, amount, source, income_date, description, created_at)
            VALUES (?, ?, ?, ?, ?, CURDATE())
        ");

        $stmt->bind_param(
            "idsss",
            $user_id,
            $amount,
            $source,
            $income_date,
            $description
        );

        if($stmt->execute()){
            $success = "Income added successfully.";
        }else{
            $error = "Failed to add income.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Income</title>

    <!-- CSS FILES -->
     
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">

<style>

/* ===== PAGE LAYOUT FIX ===== */

    


body{
    font-family:'Segoe UI', Arial, sans-serif;
    background:#f4f6f9;
    margin: 0px;

    display:flex;
    flex-direction:column;
    min-height:100vh;
}

/* Center Section */

.page-content{
    flex:1;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px 10px;
}

/* ===== FORM DESIGN ===== */

.form-wrapper{
    background:#fff;
    padding:10px 20px;
    border-radius:15px;
    box-shadow:0 15px 40px rgba(0,0,0,0.08);
    width:90%;
    max-width:420px;
}

h2{
    text-align:center;
    margin-bottom:25px;
    font-size:24px;
    font-weight:700;
    color:#1f2937;
}

.form-group{ margin-bottom:17px; }

label{
    display:block;
    font-weight:600;
    margin-bottom:4px;
    color:#374151;
    font-size:14px;
}

input,select,textarea{
    width:90%;
    padding:12px 14px;
    border-radius:10px;
    border:1px solid #d1d5db;
    font-size:14px;
}
 select {
           width: 97%
             
        }
textarea{
    resize:none;
    height:70px;
}

button{
    width:100%;
    padding:14px;
    background:linear-gradient(135deg,#4f46e5,#6366f1);
    color:#fff;
    border:none;
    border-radius:12px;
    cursor:pointer;
}

.message{ text-align:center; margin-top:12px; }
.error{ color:#dc2626; }
.success{ color:#16a34a; }

/* Add Source Button */

.add-source-btn{
    display:inline-flex;
    gap:6px;
    margin-top:10px;
    padding:6px 10px;
    font-size:13px;
    font-weight:600;
    color:#065f46;
    background:#d1fae5;
    border-radius:10px;
    text-decoration:none;
}
        /* Mobile Optimization */
        @media (max-width: 480px) {
            .form-wrapper {
                padding: 25px 20px;
            }
 
        }

</style>
</head>

<body>

<!-- ===== HEADER ===== -->
<?php include "includes/header.php"; ?>

<!-- ===== CENTER CONTENT ===== -->
<div class="page-content">

<div class="form-wrapper">

<h2>Add Income</h2>

<form method="post">

<div class="form-group">
<label>Amount (₹)</label>
<input type="number" step="0.01" name="amount" required>
</div>

<div class="form-group">
<label>Income Source</label>

<select name="source" required>
<option value="">Select source</option>
<option>Salary</option>
<option>Freelance</option>
<option>Gift</option>
<option>Business</option>
<option>Other</option>
</select>

<a href="add_source.php" class="add-source-btn">
＋ Add New Source
</a>
</div>

<div class="form-group">
<label>Income Date</label>
<input type="date" name="income_date" required>
</div>

<div class="form-group">
<label>Description</label>
<textarea name="description"></textarea>
</div>

<button type="submit" name="add_income">
Add Income
</button>

</form>

<div class="message">
<?php if($error) echo "<div class='error'>$error</div>"; ?>
<?php if($success) echo "<div class='success'>$success</div>"; ?>
</div>

</div>
</div>

<!-- ===== FOOTER ===== -->
<?php include "includes/footer.php"; ?>

</body>
</html>
