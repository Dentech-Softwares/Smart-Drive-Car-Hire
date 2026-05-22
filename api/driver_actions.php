<?php
require_once '../config/config.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole(ROLE_DRIVER)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'update_trip') {
    $id = $_GET['id'] ?? '';
    $status = $_GET['status'] ?? '';

    if (empty($id) || empty($status)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        $stmt = $pdo->prepare("SELECT vehicle_id, driver_id, customer_id, booking_reference FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        $booking = $stmt->fetch();

        if ($status === 'Ongoing') {
            // Mark driver as on trip
            $stmt = $pdo->prepare("UPDATE drivers SET status = 'on_trip' WHERE user_id = ?");
            $stmt->execute([$booking['driver_id']]);
        }
        elseif ($status === 'Completed') {
            // Update vehicle status back to Available
            $stmt = $pdo->prepare("UPDATE vehicles SET status = 'Available' WHERE id = ?");
            $stmt->execute([$booking['vehicle_id']]);

            // Mark driver as available again
            $stmt = $pdo->prepare("UPDATE drivers SET status = 'available' WHERE user_id = ?");
            $stmt->execute([$booking['driver_id']]);

            // Add earnings to driver (e.g., 2000 per day for the trip)
            // For now just add a fixed amount
            $stmt = $pdo->prepare("UPDATE drivers SET earnings = earnings + 2000 WHERE user_id = ?");
            $stmt->execute([$booking['driver_id']]);

            // Notify customer
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Trip Completed', ?)");
            $stmt->execute([$booking['customer_id'], "Your trip {$booking['booking_reference']} has been marked as completed. Thank you for choosing Smart Drive!"]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => "Trip status updated to {$status}."]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update trip.']);
    }
}
?>
