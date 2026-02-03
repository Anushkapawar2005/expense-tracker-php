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

$stmt = $conn->prepare(
    "DELETE FROM expenses WHERE expense_id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $expense_id, $user_id);
$stmt->execute();
$stmt->close();

header("Location: view_expense.php");
exit();
?>
