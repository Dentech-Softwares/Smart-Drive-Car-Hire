<?php require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Premium Car Rental</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animations -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    
    <style>
        /* Immediate background color to prevent white flash */
        html, body { background-color: #0b090a !important; }
        .page-loader { background-color: #0b090a !important; }
    </style>
    
    <!-- JS Dependencies -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body class="loading">
    <div class="page-loader">
        <div class="loader-content"></div>
    </div>
    <nav>
        <div class="container nav-container">
            <a href="<?php echo BASE_URL; ?>" class="logo">Smart<span>Drive</span></a>
            
            <ul class="nav-links">
                <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                <li><a href="<?php echo BASE_URL; ?>#vehicles">Vehicles</a></li>
                <li><a href="<?php echo BASE_URL; ?>#services">Services</a></li>
                <li><a href="<?php echo BASE_URL; ?>#contact">Contact</a></li>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (hasRole(ROLE_ADMIN)): ?>
                        <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-primary">Dashboard</a></li>
                    <?php elseif (hasRole(ROLE_CUSTOMER)): ?>
                        <li><a href="<?php echo BASE_URL; ?>customer/dashboard.php" class="btn btn-primary">My Account</a></li>
                    <?php elseif (hasRole(ROLE_DRIVER)): ?>
                        <li><a href="<?php echo BASE_URL; ?>driver/dashboard.php" class="btn btn-primary">Trips</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo BASE_URL; ?>auth/logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>auth/login.php">Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-primary">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
