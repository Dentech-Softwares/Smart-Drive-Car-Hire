<?php
require_once '../config/config.php';
require_once '../config/db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'register') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'customer';

    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
        exit;
    }

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Email already registered.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $email, $phone, $hashed_password, $role]);
        $user_id = $pdo->lastInsertId();

        if ($role === 'customer') {
            $stmt = $pdo->prepare("INSERT INTO customers (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
        } elseif ($role === 'driver') {
            $license_number = $_POST['license_number'] ?? '';
            $stmt = $pdo->prepare("INSERT INTO drivers (user_id, license_number) VALUES (?, ?)");
            $stmt->execute([$user_id, $license_number]);
        } elseif ($role === 'admin') {
            // Check if admin limit reached
            $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
            $admin_count = $stmt->fetchColumn();
            if ($admin_count >= 2) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Registration failed: Maximum number of admins (2) already reached.']);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO admins (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Registration successful. Please login.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $e->getMessage()]);
    }
} 

elseif ($action === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];

        $redirect = 'index.php';
        if ($user['role'] === ROLE_ADMIN) $redirect = 'admin/dashboard.php';
        elseif ($user['role'] === ROLE_CUSTOMER) $redirect = 'customer/dashboard.php';
        elseif ($user['role'] === ROLE_DRIVER) $redirect = 'driver/dashboard.php';

        echo json_encode([
            'status' => 'success', 
            'message' => 'Login successful!', 
            'redirect' => BASE_URL . $redirect
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
    }
}
?>
