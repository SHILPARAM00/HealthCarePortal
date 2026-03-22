<?php
session_start();
require_once '../../config/database.php';

// Admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

/* Fetch counts for cards */
$doctor_count = $conn->query("SELECT COUNT(*) AS total FROM doctors")->fetch_assoc()['total'];
$patient_count = $conn->query("SELECT COUNT(*) AS total FROM patients")->fetch_assoc()['total'];
$appointment_count = $conn->query("SELECT COUNT(*) AS total FROM appointments")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* HEADER */
header{
display:flex;
justify-content:space-between;
align-items:center;
background: linear-gradient(135deg, #2f80ed, #56ccf2);
padding:15px 50px;
color:white;
flex-wrap:wrap;
}

header h1 {
    font-size: 1.8rem;
    margin-left: 10px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

header nav a{
color:white;
text-decoration:none;
margin-left:20px;
font-weight:600;
transition: 0.3s;
}

header nav a i {
    margin-right:6px;
}

header nav a:hover{
color:#ffe600;
}

header .logo {
    height: 80px;
    width: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 15px;
    border: 2px solid #fff;
}

/* DASHBOARD */
.dashboard-header{
text-align:center;
margin:40px 0 20px;
}

.dashboard-header h2{
color:#2f80ed;
font-size:2rem;
}

.dashboard-header p{
color:#555;
font-size:1.1rem;
}

.dashboard-cards{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:20px;
padding:20px;
}

.card{
background:white;
padding:25px;
border-radius:10px;
box-shadow:0 5px 15px rgba(0,0,0,0.1);
text-align:center;
transition: 0.3s;
}

.card:hover{
transform: translateY(-5px);
}

.card h3{
margin-bottom:10px;
}

.card p{
font-size:28px;
font-weight:bold;
color:#2f80ed;
}

.card a{
display:inline-block;
margin-top:10px;
padding:8px 15px;
background:#2f80ed;
color:white;
border-radius:6px;
text-decoration:none;
transition: 0.3s;
}

.card a:hover{
background:#1c5fc0;
}

/* RESPONSIVE */
@media screen and (max-width: 768px){
    header {
        flex-direction: column;
        align-items: flex-start;
    }
    header nav {
        margin-top: 10px;
    }
}
</style>

</head>
<body>

<header>
<div style="display:flex;align-items:center;">
<img src="../../assets/images/logo.png" class="logo">
<h1>Admin Dashboard</h1>
</div>

<nav>
<a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
<a href="manage_doctors.php"><i class="fas fa-user-md"></i> Doctors</a>
<a href="manage_patients.php"><i class="fas fa-user"></i> Patients</a>
<a href="manage_appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a>
<a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>
</header>

<main>
<div class="dashboard-header">
<h2>Welcome, Admin</h2>
<p>Quick overview of the system</p>
</div>

<div class="dashboard-cards">
<div class="card">
<h3>Total Doctors</h3>
<p><?php echo $doctor_count; ?></p>
<a href="manage_doctors.php">View</a>
</div>

<div class="card">
<h3>Total Patients</h3>
<p><?php echo $patient_count; ?></p>
<a href="manage_patients.php">View</a>
</div>

<div class="card">
<h3>Total Appointments</h3>
<p><?php echo $appointment_count; ?></p>
<a href="manage_appointments.php">View</a>
</div>

</div>
</main>

</body>
</html>