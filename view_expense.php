<?php
session_start();
include "db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch expenses with category name
$sql = "SELECT e.expense_id, e.amount, e.expense_date, 
               e.description, c.category_name
        FROM expenses e
        JOIN categories c 
          ON e.category_id = c.category_id
        WHERE e.user_id = '$user_id'
        ORDER BY e.expense_date DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Expenses</title>

<!-- Same CSS as Income Page -->
<link rel="stylesheet" href="css/table.css">


</head>

<body>

<h2>All Expense Records</h2>

<!-- Top Controls -->
<div class="top-bar">

    <div>
        <a class="btn" href="add_expense.php">+ Add Expense</a>
        <a class="btn" href="dashboard.php">← Dashboard</a>
    </div>

</div>

<!-- Table Card -->
<div class="table-card">

<table>

<tr>
    <th>ID</th>
    <th>Amount (₹)</th>
    <th>Category</th>
    <th>Date</th>
    <th>Description</th>
    <th>Actions</th>
</tr>

<?php if (mysqli_num_rows($result) > 0): ?>

    <?php while ($row = mysqli_fetch_assoc($result)): ?>

        <tr>

            <td><?php echo $row['expense_id']; ?></td>

            <td>₹ <?php echo number_format($row['amount'], 2); ?></td>

            <td>
                <?php echo htmlspecialchars($row['category_name']); ?>
            </td>

            <td><?php echo $row['expense_date']; ?></td>

            <td>
                <?php echo htmlspecialchars($row['description']); ?>
            </td>

            <td class="action-links">

                <a class="edit-btn"
                   href="edit_expense.php?id=<?php echo $row['expense_id']; ?>">
                   Edit
                </a>

                <a class="delete-btn"
                   href="delete_expense.php?id=<?php echo $row['expense_id']; ?>"
                   onclick="return confirm('Do you want to delete this expense?');">
                   Delete
                </a>

            </td>

        </tr>

    <?php endwhile; ?>

<?php else: ?>

    <tr>
        <td colspan="6" class="no-data">
            No expense records found.
        </td>
    </tr>

<?php endif; ?>

</table>

</div>

</body>
</html>
