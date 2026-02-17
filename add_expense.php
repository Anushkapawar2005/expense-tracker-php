<?php
session_start();
include "db_connect.php";

/* Auth Check */
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

<!-- Header & Footer CSS -->
<link rel="stylesheet" href="css/header.css">
<link rel="stylesheet" href="css/footer.css">

<style>

/* ===== PAGE LAYOUT ===== */

body{
    font-family:'Segoe UI', Arial, sans-serif;
    background:#f4f6f9;
    margin:0;

    display:flex;
    flex-direction:column;
    min-height:100vh;
}

/* Center Content */

.page-content{
    flex:1;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px 10px;
}

/* ===== FORM DESIGN ===== */

.form-wrapper{
    background:#fff;
    padding:20px 25px;
    border-radius:15px;
    box-shadow:0 15px 40px rgba(0,0,0,0.08);
    width:90%;
    max-width:420px;
}

h2{
    text-align:center;
    margin-bottom:25px;
    font-size:24px;
    font-weight:700;
    color:#1f2937;
}

.form-group{ margin-bottom:17px; }

label{
    display:block;
    font-weight:600;
    margin-bottom:4px;
    color:#374151;
    font-size:14px;
}

input,select,textarea{
    width:90%;
    padding:12px 14px;
    border-radius:10px;
    border:1px solid #d1d5db;
    font-size:14px;
}

select{ width:97%; }

textarea{
    resize:none;
    height:70px;
}

button{
    width:100%;
    padding:14px;
    background:linear-gradient(135deg,#4f46e5,#6366f1);
    color:#fff;
    border:none;
    border-radius:12px;
    cursor:pointer;
}

.message{ text-align:center; margin-top:12px; }
.error{ color:#dc2626; }
.success{ color:#16a34a; }

/* ===== Add Category Button ===== */

.add-category-btn{
    display:inline-flex;
    gap:6px;
    margin-top:10px;
    padding:6px 10px;
    font-size:13px;
    font-weight:600;
    color:#1e3a8a;
    background:#e0e7ff;
    border-radius:10px;
    text-decoration:none;
}

/* Mobile */

@media(max-width:480px){
    .form-wrapper{
        padding:25px 20px;
    }
}

</style>
</head>

<body>

<!-- ===== HEADER ===== -->
<?php include "includes/header.php"; ?>

<!-- ===== PAGE CONTENT ===== -->
<div class="page-content">

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

<a href="add_category.php" class="add-category-btn">
＋ Add New Category
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
<label>Description</label>
<textarea name="description"></textarea>
</div>

<button type="submit" name="add_expense">
Add Expense
</button>

</form>

<div class="message">
<?php if($error) echo "<div class='error'>$error</div>"; ?>
<?php if($success) echo "<div class='success'>$success</div>"; ?>
</div>

</div>
</div>

<!-- ===== FOOTER ===== -->
<?php include "includes/footer.php"; ?>

</body>
</html>
