<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT patient_id FROM patients WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$patient_id = $stmt->get_result()->fetch_assoc()['patient_id'];

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

while (true) {
    $query = "SELECT appointment_id, status FROM appointments WHERE patient_id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while($row = $result->fetch_assoc()){
        $data[] = $row;
    }

    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();

    sleep(1); // check every 1 second
}