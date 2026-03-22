<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Healthcare Portal</title>
<link rel="stylesheet" href="/assets/css/style.css?v=1">
</head>
<body>
<header>
    <h1>Healthcare Portal</h1>
    <nav>
        <a href="/index.php">Home</a>

        <?php if (isset($_SESSION['role'])): ?>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="/modules/admin/dashboard.php">Dashboard</a>
            <?php elseif ($_SESSION['role'] == 'doctor'): ?>
                <a href="/modules/doctor/dashboard.php">Dashboard</a>
            <?php elseif ($_SESSION['role'] == 'patient'): ?>
                <a href="/modules/patient/dashboard.php">Dashboard</a>
            <?php endif; ?>
            <a href="/logout.php">Logout</a>
        <?php else: ?>
            <a href="/login.php">Login</a>
            <a href="/register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="main-container">