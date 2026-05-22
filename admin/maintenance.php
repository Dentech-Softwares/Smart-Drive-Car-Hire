<?php 
require_once '../config/db.php';
include_once 'includes/header.php'; 

$maintenance_logs = $pdo->query("SELECT m.*, v.brand, v.model FROM maintenance m JOIN vehicles v ON m.vehicle_id = v.id ORDER BY m.service_date DESC")->fetchAll();
$vehicles = $pdo->query("SELECT id, brand, model FROM vehicles")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <h2 class="text-gradient">Vehicle Maintenance</h2>
    <button onclick="openModal('addLogModal')" class="btn btn-primary"><i class="fas fa-plus"></i> Add Service Log</button>
</div>

<div class="card" style="padding: 30px;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-gray);">
                    <th style="padding: 15px;">Vehicle</th>
                    <th style="padding: 15px;">Service Type</th>
                    <th style="padding: 15px;">Date</th>
                    <th style="padding: 15px;">Cost</th>
                    <th style="padding: 15px;">Next Service</th>
                    <th style="padding: 15px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($maintenance_logs as $log): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 15px; font-weight: 600;"><?php echo $log['brand'] . ' ' . $log['model']; ?></td>
                        <td style="padding: 15px;"><?php echo $log['service_type']; ?></td>
                        <td style="padding: 15px;"><?php echo date('M d, Y', strtotime($log['service_date'])); ?></td>
                        <td style="padding: 15px;"><?php echo CURRENCY . ' ' . number_format($log['cost']); ?></td>
                        <td style="padding: 15px; color: var(--primary-color);"><?php echo date('M d, Y', strtotime($log['next_service_date'])); ?></td>
                        <td style="padding: 15px;">
                            <span style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; background: rgba(46, 204, 113, 0.1); color: #2ecc71;">
                                Completed
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Log Modal -->
<div id="addLogModal" class="modal flex-center" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; padding: 20px;">
    <div class="glass" style="max-width: 600px; width: 100%; padding: 40px; position: relative;">
        <button onclick="closeModal('addLogModal')" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        <h2 class="text-gradient" style="margin-bottom: 30px;">Add Maintenance Log</h2>
        
        <form id="addLogForm">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Select Vehicle</label>
                <select name="vehicle_id" required class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                    <?php foreach ($vehicles as $v): ?>
                        <option value="<?php echo $v['id']; ?>"><?php echo $v['brand'] . ' ' . $v['model']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Service Type</label>
                    <input type="text" name="service_type" placeholder="e.g. Oil Change" required class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Cost</label>
                    <input type="number" name="cost" required class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                </div>
            </div>
            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Service Date</label>
                    <input type="date" name="service_date" required class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Next Service Date</label>
                    <input type="date" name="next_service_date" required class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px; justify-content: center;">Save Log</button>
        </form>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    document.addEventListener('DOMContentLoaded', () => {
        ajaxForm('addLogForm', '../api/maintenance.php?action=add', (data) => {
            if (data.status === 'success') {
                notify('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                notify('error', data.message);
            }
        });
    });
</script>

<?php include_once 'includes/footer.php'; ?>
