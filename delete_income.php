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

// Secure delete
$stmt = $conn->prepare(
    "DELETE FROM income WHERE income_id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $income_id, $user_id);
$stmt->execute();
$stmt->close();

// Redirect after deletion
header("Location: view_income.php");
exit();
?>
