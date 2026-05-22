<?php
require_once '../config/config.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

if ($action === 'update_profile') {
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $id_passport = $_POST['id_passport_number'] ?? '';
    $emergency_name = $_POST['emergency_contact_name'] ?? '';
    $emergency_phone = $_POST['emergency_contact_phone'] ?? '';

    try {
        $pdo->beginTransaction();

        // Update users table
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$full_name, $phone, $user_id]);

        // Update customers table if role is customer
        if (hasRole(ROLE_CUSTOMER)) {
            $stmt = $pdo->prepare("UPDATE customers SET id_passport_number = ?, emergency_contact_name = ?, emergency_contact_phone = ? WHERE user_id = ?");
            $stmt->execute([$id_passport, $emergency_name, $emergency_phone, $user_id]);
        }

        $pdo->commit();
        $_SESSION['full_name'] = $full_name;
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update profile.']);
    }
}
?>
