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
$doctor_id = $stmt->get_result()->fetch_assoc()['doctor_id'];

/* Fetch today's appointments */
$query = "
SELECT 
    a.appointment_id,
    a.patient_id,
    u.username AS patient_name,
    p.phone,
    p.age,
    p.gender,
    a.appointment_date,
    a.time_slot,
    a.status,
    pr.prescription_id
FROM appointments a
JOIN patients p ON a.patient_id = p.patient_id
JOIN users u ON p.user_id = u.user_id
LEFT JOIN prescriptions pr ON a.appointment_id = pr.appointment_id
WHERE a.doctor_id = ? AND a.appointment_date = CURDATE()
ORDER BY a.time_slot ASC
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
<title>Today's Appointments - Doctor Dashboard</title>
<link rel="stylesheet" href="../../assets/css/style.css?v=17">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
    font-family: Arial, sans-serif;
    background: url('../../assets/images/patient_dashboard_hero_bg.png') no-repeat center center/cover;
    min-height:100vh;
}

/* Header */
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

/* Main Table */
main { max-width:1400px; margin:30px auto; padding:20px; background: rgba(255,255,255,0.95); border-radius:15px; }
h2 { color:#2f80ed; margin-bottom:20px; text-align:center; }

table { width:100%; border-collapse: collapse; margin-top:10px; }
th, td { padding:12px; text-align:center; border-bottom:1px solid #ddd; }
th { background:#2f80ed; color:#fff; }
tr:hover { background:#f0f4ff; }

/* Status Dropdown */
.status-cell {
    padding:5px 10px;
    border-radius:6px;
    min-width:90px;
}
.status-pending { background:#f39c12; color:#fff; }
.status-approved { background:#27ae60; color:#fff; }
.status-cancelled { background:#e74c3c; color:#fff; }
.status-completed { background:#3498db; color:#fff; }

/* Buttons */
.btn { padding:6px 10px; border:none; border-radius:6px; cursor:pointer; color:#fff; text-decoration:none; margin:2px; transition:0.3s; }
.btn-edit { background:#f39c12; }
.btn-edit:hover { background:#d35400; }
.btn-update { background:#27ae60; }
.btn-update:hover { background:#1f844c; }
.btn-upload { background:#3498db; }
.btn-upload:hover { background:#1f5fa8; }
.btn-delete { background:#e74c3c; }
.btn-delete:hover { background:#c0392b; }

/* Responsive */
@media screen and (max-width:1024px){
    table th, table td { font-size:0.85rem; padding:6px; }
    header { flex-direction:column; align-items:flex-start; }
    header nav { margin-top:10px; }
}
</style>
<script>
function confirmDelete() {
    return confirm("Are you sure you want to delete this appointment?");
}
</script>
</head>
<body>

<header>
    <div class="logo-title">
        <img src="../../assets/images/logo.png" alt="Logo" class="logo">
        <h1>Doctor Dashboard</h1>
    </div>
    <nav>
        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
        <a href="upcoming_appointments.php"><i class="fas fa-calendar-alt"></i> Upcoming Appointments</a>
        <a href="all_appointments.php"><i class="fas fa-calendar-check"></i> All Appointments</a>
        <a href="view_patient_history.php"><i class="fas fa-history"></i> Patient History</a>
        <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</header>

<main>
<h2>Today's Appointments</h2>
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
if($appointments->num_rows > 0){
    while($row = $appointments->fetch_assoc()){
        $statusDisabled = $row['prescription_id'] ? "disabled" : "";
        echo "<tr>";
        echo "<td>{$row['patient_id']}</td>";
        echo "<td>{$row['patient_name']}</td>";
        echo "<td>{$row['phone']}</td>";
        echo "<td>{$row['age']}</td>";
        echo "<td>{$row['gender']}</td>";
        echo "<td>{$row['appointment_date']}</td>";
        echo "<td>{$row['time_slot']}</td>";
        // Status form
        echo "<td>
        <form method='POST' action='update_status.php'>
            <input type='hidden' name='appointment_id' value='{$row['appointment_id']}'>
            <select name='status' class='status-cell status-".strtolower($row['status'])."'>
                <option value='Pending' ".($row['status']=='Pending'?'selected':'').">Pending</option>
                <option value='Approved' ".($row['status']=='Approved'?'selected':'').">Approved</option>
                <option value='Cancelled' ".($row['status']=='Cancelled'?'selected':'').">Cancelled</option>
                <option value='Completed' ".($row['status']=='Completed'?'selected':'').">Completed</option>
            </select>
            <button type='submit' class='btn btn-update'>Update</button>
        </form>
        </td>";
        // Actions
        $uploadDisabled = ($row['status']!='Completed') ? "disabled" : "";
        echo "<td>
        <a class='btn btn-upload' href='add_prescription.php?appointment_id={$row['appointment_id']}' $uploadDisabled>Upload Prescription</a>
        <a class='btn btn-delete' href='delete_appointment.php?appointment_id={$row['appointment_id']}' onclick='return confirmDelete()'>Delete</a>
        </td>";
        echo "</tr>";
    }
}else{
    echo "<tr><td colspan='9' style='text-align:center;'>No appointments today</td></tr>";
}
?>
</tbody>
</table>
</main>
</body>
</html>