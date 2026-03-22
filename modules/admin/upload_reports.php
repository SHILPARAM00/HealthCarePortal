<?php
session_start();
include '../../config/database.php';

// Allowed file types
$allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];

// Handle file upload
if (isset($_POST['upload'])) {
    $patient_id = $_POST['patient'];
    $report_name = trim($_POST['report_name']);
    $file = $_FILES['report_file'];

    // Validate file
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_types)) {
        $error = "Invalid file type. Only PDF, JPG, PNG allowed.";
    } elseif ($file['error'] !== 0) {
        $error = "File upload error.";
    } elseif (empty($report_name)) {
        $error = "Report name is required.";
    } else {
        $uploadDir = '../../uploads/reports/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filename = time() . '_' . preg_replace("/[^a-zA-Z0-9_\-\.]/", "_", basename($file['name']));
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("INSERT INTO reports (patient_id, report_name, report_file, uploaded_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iss", $patient_id, $report_name, $filename);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Report uploaded successfully!";
            header("Location: upload_reports.php");
            exit;
        } else {
            $error = "Failed to upload file.";
        }
    }
}

// Handle delete report
if (isset($_GET['delete_id'])) {
    $report_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM reports WHERE report_id=?");
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Report deleted successfully!";
    header("Location: upload_reports.php");
    exit;
}

// Fetch patients
$patients = $conn->query("SELECT patient_id, patient_name, patient_phone FROM patients ORDER BY patient_name");

// Fetch reports
$reports = $conn->query("
    SELECT r.report_id, r.report_name, r.report_file, r.uploaded_at, p.patient_name, p.patient_phone
    FROM reports r
    JOIN patients p ON r.patient_id = p.patient_id
    ORDER BY r.uploaded_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload Reports</title>
<link rel="stylesheet" href="../../assets/css/style.css">
<style>
/* Header */
header {
    display:flex; justify-content:space-between; align-items:center;
    background:#2f80ed; padding:15px 25px; color:white; flex-wrap:wrap;
}
header nav a { color:white; text-decoration:none; margin-left:20px; font-weight:600; }
header nav a:hover { color:#ffe600; }
header h1 { color:#ffe600; margin:0; }

/* Messages */
p.message { text-align:center; color:green; margin-top:10px; }
p.error { text-align:center; color:red; margin-top:10px; }

/* Upload form */
form { display:flex; gap:10px; align-items:center; margin-top:20px; flex-wrap:wrap; }
input[type="text"], input[list], input[type="file"] { padding:6px; border-radius:4px; border:1px solid #ccc; width:200px; }
button.upload-btn { background:#2f80ed; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; }
button.upload-btn:hover { background:#1c5fc0; }

/* Table */
table { width:100%; border-collapse: collapse; margin-top:20px; }
table, th, td { border:1px solid #ccc; }
th, td { padding:10px; text-align:left; vertical-align: middle; }
th { background:#2f80ed; color:white; }
td a button { margin:0; }

/* Delete button */
button.delete-btn { background:#e74c3c; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; }
button.delete-btn:hover { background:#c0392b; }
</style>
</head>
<body>

<header>
<h1>Upload Reports</h1>
<nav>
<a href="dashboard.php">Dashboard</a>
<a href="manage_doctors.php">Manage Doctors</a>
<a href="manage_patients.php">Manage Patients</a>
<a href="manage_appointments.php">Manage Appointments</a>
<a href="../../logout.php">Logout</a>
</nav>
</header>

<main>
<?php if(isset($_SESSION['message'])){ ?>
<p class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
<?php } ?>
<?php if(isset($error)){ ?>
<p class="error"><?php echo $error; ?></p>
<?php } ?>

<!-- Upload Form -->
<form method="post" enctype="multipart/form-data">
<input type="text" name="report_name" placeholder="Report Name" required>
<input list="patients" name="patient" placeholder="Type patient ID" required>
<datalist id="patients">
<?php while($p = $patients->fetch_assoc()){ ?>
<option value="<?php echo $p['patient_id']; ?>">
<?php echo htmlspecialchars($p['patient_name']) . ' (' . $p['patient_phone'] . ')'; ?>
</option>
<?php } ?>
</datalist>
<input type="file" name="report_file" required>
<button type="submit" name="upload" class="upload-btn">Upload</button>
</form>

<!-- Uploaded Reports Table -->
<h3>Uploaded Reports</h3>
<table>
<thead>
<tr>
<th>Patient</th>
<th>Report Name</th>
<th>File</th>
<th>Date</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php while($r = $reports->fetch_assoc()){ ?>
<tr>
<td><?php echo htmlspecialchars($r['patient_name']) . ' (' . $r['patient_phone'] . ')'; ?></td>
<td><?php echo htmlspecialchars($r['report_name']); ?></td>
<td><a href="../../uploads/reports/<?php echo $r['report_file']; ?>" target="_blank"><?php echo htmlspecialchars($r['report_file']); ?></a></td>
<td><?php echo htmlspecialchars($r['uploaded_at']); ?></td>
<td>
<a href="upload_reports.php?delete_id=<?php echo $r['report_id']; ?>" onclick="return confirm('Are you sure?')">
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