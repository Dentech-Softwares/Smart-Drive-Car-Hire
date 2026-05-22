<?php
require_once __DIR__ . '/../../config/config.php';
if (!isLoggedIn() || !hasRole(ROLE_ADMIN)) {
    redirect('auth/login.php');
}

// Helper to get current page name for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Immediate background color to prevent white flash */
        html, body { background-color: #0b090a !important; }
        .page-loader { background-color: #0b090a !important; }
        
        body { display: flex; min-height: 100vh; background: var(--bg-dark); }
        .sidebar { width: 260px; background: var(--bg-card); border-right: 1px solid var(--glass-border); padding: 30px 20px; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; }
        .main-content { flex: 1; margin-left: 260px; padding: 40px; }
        .sidebar-logo { font-size: 1.5rem; font-weight: 800; color: white; text-decoration: none; margin-bottom: 50px; }
        .sidebar-logo span { color: var(--primary-color); }
        .nav-menu { list-style: none; }
        .nav-item { margin-bottom: 10px; }
        .nav-link { display: flex; align-items: center; gap: 15px; padding: 12px 20px; color: var(--text-gray); text-decoration: none; border-radius: 10px; transition: var(--transition); }
        .nav-link:hover, .nav-link.active { background: var(--primary-color); color: white; }
        .nav-link i { font-size: 1.2rem; width: 25px; text-align: center; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .stat-card { background: var(--bg-card); padding: 25px; border-radius: 15px; border: 1px solid var(--glass-border); display: flex; align-items: center; gap: 20px; }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    </style>
</head>
<body class="loading">
    <div class="page-loader">
        <div class="loader-content"></div>
    </div>
    <div class="sidebar">
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="sidebar-logo">Smart<span>Admin</span></a>
        <ul class="nav-list" style="list-style: none; padding: 0; margin: 0;">
            <li class="nav-item"><a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-th-large"></i> <span>Dashboard</span></a></li>
            <li class="nav-item"><a href="vehicles.php" class="nav-link <?php echo $current_page == 'vehicles.php' ? 'active' : ''; ?>"><i class="fas fa-car"></i> <span>Vehicles</span></a></li>
            <li class="nav-item">
                <a href="bookings.php" class="nav-link <?php echo in_array($current_page, ['bookings.php', 'booking_details.php']) ? 'active' : ''; ?>" style="justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Bookings</span>
                    </div>
                    <?php 
                        $stmt_pending = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'");
                        $pending_count = $stmt_pending->fetchColumn();
                        if ($pending_count > 0):
                    ?>
                        <span id="pending-booking-badge" style="background: var(--primary-color); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; font-weight: bold; min-width: 20px; text-align: center;">
                            <?php echo $pending_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item"><a href="drivers.php" class="nav-link <?php echo $current_page == 'drivers.php' ? 'active' : ''; ?>"><i class="fas fa-user-tie"></i> <span>Drivers</span></a></li>
            <li class="nav-item"><a href="customers.php" class="nav-link <?php echo in_array($current_page, ['customers.php', 'customer_details.php']) ? 'active' : ''; ?>"><i class="fas fa-user-friends"></i> <span>Customers</span></a></li>
            <li class="nav-item">
                <a href="payments.php" class="nav-link <?php echo $current_page == 'payments.php' ? 'active' : ''; ?>" style="justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <i class="fas fa-credit-card"></i>
                        <span>Payments</span>
                    </div>
                    <?php 
                        $stmt_pending_payments = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'Pending'");
                        $pending_payments_count = $stmt_pending_payments->fetchColumn();
                        if ($pending_payments_count > 0):
                    ?>
                        <span id="pending-payment-badge" style="background: var(--primary-color); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; font-weight: bold; min-width: 20px; text-align: center;">
                            <?php echo $pending_payments_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item"><a href="maintenance.php" class="nav-link <?php echo $current_page == 'maintenance.php' ? 'active' : ''; ?>"><i class="fas fa-tools"></i> <span>Maintenance</span></a></li>
            <li class="nav-item"><a href="enquiries.php" class="nav-link <?php echo $current_page == 'enquiries.php' ? 'active' : ''; ?>"><i class="fas fa-question-circle"></i> <span>Enquiries</span></a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>"><i class="fas fa-file-invoice-dollar"></i> <span>Reports</span></a></li>
            <li class="nav-item" style="margin-top: auto;"><a href="<?php echo BASE_URL; ?>auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="top-bar">
            <h2 class="text-gradient">Overview</h2>
            <div class="user-info flex-center" style="gap: 15px;">
                <div style="text-align: right;">
                    <p style="font-weight: 600;"><?php echo $_SESSION['full_name']; ?></p>
                    <span style="font-size: 0.8rem; color: var(--text-gray);">Administrator</span>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&background=e63946&color=fff" style="width: 45px; height: 45px; border-radius: 50%; border: 2px solid var(--primary-color);">
            </div>
        </div>
