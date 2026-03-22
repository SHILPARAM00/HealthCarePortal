<?php
session_start();
require_once '../../config/database.php';

/* Check doctor login */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit;
}

/* Check appointment id */
if (isset($_GET['appointment_id'])) {
    $appointment_id = $_GET['appointment_id'];

    /* Delete appointment */
    $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);

    if ($stmt->execute()) {
        header("Location: all_appointments.php?msg=deleted");
        exit;
    } else {
        echo "Error deleting appointment.";
    }
} else {
    echo "Invalid Request.";
}
?>