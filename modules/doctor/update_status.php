<?php
session_start();
require_once '../../config/database.php';

if(isset($_POST['appointment_id']) && isset($_POST['status'])){
    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE appointments SET status=? WHERE appointment_id=?");
    $stmt->bind_param("si", $status, $appointment_id);
    $stmt->execute();
}

header("Location: all_appointments.php");
exit;
?>