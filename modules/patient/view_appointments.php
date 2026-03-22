<?php
session_start();
require_once '../../config/database.php';

/* Check if patient logged in */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* Get patient id */
$stmt = $conn->prepare("SELECT patient_id FROM patients WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$patient_id = $patient['patient_id'];

/* Cancel appointment */
if (isset($_GET['cancel_id'])) {
    $appointment_id = $_GET['cancel_id'];
    $stmt = $conn->prepare("UPDATE appointments SET status='Cancelled' WHERE appointment_id=? AND patient_id=?");
    $stmt->bind_param("ii", $appointment_id, $patient_id);
    $stmt->execute();
    header("Location: view_appointments.php");
    exit;
}

/* Fetch appointments with prescription info */
$query = "
SELECT 
    a.appointment_id,
    a.doctor_id,
    u.username AS doctor_name,
    d.specialization,
    a.appointment_date,
    a.time_slot,
    a.status,
    pr.prescription_id
FROM appointments a
JOIN doctors d ON a.doctor_id = d.doctor_id
JOIN users u ON d.user_id = u.user_id
LEFT JOIN prescriptions pr ON a.appointment_id = pr.appointment_id
WHERE a.patient_id = ?
ORDER BY a.appointment_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Appointments - Healthcare Portal</title>
<link rel="stylesheet" href="../../assets/css/style.css?v=12">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
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
header .logo { height:60px; width:60px; border-radius:50%; object-fit:cover; border:2px solid #fff; }
header h1 { font-size:1.8rem; font-weight:700; display:flex; align-items:center; margin:0; }
header h1 a { color:#fff; text-decoration:none; }
header nav { display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
header nav a { color:#fff; text-decoration:none; font-weight:600; display:flex; align-items:center; transition:0.3s; }
header nav a i { margin-right:6px; }
header nav a:hover { color:#ffeb3b; transform:scale(1.05); }

/* Main container */
main {
    max-width:1200px;
    margin:30px auto;
    padding:0 20px;
}

/* Appointments Table */
table {
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
    background:#fff;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 8px 25px rgba(0,0,0,0.1);
}
table th {
    background:#2f80ed;
    color:white;
    padding:12px;
    text-align:center;
    font-weight:600;
}
table td {
    padding:12px;
    border-bottom:1px solid #ddd;
    text-align:center;
}
table tr:hover { background:#f0f4ff; transition:0.3s; }

/* Status Colors */
.status-cell { font-weight:bold; padding:5px 10px; border-radius:6px; display:inline-block; min-width:80px; text-align:center; }
.status-pending { background:#f39c12; color:#fff; }
.status-approved { background:#27ae60; color:#fff; }
.status-cancelled { background:#e74c3c; color:#fff; }

/* Buttons */
.cancel-btn, .btn-download {
    padding:6px 12px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    transition:0.3s;
}
.cancel-btn{
    background:#e74c3c;
    color:white;
    text-decoration:none;
}
.cancel-btn:hover { background:#c0392b; transform:scale(1.05); }
.btn-download {
    background:#2f80ed;
    color:#fff;
    text-decoration:none;
}
.btn-download:hover { background:#1366d6; transform:scale(1.05); }
.btn-download.disabled {
    background:#aaa;
    cursor:not-allowed;
}

/* Responsive */
@media screen and (max-width:768px){
    header { flex-direction:column; align-items:flex-start; }
    table th, table td { padding:8px; font-size:0.9rem; }
}
</style>

<script>
function confirmCancel(){
    return confirm("Are you sure you want to cancel this appointment?");
}

// Real-time status updates using SSE
if(typeof(EventSource) !== "undefined") {
    var source = new EventSource("fetch_appointment_status.php?patient_id=<?php echo $patient_id; ?>");
    source.onmessage = function(event) {
        var data = JSON.parse(event.data);
        data.forEach(function(update) {
            var row = document.querySelector("tr[data-id='"+update.appointment_id+"']");
            if(row){
                var statusCell = row.querySelector(".status-cell");
                statusCell.textContent = update.status;
                statusCell.className = 'status-cell status-' + update.status.toLowerCase();
            }
        });
    };
}
</script>
</head>
<body>

<header>
    <div class="logo-title">
        <img src="../../assets/images/logo.png" alt="Healthcare Portal Logo" class="logo">
        <h1><a href="../../index.php">Healthcare Portal</a></h1>
    </div>
    <nav>
        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
        <a href="book_appointment.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
        <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</header>

<main>
<table>
<thead>
<tr>
<th>Appointment ID</th>
<th>Doctor</th>
<th>Specialization</th>
<th>Date & Time</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php
if ($appointments->num_rows > 0) {
    while ($row = $appointments->fetch_assoc()) {
        $dateTime = date("d M Y", strtotime($row['appointment_date'])) . " | " . $row['time_slot'];
        $statusClass = strtolower($row['status']);
        echo "<tr data-id='{$row['appointment_id']}'>";
        echo "<td>{$row['appointment_id']}</td>";
        echo "<td>Dr. {$row['doctor_name']}</td>";
        echo "<td>{$row['specialization']}</td>";
        echo "<td>{$dateTime}</td>";
        echo "<td class='status-cell status-{$statusClass}'>{$row['status']}</td>";
        echo "<td>";
        if ($row['status'] == 'Pending' || $row['status'] == 'Approved') {
            echo "<a class='cancel-btn' href='view_appointments.php?cancel_id={$row['appointment_id']}' onclick='return confirmCancel()'>Cancel</a> ";
        }
        if ($row['prescription_id']) {
            echo "<a class='btn-download' href='download_prescription.php?prescription_id={$row['prescription_id']}'>Download</a>";
        } else {
            echo "<button class='btn-download disabled' disabled>Not Ready</button>";
        }
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' style='text-align:center;'>No appointments found</td></tr>";
}
?>
</tbody>
</table>
</main>

</body>
</html>