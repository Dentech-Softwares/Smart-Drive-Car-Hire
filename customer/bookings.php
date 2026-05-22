<?php 
require_once '../config/config.php';
require_once '../config/db.php';

if (!isLoggedIn() || !hasRole(ROLE_CUSTOMER)) {
    redirect('auth/login.php');
}

include_once '../includes/header.php'; 

$user_id = $_SESSION['user_id'];
$bookings = $pdo->prepare("SELECT b.*, v.brand, v.model, v.main_image FROM bookings b 
                        JOIN vehicles v ON b.vehicle_id = v.id 
                        WHERE b.customer_id = ? 
                        ORDER BY b.created_at DESC");
$bookings->execute([$user_id]);
$all_bookings = $bookings->fetchAll();
?>

<section style="padding: 120px 0 60px;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <h2 class="text-gradient">My Bookings</h2>
            <a href="<?php echo BASE_URL; ?>vehicles/browse.php" class="btn btn-primary">New Booking <i class="fas fa-plus"></i></a>
        </div>

        <div class="card glass" style="padding: 30px;">
            <?php if (empty($all_bookings)): ?>
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--text-gray); margin-bottom: 20px;"></i>
                    <p style="color: var(--text-gray);">You have no bookings yet.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-gray);">
                                <th style="padding: 15px;">Vehicle</th>
                                <th style="padding: 15px;">Reference</th>
                                <th style="padding: 15px;">Date Range</th>
                                <th style="padding: 15px;">Total Price</th>
                                <th style="padding: 15px;">Status</th>
                                <th style="padding: 15px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_bookings as $b): ?>
                                <tr style="border-bottom: 1px solid var(--glass-border);">
                                    <td style="padding: 15px;">
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <img src="<?php echo $b['main_image']; ?>" style="width: 60px; height: 40px; border-radius: 5px; object-fit: cover;">
                                            <span><?php echo $b['brand'] . ' ' . $b['model']; ?></span>
                                        </div>
                                    </td>
                                    <td style="padding: 15px; font-weight: 600;"><?php echo $b['booking_reference']; ?></td>
                                    <td style="padding: 15px; font-size: 0.9rem;">
                                        <?php echo date('M d, Y', strtotime($b['pickup_date'])); ?> - <?php echo date('M d, Y', strtotime($b['return_date'])); ?>
                                    </td>
                                    <td style="padding: 15px; font-weight: 600;"><?php echo CURRENCY . ' ' . number_format($b['total_price']); ?></td>
                                    <td style="padding: 15px;">
                                        <span style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; background: rgba(230, 57, 70, 0.1); color: var(--primary-color);">
                                            <?php echo $b['status']; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <div style="display: flex; gap: 15px; align-items: center;">
                                            <a href="booking_details.php?id=<?php echo $b['id']; ?>" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.8rem;">Details</a>
                                            
                                            <?php if ($b['status'] === 'Pending'): ?>
                                                <a href="edit_booking.php?id=<?php echo $b['id']; ?>" class="action-btn" title="Edit" style="color: #f1c40f; font-size: 1.1rem;"><i class="fas fa-edit"></i></a>
                                            <?php endif; ?>

                                            <?php if (in_array($b['status'], ['Pending', 'Approved'])): ?>
                                                <button onclick="cancelBooking(<?php echo $b['id']; ?>)" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.8rem; border-color: var(--primary-color); color: var(--primary-color);">Cancel</button>
                                            <?php endif; ?>

                                            <?php if (in_array($b['status'], ['Cancelled', 'Rejected', 'Pending'])): ?>
                                                <button onclick="deleteBooking(<?php echo $b['id']; ?>)" class="action-btn" title="Delete" style="background: none; border: none; color: #666; cursor: pointer; padding: 0; font-size: 1.1rem;"><i class="fas fa-trash-alt"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
async function cancelBooking(id) {
    if (confirm('Are you sure you want to cancel this booking? The vehicle will be released for others.')) {
        try {
            const response = await fetch(`../api/bookings.php?action=cancel&id=${id}`);
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

async function deleteBooking(id) {
    if (confirm('Are you sure you want to remove this booking from your history? This action cannot be undone.')) {
        try {
            const response = await fetch(`../api/bookings.php?action=delete&id=${id}`);
            const data = await response.json();
            if (data.status === 'success') {
                notify('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                notify('error', data.message);
            }
        } catch (error) {
            notify('error', 'Something went wrong while deleting');
        }
    }
}
</script>

<?php include_once '../includes/footer.php'; ?>
