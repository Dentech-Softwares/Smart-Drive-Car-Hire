<?php 
require_once '../config/config.php';
require_once '../config/db.php';

if (!isLoggedIn() || !hasRole(ROLE_CUSTOMER)) {
    redirect('auth/login.php');
}

include_once '../includes/header.php'; 

$booking_id = $_GET['id'] ?? '';
$user_id = $_SESSION['user_id'];

if (!$booking_id) redirect('customer/bookings.php');

$stmt = $pdo->prepare("SELECT b.*, v.brand, v.model, v.plate_number, v.main_image, 
                    d.full_name as driver_name, d.phone as driver_phone
                    FROM bookings b 
                    JOIN vehicles v ON b.vehicle_id = v.id 
                    LEFT JOIN users d ON b.driver_id = d.id
                    WHERE b.id = ? AND b.customer_id = ?");
$stmt->execute([$booking_id, $user_id]);
$b = $stmt->fetch();

if (!$b) redirect('customer/bookings.php');

// Fetch payment info
$stmt = $pdo->prepare("SELECT * FROM payments WHERE booking_id = ?");
$stmt->execute([$booking_id]);
$payment = $stmt->fetch();
?>

<section style="padding: 120px 0 60px;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <h2 class="text-gradient">Booking Details</h2>
            <a href="bookings.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to History</a>
        </div>

        <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 30px;">
            <div>
                <div class="card glass" style="padding: 30px; margin-bottom: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 1px solid var(--glass-border); padding-bottom: 20px;">
                        <div>
                            <h4 style="color: var(--primary-color); margin-bottom: 5px;">Booking Reference</h4>
                            <h2 style="font-size: 1.8rem;"><?php echo $b['booking_reference']; ?></h2>
                        </div>
                        <div style="text-align: right;">
                            <span style="display: block; padding: 8px 20px; border-radius: 20px; font-size: 0.9rem; background: rgba(230, 57, 70, 0.1); color: var(--primary-color); font-weight: 600;">
                                <?php echo $b['status']; ?>
                            </span>
                            <p style="font-size: 0.8rem; color: var(--text-gray); margin-top: 10px;">Placed on: <?php echo date('M d, Y', strtotime($b['created_at'])); ?></p>
                        </div>
                    </div>

                    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                        <div>
                            <h4 style="margin-bottom: 15px; color: var(--text-gray); font-size: 0.9rem; text-transform: uppercase;">Vehicle Information</h4>
                            <div style="display: flex; gap: 20px;">
                                <img src="<?php echo $b['main_image']; ?>" style="width: 120px; height: 80px; object-fit: cover; border-radius: 10px;">
                                <div>
                                    <h3 style="margin-bottom: 5px;"><?php echo $b['brand'] . ' ' . $b['model']; ?></h3>
                                    <p style="color: var(--text-gray); font-size: 0.9rem;"><?php echo $b['plate_number']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 15px; color: var(--text-gray); font-size: 0.9rem; text-transform: uppercase;">Rental Schedule</h4>
                            <div style="display: flex; gap: 30px;">
                                <div>
                                    <p style="font-size: 0.8rem; color: var(--text-gray);">Pickup</p>
                                    <p style="font-weight: 600;"><?php echo date('M d, Y', strtotime($b['pickup_date'])); ?></p>
                                </div>
                                <div style="color: var(--primary-color); display: flex; align-items: center;"><i class="fas fa-long-arrow-alt-right"></i></div>
                                <div>
                                    <p style="font-size: 0.8rem; color: var(--text-gray);">Return</p>
                                    <p style="font-weight: 600;"><?php echo date('M d, Y', strtotime($b['return_date'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 30px;">
                        <div>
                            <h4 style="margin-bottom: 15px; color: var(--text-gray); font-size: 0.9rem; text-transform: uppercase;">Rental Mode</h4>
                            <p style="font-weight: 600; font-size: 1.1rem; margin-bottom: 5px;"><?php echo $b['rental_mode']; ?></p>
                            <?php if ($b['driver_id']): ?>
                                <p style="color: var(--text-gray); font-size: 0.9rem;"><i class="fas fa-user-tie"></i> Driver: <?php echo $b['driver_name']; ?></p>
                                <p style="color: var(--text-gray); font-size: 0.8rem;"><i class="fas fa-phone"></i> <?php echo $b['driver_phone']; ?></p>
                            <?php else: ?>
                                <p style="color: var(--text-gray); font-size: 0.9rem;"><i class="fas fa-id-card"></i> Self Drive</p>
                            <?php endif; ?>
                        </div>
                        <div id="countdownContainer" style="display: none;">
                            <h4 style="margin-bottom: 15px; color: var(--primary-color); font-size: 0.9rem; text-transform: uppercase;">Return Countdown</h4>
                            <div id="returnCountdown" style="font-size: 1.5rem; font-weight: 800; font-family: monospace; letter-spacing: 2px;">--:--:--:--</div>
                            <p id="lateWarning" style="color: var(--primary-color); font-size: 0.8rem; margin-top: 10px; display: none;"><i class="fas fa-exclamation-triangle"></i> LATE: 5% penalty applied every hour.</p>
                        </div>
                    </div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const returnDate = new Date("<?php echo $b['return_date']; ?>").getTime();
                    const countdownElement = document.getElementById('returnCountdown');
                    const container = document.getElementById('countdownContainer');
                    const warning = document.getElementById('lateWarning');
                    const status = "<?php echo $b['status']; ?>";

                    if (status === 'Ongoing') {
                        container.style.display = 'block';
                        
                        const timer = setInterval(() => {
                            const now = new Date().getTime();
                            const distance = returnDate - now;

                            if (distance < 0) {
                                // Overdue
                                warning.style.display = 'block';
                                countdownElement.style.color = 'var(--primary-color)';
                                
                                // Show how much time has passed since due
                                const overdue = Math.abs(distance);
                                const hours = Math.floor((overdue % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                const minutes = Math.floor((overdue % (1000 * 60 * 60)) / (1000 * 60));
                                const seconds = Math.floor((overdue % (1000 * 60)) / 1000);
                                
                                countdownElement.innerHTML = `EXPIRED: ${hours}h ${minutes}m ${seconds}s`;
                            } else {
                                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                                countdownElement.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                            }
                        }, 1000);
                    }
                });
                </script>

                <div class="card glass" style="padding: 30px;">
                    <h4 style="margin-bottom: 20px; color: var(--text-gray); font-size: 0.9rem; text-transform: uppercase;">Help & Support</h4>
                    <p style="color: var(--text-gray); font-size: 0.9rem; line-height: 1.6;">If you need to make changes to your booking or encounter any issues, please contact our 24/7 support line at <strong style="color: var(--primary-color);">+254 700 000 000</strong> or visit our office.</p>
                </div>
            </div>

            <div>
                <div class="card glass" style="padding: 30px; margin-bottom: 30px;">
                    <h4 style="margin-bottom: 25px; color: var(--text-gray); font-size: 0.9rem; text-transform: uppercase;">Payment Summary</h4>
                    <div style="margin-bottom: 20px; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="color: var(--text-gray);">Security Deposit</span>
                            <span><?php echo CURRENCY . ' ' . number_format($b['security_deposit']); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="color: var(--text-gray);">Rental Charges</span>
                            <span><?php echo CURRENCY . ' ' . number_format($b['total_price'] - $b['security_deposit']); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 15px; font-weight: 700; font-size: 1.2rem;">
                            <span>Total</span>
                            <span class="text-gradient"><?php echo CURRENCY . ' ' . number_format($b['total_price']); ?></span>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <h4 style="margin-bottom: 15px; color: var(--text-gray); font-size: 0.8rem; text-transform: uppercase;">Payment Info</h4>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; background: rgba(46, 204, 113, 0.1); color: #2ecc71;">
                                <?php echo $payment['payment_status'] ?? 'Unpaid'; ?>
                            </span>
                            <p style="font-size: 0.9rem; font-weight: 600;"><?php echo $payment['payment_method'] ?? 'N/A'; ?></p>
                        </div>
                    </div>

                    <button onclick="window.print()" class="btn btn-outline" style="width: 100%; justify-content: center; margin-bottom: 10px;"><i class="fas fa-file-invoice"></i> Download Invoice</button>
                    
                    <div style="display: flex; gap: 15px; margin-top: 10px;">
                        <?php if ($b['status'] === 'Pending'): ?>
                            <a href="edit_booking.php?id=<?php echo $b['id']; ?>" class="action-btn" title="Edit" style="color: #f1c40f; font-size: 1.2rem; width: 45px; height: 45px;"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>

                        <?php if (in_array($b['status'], ['Pending', 'Approved'])): ?>
                            <button onclick="cancelBooking(<?php echo $b['id']; ?>)" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.8rem; border-color: var(--primary-color); color: var(--primary-color);">Cancel</button>
                        <?php endif; ?>

                        <?php if (in_array($b['status'], ['Cancelled', 'Rejected', 'Pending'])): ?>
                            <button onclick="deleteBooking(<?php echo $b['id']; ?>)" class="action-btn" title="Delete Booking" style="background: none; border: 1px solid var(--glass-border); color: #666; cursor: pointer; font-size: 1.2rem; width: 45px; height: 45px;"><i class="fas fa-trash-alt"></i></button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
async function deleteBooking(id) {
    if (confirm('Are you sure you want to remove this booking from your history? This action cannot be undone.')) {
        try {
            const response = await fetch(`../api/bookings.php?action=delete&id=${id}`);
            const data = await response.json();
            if (data.status === 'success') {
                notify('success', data.message);
                setTimeout(() => {
                    window.location.href = 'bookings.php';
                }, 1500);
            } else {
                notify('error', data.message);
            }
        } catch (error) {
            notify('error', 'Something went wrong while deleting');
        }
    }
}

async function cancelBooking(id) {
    if (confirm('Are you sure you want to cancel this booking? The vehicle will be released for others.')) {
        try {
            const response = await fetch(`../api/bookings.php?action=cancel&id=${id}`);
            const data = await response.json();
            if (data.status === 'success') {
                notify('success', data.message);
                setTimeout(() => {
                    window.location.href = 'bookings.php';
                }, 1500);
            } else {
                notify('error', data.message);
            }
        } catch (error) {
            notify('error', 'Something went wrong');
        }
    }
}
</script>

<?php include_once '../includes/footer.php'; ?>
