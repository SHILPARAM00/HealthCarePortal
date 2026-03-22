<?php
session_start();
require 'config/database.php';

$message = "";
$show_reset = false;
$old_password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($password);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        $old_password = $password; // plain text password (for demo, usually hashed)
        $_SESSION['reset_email'] = $email;
        $show_reset = true;
    } else {
        $message = "No account found with this email! <a href='register.php'>Register here</a>";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password - Healthcare Portal</title>
<style>
    ody { 
    min-height: 100vh;
    display: flex; 
    flex-direction: column;
    line-height: 1.6;
    background: url('assets/images/login_register_bg.png') center/cover no-repeat fixed;
}

/* =========================
   Header
========================= */
header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 50px;
    background: linear-gradient(135deg, #2f80ed, #56ccf2);
    color: #fff;
    flex-wrap: wrap;
}

header .logo-title { display:flex; align-items:center; gap:15px; }
header .logo { height:70px; width:70px; border-radius:50%; object-fit:cover; border:2px solid #fff; }
header h1 { font-size:1.8rem; font-weight:700; display:flex; align-items:center; margin:0; }
header h1 a { color:#fff; text-decoration:none; }
header nav { display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
header nav a { color:#fff; text-decoration:none; font-weight:600; display:flex; align-items:center; transition:0.3s; }
header nav a i { margin-right:6px; }
header nav a:hover { color:#ffeb3b; transform:scale(1.05); }
body { min-height:100vh; display:flex; flex-direction:column; background: url('assets/images/login_register_bg.png') center/cover no-repeat fixed; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.auth-container { background: rgba(255,255,255,0.95); max-width:400px; margin:60px auto; padding:40px 30px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.15); position:relative; backdrop-filter: blur(6px);}
.auth-container h2 { text-align:center; margin-bottom:20px; color:#2f80ed; }
.auth-container label { display:block; margin-top:12px; font-weight:600; }
.auth-container input { width:100%; padding:12px; margin-top:5px; border:1px solid #ccc; border-radius:8px; font-size:1rem; box-sizing:border-box; background-color:#fff; }
.password-container { position:relative; width:100%; margin-top:12px; }
.password-container input { width:100%; padding-right:40px; background-color:#fff; border:1px solid #ccc; border-radius:8px; }
.password-container .eye { position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; font-size:18px; color:#555; }

button, .btn-link {
    width:100%;
    padding:12px;
    margin-top:12px;
    background:linear-gradient(135deg,#ff6b6b,#f94d6a);
    border:none;
    color:#fff;
    font-size:16px;
    font-weight:700;
    border-radius:50px;
    cursor:pointer;
    transition:0.4s;
    text-align:center;
    display:inline-block;
    text-decoration:none;
}
button:hover, .btn-link:hover { transform:translateY(-2px) scale(1.03); box-shadow:0 8px 20px rgba(0,0,0,0.25); }

.success { color:green; text-align:center; margin-top:10px; }
.error { color:red; text-align:center; margin-top:10px; }
.links { text-align:center; margin-top:15px; display:flex; flex-direction:column; gap:10px; }

form { display:flex; flex-direction:column; gap:12px; }
@media screen and (max-width:480px){ .auth-container { margin:40px 20px; padding:30px 20px; } }
</style>
</head>
<body>
    <!-- HEADER -->
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
<h2>Forgot Password</h2>

<?php if($message != ""): ?>
    <p class="error"><?php echo $message; ?></p>
<?php endif; ?>

<?php if(!$show_reset): ?>
<form method="POST">
    <label>Enter Your Registered Email</label>
    <input type="email" name="email" placeholder="Enter email" required>
    <button type="submit">Continue</button>
</form>
<?php else: ?>
    <label>Your Current Password</label>
    <div class="password-container">
        <input type="password" value="<?php echo htmlspecialchars($old_password); ?>" readonly id="current_password">
        <span class="eye" onclick="togglePassword('current_password')">👁</span>
    </div>

    <div class="links">
        <a href="reset_password.php" class="btn-link">Reset Password</a>
        <a href="login.php" class="btn-link">Back to Login</a>
    </div>
<?php endif; ?>
</div>

<script>
function togglePassword(fieldId){
    var input = document.getElementById(fieldId);
    input.type = input.type === "password" ? "text" : "password";
}
</script>

</body>
</html>