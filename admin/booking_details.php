<?php 
require_once '../config/db.php';
include_once 'includes/header.php'; 

$booking_id = $_GET['id'] ?? '';
if (!$booking_id) redirect('admin/bookings.php');

$stmt = $pdo->prepare("SELECT b.*, v.brand, v.model, v.plate_number, v.main_image, 
                    u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone,
                    d.full_name as driver_name, d.phone as driver_phone
                    FROM bookings b 
                    JOIN vehicles v ON b.vehicle_id = v.id 
                    JOIN users u ON b.customer_id = u.id 
                    LEFT JOIN users d ON b.driver_id = d.id
                    WHERE b.id = ?");
$stmt->execute([$booking_id]);
$b = $stmt->fetch();

if (!$b) redirect('admin/bookings.php');

// Fetch payment info
$stmt = $pdo->prepare("SELECT * FROM payments WHERE booking_id = ?");
$stmt->execute([$booking_id]);
$payment = $stmt->fetch();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <h2 class="text-gradient">Booking Details</h2>
    <a href="bookings.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to List</a>
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
                    <p style="font-size: 0.8rem; color: var(--text-gray); margin-top: 10px;">Placed on: <?php echo date('M d, Y H:i', strtotime($b['created_at'])); ?></p>
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
                            <p style="font-size: 0.8rem;"><?php echo date('H:i', strtotime($b['pickup_date'])); ?></p>
                        </div>
                        <div style="color: var(--primary-color); display: flex; align-items: center;"><i class="fas fa-long-arrow-alt-right"></i></div>
                        <div>
                            <p style="font-size: 0.8rem; color: var(--text-gray);">Return</p>
                            <p style="font-weight: 600;"><?php echo date('M d, Y', strtotime($b['return_date'])); ?></p>
                            <p style="font-size: 0.8rem;"><?php echo date('H:i', strtotime($b['return_date'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 30px;">
                <div>
                    <h4 style="margin-bottom: 15px; color: var(--text-gray); font-size: 0.9rem; text-transform: uppercase;">Customer Details</h4>
                    <p style="font-weight: 600; font-size: 1.1rem; margin-bottom: 5px;"><?php echo $b['customer_name']; ?></p>
                    <p style="color: var(--text-gray); font-size: 0.9rem;"><i class="fas fa-envelope"></i> <?php echo $b['customer_email']; ?></p>
                    <p style="color: var(--text-gray); font-size: 0.9rem;"><i class="fas fa-phone"></i> <?php echo $b['customer_phone']; ?></p>
                </div>
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
            </div>
        </div>

        <?php if ($b['rental_mode'] === 'Self Drive'): ?>
            <div class="card glass" style="padding: 30px;">
                <h4 style="margin-bottom: 20px; color: var(--text-gray); font-size: 0.9rem; text-transform: uppercase;">Uploaded Documents</h4>
                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div style="text-align: center;">
                        <p style="margin-bottom: 10px; font-size: 0.9rem;">ID/Passport</p>
                        <?php if ($b['id_document']): ?>
                            <img src="<?php echo BASE_URL; ?>assets/uploads/bookings/<?php echo $b['id_document']; ?>" style="width: 100%; height: 200px; object-fit: contain; border-radius: 10px; border: 1px solid var(--glass-border);">
                        <?php else: ?>
                            <div class="flex-center" style="height: 200px; background: rgba(255,255,255,0.05); border-radius: 10px; border: 1px dashed var(--glass-border);">
                                <p style="color: var(--text-gray);">No document</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: center;">
                        <p style="margin-bottom: 10px; font-size: 0.9rem;">Driving License</p>
                        <?php if ($b['license_document']): ?>
                            <img src="<?php echo BASE_URL; ?>assets/uploads/bookings/<?php echo $b['license_document']; ?>" style="width: 100%; height: 200px; object-fit: contain; border-radius: 10px; border: 1px solid var(--glass-border);">
                        <?php else: ?>
                            <div class="flex-center" style="height: 200px; background: rgba(255,255,255,0.05); border-radius: 10px; border: 1px dashed var(--glass-border);">
                                <p style="color: var(--text-gray);">No document</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div>
        <div class="card glass" style="padding: 30px; margin-bottom: 30px;">
            <h4 style="margin-bottom: 25px; color: var(--text-gray); font-size: 0.9rem; text-transform: uppercase;">Financial Summary</h4>
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
                    <span>Total Amount</span>
                    <span class="text-gradient"><?php echo CURRENCY . ' ' . number_format($b['total_price']); ?></span>
                </div>
            </div>

            <div style="margin-bottom: 25px;">
                <h4 style="margin-bottom: 15px; color: var(--text-gray); font-size: 0.8rem; text-transform: uppercase;">Payment Status</h4>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; background: rgba(46, 204, 113, 0.1); color: #2ecc71;">
                        <?php echo $payment['payment_status'] ?? 'Unpaid'; ?>
                    </span>
                    <p style="font-size: 0.9rem; font-weight: 600;"><?php echo $payment['payment_method'] ?? 'N/A'; ?></p>
                </div>
                
                <?php 
                if ($b['status'] === 'Ongoing') {
                    $now = new DateTime();
                    $return = new DateTime($b['return_date']);
                    if ($now > $return) {
                        $diff = $now->diff($return);
                        $late_hours = ($diff->days * 24) + $diff->h + ($diff->i > 0 ? 1 : 0);
                        $penalty = ($b['total_price'] * 0.05) * $late_hours;
                        ?>
                        <div class="glass" style="margin-top: 15px; padding: 15px; border: 1px solid var(--primary-color); border-radius: 10px;">
                            <p style="color: var(--primary-color); font-weight: 700; font-size: 0.8rem;"><i class="fas fa-exclamation-triangle"></i> LATE PENALTY</p>
                            <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                                <span style="font-size: 0.8rem; color: var(--text-gray);">Overdue: <?php echo $late_hours; ?> hour(s)</span>
                                <span style="font-weight: 700; color: var(--primary-color);">+<?php echo CURRENCY . ' ' . number_format($penalty); ?></span>
                            </div>
                            <p style="font-size: 0.7rem; color: var(--text-gray); margin-top: 5px;">(5% of total per hour)</p>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php if ($b['status'] === 'Pending'): ?>
                    <button onclick="updateBookingStatus(<?php echo $b['id']; ?>, 'Approved')" class="btn btn-primary" style="width: 100%; justify-content: center;">Approve Booking</button>
                    <button onclick="updateBookingStatus(<?php echo $b['id']; ?>, 'Rejected')" class="btn btn-outline" style="width: 100%; justify-content: center; border-color: #666; color: #666;">Reject Booking</button>
                <?php elseif ($b['status'] === 'Approved'): ?>
                    <button onclick="updateBookingStatus(<?php echo $b['id']; ?>, 'Ongoing')" class="btn btn-primary" style="width: 100%; justify-content: center; background: #3498db; margin-bottom: 10px;">Mark as Picked Up</button>
                    <button onclick="updateBookingStatus(<?php echo $b['id']; ?>, 'Cancelled')" class="btn btn-outline" style="width: 100%; justify-content: center; border-color: var(--primary-color); color: var(--primary-color);">Cancel Booking</button>
                <?php elseif ($b['status'] === 'Ongoing'): ?>
                    <button onclick="updateBookingStatus(<?php echo $b['id']; ?>, 'Completed')" class="btn btn-primary" style="width: 100%; justify-content: center; background: #2ecc71; margin-bottom: 10px;">Mark as Completed</button>
                    <button onclick="updateBookingStatus(<?php echo $b['id']; ?>, 'Cancelled')" class="btn btn-outline" style="width: 100%; justify-content: center; border-color: var(--primary-color); color: var(--primary-color);">Cancel Booking</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    async function updateBookingStatus(id, status) {
        if (confirm(`Change booking status to ${status}?`)) {
            try {
                const response = await fetch(`../api/admin_actions.php?action=update_booking&id=${id}&status=${status}`);
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
    }
</script>

<?php include_once 'includes/footer.php'; ?>
