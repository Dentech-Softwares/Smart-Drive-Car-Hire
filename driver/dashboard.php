<?php 
require_once '../config/config.php';
require_once '../config/db.php';

if (!isLoggedIn() || !hasRole(ROLE_DRIVER)) {
    redirect('auth/login.php');
}

include_once '../includes/header.php'; 

$driver_id = $_SESSION['user_id'];

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE driver_id = ? AND status = 'Ongoing'");
$stmt->execute([$driver_id]);
$active_trips = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT earnings FROM drivers WHERE user_id = ?");
$stmt->execute([$driver_id]);
$earnings = $stmt->fetchColumn() ?: 0;

// Assigned Trips
$stmt = $pdo->prepare("SELECT b.*, v.brand, v.model, u.full_name, u.phone FROM bookings b 
                    JOIN vehicles v ON b.vehicle_id = v.id 
                    JOIN users u ON b.customer_id = u.id 
                    WHERE b.driver_id = ? AND b.status IN ('Approved', 'Ongoing')
                    ORDER BY b.pickup_date ASC");
$stmt->execute([$driver_id]);
$trips = $stmt->fetchAll();
?>

<section style="padding: 120px 0 60px;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <h2 class="text-gradient">Driver Dashboard</h2>
            <div style="text-align: right;">
                <span style="color: var(--text-gray); font-size: 0.9rem;">Current Status</span>
                <p style="color: #2ecc71; font-weight: 700;"><i class="fas fa-circle" style="font-size: 0.7rem; margin-right: 5px;"></i> Available</p>
            </div>
        </div>

        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-bottom: 50px;">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(230, 57, 70, 0.1); color: var(--primary-color);">
                    <i class="fas fa-route"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.8rem;"><?php echo $active_trips; ?></h3>
                    <p style="color: var(--text-gray);">Active Trips</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71;">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.8rem;"><?php echo CURRENCY . ' ' . number_format($earnings); ?></h3>
                    <p style="color: var(--text-gray);">Total Earnings</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(52, 152, 219, 0.1); color: #3498db;">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.8rem;">4.9</h3>
                    <p style="color: var(--text-gray);">Your Rating</p>
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 25px;">Assigned Trips</h3>
        <?php if (empty($trips)): ?>
            <div class="glass" style="padding: 60px; text-align: center;">
                <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--text-gray); margin-bottom: 20px;"></i>
                <p style="color: var(--text-gray);">No trips assigned to you yet.</p>
            </div>
        <?php else: ?>
            <div class="grid" style="gap: 20px;">
                <?php foreach ($trips as $trip): ?>
                    <div class="card glass" style="padding: 30px;">
                        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; align-items: center;">
                            <div>
                                <h4 style="color: var(--primary-color); margin-bottom: 10px;">Booking Ref: <?php echo $trip['booking_reference']; ?></h4>
                                <h3 style="margin-bottom: 15px;"><?php echo $trip['brand'] . ' ' . $trip['model']; ?></h3>
                                <div style="display: flex; gap: 15px; color: var(--text-gray); font-size: 0.9rem;">
                                    <span><i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($trip['pickup_date'])); ?></span>
                                    <span><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($trip['pickup_date'])); ?></span>
                                </div>
                            </div>
                            <div>
                                <h4 style="margin-bottom: 10px;">Customer Details</h4>
                                <p style="font-weight: 600;"><?php echo $trip['full_name']; ?></p>
                                <p style="color: var(--text-gray); font-size: 0.9rem;"><i class="fas fa-phone"></i> <?php echo $trip['phone']; ?></p>
                            </div>
                            <div style="text-align: right;">
                                <span style="display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; background: rgba(230, 57, 70, 0.1); color: var(--primary-color); margin-bottom: 15px;">
                                    <?php echo $trip['status']; ?>
                                </span>
                                <?php if ($trip['status'] === 'Approved'): ?>
                                    <button onclick="updateTrip(<?php echo $trip['id']; ?>, 'Ongoing')" class="btn btn-primary" style="width: 100%;">Start Trip</button>
                                <?php elseif ($trip['status'] === 'Ongoing'): ?>
                                    <button onclick="updateTrip(<?php echo $trip['id']; ?>, 'Completed')" class="btn btn-primary" style="width: 100%; background: #2ecc71;">Complete Trip</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    async function updateTrip(id, status) {
        try {
            const response = await fetch(`../api/driver_actions.php?action=update_trip&id=${id}&status=${status}`);
            const data = await response.json();
            if (data.status === 'success') {
                notify('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                notify('error', data.message);
            }
        } catch (error) {
            notify('error', 'Something went wrong');
        }
    }
</script>

<?php include_once '../includes/footer.php'; ?>
