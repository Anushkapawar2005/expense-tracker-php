<?php
include "db_connect.php";

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $insert = $conn->prepare(
                "INSERT INTO users (name, email, password, created_at)
                 VALUES (?, ?, ?, CURDATE())"
            );
            $insert->bind_param("sss", $name, $email, $hashed_password);

            if ($insert->execute()) {
                $success = "Registration successful. Please login.";
            } else {
                $error = "Registration failed. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Registration</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-box">

        <!-- LEFT PANEL -->
        <div class="panel left">
            <h1>WELCOME!</h1>
        </div>

        <!-- RIGHT PANEL -->
        <div class="panel right form-box animation">
            <h2>Register</h2>

            <form method="post">
                <div class="input-box">
                    <input type="text" name="name" required>
                    <label>Username</label>
                </div>

                <div class="input-box">
                    <input type="email" name="email" required>
                    <label>Email</label>
                </div>

                <div class="input-box">
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>

                <button type="submit" name="register" class="btn">
                    Register
                </button>
            </form>

            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>

            <?php if ($success): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>

            <p class="switch">
                Already have an account?
                <a href="login.php">Sign in</a>
            </p>
        </div>

    </div>
</div>

</body>
</html>
