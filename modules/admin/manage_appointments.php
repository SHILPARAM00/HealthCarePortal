<?php
session_start();
include '../../config/database.php';

// Admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Handle Status Update
if (isset($_POST['update_status'])) {
    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE appointments SET status=? WHERE appointment_id=?");
    $stmt->bind_param("si", $status, $appointment_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Appointment updated successfully!";
    header("Location: manage_appointments.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $appointment_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id=?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Appointment deleted successfully!";
    header("Location: manage_appointments.php");
    exit;
}

// Fetch appointments
$appointments = $conn->query("
    SELECT a.appointment_id, a.appointment_date, a.time_slot, a.status,
           a.patient_name, a.patient_age, a.patient_gender, a.patient_phone, a.relationship
    FROM appointments a
    ORDER BY a.appointment_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Appointments</title>

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
header h1 { color:white; margin:0; text-shadow:1px 1px 2px rgba(0,0,0,0.3); }
header nav a{ color:white; text-decoration:none; margin-left:20px; font-weight:600; }
header nav a:hover{ color:#ffe600; }

/* DASHBOARD HEADER */
.dashboard-header{
text-align:center;
margin:40px 0;
}
.dashboard-header h2{ color:#2f80ed; }
.dashboard-header p{ font-size:16px; color:#555; }

/* TABLE */
table{
width:100%;
border-collapse:collapse;
margin-top:20px;
background:white;
box-shadow:0 5px 15px rgba(0,0,0,0.1);
border-radius:8px;
overflow:hidden;
}
th, td{
padding:12px;
border-bottom:1px solid #ddd;
text-align:center;
}
th{
background:#2f80ed;
color:white;
font-weight:600;
}

/* STATUS */
.status-Pending{ color:orange; font-weight:bold; }
.status-Approved{ color:green; font-weight:bold; }
.status-Cancelled{ color:red; font-weight:bold; }

/* ACTIONS */
button{
padding:6px 12px;
border-radius:5px;
cursor:pointer;
border:none;
color:white;
margin:2px;
}
button.update-btn{ background:#2f80ed; }
button.update-btn:hover{ background:#1c5fc0; }
button.delete-btn{ background:#e74c3c; }
button.delete-btn:hover{ background:#c0392b; }
select{
padding:6px;
border-radius:4px;
border:1px solid #ccc;
}

/* MESSAGE */
.message{
color:green;
margin-top:15px;
text-align:center;
font-weight:600;
}

/* FORM */
form.inline-form{ display:flex; gap:5px; justify-content:center; align-items:center; }

/* RESPONSIVE */
@media(max-width:768px){
table, thead, tbody, th, td, tr{ display:block; }
th{ text-align:left; }
td{ text-align:left; padding-left:50%; position:relative; }
td:before{
position:absolute;
left:10px;
width:45%;
white-space:nowrap;
font-weight:bold;
content:attr(data-label);
}
form.inline-form{ flex-direction:column; }
}
</style>
</head>

<body>

<header>
<div style="display:flex;align-items:center;">
<img src="../../assets/images/logo.png" class="logo" style="width:80px; height:80px; border-radius:50%; margin-right:15px;">
<h1>Admin Dashboard</h1>
</div>
<nav>
<a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
<a href="manage_doctors.php"><i class="fas fa-user-md"></i> Doctors</a>
<a href="manage_patients.php"><i class="fas fa-user"></i> Patients</a>
<a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>
</header>

<main>
<div class="dashboard-header">
<h2>Manage Appointments</h2>
<p>View, update, or delete appointments</p>
</div>

<?php if(isset($_SESSION['message'])){ ?>
<p class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
<?php } ?>

<table>
<thead>
<tr>
<th>Patient</th>
<th>Age</th>
<th>Gender</th>
<th>Phone</th>
<th>Relationship</th>
<th>Date</th>
<th>Time</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php while($app = $appointments->fetch_assoc()){ ?>
<tr>
<td data-label="Patient"><?php echo htmlspecialchars($app['patient_name'] ?? ''); ?></td>
<td data-label="Age"><?php echo htmlspecialchars($app['patient_age'] ?? ''); ?></td>
<td data-label="Gender"><?php echo htmlspecialchars($app['patient_gender'] ?? ''); ?></td>
<td data-label="Phone"><?php echo htmlspecialchars($app['patient_phone'] ?? ''); ?></td>
<td data-label="Relationship"><?php echo htmlspecialchars($app['relationship'] ?? ''); ?></td>
<td data-label="Date"><?php echo htmlspecialchars($app['appointment_date'] ?? ''); ?></td>
<td data-label="Time"><?php echo htmlspecialchars($app['time_slot'] ?? ''); ?></td>
<td data-label="Status">
<form method="POST" action="manage_appointments.php" class="inline-form">
<input type="hidden" name="appointment_id" value="<?php echo $app['appointment_id']; ?>">
<select name="status">
<option value="Pending" <?php if($app['status']=='Pending') echo 'selected'; ?>>Pending</option>
<option value="Approved" <?php if($app['status']=='Approved') echo 'selected'; ?>>Approved</option>
<option value="Cancelled" <?php if($app['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
</select>
<button type="submit" name="update_status" class="update-btn">Update</button>
</form>
</td>
<td data-label="Actions">
<a href="manage_appointments.php?delete_id=<?php echo $app['appointment_id']; ?>" onclick="return confirm('Are you sure?')">
<button type="button" class="delete-btn">Delete</button>
</a>
</td>
</tr>
<?php } ?>
</tbody>
</table>

</main>
</body>
</html>