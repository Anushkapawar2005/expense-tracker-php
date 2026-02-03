<?php
session_start();
include "db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all income records for this user
$income_query = mysqli_query($conn, "SELECT * FROM income WHERE user_id='$user_id' ORDER BY income_date DESC");

?>

<!DOCTYPE html>
<html>
<head>
    <title>View Income</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        a { text-decoration: none; color: #007bff; }
        a:hover { text-decoration: underline; }
        .nav { margin-top: 15px; }
    </style>
</head>
<body>

<h2>All Income Records</h2>

<a href="add_income.php">Add New Income</a>
<div class="nav">
    <a href="dashboard.php">Back to Dashboard</a>
</div>

<table>
    <tr>
        <th>ID</th>
        <th>Amount (₹)</th>
        <th>Source</th>
        <th>Income Date</th>
        <th>Description</th>
        <th>Added On</th>
        <th>Actions</th>

    </tr>
    <?php if (mysqli_num_rows($income_query) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($income_query)): ?>
        <tr>
            <td><?php echo $row['income_id']; ?></td>
            <td><?php echo number_format($row['amount'], 2); ?></td>
            <td><?php echo htmlspecialchars($row['source']); ?></td>
            <td><?php echo $row['income_date']; ?></td>
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td><?php echo $row['created_at']; ?></td>
            <td>
    <a href="edit_income.php?id=<?php echo $row['income_id']; ?>">Edit</a> |
    <a href="delete_income.php?id=<?php echo $row['income_id']; ?>"
       onclick="return confirm('Are you sure you want to delete this income?');">
       Delete
    </a>
</td>

        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" style="text-align:center;">No income records found.</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>
