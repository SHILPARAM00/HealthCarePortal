<?php
session_start();
require 'config/database.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $gender = $_POST['gender'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $age = (int)$_POST['age'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            // Insert into users table
            $role = 'patient';
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;

                // Insert into patients table
                $status = 'active';
                $stmt2 = $conn->prepare("INSERT INTO patients (name, user_id, password, gender, phone, address, age, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param("sissssis", $name, $user_id, $hashed_password, $gender, $phone, $address, $age, $status);
                $stmt2->execute();

                $success = "Registration successful! Please login to continue.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }

    $stmt->close();
    if (isset($stmt2)) $stmt2->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Registration - Healthcare Portal</title>
<link rel="stylesheet" href="assets/css/style.css?v=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
* { box-sizing:border-box; margin:0; padding:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { min-height:100vh; display:flex; flex-direction:column; background:url('assets/images/login_register_bg.png') center/cover no-repeat fixed; }

/* Header */
header { display:flex; align-items:center; justify-content:space-between; padding:15px 50px; background: linear-gradient(135deg,#2f80ed,#56ccf2); color:#fff; flex-wrap:wrap; }
header .logo-title { display:flex; align-items:center; gap:15px; }
header .logo { height:70px; width:70px; border-radius:50%; object-fit:cover; border:2px solid #fff; }
header h1 { font-size:1.8rem; font-weight:700; display:flex; align-items:center; margin:0; }
header h1 a { color:#fff; text-decoration:none; }
header nav { display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
header nav a { color:#fff; text-decoration:none; font-weight:600; display:flex; align-items:center; transition:0.3s; }
header nav a i { margin-right:6px; }
header nav a:hover { color:#ffeb3b; transform:scale(1.05); }

/* Auth Container */
.auth-container { 
    background: rgba(255,255,255,0.95);
    max-width:900px; 
    margin:60px auto; 
    padding:40px 30px; 
    border-radius:12px; 
    box-shadow:0 10px 25px rgba(0,0,0,0.15); 
    display:grid; 
    grid-template-columns: 1fr 1fr; 
    gap:20px;
}

.auth-container h2 { grid-column:1 / -1; text-align:center; margin-bottom:20px; color:#2f80ed; }
.auth-container label { display:block; margin-top:12px; font-weight:600; }
.auth-container input, .auth-container select { width:100%; padding:12px; margin-top:5px; border:1px solid #ccc; border-radius:8px; font-size:1rem; }

/* Password Eye */
.password-container { position:relative; width:100%; }
.password-container input { padding-right:40px; }
.password-container .eye { position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; font-size:18px; color:#555; }

/* Button */
button { grid-column:1 / -1; width:100%; padding:12px; margin-top:20px; background:linear-gradient(135deg,#ff6b6b,#f94d6a); border:none; color:#fff; font-size:16px; font-weight:700; border-radius:50px; cursor:pointer; transition:0.4s; }
button:hover { transform:translateY(-2px) scale(1.03); box-shadow:0 8px 20px rgba(0,0,0,0.2); }

.success { color:green; text-align:center; grid-column:1 / -1; margin-top:10px; }
.error { color:red; text-align:center; grid-column:1 / -1; margin-top:10px; }

.links { text-align:center; grid-column:1 / -1; margin-top:15px; }
.links a { text-decoration:none; color:#2f80ed; transition:0.3s; }
.links a:hover { color:#ff6b6b; }

@media screen and (max-width:900px){
    .auth-container { grid-template-columns:1fr; max-width:450px; margin:40px auto; padding:30px 20px; }
}
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
    <h2>Patient Registration</h2>

    <?php if($error != ""): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if($success != ""): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <div class="links">
            <p><a href="login.php">Click here to login</a></p>
        </div>
    <?php endif; ?>

    <?php if($success == ""): ?>
    <form method="POST" style="grid-column:1 / -1; display:grid; grid-template-columns:1fr 1fr; gap:20px;">
        <div>
            <label>Full Name</label>
            <input type="text" name="name" placeholder="Enter full name" required>

            <label>Email</label>
            <input type="email" name="email" placeholder="Enter email" required>

            <label>Gender</label>
            <select name="gender" required>
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>

            <label>Phone</label>
            <input type="text" name="phone" placeholder="Enter phone number" required>
        </div>

        <div>
            <label>Address</label>
            <input type="text" name="address" placeholder="Enter address" required>

            <label>Age</label>
            <input type="number" name="age" placeholder="Enter age" required>

            <label>Password</label>
            <div class="password-container">
                <input type="password" name="password" placeholder="Enter password" required>
                <span class="eye" onclick="togglePassword(this)">👁</span>
            </div>

            <label>Confirm Password</label>
            <div class="password-container">
                <input type="password" name="confirm_password" placeholder="Confirm password" required>
                <span class="eye" onclick="togglePassword(this)">👁</span>
            </div>
        </div>

        <button type="submit">Register</button>
    </form>
    <?php endif; ?>

    <div class="links">
        <p>Already have an account? <a href="login.php">Login here</a></p>
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