<?php
require_once '../config/config.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to book a vehicle.']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'create') {
    $vehicle_id = $_POST['vehicle_id'] ?? '';
    $pickup_date = $_POST['pickup_date'] ?? '';
    $return_date = $_POST['return_date'] ?? '';
    $rental_mode = $_POST['rental_mode'] ?? 'Self Drive';
    $payment_method = $_POST['payment_method'] ?? 'M-Pesa';
    $customer_id = $_SESSION['user_id'];

    if (empty($vehicle_id) || empty($pickup_date) || empty($return_date)) {
        echo json_encode(['status' => 'error', 'message' => 'Required fields are missing.']);
        exit;
    }

    // Basic validation
    if (strtotime($return_date) <= strtotime($pickup_date)) {
        echo json_encode(['status' => 'error', 'message' => 'Return date must be after pickup date.']);
        exit;
    }

    // Check for overlapping bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings 
                          WHERE vehicle_id = ? 
                          AND status NOT IN ('Cancelled', 'Rejected') 
                          AND (
                              (pickup_date <= ? AND return_date >= ?) OR
                              (pickup_date <= ? AND return_date >= ?) OR
                              (pickup_date >= ? AND return_date <= ?)
                          )");
    $stmt->execute([$vehicle_id, $pickup_date, $pickup_date, $return_date, $return_date, $pickup_date, $return_date]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'This vehicle is already booked for the selected dates. Please check the availability schedule below.']);
        exit;
    }

    // Fetch vehicle details
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->execute([$vehicle_id]);
    $vehicle = $stmt->fetch();

    if (!$vehicle) {
        echo json_encode(['status' => 'error', 'message' => 'Vehicle not found.']);
        exit;
    }

    // Calculate price
    $diff = strtotime($return_date) - strtotime($pickup_date);
    $days = ceil($diff / (60 * 60 * 24));
    $base_price = $days * $vehicle['price_per_day'];
    $deposit = 0; // Removed security deposit as per user request
    $driver_fee = ($rental_mode === 'With Driver') ? ($days * 1000) : 0;
    $total_price = $base_price + $deposit + $driver_fee;

    // Generate reference
    $booking_ref = 'BK-' . strtoupper(substr(uniqid(), -8));

    // Handle document uploads
    $id_doc = '';
    $license_doc = '';
    if ($rental_mode === 'Self Drive') {
        $upload_dir = '../assets/uploads/bookings/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        if (isset($_FILES['id_document']) && $_FILES['id_document']['error'] === 0) {
            $id_doc = time() . '_id_' . $_FILES['id_document']['name'];
            move_uploaded_file($_FILES['id_document']['tmp_name'], $upload_dir . $id_doc);
        }
        if (isset($_FILES['license_document']) && $_FILES['license_document']['error'] === 0) {
            $license_doc = time() . '_lic_' . $_FILES['license_document']['name'];
            move_uploaded_file($_FILES['license_document']['tmp_name'], $upload_dir . $license_doc);
        }
    }

    try {
        $pdo->beginTransaction();

        // Assign driver if needed
        $driver_id = null;
        if ($rental_mode === 'With Driver') {
            $stmt = $pdo->query("SELECT user_id FROM drivers WHERE status = 'available' LIMIT 1");
            $driver = $stmt->fetch();
            if ($driver) {
                $driver_id = $driver['user_id'];
            }
        }

        // Insert booking
        $stmt = $pdo->prepare("INSERT INTO bookings (booking_reference, customer_id, vehicle_id, driver_id, pickup_date, return_date, rental_mode, total_price, security_deposit, status, id_document, license_document) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?)");
        $stmt->execute([$booking_ref, $customer_id, $vehicle_id, $driver_id, $pickup_date, $return_date, $rental_mode, $total_price, $deposit, $id_doc, $license_doc]);
        $booking_id = $pdo->lastInsertId();

        // Create initial payment record
        $stmt = $pdo->prepare("INSERT INTO payments (booking_id, payment_method, amount, payment_status) VALUES (?, ?, ?, 'Pending')");
        $stmt->execute([$booking_id, $payment_method, $total_price]);

        // Update vehicle status (optional, maybe wait for approval)
        // $stmt = $pdo->prepare("UPDATE vehicles SET status = 'Reserved' WHERE id = ?");
        // $stmt->execute([$vehicle_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Booking submitted successfully! Our team will verify and approve shortly.', 'booking_id' => $booking_id]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Booking failed: ' . $e->getMessage()]);
    }
}

elseif ($action === 'update') {
    $booking_id = $_POST['booking_id'] ?? '';
    $pickup_date = $_POST['pickup_date'] ?? '';
    $return_date = $_POST['return_date'] ?? '';
    $rental_mode = $_POST['rental_mode'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($booking_id) || empty($pickup_date) || empty($return_date)) {
        echo json_encode(['status' => 'error', 'message' => 'Required fields are missing.']);
        exit;
    }

    // Check if booking belongs to user and is Pending
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND customer_id = ? AND status = 'Pending'");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        echo json_encode(['status' => 'error', 'message' => 'Booking not found, already approved, or access denied.']);
        exit;
    }

    // Basic validation
    if (strtotime($return_date) <= strtotime($pickup_date)) {
        echo json_encode(['status' => 'error', 'message' => 'Return date must be after pickup date.']);
        exit;
    }

    // Check for overlapping bookings (excluding this one)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings 
                          WHERE vehicle_id = ? 
                          AND id != ?
                          AND status NOT IN ('Cancelled', 'Rejected') 
                          AND (
                              (pickup_date <= ? AND return_date >= ?) OR
                              (pickup_date <= ? AND return_date >= ?) OR
                              (pickup_date >= ? AND return_date <= ?)
                          )");
    $stmt->execute([$booking['vehicle_id'], $booking_id, $pickup_date, $pickup_date, $return_date, $return_date, $pickup_date, $return_date]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'This vehicle is already booked for the selected dates.']);
        exit;
    }

    // Fetch vehicle details for price calculation
    $stmt = $pdo->prepare("SELECT price_per_day FROM vehicles WHERE id = ?");
    $stmt->execute([$booking['vehicle_id']]);
    $price_per_day = $stmt->fetchColumn();

    // Calculate price
    $diff = strtotime($return_date) - strtotime($pickup_date);
    $days = ceil($diff / (60 * 60 * 24));
    $base_price = $days * $price_per_day;
    $driver_fee = ($rental_mode === 'With Driver') ? ($days * 1000) : 0;
    $total_price = $base_price + $driver_fee;

    try {
        $pdo->beginTransaction();

        // Update booking
        $stmt = $pdo->prepare("UPDATE bookings SET pickup_date = ?, return_date = ?, rental_mode = ?, total_price = ? WHERE id = ?");
        $stmt->execute([$pickup_date, $return_date, $rental_mode, $total_price, $booking_id]);

        // Update payment amount
        $stmt = $pdo->prepare("UPDATE payments SET amount = ?, payment_method = ? WHERE booking_id = ?");
        $stmt->execute([$total_price, $payment_method, $booking_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Booking updated successfully!']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()]);
    }
}

elseif ($action === 'cancel') {
    $booking_id = $_GET['id'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($booking_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Booking ID is required.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Check if booking belongs to user and is in a cancellable state (Pending or Approved)
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND customer_id = ?");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            echo json_encode(['status' => 'error', 'message' => 'Booking not found or access denied.']);
            exit;
        }

        if (in_array($booking['status'], ['Completed', 'Cancelled', 'Rejected'])) {
            echo json_encode(['status' => 'error', 'message' => 'This booking cannot be cancelled as it is already ' . $booking['status'] . '.']);
            exit;
        }

        // Update booking status
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ?");
        $stmt->execute([$booking_id]);

        // Release vehicle
        $stmt = $pdo->prepare("UPDATE vehicles SET status = 'Available' WHERE id = ?");
        $stmt->execute([$booking['vehicle_id']]);

        // If driver was assigned, release them
        if ($booking['driver_id']) {
            $stmt = $pdo->prepare("UPDATE drivers SET status = 'available' WHERE user_id = ?");
            $stmt->execute([$booking['driver_id']]);
        }

        // Notify admins? (Optional but good)
        // For now just notify customer
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Booking Cancelled', ?)");
        $stmt->execute([$user_id, "You have successfully cancelled your booking {$booking['booking_reference']}."]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Booking cancelled successfully. Vehicle is now available for others.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Cancellation failed: ' . $e->getMessage()]);
    }
}

elseif ($action === 'get_booked_dates') {
    $vehicle_id = $_GET['vehicle_id'] ?? '';
    if (!$vehicle_id) {
        echo json_encode([]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT pickup_date, return_date FROM bookings WHERE vehicle_id = ? AND status NOT IN ('Cancelled', 'Rejected') AND return_date >= CURRENT_DATE");
    $stmt->execute([$vehicle_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($bookings);
    exit;
}

elseif ($action === 'delete') {
    $booking_id = $_GET['id'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($booking_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Booking ID is required.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Check if booking belongs to user and is in a deletable state
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND customer_id = ?");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            echo json_encode(['status' => 'error', 'message' => 'Booking not found or access denied.']);
            exit;
        }

        // Only allow deleting Cancelled, Rejected, or Pending (if user changed their mind)
        // If Approved or Ongoing, they should cancel first
        if (in_array($booking['status'], ['Approved', 'Ongoing', 'Completed'])) {
            echo json_encode(['status' => 'error', 'message' => 'Active or completed bookings cannot be deleted. Please cancel it first if it is still ongoing.']);
            exit;
        }

        // Delete related payments first
        $stmt = $pdo->prepare("DELETE FROM payments WHERE booking_id = ?");
        $stmt->execute([$booking_id]);

        // Delete the booking
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Booking record removed from your history.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Deletion failed: ' . $e->getMessage()]);
    }
}
?>
