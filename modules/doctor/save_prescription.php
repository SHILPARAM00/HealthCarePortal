<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $appointment_id = $_POST['appointment_id'];
    $doctor_id = $_POST['doctor_id'];

    $diagnosis = $_POST['diagnosis'];
    $medicines = $_POST['medicines'];
    $dosage = $_POST['dosage'];
    $instructions = $_POST['instructions'];
    $advice = $_POST['advice'];
    $prescription_date = $_POST['prescription_date'];

    /* Get patient_id from appointments table */
    $stmt = $conn->prepare("SELECT patient_id FROM appointments WHERE appointment_id=?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $patient_id = $row['patient_id'];

    /* Insert prescription */
    $stmt = $conn->prepare("
        INSERT INTO prescriptions
        (appointment_id, patient_id, doctor_id, diagnosis, medicines, dosage, instructions, advice, prescription_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iiissssss",
        $appointment_id,
        $patient_id,
        $doctor_id,
        $diagnosis,
        $medicines,
        $dosage,
        $instructions,
        $advice,
        $prescription_date
    );

    $stmt->execute();

    header("Location: all_appointments.php");
    exit;
}
?>