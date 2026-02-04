<?php
session_start();
include "db_connect.php";

// Access control
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate income ID
if (!isset($_GET['id'])) {
    header("Location: view_income.php");
    exit();
}

$income_id = $_GET['id'];
$error = "";
$success = "";

// Fetch existing income data
$stmt = $conn->prepare("SELECT * FROM income WHERE income_id = ? AND user_id = ?");
$stmt->bind_param("ii", $income_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: view_income.php");
    exit();
}

$income = $result->fetch_assoc();
$stmt->close();

// Handle update
if (isset($_POST['update_income'])) {
    $amount = $_POST['amount'];
    $source = $_POST['source'];
    $income_date = $_POST['income_date'];
    $description = $_POST['description'];

    if (empty($amount) || empty($source) || empty($income_date)) {
        $error = "Amount, Source, and Date are mandatory.";
    } else {
        $update = $conn->prepare(
            "UPDATE income 
             SET amount=?, source=?, income_date=?, description=? 
             WHERE income_id=? AND user_id=?"
        );
        $update->bind_param(
            "dsssii",
            $amount,
            $source,
            $income_date,
            $description,
            $income_id,
            $user_id
        );

        if ($update->execute()) {
            $success = "Income updated successfully.";
        } else {
            $error = "Update operation failed.";
        }

        $update->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Income</title>
    <link rel="stylesheet" href="css/form.css">
</head>
<body>
<div class="form-wrapper">
    <h2>Edit Income</h2>

    <form method="post">

        <div class="form-group">
            <label>Amount (₹)</label>
            <input type="number" step="0.01" name="amount"
                   value="<?php echo $income['amount']; ?>" required>
        </div>

        <div class="form-group">
            <label>Source</label>
            <input type="text" name="source"
                   value="<?php echo htmlspecialchars($income['source']); ?>" required>
        </div>

        <div class="form-group">
            <label>Income Date</label>
            <input type="date" name="income_date"
                   value="<?php echo $income['income_date']; ?>" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description"><?php
                echo htmlspecialchars($income['description']);
            ?></textarea>
        </div>

        <button type="submit" name="update_income">Update Income</button>
    </form>

    <div class="message">
        <?php if ($error) echo "<div class='error'>$error</div>"; ?>
        <?php if ($success) echo "<div class='success'>$success</div>"; ?>
    </div>

    <div class="back-link">
        <a href="view_income.php">← Back to Income List</a>
    </div>
</div>
</body>
