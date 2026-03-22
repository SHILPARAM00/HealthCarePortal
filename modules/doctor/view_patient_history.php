<?php
session_start();
require_once '../../config/database.php';

/* Check if doctor is logged in */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* Get doctor ID */
$stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor_id = $result->fetch_assoc()['doctor_id'];

/* Fetch past appointments (today and before) */
$query = "
SELECT a.appointment_id, a.patient_id, a.appointment_date, a.time_slot, a.status,
       u.username AS patient_name, p.phone, p.age, p.gender,
       pr.prescription_id
FROM appointments a
JOIN patients p ON a.patient_id = p.patient_id
JOIN users u ON p.user_id = u.user_id
LEFT JOIN prescriptions pr ON a.appointment_id = pr.appointment_id
WHERE a.doctor_id=? AND a.appointment_date <= CURDATE()
ORDER BY a.appointment_date DESC, a.time_slot DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient History - Doctor Dashboard</title>
<link rel="stylesheet" href="../../assets/css/style.css?v=18">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* =========================
   HEADER
========================= */
header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:15px 50px;
    background: linear-gradient(135deg,#2f80ed,#56ccf2);
    color:#fff;
    flex-wrap:wrap;
}
header .logo-title { display:flex; align-items:center; gap:15px; }
header .logo { height:80px; width:80px; border-radius:50%; object-fit:cover; border:2px solid #fff; }
header h1 { font-size:1.8rem; font-weight:700; color:#fff; margin:0; }
header nav a { color:#fff; text-decoration:none; font-weight:600; display:flex; align-items:center; gap:5px; margin-left:20px; }
header nav a:hover { color:#ffeb3b; transform:scale(1.05); }

/* =========================
   MAIN & TABLE
========================= */
main { max-width:1200px; margin:30px auto 50px auto; padding:0 20px; }
table {
    width:100%;
    border-collapse:collapse;
    background:#fff;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 8px 25px rgba(0,0,0,0.1);
}
table th { background:#2f80ed; color:#fff; padding:12px; text-align:center; font-weight:600; }
table td { padding:12px; border-bottom:1px solid #ddd; text-align:center; }
table tr:hover { background:#f0f4ff; transition:0.3s; }

/* =========================
   STATUS COLORS
========================= */
.status-cell { font-weight:bold; padding:5px 10px; border-radius:6px; display:inline-block; min-width:80px; text-align:center; }
.status-pending { background:#f39c12; color:#fff; }
.status-approved { background:#27ae60; color:#fff; }
.status-cancelled { background:#e74c3c; color:#fff; }
.status-completed { background:#2d3436; color:#fff; }

/* =========================
   BUTTONS
========================= */
.btn { padding:6px 12px; border:none; border-radius:6px; cursor:pointer; transition:0.3s; text-decoration:none; color:#fff; }
.btn-view { background:#2f80ed; }
.btn-view:hover { background:#1c5fc0; transform:scale(1.05); }
.btn-download { background:#27ae60; }
.btn-download:hover { background:#1f844c; transform:scale(1.05); }

/* =========================
   RESPONSIVE
========================= */
@media screen and (max-width:768px){
    header { flex-direction:column; align-items:flex-start; }
    table th, table td { padding:8px; font-size:0.9rem; }
}
</style>
</head>
<body>

<header>
    <div class="logo-title">
        <img src="../../assets/images/logo.png" alt="Logo" class="logo">
        <h1>Doctor Dashboard</h1>
    </div>
    <nav>
        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
        <a href="today_appointments.php"><i class="fas fa-calendar-day"></i> Today Appointments</a>
        <a href="upcoming_appointments.php"><i class="fas fa-calendar-alt"></i> Upcoming Appointments</a>
        <a href="all_appointments.php"><i class="fas fa-calendar-check"></i> All Appointments</a>
        <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</header>

<main>
<h2 style="color:#2f80ed; text-align:center; margin-bottom:25px;">Patient History</h2>
<table>
<thead>
<tr>
<th>Patient ID</th>
<th>Name</th>
<th>Phone</th>
<th>Age</th>
<th>Gender</th>
<th>Date</th>
<th>Time Slot</th>
<th>Status</th>
<th>Prescription</th>
</tr>
</thead>
<tbody>
<?php
if ($appointments->num_rows > 0) {
    while($row = $appointments->fetch_assoc()) {
        $statusClass = strtolower($row['status']);
        echo "<tr>";
        echo "<td>{$row['patient_id']}</td>";
        echo "<td>{$row['patient_name']}</td>";
        echo "<td>{$row['phone']}</td>";
        echo "<td>{$row['age']}</td>";
        echo "<td>{$row['gender']}</td>";
        echo "<td>{$row['appointment_date']}</td>";
        echo "<td>{$row['time_slot']}</td>";
        echo "<td class='status-cell status-{$statusClass}'>{$row['status']}</td>";
        echo "<td>";
        if ($row['prescription_id']) {
            echo "<a class='btn-download' href='download_prescription.php?prescription_id={$row['prescription_id']}'><i class='fas fa-download'></i> Download</a>";
        } else {
            echo "<span style='color:#e74c3c;'>Not Added</span>";
        }
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9' style='text-align:center;'>No patient history found</td></tr>";
}
?>
</tbody>
</table>
</main>

</body>
</html>