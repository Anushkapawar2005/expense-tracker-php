
<?php
session_start();
include "db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Handle form submission
if (isset($_POST['add_income'])) {
    $amount = $_POST['amount'];
    $source = $_POST['source'];
    $income_date = $_POST['income_date'];
    $description = $_POST['description'];

    if (empty($amount) || empty($source) || empty($income_date)) {
        $error = "Amount, Source, and Date are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO income (user_id, amount, source, income_date, description, created_at) VALUES (?, ?, ?, ?, ?, CURDATE())");
        $stmt->bind_param("idsss", $user_id, $amount, $source, $income_date, $description);

        if ($stmt->execute()) {
            $success = "Income added successfully.";
        } else {
            $error = "Failed to add income: " . $stmt->error;
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
</head>
<body>
<div class="form-wrapper">
    <h2>Add Income</h2>

    <form method="post">
        <div class="form-group">
            <label>Amount (₹)</label>
            <input type="number" step="0.01" name="amount" placeholder="Enter amount" required>
        </div>

        <div class="form-group">
            <label>Income Source</label>
            <select name="source" required>
                <option value="">Select source</option>
                <option value="Salary">Salary</option>
                <option value="Freelance">Freelance</option>
                <option value="Gift">Gift</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label>Income Date</label>
            <input type="date" name="income_date" required>
        </div>

        <div class="form-group">
            <label>Description (Optional)</label>
            <textarea name="description" placeholder="Enter a short description"></textarea>
        </div>

        <button type="submit" name="add_income">Add Income</button>
    </form>

    <div class="message">
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <?php if($success) echo "<div class='success'>$success</div>"; ?>
    </div>

    <div class="back-link">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
