<?php 
require_once '../config/db.php';
include_once 'includes/header.php'; 

$customers = $pdo->query("SELECT u.*, c.id_passport_number, 
                         (SELECT COUNT(*) FROM bookings WHERE customer_id = u.id) as total_bookings,
                         (SELECT SUM(amount) FROM payments p JOIN bookings b ON p.booking_id = b.id WHERE b.customer_id = u.id AND p.payment_status = 'Paid') as total_spent
                         FROM users u 
                         JOIN customers c ON u.id = c.user_id 
                         WHERE u.role = 'customer' 
                         ORDER BY u.created_at DESC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <h2 class="text-gradient">Manage Customers</h2>
</div>

<div class="card" style="padding: 30px;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-gray);">
                    <th style="padding: 15px;">Customer Name</th>
                    <th style="padding: 15px;">Contact</th>
                    <th style="padding: 15px;">ID/Passport</th>
                    <th style="padding: 15px;">Total Bookings</th>
                    <th style="padding: 15px;">Total Spent</th>
                    <th style="padding: 15px;">Joined Date</th>
                    <th style="padding: 15px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $c): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 15px;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($c['full_name']); ?>&background=random" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid var(--glass-border);">
                                <span><?php echo $c['full_name']; ?></span>
                            </div>
                        </td>
                        <td style="padding: 15px;">
                            <p style="font-size: 0.9rem;"><?php echo $c['email']; ?></p>
                            <p style="font-size: 0.8rem; color: var(--text-gray);"><?php echo $c['phone']; ?></p>
                        </td>
                        <td style="padding: 15px;"><?php echo $c['id_passport_number'] ?: 'Not Provided'; ?></td>
                        <td style="padding: 15px; text-align: center;"><?php echo $c['total_bookings']; ?></td>
                        <td style="padding: 15px; font-weight: 600;"><?php echo CURRENCY . ' ' . number_format($c['total_spent'] ?: 0); ?></td>
                        <td style="padding: 15px;"><?php echo date('M d, Y', strtotime($c['created_at'])); ?></td>
                        <td style="padding: 15px;">
                            <a href="customer_details.php?id=<?php echo $c['id']; ?>" style="color: var(--accent-color);"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
