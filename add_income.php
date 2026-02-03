
<?php
session_start();
include "db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Handle form submission
if (isset($_POST['add_income'])) {
    $amount = $_POST['amount'];
    $source = $_POST['source'];
    $income_date = $_POST['income_date'];
    $description = $_POST['description'];

    if (empty($amount) || empty($source) || empty($income_date)) {
        $error = "Amount, Source, and Date are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO income (user_id, amount, source, income_date, description, created_at) VALUES (?, ?, ?, ?, ?, CURDATE())");
        $stmt->bind_param("idsss", $user_id, $amount, $source, $income_date, $description);

        if ($stmt->execute()) {
            $success = "Income added successfully.";
        } else {
            $error = "Failed to add income: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Income</title>
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
            max-width: 420px;
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
            width: 90%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fff;
            appearance: none; /* remove default arrow */
        }

        select {
           width: 97%
             
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

        /* Mobile Optimization */
        @media (max-width: 480px) {
            .form-wrapper {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <h2>Add Income</h2>

    <form method="post">
        <div class="form-group">
            <label>Amount (₹)</label>
            <input type="number" step="0.01" name="amount" placeholder="Enter amount" required>
        </div>

        <div class="form-group">
            <label>Income Source</label>
            <select name="source" required>
                <option value="">Select source</option>
                <option value="Salary">Salary</option>
                <option value="Freelance">Freelance</option>
                <option value="Gift">Gift</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label>Income Date</label>
            <input type="date" name="income_date" required>
        </div>

        <div class="form-group">
            <label>Description (Optional)</label>
            <textarea name="description" placeholder="Enter a short description"></textarea>
        </div>

        <button type="submit" name="add_income">Add Income</button>
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
