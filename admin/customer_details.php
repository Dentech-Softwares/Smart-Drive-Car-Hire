<?php 
require_once '../config/db.php';
include_once 'includes/header.php'; 

$customer_id = $_GET['id'] ?? '';
if (!$customer_id) redirect('admin/customers.php');

$stmt = $pdo->prepare("SELECT u.*, c.id_passport_number, c.driving_license_number, c.id_image, c.license_image, c.emergency_contact_name, c.emergency_contact_phone 
                    FROM users u 
                    JOIN customers c ON u.id = c.user_id 
                    WHERE u.id = ?");
$stmt->execute([$customer_id]);
$c = $stmt->fetch();

if (!$c) redirect('admin/customers.php');

// Fetch booking history
$stmt = $pdo->prepare("SELECT b.*, v.brand, v.model FROM bookings b 
                    JOIN vehicles v ON b.vehicle_id = v.id 
                    WHERE b.customer_id = ? 
                    ORDER BY b.created_at DESC");
$stmt->execute([$customer_id]);
$bookings = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <h2 class="text-gradient">Customer Profile</h2>
    <a href="customers.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to List</a>
</div>

<div class="grid" style="grid-template-columns: 1fr 2fr; gap: 30px;">
    <div>
        <div class="card glass" style="padding: 30px; text-align: center; margin-bottom: 30px;">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($c['full_name']); ?>&background=random" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-color); margin-bottom: 20px;">
            <h3 style="margin-bottom: 5px;"><?php echo $c['full_name']; ?></h3>
            <p style="color: var(--text-gray); font-size: 0.9rem; margin-bottom: 20px;">Customer since <?php echo date('M Y', strtotime($c['created_at'])); ?></p>
            <div style="text-align: left; border-top: 1px solid var(--glass-border); padding-top: 20px;">
                <p style="font-size: 0.9rem; margin-bottom: 10px;"><i class="fas fa-envelope" style="width: 25px; color: var(--primary-color);"></i> <?php echo $c['email']; ?></p>
                <p style="font-size: 0.9rem; margin-bottom: 10px;"><i class="fas fa-phone" style="width: 25px; color: var(--primary-color);"></i> <?php echo $c['phone']; ?></p>
                <p style="font-size: 0.9rem;"><i class="fas fa-id-card" style="width: 25px; color: var(--primary-color);"></i> <?php echo $c['id_passport_number'] ?: 'N/A'; ?></p>
            </div>
        </div>

        <div class="card glass" style="padding: 30px;">
            <h4 style="margin-bottom: 20px; color: var(--text-gray); font-size: 0.9rem; text-transform: uppercase;">Emergency Contact</h4>
            <p style="font-weight: 600;"><?php echo $c['emergency_contact_name'] ?: 'Not Provided'; ?></p>
            <p style="color: var(--text-gray); font-size: 0.9rem;"><?php echo $c['emergency_contact_phone']; ?></p>
        </div>
    </div>

    <div>
        <div class="card glass" style="padding: 30px; margin-bottom: 30px;">
            <h3 style="margin-bottom: 25px;">Booking History</h3>
            <?php if (empty($bookings)): ?>
                <p style="color: var(--text-gray); text-align: center; padding: 20px;">This customer has no bookings yet.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-gray);">
                                <th style="padding: 15px;">Reference</th>
                                <th style="padding: 15px;">Vehicle</th>
                                <th style="padding: 15px;">Date</th>
                                <th style="padding: 15px;">Status</th>
                                <th style="padding: 15px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                                <tr style="border-bottom: 1px solid var(--glass-border);">
                                    <td style="padding: 15px; font-weight: 600;"><?php echo $b['booking_reference']; ?></td>
                                    <td style="padding: 15px;"><?php echo $b['brand'] . ' ' . $b['model']; ?></td>
                                    <td style="padding: 15px; font-size: 0.9rem;"><?php echo date('M d, Y', strtotime($b['created_at'])); ?></td>
                                    <td style="padding: 15px;">
                                        <span style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; background: rgba(230, 57, 70, 0.1); color: var(--primary-color);">
                                            <?php echo $b['status']; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <a href="booking_details.php?id=<?php echo $b['id']; ?>" style="color: var(--accent-color);"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
