<?php
require_once '../config/config.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$data = [];

if ($role === ROLE_ADMIN) {
    // Admin Stats
    $data['total_vehicles'] = $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
    $data['total_bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $data['total_revenue'] = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Paid'")->fetchColumn() ?: 0;
    $data['active_drivers'] = $pdo->query("SELECT COUNT(*) FROM drivers WHERE status = 'available'")->fetchColumn();
    
    // Fleet Status for Chart
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM vehicles GROUP BY status");
    $data['fleet_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Revenue Overview for Chart (Last 6 months)
    $revenue_data = [];
    $months = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('M', strtotime("-$i months"));
        $months[] = $month;
        $start_date = date('Y-m-01', strtotime("-$i months"));
        $end_date = date('Y-m-t', strtotime("-$i months"));
        
        $stmt = $pdo->prepare("SELECT SUM(amount) FROM payments WHERE payment_status = 'Paid' AND payment_date BETWEEN ? AND ?");
        $stmt->execute([$start_date, $end_date]);
        $revenue_data[] = $stmt->fetchColumn() ?: 0;
    }
    $data['revenue_chart'] = ['labels' => $months, 'data' => $revenue_data];

} elseif ($role === ROLE_CUSTOMER) {
    // Customer Stats
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id = ? AND status IN ('Approved', 'Ongoing')");
    $stmt->execute([$user_id]);
    $data['active_bookings'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT SUM(amount) FROM payments p JOIN bookings b ON p.booking_id = b.id WHERE b.customer_id = ? AND p.payment_status = 'Paid'");
    $stmt->execute([$user_id]);
    $data['total_spent'] = $stmt->fetchColumn() ?: 0;

    $data['favorite_cars'] = 0; // Placeholder for now
}

echo json_encode(['status' => 'success', 'data' => $data]);
?>
