<?php
session_start();
include '../../config/database.php';

// Handle Delete Patient
if (isset($_GET['delete_id'])) {
    $patient_id = $_GET['delete_id'];
    
    $stmt = $conn->prepare("DELETE FROM patients WHERE patient_id=?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Patient deleted successfully!";
    header("Location: manage_patients.php");
    exit;
}

// Handle Report Upload
if (isset($_POST['upload_report'])) {
    $patient_id = $_POST['patient_id'];
    $report_name = $_POST['report_name'];
    $file = $_FILES['report_file'];

    if ($file['error'] === 0) {
        $uploadDir = '../../uploads/reports/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filename = time() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("INSERT INTO reports (patient_id, report_name, report_file, uploaded_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iss", $patient_id, $report_name, $filename);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Report uploaded successfully!";
            header("Location: manage_patients.php");
            exit;
        } else {
            $error = "Failed to upload file.";
        }
    } else {
        $error = "File upload error.";
    }
}

// Fetch patients with full info: phone, gender, address
$patients = $conn->query("
    SELECT p.patient_id, p.age, p.gender, p.address, p.status, u.username AS name, u.email, u.phone
    FROM patients p
    JOIN users u ON p.user_id = u.user_id
    ORDER BY p.patient_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Patients</title>
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
header h1{ color:white; margin:0; text-shadow:1px 1px 2px rgba(0,0,0,0.3);}
header nav a{ color:white; text-decoration:none; margin-left:20px; font-weight:600;}
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
text-align:center;
border-bottom:1px solid #ddd;
}
th{ background:#2f80ed; color:white; font-weight:600; }

/* BUTTONS */
button.delete-btn{ background:#e74c3c; color:white; border:none; border-radius:5px; padding:6px 12px; cursor:pointer; }
button.delete-btn:hover{ background:#c0392b; }
button.add-report-btn{ background:#2ecc71; color:white; border:none; border-radius:5px; padding:6px 12px; cursor:pointer; margin-left:5px; }
button.add-report-btn:hover{ background:#27ae60; }

/* INLINE REPORT FORM */
.report-form{
display:none;
margin-top:10px;
border:1px solid #ccc;
padding:10px;
border-radius:5px;
background:#f9f9f9;
text-align:left;
}
.report-form input[type="text"], .report-form input[type="file"]{
margin-right:10px;
padding:5px;
border-radius:4px;
border:1px solid #ccc;
}
.report-form button{
padding:5px 10px;
border-radius:4px;
cursor:pointer;
}

/* MESSAGE */
.message{ color: green; margin-top: 10px; text-align: center; font-weight:600; }
.error{ color: red; margin-top: 10px; text-align: center; font-weight:600; }

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
.report-form{ text-align:left; margin-top:10px; }
}
</style>
</head>
<body>

<header>
<div style="display:flex;align-items:center;">
<img src="../../assets/images/logo.png" class="logo" style="width:80px;height:80px;border-radius:50%;margin-right:15px;">
<h1>Admin Dashboard</h1>
</div>
<nav>
<a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
<a href="manage_doctors.php"><i class="fas fa-user-md"></i> Doctors</a>
<a href="manage_appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a>
<a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>
</header>

<main>
<div class="dashboard-header">
<h2>Manage Patients</h2>
<p>View, delete, or upload reports for patients</p>
</div>

<?php if(isset($_SESSION['message'])){ ?>
<p class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
<?php } ?>
<?php if(isset($error)){ ?>
<p class="error"><?php echo $error; ?></p>
<?php } ?>

<!-- Patients Table -->
<table>
<thead>
<tr>
<th>Patient ID</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Age</th>
<th>Gender</th>
<th>Address</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php while($pat = $patients->fetch_assoc()){ ?>
<tr>
<td data-label="Patient ID"><?php echo $pat['patient_id']; ?></td>
<td data-label="Name"><?php echo htmlspecialchars($pat['name'] ?? ''); ?></td>
<td data-label="Email"><?php echo htmlspecialchars($pat['email'] ?? ''); ?></td>
<td data-label="Phone"><?php echo htmlspecialchars($pat['phone'] ?? ''); ?></td>
<td data-label="Age"><?php echo htmlspecialchars($pat['age'] ?? ''); ?></td>
<td data-label="Gender"><?php echo htmlspecialchars($pat['gender'] ?? ''); ?></td>
<td data-label="Address"><?php echo htmlspecialchars($pat['address'] ?? ''); ?></td>
<td data-label="Status"><?php echo htmlspecialchars($pat['status'] ?? ''); ?></td>
<td data-label="Actions">
<a href="manage_patients.php?delete_id=<?php echo $pat['patient_id']; ?>" onclick="return confirm('Are you sure?')">
<button type="button" class="delete-btn">Delete</button>
</a>

<button type="button" class="add-report-btn" onclick="toggleReportForm(<?php echo $pat['patient_id']; ?>)">Add Report</button>

<div class="report-form" id="reportForm<?php echo $pat['patient_id']; ?>">
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="patient_id" value="<?php echo $pat['patient_id']; ?>">
<input type="text" name="report_name" placeholder="Report Name" required>
<input type="file" name="report_file" required>
<button type="submit" name="upload_report">Upload</button>
<button type="button" onclick="toggleReportForm(<?php echo $pat['patient_id']; ?>)">Cancel</button>
</form>
</div>

</td>
</tr>
<?php } ?>
</tbody>
</table>

<script>
function toggleReportForm(id) {
    const form = document.getElementById('reportForm' + id);
    form.style.display = (form.style.display === 'block') ? 'none' : 'block';
}
</script>

</main>
</body>
</html>