<?php
require_once '../config/config.php';
require_once '../config/db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'create') {
    $vehicle_id = $_POST['vehicle_id'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null;

    if (empty($vehicle_id) || empty($full_name) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO enquiries (vehicle_id, user_id, full_name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$vehicle_id, $user_id, $full_name, $email, $phone, $subject, $message]);
        
        echo json_encode(['status' => 'success', 'message' => 'Your enquiry has been submitted successfully! We will get back to you soon.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to submit enquiry. Please try again.']);
    }
}

elseif ($action === 'list' && isLoggedIn() && hasRole(ROLE_ADMIN)) {
    try {
        $stmt = $pdo->query("SELECT e.*, v.brand, v.model FROM enquiries e JOIN vehicles v ON e.vehicle_id = v.id ORDER BY e.created_at DESC");
        $enquiries = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $enquiries]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch enquiries.']);
    }
}

elseif ($action === 'update_status' && isLoggedIn() && hasRole(ROLE_ADMIN)) {
    $id = $_GET['id'] ?? '';
    $status = $_GET['status'] ?? '';

    if (empty($id) || empty($status)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE enquiries SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['status' => 'success', 'message' => 'Enquiry status updated successfully.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update status.']);
    }
}
?>
