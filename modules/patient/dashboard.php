<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* Get patient name */
$query = "SELECT username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$patient_name = $user['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Dashboard - Healthcare Portal</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* =========================
   GLOBAL & RESET
========================= */
* { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }

/* =========================
   HEADER STYLES
========================= */
header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding: 15px 50px;
    background: linear-gradient(135deg, #2f80ed, #56ccf2);
    color:#fff;
    flex-wrap:wrap;
}
header .logo-title { display:flex; align-items:center; gap:15px; }
header .logo { height:80px; width:80px; border-radius:50%; object-fit:cover; border:2px solid #fff; }
header h1 { font-size:1.8rem; font-weight:700; margin:0; color:#fff; }
header nav { display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
header nav a { color:#fff; text-decoration:none; font-weight:600; transition:0.3s; display:flex; align-items:center; }
header nav a i { margin-right:6px; }
header nav a:hover { color:#ffeb3b; transform:scale(1.05); }

/* =========================
   HERO BACKGROUND
========================= */
body {
    background: url('../../assets/images/patient_dashboard_hero_bg.png') no-repeat center center;
    background-size: cover;
    min-height:100vh;
    display:flex;
    flex-direction:column;
}

/* =========================
   MAIN CONTAINER
========================= */
main {
    flex:1;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:50px 20px;
}

/* =========================
   DASHBOARD CARDS
========================= */
.dashboard-cards {
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap:40px;
    width:100%;
    max-width:900px;
}

.card {
    background: rgba(255,255,255,0.95);
    padding:40px 25px;
    border-radius:20px;
    box-shadow:0 6px 25px rgba(0,0,0,0.1);
    text-align:center;
    transition: transform 0.3s, box-shadow 0.3s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow:0 12px 30px rgba(0,0,0,0.15);
}

.card h3 { font-size:1.5rem; margin-bottom:15px; color:#2f80ed; }
.card i { font-size:3rem; color:#2f80ed; margin-bottom:20px; display:block; }
.card a {
    display:inline-block;
    margin-top:15px;
    padding:12px 20px;
    background:#2f80ed;
    color:#fff;
    border-radius:10px;
    font-weight:600;
    text-decoration:none;
    transition:0.3s;
}
.card a:hover { background:#1c5fc0; transform:scale(1.05); }

/* =========================
   RESPONSIVE
========================= */
@media screen and (max-width:768px){
    header { flex-direction:column; align-items:flex-start; }
    header nav { margin-top:10px; }
    main { padding:30px 10px; }
}
</style>
</head>
<body>

<header>
    <div class="logo-title">
        <img src="../../assets/images/logo.png" alt="Logo" class="logo">
        <h1>Welcome, <?php echo $patient_name; ?> </h1>
    </div>
    <nav>
        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
        <a href="book_appointment.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
        <a href="view_appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a>
        <a href="view_prescription.php"><i class="fas fa-file-medical"></i> Prescriptions</a>
        <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</header>

<main>
<div class="dashboard-cards">
    <div class="card">
        <i class="fas fa-calendar-plus"></i>
        <h3>Book Your Appointment</h3>
        <a href="book_appointment.php"><i class="fas fa-plus-circle"></i> Book Now</a>
    </div>

    <div class="card">
        <i class="fas fa-calendar-check"></i>
        <h3>View Your Appointments</h3>
        <a href="view_appointments.php"><i class="fas fa-eye"></i> View</a>
    </div>
</div>
</main>

</body>
</html>