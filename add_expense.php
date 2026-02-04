<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Fetch categories
$cat_result = mysqli_query($conn, "SELECT * FROM categories WHERE user_id='$user_id'");

if (isset($_POST['add_expense'])) {
    $category_id = $_POST['category_id'];
    $amount = $_POST['amount'];
    $expense_date = $_POST['expense_date'];
    $description = $_POST['description'];

    if (empty($category_id) || empty($amount) || empty($expense_date)) {
        $error = "Please fill all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO expenses (user_id, category_id, amount, expense_date, description, created_at) VALUES (?, ?, ?, ?, ?, CURDATE())");
        $stmt->bind_param("iidss", $user_id, $category_id, $amount, $expense_date, $description);

        if ($stmt->execute()) {
            $success = "Expense added successfully.";
        } else {
            $error = "Failed to add expense: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Expense</title>
     <link rel="stylesheet" href="css/form.css">
</head>
<body>
<div class="form-wrapper">
    <h2>Add Expense</h2>
    <form method="post">
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php while($row = mysqli_fetch_assoc($cat_result)) { ?>
                    <option value="<?php echo $row['category_id']; ?>"><?php echo $row['category_name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label>Amount (₹)</label>
            <input type="number" step="0.01" name="amount" required>
        </div>
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="expense_date" required>
        </div>
        <div class="form-group">
            <label>Description (Optional)</label>
            <textarea name="description"></textarea>
        </div>
        <button type="submit" name="add_expense">Add Expense</button>
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
