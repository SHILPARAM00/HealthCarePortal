<?php
session_start();
require 'config/database.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, username, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        $user_id = $user['user_id'];
        $username = $user['username'];
        $stored_password = $user['password'];
        $role = $user['role'];

        // If password is plain text (doctor), hash it automatically
        if ($role === "doctor" && strlen($stored_password) < 60) { 
            $hashed_password = password_hash($stored_password, PASSWORD_DEFAULT);
            // Update in database
            $update = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
            $update->bind_param("si", $hashed_password, $user_id);
            $update->execute();
        } else {
            $hashed_password = $stored_password;
        }

        // Verify password
        if (password_verify($password, $hashed_password)) {

            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            if ($role === "admin") {
                header("Location: modules/admin/dashboard.php");
            } 
            elseif ($role === "doctor") {
                header("Location: modules/doctor/dashboard.php");
            } 
            elseif ($role === "patient") {
                header("Location: modules/patient/dashboard.php");
            }
            exit;

        } else {
            $error = "Invalid password!";
        }

    } else {
        $error = "No account found with this email!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Healthcare Portal</title>
<link rel="stylesheet" href="assets/css/style.css?v=7">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
* { box-sizing: border-box; margin:0; padding:0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { 
    min-height: 100vh;
    display: flex; 
    flex-direction: column;
    line-height: 1.6;
    background: url('assets/images/login_register_bg.png') center/cover no-repeat fixed;
}
header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 15px 50px; background: linear-gradient(135deg, #2f80ed, #56ccf2); color: #fff; flex-wrap: wrap;
}
header .logo-title { display:flex; align-items:center; gap:15px; }
header .logo { height:70px; width:70px; border-radius:50%; object-fit:cover; border:2px solid #fff; }
header h1 { font-size:1.8rem; font-weight:700; display:flex; align-items:center; margin:0; }
header h1 a { color:#fff; text-decoration:none; }
header nav { display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
header nav a { color:#fff; text-decoration:none; font-weight:600; display:flex; align-items:center; transition:0.3s; }
header nav a i { margin-right:6px; }
header nav a:hover { color:#ffeb3b; transform:scale(1.05); }

.auth-container {
    background: rgba(255,255,255,0.95); max-width:400px;
    margin:60px auto; padding:40px 30px; border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.15); position:relative;
    backdrop-filter: blur(6px);
}
.auth-container h2 { text-align:center; margin-bottom:20px; color:#2f80ed; }
.auth-container label { display:block; margin-top:12px; font-weight:600; }
.auth-container input { width:100%; padding:12px; margin-top:5px; border:1px solid #ccc; border-radius:8px; font-size:1rem; }
.password-container { position:relative; width:100%; }
.password-container input { padding-right:40px; }
.password-container .eye { position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; font-size:18px; }
button { width:100%; padding:12px; margin-top:20px; background:linear-gradient(135deg,#ff6b6b,#f94d6a); border:none; color:#fff; font-size:16px; font-weight:700; border-radius:50px; cursor:pointer; transition:0.4s; }
button:hover { transform:translateY(-2px) scale(1.03); box-shadow:0 8px 20px rgba(0,0,0,0.25); }
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
        <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
    </nav>
</header>

<div class="auth-container">
    <h2>Login</h2>

    <?php if ($error != ""): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="login.php" method="post">
        <label>Email</label>
        <input type="email" name="email" placeholder="Enter your email" required>

        <label>Password</label>
        <div class="password-container">
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <span class="eye" onclick="togglePassword()">👁</span>
        </div>

        <div style="text-align:right; margin-top:8px;">
            <a href="forget_password.php">Forgot Password?</a>
        </div>

        <button type="submit">Login</button>
    </form>

    <div class="links">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>

<script>
function togglePassword(){
    var password = document.getElementById("password");
    password.type = password.type === "password" ? "text" : "password";
}
</script>

</body>
</html>