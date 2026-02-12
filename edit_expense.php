<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: view_expense.php");
    exit();
}

$expense_id = $_GET['id'];
$error = "";
$success = "";

// Fetch existing expense
$stmt = $conn->prepare(
    "SELECT * FROM expenses WHERE expense_id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $expense_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: view_expense.php");
    exit();
}

$expense = $result->fetch_assoc();
$stmt->close();

// Update logic
if (isset($_POST['update_expense'])) {
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $expense_date = $_POST['expense_date'];
    $description = $_POST['description'];

    if (empty($amount) || empty($category) || empty($expense_date)) {
        $error = "Amount, Category, and Date are mandatory.";
    } else {
        $update = $conn->prepare(
            "UPDATE expenses 
             SET amount=?, category_id=?, expense_date=?, description=? 
             WHERE expense_id=? AND user_id=?"
        );
        $update->bind_param(
            "dsssii",
            $amount,
            $category,
            $expense_date,
            $description,
            $expense_id,
            $user_id
        );

        if ($update->execute()) {
            $success = "Expense updated successfully.";
        } else {
            $error = "Expense update failed.";
        }
        $update->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Expense</title>
     <link rel="stylesheet" href="css/form.css">
</head>
<body>
<div class="form-wrapper">
    <h2>Edit Expense</h2>

    <form method="post">

        <div class="form-group">
            <label>Amount (₹)</label>
            <input type="number" step="0.01" name="amount"
                   value="<?php echo $expense['amount']; ?>" required>
        </div>

        <div class="form-group">
            <label>Category</label>
            <input type="text" name="category"
                   value="<?php echo htmlspecialchars($expense['category_id']); ?>" required>
        </div>

        <div class="form-group">
            <label>Expense Date</label>
            <input type="date" name="expense_date"
                   value="<?php echo $expense['expense_date']; ?>" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description"><?php
                echo htmlspecialchars($expense['description']);
            ?></textarea>
        </div>

        <button type="submit" name="update_expense">Update Expense</button>
    </form>

    <div class="message">
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <?php if($success) echo "<div class='success'>$success</div>"; ?>
    </div>

    <div class="back-link">
        <a href="view_expense.php">← Back to Expenses</a>
    </div>
</div>
</body>
