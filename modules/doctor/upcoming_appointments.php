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

/* Delete appointment */
if (isset($_GET['delete_id'])) {
    $appointment_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id=? AND doctor_id=?");
    $stmt->bind_param("ii", $appointment_id, $doctor_id);
    $stmt->execute();
    header("Location: upcoming_appointments.php");
    exit;
}

/* Fetch upcoming appointments */
$query = "
SELECT a.appointment_id, a.patient_id, a.appointment_date, a.time_slot, a.status,
       u.username AS patient_name,
       a.patient_phone AS phone,
       a.patient_age AS age,
       a.patient_gender AS gender,
       pr.prescription_id
FROM appointments a
JOIN patients p ON a.patient_id = p.patient_id
JOIN users u ON p.user_id = u.user_id
LEFT JOIN prescriptions pr ON a.appointment_id = pr.appointment_id
WHERE a.doctor_id=? AND a.appointment_date > CURDATE()
ORDER BY a.appointment_date ASC, a.time_slot ASC
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
<title>Upcoming Appointments</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
* { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }

/* Header */
header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:15px 50px;
    background: linear-gradient(135deg,#2f80ed,#56ccf2);
    color:#fff;
}
header .logo-title { display:flex; align-items:center; gap:15px; }
header .logo { height:70px; width:70px; border-radius:50%; }
header nav a { color:#fff; text-decoration:none; font-weight:600; display:flex; align-items:center; gap:5px; margin-left:20px; }
header nav a:hover { color:#ffeb3b; }

/* Table */
main { max-width:1200px; margin:30px auto; }
table {
    width:100%;
    border-collapse:collapse;
    background:#fff;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 8px 25px rgba(0,0,0,0.1);
}
table th {
    background:#2f80ed;
    color:#fff;
    padding:12px;
}
table td {
    padding:12px;
    border-bottom:1px solid #ddd;
    text-align:center;
}
table tr:hover { background:#f0f4ff; }

/* Status */
.status-cell {
    font-weight:bold;
    padding:6px 12px;
    border-radius:20px;
    display:inline-block;
    min-width:100px;
}
.status-pending { background:#f39c12; color:#fff; }
.status-approved { background:#27ae60; color:#fff; }
.status-cancelled { background:#e74c3c; color:#fff; }
.status-completed { background:#2d3436; color:#fff; }

/* Buttons */
.btn {
    padding:6px 12px;
    border-radius:6px;
    text-decoration:none;
    color:#fff;
    font-size:13px;
    margin:2px;
    display:inline-block;
}
.btn-edit { background:#2f80ed; }
.btn-prescription { background:#27ae60; }
.btn-delete { background:#e74c3c; }

.btn:hover { opacity:0.85; }
</style>
</head>
<body>

<header>
    <div class="logo-title">
        <img src="../../assets/images/logo.png" class="logo">
        <h2>Doctor Dashboard</h2>
    </div>
    <nav>
        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
        <a href="today_appointments.php"><i class="fas fa-calendar-day"></i> Today Appointments</a>
        <a href="all_appointments.php"><i class="fas fa-calendar-check"></i> All Appointments</a>
        <a href="view_patient_history.php"><i class="fas fa-history"></i> Patient History</a>
        <a href="../../logout.php">Logout</a>
    </nav>
</header>

<main>
<h2 style="text-align:center; color:#2f80ed; margin-bottom:20px;">
Upcoming Appointments
</h2>

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
<th>Actions</th>
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
        echo "<td>" . date('Y-m-d', strtotime($row['appointment_date'])) . "</td>";
        echo "<td>{$row['time_slot']}</td>";
        echo "<td><span class='status-cell status-{$statusClass}'>{$row['status']}</span></td>";

        echo "<td>";
        echo "<a class='btn btn-edit' href='update_status.php?appointment_id={$row['appointment_id']}'><i class='fas fa-edit'></i> Edit</a>";

        if ($row['status'] === 'Completed' && !$row['prescription_id']) {
            echo "<a class='btn btn-prescription' href='add_prescription.php?patient_id={$row['patient_id']}&appointment_id={$row['appointment_id']}'><i class='fas fa-file-medical'></i> Prescription</a>";
        }

        echo "<a class='btn btn-delete' href='upcoming_appointments.php?delete_id={$row['appointment_id']}' onclick='return confirm(\"Are you sure?\")'><i class='fas fa-trash'></i> Delete</a>";
        echo "</td>";

        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9'>No upcoming appointments</td></tr>";
}
?>

</tbody>
</table>
</main>

</body>
</html>