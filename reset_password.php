<?php
session_start();
require 'config/database.php';

// Initialize variables to avoid warnings
$error = "";
$success = "";

// Check if reset email is set
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Hash new password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Update password in DB
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            $success = "Password updated successfully! You can now login.";
            unset($_SESSION['reset_email']); // remove email from session
        } else {
            $error = "Failed to reset password. Try again.";
        }

        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password - Healthcare Portal</title>
<link rel="stylesheet" href="assets/css/style.css?v=12">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* CSS same as forgot_password */
* { box-sizing:border-box; margin:0; padding:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { min-height:100vh; display:flex; flex-direction:column; background:url('assets/images/login_register_bg.png') center/cover no-repeat fixed; }
header { display:flex; align-items:center; justify-content:space-between; padding:15px 50px; background: linear-gradient(135deg,#2f80ed,#56ccf2); color:#fff; flex-wrap:wrap; }
header .logo-title { display:flex; align-items:center; gap:15px; }
header .logo { height:70px; width:70px; border-radius:50%; object-fit:cover; border:2px solid #fff; }
header h1 { font-size:1.8rem; font-weight:700; display:flex; align-items:center; margin:0; }
header h1 a { color:#fff; text-decoration:none; }
header nav { display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
header nav a { color:#fff; text-decoration:none; font-weight:600; display:flex; align-items:center; transition:0.3s; }
header nav a i { margin-right:6px; }
header nav a:hover { color:#ffeb3b; transform:scale(1.05); }
.auth-container { background: rgba(255,255,255,0.95); max-width:400px; margin:60px auto; padding:40px 30px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.15); position:relative; backdrop-filter: blur(6px); }
.auth-container h2 { text-align:center; margin-bottom:20px; color:#2f80ed; }
.auth-container label { display:block; margin-top:12px; font-weight:600; }
.auth-container input { width:100%; padding:12px; margin-top:5px; border:1px solid #ccc; border-radius:8px; font-size:1rem; }

/* Eye icon for password */
.password-container { position:relative; width:100%; margin-top:12px; }
.password-container input { padding-right:40px; }
.password-container .eye { position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; font-size:18px; color:#555; }

button { width:100%; padding:12px; margin-top:20px; background:linear-gradient(135deg,#ff6b6b,#f94d6a); border:none; color:#fff; font-size:16px; font-weight:700; border-radius:50px; cursor:pointer; transition:0.4s; }
button:hover { transform:translateY(-2px) scale(1.03); box-shadow:0 8px 20px rgba(0,0,0,0.2); }

.success { color:green; text-align:center; margin-top:10px; }
.error { color:red; text-align:center; margin-top:10px; }

.links { text-align:center; margin-top:15px; }
.links a { text-decoration:none; color:#2f80ed; transition:0.3s; }
.links a:hover { color:#ff6b6b; }

@media screen and (max-width:480px){ .auth-container { margin:40px 20px; padding:30px 20px; } }
</style>
</head>
<body>

<header>
    <div class="logo-title">
        <img src="assets/images/logo.png" alt="Healthcare Portal Logo" class="logo">
        <h1><a href="index.php">Healthcare Portal</a></h1>
    </div>
    <nav>
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
    </nav>
</header>

<div class="auth-container">
    <h2>Reset Password</h2>

    <?php if($error != ""): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if($success != ""): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <?php if($success==""): ?>
    <form method="POST">
        <label>New Password</label>
        <div class="password-container">
            <input type="password" name="password" placeholder="Enter new password" required>
            <span class="eye" onclick="togglePassword(this)">👁</span>
        </div>

        <label>Confirm Password</label>
        <div class="password-container">
            <input type="password" name="confirm_password" placeholder="Confirm password" required>
            <span class="eye" onclick="togglePassword(this)">👁</span>
        </div>

        <button type="submit">Reset Password</button>
    </form>
    <?php endif; ?>

    <div class="links">
        <p><a href="login.php">Back to Login</a></p>
    </div>
</div>

<script>
function togglePassword(el){
    const input = el.previousElementSibling;
    input.type = input.type === "password" ? "text" : "password";
}
</script>

</body>
</html>