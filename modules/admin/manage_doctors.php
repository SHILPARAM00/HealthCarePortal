<?php
session_start();
include '../../config/database.php';

// Handle Add Doctor
if (isset($_POST['add_doctor'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $specialization = $_POST['specialization'] ?? '';

    if($name && $email && $phone && $specialization){
        // Add user first
        $stmt = $conn->prepare("INSERT INTO users (username, email, role) VALUES (?, ?, 'doctor')");
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        $stmt->close();

        // Add doctor linked to user_id
        $stmt = $conn->prepare("INSERT INTO doctors (user_id, phone, specialization) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $phone, $specialization);
        $stmt->execute();
        $stmt->close();

        $_SESSION['message'] = "Doctor added successfully!";
        header("Location: manage_doctors.php");
        exit;
    }
}

// Handle Delete Doctor
if (isset($_GET['delete_id'])) {
    $doctor_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM doctors WHERE doctor_id=?");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Doctor deleted successfully!";
    header("Location: manage_doctors.php");
    exit;
}

// Fetch doctors
$doctors = $conn->query("
    SELECT d.doctor_id, d.phone, d.specialization, u.username AS name, u.email
    FROM doctors d
    JOIN users u ON d.user_id = u.user_id
    ORDER BY d.doctor_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Doctors</title>

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
header h1{ color:white; margin:0; text-shadow:1px 1px 2px rgba(0,0,0,0.3); }
header nav a{ color:white; text-decoration:none; margin-left:20px; font-weight:600; }
header nav a:hover{ color:#ffe600; }

/* DASHBOARD HEADER */
.dashboard-header{
text-align:center;
margin:40px 0;
}
.dashboard-header h2{ color:#2f80ed; }
.dashboard-header p{ font-size:16px; color:#555; }

/* FORM */
form.add-form{
margin: 20px auto;
display: flex;
flex-wrap: wrap;
gap: 15px;
justify-content: center;
align-items: center;
max-width: 1000px;
padding: 15px;
background: #f9f9f9;
border-radius: 8px;
box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
form.add-form input{
padding:10px;
width:220px;
border:1px solid #ccc;
border-radius:4px;
}
form.add-form button{
background: #2f80ed;
color:white;
border:none;
padding:10px 20px;
border-radius:4px;
cursor:pointer;
}
form.add-form button:hover{ background:#1c5fc0; }

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
th, td{ padding:12px; text-align:center; border-bottom:1px solid #ddd; }
th{ background:#2f80ed; color:white; font-weight:600; }

/* BUTTONS */
button.delete-btn{ background:#e74c3c; color:white; border:none; border-radius:5px; padding:6px 12px; cursor:pointer; }
button.delete-btn:hover{ background:#c0392b; }

/* MESSAGE */
.message{ color:green; margin-top:15px; text-align:center; font-weight:600; }

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
form.add-form{ flex-direction:column; gap:10px; }
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
<a href="manage_patients.php"><i class="fas fa-user"></i> Patients</a>
<a href="manage_appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a>
<a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>
</header>

<main>
<div class="dashboard-header">
<h2>Manage Doctors</h2>
<p>Add or remove doctors in the system</p>
</div>

<?php if(isset($_SESSION['message'])){ ?>
<p class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
<?php } ?>

<!-- Add Doctor Form -->
<form method="POST" action="manage_doctors.php" class="add-form">
<input type="text" name="name" placeholder="Doctor Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="text" name="phone" placeholder="Phone Number" required>
<input type="text" name="specialization" placeholder="Specialization" required>
<button type="submit" name="add_doctor">Add Doctor</button>
</form>

<!-- Doctors Table -->
<table>
<thead>
<tr>
<th>Doctor ID</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Specialization</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php while($doc = $doctors->fetch_assoc()){ ?>
<tr>
<td data-label="Doctor ID"><?php echo $doc['doctor_id']; ?></td>
<td data-label="Name"><?php echo htmlspecialchars($doc['name'] ?? ''); ?></td>
<td data-label="Email"><?php echo htmlspecialchars($doc['email'] ?? ''); ?></td>
<td data-label="Phone"><?php echo htmlspecialchars($doc['phone'] ?? ''); ?></td>
<td data-label="Specialization"><?php echo htmlspecialchars($doc['specialization'] ?? ''); ?></td>
<td data-label="Actions">
<a href="manage_doctors.php?delete_id=<?php echo $doc['doctor_id']; ?>" onclick="return confirm('Are you sure?')">
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