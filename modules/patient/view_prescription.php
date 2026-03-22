<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../../login.php");
    exit;
}

$appointment_id = $_GET['appointment_id'] ?? 0;

/* Get prescription details */
$query = "
SELECT p.*, a.patient_name, a.appointment_date
FROM prescriptions p
JOIN appointments a ON p.appointment_id = a.appointment_id
WHERE p.appointment_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$prescription = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>View Prescription</title>
</head>
<body>

<h2>Prescription Details</h2>

<?php if($prescription){ ?>

<p><b>Patient Name:</b> <?php echo $prescription['patient_name']; ?></p>
<p><b>Appointment Date:</b> <?php echo $prescription['appointment_date']; ?></p>
<p><b>Diagnosis:</b> <?php echo $prescription['diagnosis']; ?></p>
<p><b>Medicines:</b> <?php echo $prescription['medicines']; ?></p>
<p><b>Dosage:</b> <?php echo $prescription['dosage']; ?></p>
<p><b>Instructions:</b> <?php echo $prescription['instructions']; ?></p>
<p><b>Advice:</b> <?php echo $prescription['advice']; ?></p>
<p><b>Prescription Date:</b> <?php echo $prescription['prescription_date']; ?></p>

<?php } else { ?>
<p>No Prescription Available</p>
<?php } ?>

</body>
</html>