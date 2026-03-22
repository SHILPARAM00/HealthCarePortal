<?php
require_once '../../config/database.php';

// Force JSON output
header('Content-Type: application/json');

$doctors = [];

if (isset($_GET['specialization']) && !empty($_GET['specialization'])) {

    $specialization = $_GET['specialization'];

    $stmt = $conn->prepare("
        SELECT d.doctor_id, u.username, d.specialization
        FROM doctors d
        JOIN users u ON d.user_id = u.user_id
        WHERE d.specialization = ?
    ");

    if ($stmt) {
        $stmt->bind_param("s", $specialization);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $doctors[] = $row;
        }
    }
}

// IMPORTANT: no echo, no HTML, only JSON
echo json_encode($doctors);
exit;
?>