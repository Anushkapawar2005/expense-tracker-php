<?php
session_start();
include "db_connect.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch expenses with category name
$sql = "SELECT e.expense_id, e.amount, e.expense_date, e.description,
               c.category_name
        FROM expenses e
        JOIN categories c ON e.category_id = c.category_id
        WHERE e.user_id = '$user_id'
        ORDER BY e.expense_date DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Expenses</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #999;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>My Expenses</h2>

<table>
    <tr>
        <th>Date</th>
        <th>Category</th>
        <th>Amount (₹)</th>
        <th>Description</th>
        <th>Actions</th>
    </tr>

    <?php
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {

        echo "<tr>";
        echo "<td>" . $row['expense_date'] . "</td>";
        echo "<td>" . $row['category_name'] . "</td>";
        echo "<td>" . number_format($row['amount'], 2) . "</td>";
        echo "<td>" . $row['description'] . "</td>";

        // Actions column
        echo "<td>
                <a href='edit_expense.php?id=" . $row['expense_id'] . "'>Edit</a> |
                <a href='delete_expense.php?id=" . $row['expense_id'] . "'
                   onclick=\"return confirm('Do you want to delete this expense?');\">
                   Delete
                </a>
              </td>";

        echo "</tr>";
    }
} else {
    echo "<tr>
            <td colspan='5' style='text-align:center;'>No expenses found.</td>
          </tr>";
}
?>

</table>

<br>
<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>
