<?php
session_start();
require_once '../../config/database.php';

/* Only allow patients to access */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    http_response_code(403);
    exit;
}

$user_id = $_SESSION['user_id'];

/* Get patient ID */
$stmt = $conn->prepare("SELECT patient_id FROM patients WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$patient_id = $patient['patient_id'];

/* Set headers for Server-Sent Events */
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

/* Fetch latest status of all appointments */
$stmt = $conn->prepare("
    SELECT appointment_id, status
    FROM appointments
    WHERE patient_id = ?
");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

$updates = [];
while ($row = $result->fetch_assoc()) {
    $updates[] = [
        'appointment_id' => $row['appointment_id'],
        'status' => $row['status']
    ];
}

/* Send data as JSON */
echo "data: " . json_encode($updates) . "\n\n";
flush();
?>