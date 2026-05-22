<?php 
require_once '../config/db.php';
include_once 'includes/header.php'; 

$payments = $pdo->query("SELECT p.*, b.booking_reference, u.full_name as customer_name 
                        FROM payments p 
                        JOIN bookings b ON p.booking_id = b.id 
                        JOIN users u ON b.customer_id = u.id 
                        ORDER BY p.payment_date DESC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <h2 class="text-gradient">Payment Transactions</h2>
</div>

<div class="card" style="padding: 30px;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-gray);">
                    <th style="padding: 15px;">Transaction ID</th>
                    <th style="padding: 15px;">Booking Ref</th>
                    <th style="padding: 15px;">Customer</th>
                    <th style="padding: 15px;">Amount</th>
                    <th style="padding: 15px;">Method</th>
                    <th style="padding: 15px;">Status</th>
                    <th style="padding: 15px;">Date</th>
                    <th style="padding: 15px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $p): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 15px; font-weight: 600;"><?php echo $p['transaction_id'] ?: 'N/A'; ?></td>
                        <td style="padding: 15px;"><?php echo $p['booking_reference']; ?></td>
                        <td style="padding: 15px;"><?php echo $p['customer_name']; ?></td>
                        <td style="padding: 15px; font-weight: 600;"><?php echo CURRENCY . ' ' . number_format($p['amount']); ?></td>
                        <td style="padding: 15px;"><?php echo $p['payment_method']; ?></td>
                        <td style="padding: 15px;">
                            <?php 
                                $status_colors = [
                                    'Paid' => '#2ecc71',
                                    'Pending' => '#f1c40f',
                                    'Failed' => '#e63946',
                                    'Refunded' => '#3498db'
                                ];
                                $color = $status_colors[$p['payment_status']] ?? '#fff';
                            ?>
                            <span style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; background: <?php echo $color; ?>22; color: <?php echo $color; ?>;">
                                <?php echo $p['payment_status']; ?>
                            </span>
                        </td>
                        <td style="padding: 15px;"><?php echo date('M d, Y H:i', strtotime($p['payment_date'])); ?></td>
                        <td style="padding: 15px;">
                            <button onclick="updatePaymentStatus(<?php echo $p['id']; ?>, 'Paid')" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;" <?php echo $p['payment_status'] === 'Paid' ? 'disabled' : ''; ?>>Verify</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    async function updatePaymentStatus(id, status) {
        if (confirm(`Mark this transaction as ${status}?`)) {
            try {
                const response = await fetch(`../api/admin_actions.php?action=update_payment&id=${id}&status=${status}`);
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
