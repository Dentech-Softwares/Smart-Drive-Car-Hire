<?php 
require_once '../config/config.php';
require_once '../config/db.php';

if (!isLoggedIn() || !hasRole(ROLE_CUSTOMER)) {
    redirect('auth/login.php');
}

include_once '../includes/header.php'; 

$user_id = $_SESSION['user_id'];
$payments = $pdo->prepare("SELECT p.*, b.booking_reference FROM payments p 
                        JOIN bookings b ON p.booking_id = b.id 
                        WHERE b.customer_id = ? 
                        ORDER BY p.payment_date DESC");
$payments->execute([$user_id]);
$all_payments = $payments->fetchAll();
?>

<section style="padding: 120px 0 60px;">
    <div class="container">
        <h2 class="text-gradient" style="margin-bottom: 40px;">Payment History</h2>

        <div class="card glass" style="padding: 30px;">
            <?php if (empty($all_payments)): ?>
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-credit-card" style="font-size: 3rem; color: var(--text-gray); margin-bottom: 20px;"></i>
                    <p style="color: var(--text-gray);">No payment records found.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-gray);">
                                <th style="padding: 15px;">Transaction ID</th>
                                <th style="padding: 15px;">Booking Ref</th>
                                <th style="padding: 15px;">Amount</th>
                                <th style="padding: 15px;">Method</th>
                                <th style="padding: 15px;">Status</th>
                                <th style="padding: 15px;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_payments as $p): ?>
                                <tr style="border-bottom: 1px solid var(--glass-border);">
                                    <td style="padding: 15px; font-weight: 600;"><?php echo $p['transaction_id'] ?: 'N/A'; ?></td>
                                    <td style="padding: 15px;"><?php echo $p['booking_reference']; ?></td>
                                    <td style="padding: 15px; font-weight: 600;"><?php echo CURRENCY . ' ' . number_format($p['amount']); ?></td>
                                    <td style="padding: 15px;"><?php echo $p['payment_method']; ?></td>
                                    <td style="padding: 15px;">
                                        <span style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; background: rgba(46, 204, 113, 0.1); color: #2ecc71;">
                                            <?php echo $p['payment_status']; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px; font-size: 0.9rem;"><?php echo date('M d, Y H:i', strtotime($p['payment_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include_once '../includes/footer.php'; ?>
