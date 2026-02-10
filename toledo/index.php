<?php
session_start();
require_once "db.php";

$error = "";

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare(
        "SELECT username, password, role FROM users WHERE username = ?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // NO HASHING — direct comparison
        if ($password === $user['password']) {

            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: dashboard.php");
            } elseif ($user['role'] === 'staff') {
                header("Location: staff_dashboard.php");
            }
            exit;
        }
    }

    $error = "Invalid username or password";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jovern's Inventory Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-body">

<div class="login-container">
    <div class="login-box">
        <div class="login-header">
            <h1>Jovern's Fabricating Shop</h1>
            <p class="login-subtitle">Inventory Management System</p>
        </div>

        <div class="login-content">
            <?php if (!empty($error)): ?>
                <div class="login-error">
                    <span class="error-icon">⚠</span>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <p class="login-greeting">Please enter your credentials to access the system</p>

            <form method="post" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" name="login" class="login-btn">Sign In</button>
            </form>
        </div>

        <div class="login-footer">
            <p>© 2026 Jovern’s Fabricating Shop | Built with Quality & Care
</p>
        </div>
    </div>
</div>

</body>
</html>
