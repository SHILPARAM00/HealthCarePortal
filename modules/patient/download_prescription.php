<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../../login.php");
    exit;
}

if (!isset($_GET['prescription_id'])) {
    echo "Invalid Prescription";
    exit;
}

$prescription_id = $_GET['prescription_id'];

/* Fetch Prescription Details */
$stmt = $conn->prepare("
SELECT 
    pr.prescription_id,
    pr.patient_id,
    pr.doctor_id,
    pr.diagnosis,
    pr.medicines,
    pr.dosage,
    pr.instructions,
    pr.advice,
    pr.prescription_date,
    a.appointment_date,
    u1.username AS patient_name,
    u2.username AS doctor_name
FROM prescriptions pr
JOIN appointments a ON pr.appointment_id = a.appointment_id
JOIN patients p ON pr.patient_id = p.patient_id
JOIN users u1 ON p.user_id = u1.user_id
JOIN doctors d ON pr.doctor_id = d.doctor_id
JOIN users u2 ON d.user_id = u2.user_id
WHERE pr.prescription_id = ?
");

$stmt->bind_param("i", $prescription_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

/* Format Dates (Remove 00:00:00) */
$appointment_date = date("d-m-Y", strtotime($data['appointment_date']));
$prescription_date = date("d-m-Y", strtotime($data['prescription_date']));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Download Prescription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
        }
        .container {
            max-width: 700px;
            margin: auto;
            border: 1px solid #ccc;
            padding: 30px;
        }
        h2 {
            text-align: center;
        }
        hr {
            margin: 20px 0;
        }
        p {
            font-size: 16px;
        }
        .print-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background: #2f80ed;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .print-btn:hover {
            background: #1c5fc0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Prescription</h2>
    <hr>

    <p><strong>Prescription ID:</strong> <?php echo $data['prescription_id']; ?></p>
    <p><strong>Patient Name:</strong> <?php echo $data['patient_name']; ?></p>
    <p><strong>Patient ID:</strong> <?php echo $data['patient_id']; ?></p>
    <p><strong>Doctor Name:</strong> Dr. <?php echo $data['doctor_name']; ?></p>
    <p><strong>Doctor ID:</strong> <?php echo $data['doctor_id']; ?></p>
    <p><strong>Appointment Date:</strong> <?php echo $appointment_date; ?></p>
    <p><strong>Prescription Date:</strong> <?php echo $prescription_date; ?></p>

    <hr>

    <p><strong>Diagnosis:</strong><br>
    <?php echo $data['diagnosis']; ?></p>

    <p><strong>Medicines:</strong><br>
    <?php echo $data['medicines']; ?></p>

    <p><strong>Dosage:</strong><br>
    <?php echo $data['dosage']; ?></p>

    <p><strong>Instructions:</strong><br>
    <?php echo $data['instructions']; ?></p>

    <p><strong>Advice:</strong><br>
    <?php echo $data['advice']; ?></p>

    <button class="print-btn" onclick="window.print()">Print / Save PDF</button>
</div>

</body>
</html>