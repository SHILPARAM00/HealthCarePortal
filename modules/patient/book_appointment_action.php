<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    echo "You must be logged in as patient to book an appointment.";
    exit;
}

$logged_user_id = $_SESSION['user_id'];

// Get POST data
$doctor_id = $_POST['doctor_id'];
$appointment_date = $_POST['appointment_date'];
$time_slot = $_POST['time_slot'];
$patient_name = $_POST['patient_name'];
$patient_age = $_POST['patient_age'];
$patient_gender = $_POST['patient_gender'];
$patient_phone = $_POST['patient_phone'];
$relationship = $_POST['relationship'];
$status = "Pending";
$today = date("Y-m-d");

// Check if appointment date is not in past
if ($appointment_date < $today) {
    echo "You cannot book an appointment for a past date.";
    exit;
}

// Check if time slot is already booked for this doctor
$check = $conn->prepare("SELECT appointment_id FROM appointments WHERE doctor_id=? AND appointment_date=? AND time_slot=?");
$check->bind_param("iss", $doctor_id, $appointment_date, $time_slot);
$check->execute();
$checkResult = $check->get_result();

if($checkResult->num_rows > 0){
    echo "This time slot is already booked.";
    exit;
}

// Determine patient_id
$stmt = $conn->prepare("SELECT patient_id FROM patients WHERE user_id=?");
$stmt->bind_param("i", $logged_user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $row = $result->fetch_assoc();
    $patient_id = $row['patient_id'];
} else {
    // Create patient record if missing
    $stmt2 = $conn->prepare("INSERT INTO patients (user_id) VALUES (?)");
    $stmt2->bind_param("i", $logged_user_id);
    $stmt2->execute();
    $patient_id = $stmt2->insert_id;
}

// Insert appointment
$stmt = $conn->prepare("INSERT INTO appointments 
    (patient_id, doctor_id, appointment_date, time_slot, status, patient_name, patient_age, patient_gender, patient_phone, relationship)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param(
    "iisssissss",
    $patient_id,
    $doctor_id,
    $appointment_date,
    $time_slot,
    $status,
    $patient_name,
    $patient_age,
    $patient_gender,
    $patient_phone,
    $relationship
);

if($stmt->execute()){
    echo "Appointment booked successfully";
} else {
    echo "Error booking appointment.";
}
?>