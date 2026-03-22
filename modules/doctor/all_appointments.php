<?php
session_start();
require_once '../../config/database.php';

/* Check doctor login */
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

/* Fetch appointments */
$query = "
SELECT 
    appointment_id,
    patient_id,
    patient_name,
    patient_phone,
    patient_age,
    patient_gender,
    appointment_date,
    time_slot,
    status
FROM appointments
WHERE doctor_id = ?
ORDER BY appointment_date DESC, time_slot ASC
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
<title>All Appointments</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
* { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }

body {
    background: url('../../assets/images/patient_dashboard_hero_bg.png') no-repeat center center;
    background-size: cover;
    min-height:100vh;
    display:flex;
    flex-direction:column;
}

/* HEADER */
header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding: 15px 50px;
    background: linear-gradient(135deg,#2f80ed,#56ccf2);
    color:#fff;
    flex-wrap:wrap;
}
header .logo-title { display:flex; align-items:center; gap:15px; }
header .logo { height:80px; width:80px; border-radius:50%; object-fit:cover; border:2px solid #fff; }
header h1 { font-size:1.8rem; font-weight:700; color:#fff; margin:0; }
header nav a { color:#fff; text-decoration:none; font-weight:600; display:flex; align-items:center; gap:5px; margin-left:20px; }
header nav a:hover { color:#ffeb3b; transform:scale(1.05); }

/* MAIN */
main {
    width:95%;
    margin:30px auto;
    background: rgba(255,255,255,0.95);
    padding:25px;
    border-radius:15px;
}

h2 {
    text-align:center;
    margin-bottom:20px;
    color:#2f80ed;
}

/* TABLE */
table {
    width:100%;
    border-collapse: collapse;
}

th, td {
    padding:12px;
    text-align:center;
    border-bottom:1px solid #ddd;
}

th {
    background:#2f80ed;
    color:white;
}

tr:hover {
    background:#f2f6ff;
}

/* BUTTONS */
.btn {
    padding:6px 12px;
    border:none;
    border-radius:6px;
    color:white;
    cursor:pointer;
    margin:2px;
}

.btn-edit { background:#f39c12; }
.btn-save { background:#27ae60; }
.btn-upload { background:#3498db; }
.delete-btn {
    background-color: #e74c3c;
    color: white;
    padding: 6px 12px;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
}

.delete-btn:hover {
    background-color: #c0392b;
}

select {
    padding:5px;
    border-radius:5px;
}
</style>

<script>
function enableEdit(id){
    document.getElementById('status-'+id).disabled = false;
    document.getElementById('save-'+id).style.display = 'inline-block';
}

function beforeSubmit(id){
    document.getElementById('status-'+id).disabled = false;
    return true;
}

function confirmDelete(){
    return confirm("Delete this appointment?");
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
        <a href="today_appointments.php"><i class="fas fa-calendar-day"></i> Today's Appointments</a>
        <a href="upcoming_appointments.php"><i class="fas fa-calendar-plus"></i> Upcoming Appointments</a>
        <a href="view_patient_history.php"><i class="fas fa-history"></i> Patient History</a>
        <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</header>

<main>
<h2>All Appointments</h2>

<table>
<tr>
<th>Patient ID</th>
<th>Name</th>
<th>Phone</th>
<th>Age</th>
<th>Gender</th>
<th>Date</th>
<th>Time</th>
<th>Status</th>
<th>Actions</th>
</tr>

<?php
if($appointments->num_rows > 0){
    while($row = $appointments->fetch_assoc()){
?>

<tr>
<td><?php echo $row['patient_id']; ?></td>
<td><?php echo $row['patient_name']; ?></td>
<td><?php echo $row['patient_phone']; ?></td>
<td><?php echo $row['patient_age']; ?></td>
<td><?php echo $row['patient_gender']; ?></td>
<td><?php echo date('d-m-Y', strtotime($row['appointment_date'])); ?></td>
<td><?php echo $row['time_slot']; ?></td>

<td>
<form action="update_status.php" method="POST" onsubmit="return beforeSubmit(<?php echo $row['appointment_id']; ?>)">
<input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">

<select name="status" id="status-<?php echo $row['appointment_id']; ?>" disabled>
    <option value="pending" <?php if($row['status']=='pending') echo 'selected'; ?>>Pending</option>
    <option value="approved" <?php if($row['status']=='approved') echo 'selected'; ?>>Approved</option>
    <option value="completed" <?php if($row['status']=='completed') echo 'selected'; ?>>Completed</option>
    <option value="cancelled" <?php if($row['status']=='cancelled') echo 'selected'; ?>>Cancelled</option>
</select>
</td>

<td>
<button type="button" class="btn btn-edit" onclick="enableEdit(<?php echo $row['appointment_id']; ?>)">Edit</button>
<button type="submit" class="btn btn-save" id="save-<?php echo $row['appointment_id']; ?>" style="display:none;">Save</button>
</form>

<a href="add_prescription.php?appointment_id=<?php echo $row['appointment_id']; ?>&patient_id=<?php echo $row['patient_id']; ?>" class="btn btn-upload">Upload</a>
<a class="btn delete-btn" 
   href="delete_appointment.php?appointment_id=<?php echo $row['appointment_id']; ?>"
   onclick="return confirm('Delete this appointment?');">
   Delete
</a>
</td>
</tr>

<?php
    }
} else {
    echo "<tr><td colspan='9'>No Appointments Found</td></tr>";
}
?>

</table>
</main>

</body>
</html>