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

// Fetch categories
$cat_result = mysqli_query($conn, "SELECT * FROM categories WHERE user_id='$user_id'");

if (isset($_POST['add_expense'])) {
    $category_id = $_POST['category_id'];
    $amount = $_POST['amount'];
    $expense_date = $_POST['expense_date'];
    $description = $_POST['description'];

    if (empty($category_id) || empty($amount) || empty($expense_date)) {
        $error = "Please fill all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO expenses (user_id, category_id, amount, expense_date, description, created_at) VALUES (?, ?, ?, ?, ?, CURDATE())");
        $stmt->bind_param("iidss", $user_id, $category_id, $amount, $expense_date, $description);

        if ($stmt->execute()) {
            $success = "Expense added successfully.";
        } else {
            $error = "Failed to add expense: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Expense</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 10px;
        }

        .form-wrapper {
            background: #fff;
            padding: 35px 30px;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 450px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .form-wrapper:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.12);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #374151;
            font-size: 14px;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fff;
            box-sizing: border-box;
            appearance: none;
        }

        select {
            width: 105%;               /* wider than inputs */
            margin-left: -2.5%;        /* center the wider select */
            background: #fff url('data:image/svg+xml;utf8,<svg fill="%23747474" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 12px center;
            background-size: 16px 16px;
            padding-right: 40px;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79,70,229,0.15);
            outline: none;
        }

        textarea {
            resize: none;
            height: 70px;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(79,70,229,0.35);
        }

        .message {
            text-align: center;
            margin-top: 12px;
            font-size: 14px;
        }

        .error { color: #dc2626; }
        .success { color: #16a34a; }

        .back-link {
            text-align: center;
            margin-top: 18px;
        }

        .back-link a {
            color: #4f46e5;
            font-weight: 600;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        @media(max-width:480px){
            .form-wrapper { padding: 25px 20px; }
            select { width: 100%; margin-left:0; } /* full width on mobile */
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <h2>Add Expense</h2>
    <form method="post">
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php while($row = mysqli_fetch_assoc($cat_result)) { ?>
                    <option value="<?php echo $row['category_id']; ?>"><?php echo $row['category_name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label>Amount (₹)</label>
            <input type="number" step="0.01" name="amount" required>
        </div>
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="expense_date" required>
        </div>
        <div class="form-group">
            <label>Description (Optional)</label>
            <textarea name="description"></textarea>
        </div>
        <button type="submit" name="add_expense">Add Expense</button>
    </form>

    <div class="message">
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <?php if($success) echo "<div class='success'>$success</div>"; ?>
    </div>

    <div class="back-link">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
