<?php
require_once '../../config/database.php';

$doctor_id = $_POST['doctor_id'];
$appointment_date = $_POST['date'];

// Define all possible time slots
$all_slots = [
    "09:00 - 09:30",
    "09:30 - 10:00",
    "10:00 - 10:30",
    "10:30 - 11:00",
    "11:00 - 11:30",
    "11:30 - 12:00",
    "12:00 - 12:30",
    "12:30 - 13:00",
    "15:00 - 15:30",
    "15:30 - 16:00",
    "16:00 - 16:30",
    "16:30 - 17:00"
];

// Fetch already booked slots for this doctor & date
$stmt = $conn->prepare("SELECT time_slot FROM appointments WHERE doctor_id = ? AND appointment_date = ?");
$stmt->bind_param("is", $doctor_id, $appointment_date);
$stmt->execute();
$result = $stmt->get_result();

$booked_slots = [];
while($row = $result->fetch_assoc()){
    $booked_slots[] = $row['time_slot'];
}

// Show only free slots
$available_slots = array_diff($all_slots, $booked_slots);

if(count($available_slots) > 0){
    foreach($available_slots as $slot){
        echo '<button type="button" class="time-slot-btn" data-time="'.$slot.'">'.$slot.'</button>';
    }
} else {
    echo '<p>No available time slots for this date.</p>';
}
?>