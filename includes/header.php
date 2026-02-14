<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header class="main-header">

    <div class="logo">
        💰 ExpenseTracker
    </div>

    <nav class="menu" id="menu">

        <a href="dashboard.php">Dashboard</a>
        <a href="add_expense.php">Add Expense</a>
        <a href="add_income.php">Add Income</a>
        <a href="budget.php">Budget</a>
        <a href="reports.php">Reports</a>
        <a href="logout.php" class="logout">Logout</a>

    </nav>

    <!-- Mobile Toggle -->
    <div class="toggle" onclick="toggleMenu()">
        ☰
    </div>

</header>

<script>
function toggleMenu(){
    document.getElementById("menu").classList.toggle("active");
}
</script>
