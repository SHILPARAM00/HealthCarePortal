<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit;
}

$appointment_id = $_GET['appointment_id'] ?? 0;
$patient_id = $_GET['patient_id'] ?? 0;

/* Get doctor_id */
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();
$doctor_id = $doctor['doctor_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Prescription</title>
<link rel="stylesheet" href="../../assets/css/style.css">

<style>

/* Header */
header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:15px 50px;
    background: linear-gradient(135deg,#2f80ed,#56ccf2);
    color:#fff;
}
header .logo-title {
    display:flex;
    align-items:center;
    gap:15px;
}
header .logo {
    height:60px;
    width:60px;
    border-radius:50%;
    border:2px solid #fff;
}
header nav a {
    color:#fff;
    text-decoration:none;
    margin-left:20px;
    font-weight:600;
}
header nav a:hover {
    color:#ffeb3b;
}

/* Form Container */
.container {
    width:600px;
    margin:40px auto;
    background:#fff;
    padding:30px;
    border-radius:12px;
    box-shadow:0 8px 25px rgba(0,0,0,0.1);
}

/* Form */
.container h2 {
    text-align:center;
    margin-bottom:20px;
}

.container input,
.container textarea {
    width:100%;
    padding:10px;
    margin-top:5px;
    margin-bottom:15px;
    border-radius:6px;
    border:1px solid #ccc;
}

.container button {
    width:100%;
    padding:12px;
    background:#2f80ed;
    color:white;
    border:none;
    border-radius:8px;
    font-size:16px;
    cursor:pointer;
}

.container button:hover {
    background:#1366d6;
}

</style>
</head>
<body>

<header>
    <div class="logo-title">
        <img src="../../assets/images/logo.png" class="logo">
        <h2>Healthcare Portal - Doctor</h2>
    </div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="today_appointments.php">Today Appointments</a>
        <a href="all_appointments.php">All Appointments</a>
        <a href="../../logout.php">Logout</a>
    </nav>
</header>

<div class="container">
<h2>Add Prescription</h2>

<form action="save_prescription.php" method="POST">
    <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
    <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">

    Diagnosis:
    <textarea name="diagnosis" required></textarea>

    Medicines:
    <textarea name="medicines" required></textarea>

    Dosage:
    <input type="text" name="dosage" required>

    Instructions:
    <textarea name="instructions"></textarea>

    Advice:
    <textarea name="advice"></textarea>

    Prescription Date:
    <input type="date" name="prescription_date" required>

    <button type="submit">Save Prescription</button>
</form>
</div>

</body>
</html>