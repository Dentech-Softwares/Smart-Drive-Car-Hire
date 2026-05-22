<?php 
require_once '../config/config.php';
require_once '../config/db.php';

if (!isLoggedIn() || !hasRole(ROLE_CUSTOMER)) {
    redirect('auth/login.php');
}

include_once '../includes/header.php'; 

$user_id = $_SESSION['user_id'];

// Stats
$active_bookings = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id = ? AND status IN ('Approved', 'Ongoing')");
$active_bookings->execute([$user_id]);
$active_count = $active_bookings->fetchColumn();

$total_spent = $pdo->prepare("SELECT SUM(amount) FROM payments p JOIN bookings b ON p.booking_id = b.id WHERE b.customer_id = ? AND p.payment_status = 'Paid'");
$total_spent->execute([$user_id]);
$spent_count = $total_spent->fetchColumn() ?: 0;

// Recent Bookings
$stmt = $pdo->prepare("SELECT b.*, v.brand, v.model, v.main_image FROM bookings b 
                    JOIN vehicles v ON b.vehicle_id = v.id 
                    WHERE b.customer_id = ? 
                    ORDER BY b.created_at DESC LIMIT 3");
$stmt->execute([$user_id]);
$recent_bookings = $stmt->fetchAll();
?>

<section style="padding: 120px 0 60px;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <h2 class="text-gradient">Welcome, <?php echo $_SESSION['full_name']; ?></h2>
            <a href="<?php echo BASE_URL; ?>vehicles/browse.php" class="btn btn-primary">Book a Car <i class="fas fa-plus"></i></a>
        </div>

        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 50px;">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(230, 57, 70, 0.1); color: var(--primary-color);">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.8rem;" id="stat_active"><?php echo $active_count; ?></h3>
                    <p style="color: var(--text-gray);">Active Bookings</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71;">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.8rem;" id="stat_spent"><?php echo CURRENCY . ' ' . number_format($spent_count); ?></h3>
                    <p style="color: var(--text-gray);">Total Spent</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(52, 152, 219, 0.1); color: #3498db;">
                    <i class="fas fa-heart"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.8rem;" id="stat_favorites">0</h3>
                    <p style="color: var(--text-gray);">Favorite Cars</p>
                </div>
            </div>
        </div>

<script>
    async function updateCustomerStats() {
        try {
            const response = await fetch('../api/stats.php');
            const result = await response.json();
            
            if (result.status === 'success') {
                const data = result.data;
                document.getElementById('stat_active').innerText = data.active_bookings;
                document.getElementById('stat_spent').innerText = '<?php echo CURRENCY; ?> ' + parseInt(data.total_spent).toLocaleString();
                document.getElementById('stat_favorites').innerText = data.favorite_cars;
            }
        } catch (error) {
            console.error('Failed to fetch realtime customer stats:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Poll every 10 seconds
        setInterval(updateCustomerStats, 10000);
    });
</script>

        <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 40px;">
            <div>
                <h3 style="margin-bottom: 25px;">Recent Bookings</h3>
                <?php if (empty($recent_bookings)): ?>
                    <div class="glass" style="padding: 40px; text-align: center;">
                        <p style="color: var(--text-gray);">You haven't made any bookings yet.</p>
                        <a href="<?php echo BASE_URL; ?>vehicles/browse.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600; display: inline-block; margin-top: 10px;">Start Browsing</a>
                    </div>
                <?php else: ?>
                    <div class="grid" style="gap: 20px;">
                        <?php foreach ($recent_bookings as $booking): ?>
                            <div class="card glass" style="display: flex; gap: 25px; padding: 20px; align-items: center;">
                                <img src="<?php echo $booking['main_image']; ?>" style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px;">
                                <div style="flex: 1;">
                                    <h4 style="margin-bottom: 5px;"><?php echo $booking['brand'] . ' ' . $booking['model']; ?></h4>
                                    <p style="color: var(--text-gray); font-size: 0.9rem;">Ref: <?php echo $booking['booking_reference']; ?></p>
                                    <p style="color: var(--text-gray); font-size: 0.8rem;"><?php echo date('M d, Y', strtotime($booking['pickup_date'])); ?> - <?php echo date('M d, Y', strtotime($booking['return_date'])); ?></p>
                                </div>
                                <div style="text-align: right;">
                                    <span style="display: block; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; background: rgba(230, 57, 70, 0.1); color: var(--primary-color); margin-bottom: 10px;">
                                        <?php echo $booking['status']; ?>
                                    </span>
                                    <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.8rem;">Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="bookings.php" class="btn btn-outline" style="width: 100%; justify-content: center; margin-top: 20px;">View All History</a>
                <?php endif; ?>
            </div>

            <div>
                <h3 style="margin-bottom: 25px;">Quick Actions</h3>
                <div class="grid" style="gap: 15px;">
                    <a href="profile.php" class="card glass" style="display: flex; align-items: center; gap: 15px; text-decoration: none; color: white;">
                        <i class="fas fa-user-edit" style="color: var(--primary-color);"></i>
                        <span>Update Profile</span>
                    </a>
                    <a href="payments.php" class="card glass" style="display: flex; align-items: center; gap: 15px; text-decoration: none; color: white;">
                        <i class="fas fa-history" style="color: var(--primary-color);"></i>
                        <span>Payment History</span>
                    </a>
                    <a href="#" class="card glass" style="display: flex; align-items: center; gap: 15px; text-decoration: none; color: white;">
                        <i class="fas fa-headset" style="color: var(--primary-color);"></i>
                        <span>Support Center</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once '../includes/footer.php'; ?>
