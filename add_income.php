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
    <link rel="stylesheet" href="css/form.css">

    <!-- Source Button Styling -->
    <style>

    /* Add Source Button */
    .add-source-btn{
        display:inline-flex;
        align-items:center;
        gap:6px;

        margin-top:12px;
        padding:6px 10px;

        font-size:13px;
        font-weight:600;

        color:#065f46;
        background:linear-gradient(135deg,#ecfdf5,#d1fae5);

        border:1px solid #a7f3d0;
        border-radius:12px;

        text-decoration:none;
        transition:all 0.30s ease;
    }

    /* Icon Box */
    .add-source-btn .icon{
        display:flex;
        align-items:center;
        justify-content:center;

        width:22px;
        height:22px;

        background:#10b981;
        color:#fff;

        font-size:14px;
        border-radius:6px;
    }

    /* Hover */
    .add-source-btn:hover{
        background:linear-gradient(135deg,#10b981,#34d399);
        color:#fff;
        transform:translateY(-2px);
        box-shadow:0 10px 20px rgba(16,185,129,0.35);
    }

    .add-source-btn:hover .icon{
        background:#fff;
        color:#10b981;
    }

    </style>
</head>

<body>

<div class="form-wrapper">

    <h2>Add Income</h2>

    <form method="post">

        <!-- Amount -->
        <div class="form-group">
            <label>Amount (₹)</label>
            <input type="number" step="0.01" name="amount" placeholder="Enter amount" required>
        </div>

        <!-- Source -->
        <div class="form-group">

            <label>Income Source</label>

            <select name="source" required>
                <option value="">Select source</option>
                <option value="Salary">Salary</option>
                <option value="Freelance">Freelance</option>
                <option value="Gift">Gift</option>
                <option value="Business">Business</option>
                <option value="Other">Other</option>
            </select>

            <!-- Add Source Button -->
            <a href="add_source.php" class="add-source-btn">
                <span class="icon">＋</span>
                Add New Source
            </a>

        </div>

        <!-- Date -->
        <div class="form-group">
            <label>Income Date</label>
            <input type="date" name="income_date" required>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label>Description (Optional)</label>
            <textarea name="description"
             placeholder="Enter a short description"></textarea>
        </div>

        <button type="submit" name="add_income">
            Add Income
        </button>

    </form>

    <!-- Messages -->
    <div class="message">
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <?php if($success) echo "<div class='success'>$success</div>"; ?>
    </div>

    <!-- Back -->
    <div class="back-link">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

</div>

</body>
</html>
