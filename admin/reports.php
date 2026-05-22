<?php 
require_once '../config/db.php';
include_once 'includes/header.php'; 

// Fetch report data
$bookings_report = $pdo->query("SELECT b.*, v.brand, v.model, u.full_name, p.amount, p.payment_status FROM bookings b 
                                JOIN vehicles v ON b.vehicle_id = v.id 
                                JOIN users u ON b.customer_id = u.id 
                                LEFT JOIN payments p ON b.id = p.booking_id
                                ORDER BY b.created_at DESC")->fetchAll();

$total_revenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Paid'")->fetchColumn() ?: 0;
$total_bookings = count($bookings_report);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <h2 class="text-gradient">System Reports</h2>
    <button onclick="window.print()" class="btn btn-outline"><i class="fas fa-print"></i> Export to PDF</button>
</div>

<div class="grid" style="grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71;">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div>
            <h3 style="font-size: 1.8rem;"><?php echo CURRENCY . ' ' . number_format($total_revenue); ?></h3>
            <p style="color: var(--text-gray);">Total Realized Revenue</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(52, 152, 219, 0.1); color: #3498db;">
            <i class="fas fa-chart-line"></i>
        </div>
        <div>
            <h3 style="font-size: 1.8rem;"><?php echo $total_bookings; ?></h3>
            <p style="color: var(--text-gray);">Total Bookings Processed</p>
        </div>
    </div>
</div>

<div class="card" style="padding: 30px;">
    <h3 style="margin-bottom: 25px;">Detailed Transaction Report</h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-gray);">
                    <th style="padding: 15px;">Date</th>
                    <th style="padding: 15px;">Reference</th>
                    <th style="padding: 15px;">Customer</th>
                    <th style="padding: 15px;">Vehicle</th>
                    <th style="padding: 15px;">Amount</th>
                    <th style="padding: 15px;">Payment Status</th>
                    <th style="padding: 15px;">Booking Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings_report as $row): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 15px;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td style="padding: 15px; font-weight: 600;"><?php echo $row['booking_reference']; ?></td>
                        <td style="padding: 15px;"><?php echo $row['full_name']; ?></td>
                        <td style="padding: 15px;"><?php echo $row['brand'] . ' ' . $row['model']; ?></td>
                        <td style="padding: 15px;"><?php echo CURRENCY . ' ' . number_format($row['amount']); ?></td>
                        <td style="padding: 15px;">
                            <span style="color: <?php echo $row['payment_status'] == 'Paid' ? '#2ecc71' : '#e63946'; ?>;">
                                <?php echo $row['payment_status']; ?>
                            </span>
                        </td>
                        <td style="padding: 15px;"><?php echo $row['status']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    .sidebar, .btn, .text-gradient { display: none; }
    .main-content { margin-left: 0; padding: 0; }
    .card { border: none; box-shadow: none; }
    body { background: white; color: black; }
    .stat-card { border: 1px solid #ddd; }
}
</style>

<?php include_once 'includes/footer.php'; ?>
