<?php
require_once '../config/config.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'add') {
    $vehicle_id = $_POST['vehicle_id'] ?? '';
    $service_type = $_POST['service_type'] ?? '';
    $cost = $_POST['cost'] ?? '';
    $service_date = $_POST['service_date'] ?? '';
    $next_service_date = $_POST['next_service_date'] ?? '';

    if (empty($vehicle_id) || empty($service_type) || empty($cost)) {
        echo json_encode(['status' => 'error', 'message' => 'Required fields are missing.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO maintenance (vehicle_id, service_type, cost, service_date, next_service_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$vehicle_id, $service_type, $cost, $service_date, $next_service_date]);
        
        echo json_encode(['status' => 'success', 'message' => 'Maintenance log added successfully.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add log.']);
    }
}
?>
