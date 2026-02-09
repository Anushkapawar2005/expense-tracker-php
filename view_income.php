<?php
session_start();
include "db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all income records
$income_query = mysqli_query(
    $conn,
    "SELECT * FROM income 
     WHERE user_id='$user_id' 
     ORDER BY income_date DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Income</title>

    <!-- External CSS -->
    <link rel="stylesheet" href="css/table.css">

    
</head>

<body>

<h2>All Income Records</h2>

<!-- Top Controls -->
<div class="top-bar">

    <div>
        <a class="btn" href="add_income.php">+ Add Income</a>
        <a class="btn" href="dashboard.php">← Dashboard</a>
    </div>

    

</div>

<!-- Table Card -->
<div class="table-card">

<table>

<tr>
    <th>ID</th>
    <th>Amount (₹)</th>
    <th>Source</th>
    <th>Date</th>
    <th>Description</th>
    <th>Added On</th>
    <th>Actions</th>
</tr>

<?php if (mysqli_num_rows($income_query) > 0): ?>

    <?php while ($row = mysqli_fetch_assoc($income_query)): ?>

        <tr>
            <td><?php echo $row['income_id']; ?></td>

            <td>₹ <?php echo number_format($row['amount'], 2); ?></td>

            <td>
                <?php echo htmlspecialchars($row['source']); ?>
            </td>

            <td><?php echo $row['income_date']; ?></td>

            <td>
                <?php echo htmlspecialchars($row['description']); ?>
            </td>

            <td><?php echo $row['created_at']; ?></td>

            <td class="action-links">

                <a class="edit-btn"
                   href="edit_income.php?id=<?php echo $row['income_id']; ?>">
                   Edit
                </a>

                <a class="delete-btn"
                   href="delete_income.php?id=<?php echo $row['income_id']; ?>"
                   onclick="return confirm('Are you sure you want to delete this income?');">
                   Delete
                </a>

            </td>
        </tr>

    <?php endwhile; ?>

<?php else: ?>

    <tr>
        <td colspan="7" class="no-data">
            No income records found.
        </td>
    </tr>

<?php endif; ?>

</table>

</div>

</body>
</html>
