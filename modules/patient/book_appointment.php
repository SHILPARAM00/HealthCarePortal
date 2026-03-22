<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

/* Get patient id */
$stmt = $conn->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$patient_id = $patient['patient_id'];

/* Fetch specializations */
$specializations = $conn->query("SELECT DISTINCT specialization FROM doctors");

/* Book appointment */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $time_slot = $_POST['time_slot'];
    $patient_name = $_POST['patient_name'];
    $patient_age = $_POST['patient_age'];
    $patient_gender = $_POST['patient_gender'];
    $patient_phone = $_POST['patient_phone'];
    $relationship = $_POST['relationship'];
    $status = "Pending";
    $today = date("Y-m-d");

    if ($appointment_date < $today) {
        $message = "You cannot book past date.";
    } else {

        $check = $conn->prepare("SELECT appointment_id FROM appointments 
        WHERE doctor_id=? AND appointment_date=? AND time_slot=?");
        $check->bind_param("iss", $doctor_id, $appointment_date, $time_slot);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $message = "Time slot already booked.";
        } else {

            $stmt = $conn->prepare("INSERT INTO appointments
            (patient_id, doctor_id, appointment_date, time_slot, status,
            patient_name, patient_age, patient_gender, patient_phone, relationship)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param(
                "iisssissss",
                $patient_id,
                $doctor_id,
                $appointment_date,
                $time_slot,
                $status,
                $patient_name,
                $patient_age,
                $patient_gender,
                $patient_phone,
                $relationship
            );

            if ($stmt->execute()) {
                $message = "success";
            } else {
                $message = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Appointment</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* ===== HEADER (COPIED EXACT STYLE) ===== */
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

/* ===== BODY ===== */
body {
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background:#f5f7fb;
}

/* ===== FORM SECTION ===== */
main {
    display:flex;
    justify-content:center;
    margin-top:40px;
}

.form-container {
    width:600px;
    background:#fff;
    padding:30px;
    border-radius:15px;
    box-shadow:0 6px 20px rgba(0,0,0,0.08);
}

.form-container h2 {
    text-align:center;
    margin-bottom:20px;
    color:#2f80ed;
}

/* FORM */
.form-row {
    display:flex;
    gap:10px;
}

input, select {
    width:100%;
    padding:12px;
    margin-top:10px;
    border:1px solid #ccc;
    border-radius:8px;
}

/* BUTTON */
button {
    width:100%;
    padding:12px;
    margin-top:20px;
    background:linear-gradient(135deg,#ff6b6b,#f94d6a);
    border:none;
    color:#fff;
    font-weight:bold;
    border-radius:50px;
    cursor:pointer;
}

/* MESSAGE */
.success-msg, .error-msg {
    max-width:600px;
    margin:20px auto;
    padding:10px;
    text-align:center;
    border-radius:8px;
}

.success-msg {
    background:#d4edda;
    color:#155724;
}

.error-msg {
    background:#f8d7da;
    color:#721c24;
}

/* RESPONSIVE */
@media(max-width:600px){
    .form-container { width:90%; }
    .form-row { flex-direction:column; }
}

</style>
</head>

<body>

<header>
    <div class="logo-title">
        <img src="../../assets/images/logo.png" class="logo">
        <h1>Book Appointment</h1>
    </div>

    <nav>
        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
        
        <a href="view_appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a>
        
        <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</header>

<?php
if ($message == "success") {
    echo "<div class='success-msg'>Appointment booked successfully</div>";
}
elseif ($message == "error") {
    echo "<div class='error-msg'>Error booking appointment</div>";
}
elseif ($message != "") {
    echo "<div class='error-msg'>$message</div>";
}
?>

<main>
<div class="form-container">

<h2>Book Appointment</h2>

<form method="POST">

<div class="form-row">
<input type="text" name="patient_name" placeholder="Patient Name" required>
<input type="number" name="patient_age" placeholder="Age" required>
</div>

<div class="form-row">
<select name="patient_gender" required>
<option value="">Gender</option>
<option>Male</option>
<option>Female</option>
<option>Other</option>
</select>

<input type="text" name="patient_phone" placeholder="Phone" required>
</div>

<div class="form-row">
<select name="relationship">
<option>Self</option>
<option>Father</option>
<option>Mother</option>
<option>Child</option>
<option>Spouse</option>
</select>

<input type="date" name="appointment_date" required>
</div>

<select id="specialization">
<option value="">Select Specialization</option>
<?php while ($row = $specializations->fetch_assoc()) {
echo "<option value='".$row['specialization']."'>".$row['specialization']."</option>";
} ?>
</select>

<select name="doctor_id" id="doctor" required>
<option value="">Select Doctor</option>
</select>

<select name="time_slot" required>
<option value="">Select Time Slot</option>
<option>09:00 AM - 09:30 AM</option>
<option>09:30 AM - 10:00 AM</option>
<option>10:00 AM - 10:30 AM</option>
<option>10:30 AM - 11:00 AM</option>
<option>11:00 AM - 11:30 AM</option>
<option>11:30 AM - 12:00 PM</option>
<option>02:00 PM - 02:30 PM</option>
<option>02:30 PM - 03:00 PM</option>
<option>03:00 PM - 03:30 PM</option>
<option>03:30 PM - 04:00 PM</option>
</select>

<button type="submit">Book Appointment</button>

</form>
</div>
</main>

<script>
document.getElementById("specialization").addEventListener("change", function(){

let specialization = this.value;
let doctorSelect = document.getElementById("doctor");

doctorSelect.innerHTML = "<option value=''>Select Doctor</option>";

if(specialization === "") return;

fetch("get_doctors.php?specialization=" + encodeURIComponent(specialization))
.then(res => res.json())
.then(data => {
data.forEach(doc => {
doctorSelect.innerHTML += `<option value="${doc.doctor_id}">Dr. ${doc.username}</option>`;
});
});
});
</script>

</body>
</html>