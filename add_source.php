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

if (isset($_POST['add_source'])) {

    $source_name = trim($_POST['source_name']);

    if (empty($source_name)) {
        $error = "Source name is required.";
    } 
    else {

        // Duplicate check
        $check = mysqli_query($conn, "
            SELECT * FROM income_sources
            WHERE user_id='$user_id'
            AND source_name='$source_name'
        ");

        if (mysqli_num_rows($check) > 0) {
            $error = "Source already exists.";
        } 
        else {

            $stmt = $conn->prepare("
                INSERT INTO income_sources
                (user_id, source_name)
                VALUES (?, ?)
            ");

            $stmt->bind_param("is", $user_id, $source_name);

            if ($stmt->execute()) {
                $success = "Source added successfully.";
            } else {
                $error = "Failed to add source.";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Source</title>

    <!-- Same CSS used in Add Category -->
    <link rel="stylesheet" href="css/form.css">
</head>

<body>

<div class="form-wrapper">

    <h2>Add Income Source</h2>

    <form method="post">

        <div class="form-group">
            <label>Source Name</label>
            <input type="text" name="source_name" required>
        </div>

        <button type="submit" name="add_source">
            Add Source
        </button>

    </form>

    <div class="message">
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <?php if($success) echo "<div class='success'>$success</div>"; ?>
    </div>

    <div class="back-link">
        <a href="add_income.php">← Back to Income</a>
    </div>

</div>

</body>
</html>
