<?php
require_once '../config/config.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'update_booking') {
    $id = $_GET['id'] ?? '';
    $status = $_GET['status'] ?? '';

    if (empty($id) || empty($status)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Update booking status
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        // Logic for different statuses
        if (in_array($status, ['Approved', 'Ongoing', 'Completed', 'Rejected', 'Cancelled'])) {
            $stmt = $pdo->prepare("SELECT vehicle_id, customer_id, driver_id, booking_reference FROM bookings WHERE id = ?");
            $stmt->execute([$id]);
            $booking = $stmt->fetch();

            if ($status === 'Approved') {
                // Mark vehicle as booked
                $stmt = $pdo->prepare("UPDATE vehicles SET status = 'Booked' WHERE id = ?");
                $stmt->execute([$booking['vehicle_id']]);
                
                // Notify customer
                $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Booking Approved', ?)");
                $stmt->execute([$booking['customer_id'], "Your booking {$booking['booking_reference']} has been approved. Enjoy your ride!"]);
            } 
            elseif ($status === 'Ongoing') {
                // If there's a driver, mark them as on trip
                if ($booking['driver_id']) {
                    $stmt = $pdo->prepare("UPDATE drivers SET status = 'on_trip' WHERE user_id = ?");
                    $stmt->execute([$booking['driver_id']]);
                }
            }
            elseif (in_array($status, ['Completed', 'Rejected', 'Cancelled'])) {
                // Mark vehicle back to Available
                $stmt = $pdo->prepare("UPDATE vehicles SET status = 'Available' WHERE id = ?");
                $stmt->execute([$booking['vehicle_id']]);

                // If there's a driver, mark them as available again
                if ($booking['driver_id']) {
                    $stmt = $pdo->prepare("UPDATE drivers SET status = 'available' WHERE user_id = ?");
                    $stmt->execute([$booking['driver_id']]);
                }

                $notif_title = "Booking " . $status;
                $notif_msg = "Your booking {$booking['booking_reference']} has been {$status}.";
                
                $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
                $stmt->execute([$booking['customer_id'], $notif_title, $notif_msg]);
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => "Booking {$status} successfully."]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update booking.']);
    }
}

elseif ($action === 'update_driver') {
    $id = $_GET['id'] ?? '';
    $status = $_GET['status'] ?? '';

    if (empty($id) || empty($status)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE drivers SET status = ? WHERE user_id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['status' => 'success', 'message' => "Driver status updated to {$status}."]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update driver.']);
    }
}

elseif ($action === 'update_payment') {
    $id = $_GET['id'] ?? '';
    $status = $_GET['status'] ?? '';

    if (empty($id) || empty($status)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE payments SET payment_status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['status' => 'success', 'message' => "Payment marked as {$status}."]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update payment.']);
    }
}

elseif ($action === 'delete_booking') {
    $id = $_GET['id'] ?? '';

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Booking ID is required.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Get booking details first to release resources if needed
        $stmt = $pdo->prepare("SELECT vehicle_id, driver_id, status FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        $booking = $stmt->fetch();

        if ($booking) {
            // If booking was active/approved, release the vehicle and driver
            if (in_array($booking['status'], ['Approved', 'Ongoing'])) {
                $stmt = $pdo->prepare("UPDATE vehicles SET status = 'Available' WHERE id = ?");
                $stmt->execute([$booking['vehicle_id']]);

                if ($booking['driver_id']) {
                    $stmt = $pdo->prepare("UPDATE drivers SET status = 'available' WHERE user_id = ?");
                    $stmt->execute([$booking['driver_id']]);
                }
            }

            // Delete related records (assuming cascading delete is not fully trusted or set up)
            $stmt = $pdo->prepare("DELETE FROM payments WHERE booking_id = ?");
            $stmt->execute([$id]);

            // Delete the booking itself
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$id]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Booking deleted successfully.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete booking: ' . $e->getMessage()]);
    }
}
?>
