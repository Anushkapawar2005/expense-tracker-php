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

/* Fetch Categories */
$cat_result = mysqli_query($conn,"
    SELECT * FROM categories
    WHERE user_id='$user_id'
");

/* Add Expense Logic */
if(isset($_POST['add_expense'])){

    $category_id  = $_POST['category_id'];
    $amount       = $_POST['amount'];
    $expense_date = $_POST['expense_date'];
    $description  = $_POST['description'];

    if(empty($category_id) || empty($amount) || empty($expense_date)){
        $error = "Please fill all required fields.";
    }
    else{

        $stmt = $conn->prepare("
            INSERT INTO expenses
            (user_id, category_id, amount, expense_date, description, created_at)
            VALUES (?, ?, ?, ?, ?, CURDATE())
        ");

        $stmt->bind_param(
            "iidss",
            $user_id,
            $category_id,
            $amount,
            $expense_date,
            $description
        );

        if($stmt->execute()){
            $success = "Expense added successfully.";
        }else{
            $error = "Failed to add expense.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Expense</title>
    <link rel="stylesheet" href="css/form.css">

    <!-- Internal CSS for Add Category Button -->
    <style>

    /* Category Button Design */
    .add-category-btn2{
        display:inline-flex;
        align-items:center;
        gap:5px;

        margin-top:12px;
        padding:5px 8px;

        font-size:13px;
        font-weight:600;

        color:black;
        background:linear-gradient(135deg,#eef2ff,#e0e7ff);

        border:1px solid #c7d2fe;
        border-radius:12px;

        text-decoration:none;
        transition:all 0.30s ease;
    }

    .add-category-btn2 .icon{
        display:flex;
        align-items:center;
        justify-content:center;

        width:22px;
        height:22px;

        background:#4f46e5;
        color:#fff;

        font-size:14px;
        border-radius:6px;
    }

    .add-category-btn2:hover{
        background:linear-gradient(135deg,#4f46e5,#6366f1);
        color:#fff;
        transform:translateY(-2px);
        box-shadow:0 10px 20px rgba(79,70,229,0.35);
    }

    .add-category-btn2:hover .icon{
        background:#fff;
        color:#4f46e5;
    }

    </style>
</head>

<body>

<div class="form-wrapper">

    <h2>Add Expense</h2>

    <form method="post">

        <!-- Category -->
        <div class="form-group">

            <label>Category</label>

            <select name="category_id" required>
                <option value="">Select Category</option>

                <?php while($row=mysqli_fetch_assoc($cat_result)){ ?>
                    <option value="<?php echo $row['category_id']; ?>">
                        <?php echo $row['category_name']; ?>
                    </option>
                <?php } ?>

            </select>

            <!-- Add Category Button -->
            <a href="add_category.php" class="add-category-btn2">
                <span class="icon">＋</span>
                Add New Category
            </a>

        </div>

        <!-- Amount -->
        <div class="form-group">
            <label>Amount (₹)</label>
            <input type="number" step="0.01" name="amount" required>
        </div>

        <!-- Date -->
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="expense_date" required>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label>Description (Optional)</label>
            <textarea name="description"></textarea>
        </div>

        <button type="submit" name="add_expense">
            Add Expense
        </button>

    </form>

    <!-- Messages -->
    <div class="message">
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <?php if($success) echo "<div class='success'>$success</div>"; ?>
    </div>

    <!-- Back -->
    <div class="back-link">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

</div>

</body>
</html>
