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

if (isset($_POST['add_category'])) {

    $category_name = trim($_POST['category_name']);

    if (empty($category_name)) {
        $error = "Category name required.";
    } 
    else {

        // Duplicate check
        $check = mysqli_query($conn, "
            SELECT * FROM categories
            WHERE user_id='$user_id'
            AND category_name='$category_name'
        ");

        if (mysqli_num_rows($check) > 0) {
            $error = "Category already exists.";
        } 
        else {

            $stmt = $conn->prepare("
                INSERT INTO categories
                (user_id, category_name)
                VALUES (?, ?)
            ");

            $stmt->bind_param("is", $user_id, $category_name);

            if ($stmt->execute()) {
                $success = "Category added successfully.";
            } else {
                $error = "Failed to add category.";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Category</title>
    <link rel="stylesheet" href="css/form.css">
</head>
<body>

<div class="form-wrapper">

    <h2>Add Category</h2>

    <form method="post">

        <div class="form-group">
            <label>Category Name</label>
            <input type="text" name="category_name" required>
        </div>

        <button type="submit" name="add_category">
            Add Category
        </button>

    </form>

    <div class="message">
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <?php if($success) echo "<div class='success'>$success</div>"; ?>
    </div>

    <div class="back-link">
        <a href="add_expense.php">← Back to Expense</a>
    </div>

</div>

</body>
</html>
