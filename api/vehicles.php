<?php
// Force full error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set error handler to catch fatal errors and return JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        ob_clean();
        echo json_encode([
            'status' => 'error', 
            'message' => 'PHP Fatal Error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']
        ]);
    }
});

require_once '../config/config.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'add') {
    $brand = isset($_POST['brand']) ? trim($_POST['brand']) : '';
    $model = isset($_POST['model']) ? trim($_POST['model']) : '';
    $plate_number = isset($_POST['plate_number']) ? trim($_POST['plate_number']) : '';
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    $transmission = isset($_POST['transmission']) ? $_POST['transmission'] : '';
    $fuel_type = isset($_POST['fuel_type']) ? $_POST['fuel_type'] : '';
    $seating_capacity = isset($_POST['seating_capacity']) ? (int)$_POST['seating_capacity'] : 0;
    $price_per_day = isset($_POST['price_per_day']) ? (float)$_POST['price_per_day'] : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if (empty($brand) || empty($model) || empty($plate_number) || $price_per_day <= 0 || $seating_capacity <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields correctly.']);
        exit;
    }

    $upload_dir = '../assets/uploads/vehicles/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $main_image = '';
    $uploaded_images = [];

    if (isset($_FILES['vehicle_images']) && !empty($_FILES['vehicle_images']['name'][0])) {
        $allowed = array('jpg', 'jpeg', 'png', 'webp');
        
        foreach ($_FILES['vehicle_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['vehicle_images']['error'][$key] === 0) {
                $ext = strtolower(pathinfo($_FILES['vehicle_images']['name'][$key], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    if (move_uploaded_file($tmp_name, $upload_dir . $filename)) {
                        $image_url = BASE_URL . 'assets/uploads/vehicles/' . $filename;
                        $uploaded_images[] = $image_url;
                    }
                }
            }
        }
    }

    if (empty($uploaded_images)) {
        echo json_encode(['status' => 'error', 'message' => 'Please upload at least one image.']);
        exit;
    }

    // Set first image as main
    $main_image = $uploaded_images[0];

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO vehicles (brand, model, plate_number, category, transmission, fuel_type, seating_capacity, price_per_day, description, main_image, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Available')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$brand, $model, $plate_number, $category, $transmission, $fuel_type, $seating_capacity, $price_per_day, $description, $main_image]);
        $vehicle_id = $pdo->lastInsertId();

        // Insert all images into gallery (including the first one)
        foreach ($uploaded_images as $image_path) {
            $stmt_gal = $pdo->prepare("INSERT INTO vehicle_images (vehicle_id, image_path) VALUES (?, ?)");
            $stmt_gal->execute([$vehicle_id, $image_path]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Vehicle and images uploaded successfully!']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            echo json_encode(['status' => 'error', 'message' => 'Plate number already exists in the system.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}

elseif ($action === 'get') {
    $id = $_GET['id'] ?? '';
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'ID is required.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->execute([$id]);
    $vehicle = $stmt->fetch();

    if ($vehicle) {
        // Fetch gallery images
        $stmt_img = $pdo->prepare("SELECT image_path FROM vehicle_images WHERE vehicle_id = ?");
        $stmt_img->execute([$id]);
        $vehicle['images'] = $stmt_img->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode(['status' => 'success', 'data' => $vehicle]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Vehicle not found.']);
    }
}

elseif ($action === 'update') {
    $id = $_POST['id'] ?? '';
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $plate_number = trim($_POST['plate_number'] ?? '');
    $category = $_POST['category'] ?? '';
    $transmission = $_POST['transmission'] ?? '';
    $fuel_type = $_POST['fuel_type'] ?? '';
    $seating_capacity = (int)($_POST['seating_capacity'] ?? 0);
    $price_per_day = (float)($_POST['price_per_day'] ?? 0);
    $status = $_POST['status'] ?? 'Available';
    $description = trim($_POST['description'] ?? '');

    if (empty($id) || empty($brand) || empty($model) || empty($plate_number) || $price_per_day <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Update vehicle basic info
        $sql = "UPDATE vehicles SET brand=?, model=?, plate_number=?, category=?, transmission=?, fuel_type=?, seating_capacity=?, price_per_day=?, status=?, description=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$brand, $model, $plate_number, $category, $transmission, $fuel_type, $seating_capacity, $price_per_day, $status, $description, $id]);

        // Handle new image uploads if any
        if (isset($_FILES['vehicle_images']) && !empty($_FILES['vehicle_images']['name'][0])) {
            $upload_dir = '../assets/uploads/vehicles/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $allowed = array('jpg', 'jpeg', 'png', 'webp');
            $uploaded_images = [];
            
            foreach ($_FILES['vehicle_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['vehicle_images']['error'][$key] === 0) {
                    $ext = strtolower(pathinfo($_FILES['vehicle_images']['name'][$key], PATHINFO_EXTENSION));
                    if (in_array($ext, $allowed)) {
                        $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        if (move_uploaded_file($tmp_name, $upload_dir . $filename)) {
                            $image_url = BASE_URL . 'assets/uploads/vehicles/' . $filename;
                            $uploaded_images[] = $image_url;
                        }
                    }
                }
            }

            if (!empty($uploaded_images)) {
                // Update main image to the first new one
                $main_image = $uploaded_images[0];
                $stmt_main = $pdo->prepare("UPDATE vehicles SET main_image = ? WHERE id = ?");
                $stmt_main->execute([$main_image, $id]);

                // Clear old gallery and insert new one
                $stmt_del = $pdo->prepare("DELETE FROM vehicle_images WHERE vehicle_id = ?");
                $stmt_del->execute([$id]);

                foreach ($uploaded_images as $image_path) {
                    $stmt_gal = $pdo->prepare("INSERT INTO vehicle_images (vehicle_id, image_path) VALUES (?, ?)");
                    $stmt_gal->execute([$id, $image_path]);
                }
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Vehicle updated successfully.']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            echo json_encode(['status' => 'error', 'message' => 'Plate number already exists in the system.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update vehicle: ' . $e->getMessage()]);
    }
}

elseif ($action === 'update_status_simple') {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';

    if (empty($id) || empty($status)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE vehicles SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['status' => 'success', 'message' => "Vehicle status declared as {$status}."]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update vehicle status.']);
    }
}

elseif ($action === 'delete') {
    $id = $_GET['id'] ?? '';
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'ID is required.']);
        exit;
    }

    try {
        // Check if vehicle has any bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE vehicle_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete vehicle because it has existing bookings. Mark it as "Out of Service" instead.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'success', 'message' => 'Vehicle deleted successfully.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete vehicle.']);
    }
}
?>
