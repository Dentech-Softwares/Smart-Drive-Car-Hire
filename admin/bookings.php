<?php 
require_once '../config/db.php';
include_once 'includes/header.php'; 

$filter = $_GET['status'] ?? 'All';
$query = "SELECT b.*, v.brand, v.model, u.full_name FROM bookings b 
          JOIN vehicles v ON b.vehicle_id = v.id 
          JOIN users u ON b.customer_id = u.id";

if ($filter !== 'All') {
    $query .= " WHERE b.status = " . $pdo->quote($filter);
}

$query .= " ORDER BY b.created_at DESC";
$bookings = $pdo->query($query)->fetchAll();

// Get counts for tabs
$counts = [
    'All' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'Pending' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn(),
    'Approved' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Approved'")->fetchColumn(),
    'Ongoing' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Ongoing'")->fetchColumn(),
    'Completed' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Completed'")->fetchColumn()
];
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <h2 class="text-gradient">Manage Bookings</h2>
</div>

<!-- Status Tabs -->
<div style="display: flex; gap: 15px; margin-bottom: 30px; overflow-x: auto; padding-bottom: 10px;">
    <?php foreach ($counts as $status => $count): ?>
        <a href="?status=<?php echo $status; ?>" class="glass" style="padding: 10px 20px; border-radius: 12px; text-decoration: none; color: <?php echo $filter == $status ? 'var(--primary-color)' : 'var(--text-gray)'; ?>; border: 1px solid <?php echo $filter == $status ? 'var(--primary-color)' : 'var(--glass-border)'; ?>; white-space: nowrap; transition: var(--transition);">
            <?php echo $status; ?> 
            <span style="background: <?php echo $filter == $status ? 'var(--primary-color)' : 'rgba(255,255,255,0.1)'; ?>; color: <?php echo $filter == $status ? 'white' : 'var(--text-gray)'; ?>; padding: 2px 8px; border-radius: 8px; font-size: 0.75rem; margin-left: 8px;">
                <?php echo $count; ?>
            </span>
        </a>
    <?php endforeach; ?>
</div>

<div class="card" style="padding: 30px;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-gray);">
                    <th style="padding: 15px;">Reference</th>
                    <th style="padding: 15px;">Customer</th>
                    <th style="padding: 15px;">Vehicle</th>
                    <th style="padding: 15px;">Rental Mode</th>
                    <th style="padding: 15px;">Total Price</th>
                    <th style="padding: 15px;">Status</th>
                    <th style="padding: 15px;">Vehicle Status</th>
                    <th style="padding: 15px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): 
                    // Fetch current vehicle status
                    $v_stmt = $pdo->prepare("SELECT status FROM vehicles WHERE id = ?");
                    $v_stmt->execute([$b['vehicle_id']]);
                    $v_status = $v_stmt->fetchColumn();
                ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 15px; font-weight: 600;"><?php echo $b['booking_reference']; ?></td>
                        <td style="padding: 15px;"><?php echo $b['full_name']; ?></td>
                        <td style="padding: 15px;"><?php echo $b['brand'] . ' ' . $b['model']; ?></td>
                        <td style="padding: 15px;"><?php echo $b['rental_mode']; ?></td>
                        <td style="padding: 15px;"><?php echo CURRENCY . ' ' . number_format($b['total_price']); ?></td>
                        <td style="padding: 15px;">
                            <span style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; background: rgba(230, 57, 70, 0.1); color: var(--primary-color);">
                                <?php echo $b['status']; ?>
                            </span>
                        </td>
                        <td style="padding: 15px;">
                            <button onclick="declareVehicleStatus(<?php echo $b['vehicle_id']; ?>, '<?php echo $v_status; ?>')" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.75rem; border-color: <?php echo $v_status == 'Available' ? '#2ecc71' : 'var(--primary-color)'; ?>; color: <?php echo $v_status == 'Available' ? '#2ecc71' : 'var(--primary-color)'; ?>;">
                                <?php echo $v_status; ?> <i class="fas fa-edit"></i>
                            </button>
                        </td>
                        <td style="padding: 15px;">
                            <div style="display: flex; gap: 10px;">
                                <?php if ($b['status'] === 'Pending'): ?>
                                    <button onclick="updateBooking(<?php echo $b['id']; ?>, 'Approved')" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;">Approve</button>
                                    <button onclick="updateBooking(<?php echo $b['id']; ?>, 'Rejected')" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem; border-color: #666; color: #666;">Reject</button>
                                <?php else: ?>
                                    <a href="booking_details.php?id=<?php echo $b['id']; ?>" style="color: var(--accent-color);"><i class="fas fa-eye"></i></a>
                                <?php endif; ?>
                                <button onclick="deleteBooking(<?php echo $b['id']; ?>)" style="background: none; border: none; color: var(--primary-color); cursor: pointer; font-size: 1.1rem; padding: 0 5px;" title="Delete Booking"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php include_once 'includes/footer.php'; ?>

<!-- Vehicle Status Modal -->
<div id="statusModal" class="modal flex-center" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; padding: 20px;">
    <div class="glass" style="max-width: 400px; width: 100%; padding: 40px; position: relative;">
        <button onclick="closeModal('statusModal')" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        <h3 class="text-gradient" style="margin-bottom: 20px;">Declare Vehicle Status</h3>
        
        <form id="statusForm">
            <input type="hidden" name="id" id="status_v_id">
            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 10px; color: var(--text-gray);">Select New Status</label>
                <select name="status" id="status_select" class="glass" required>
                    <option value="Available">Available</option>
                    <option value="Booked">Booked</option>
                    <option value="Under Maintenance">Under Maintenance</option>
                    <option value="Reserved">Reserved</option>
                    <option value="Out of Service">Out of Service</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Update Status</button>
        </form>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function declareVehicleStatus(v_id, current_status) {
        document.getElementById('status_v_id').value = v_id;
        document.getElementById('status_select').value = current_status;
        openModal('statusModal');
    }

    document.addEventListener('DOMContentLoaded', () => {
        ajaxForm('statusForm', '../api/vehicles.php?action=update_status_simple', (data) => {
            if (data.status === 'success') {
                notify('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                notify('error', data.message);
            }
        });
    });

    async function updateBooking(id, status) {
        if (confirm(`Are you sure you want to ${status.toLowerCase()} this booking?`)) {
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

    async function deleteBooking(id) {
        if (confirm('Are you sure you want to PERMANENTLY delete this booking? This action cannot be undone and will remove all related payment records.')) {
            try {
                const response = await fetch(`../api/admin_actions.php?action=delete_booking&id=${id}`);
                const data = await response.json();
                if (data.status === 'success') {
                    notify('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    notify('error', data.message);
                }
            } catch (error) {
                notify('error', 'Something went wrong while deleting the booking.');
            }
        }
    }
</script>

<?php include_once 'includes/footer.php'; ?>
