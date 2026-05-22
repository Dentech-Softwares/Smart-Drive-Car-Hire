<?php 
require_once '../config/db.php';
include_once 'includes/header.php'; 

$drivers = $pdo->query("SELECT u.*, d.status as driver_status, d.experience_years, d.rating, d.earnings 
                        FROM users u 
                        JOIN drivers d ON u.id = d.user_id 
                        WHERE u.role = 'driver' 
                        ORDER BY u.created_at DESC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <h2 class="text-gradient">Manage Drivers</h2>
</div>

<div class="card" style="padding: 30px;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-gray);">
                    <th style="padding: 15px;">Driver Name</th>
                    <th style="padding: 15px;">Contact</th>
                    <th style="padding: 15px;">Exp. (Years)</th>
                    <th style="padding: 15px;">Rating</th>
                    <th style="padding: 15px;">Status</th>
                    <th style="padding: 15px;">Earnings</th>
                    <th style="padding: 15px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($drivers as $d): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 15px;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($d['full_name']); ?>&background=random" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid var(--glass-border);">
                                <span><?php echo $d['full_name']; ?></span>
                            </div>
                        </td>
                        <td style="padding: 15px;">
                            <p style="font-size: 0.9rem;"><?php echo $d['email']; ?></p>
                            <p style="font-size: 0.8rem; color: var(--text-gray);"><?php echo $d['phone']; ?></p>
                        </td>
                        <td style="padding: 15px;"><?php echo $d['experience_years']; ?></td>
                        <td style="padding: 15px;">
                            <span style="color: #ffc107;"><i class="fas fa-star"></i> <?php echo $d['rating']; ?></span>
                        </td>
                        <td style="padding: 15px;">
                            <?php 
                                $status_colors = [
                                    'available' => '#2ecc71',
                                    'on_trip' => '#3498db',
                                    'suspended' => '#e63946',
                                    'inactive' => '#95a5a6'
                                ];
                                $color = $status_colors[$d['driver_status']] ?? '#fff';
                            ?>
                            <span style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; background: <?php echo $color; ?>22; color: <?php echo $color; ?>; text-transform: capitalize;">
                                <?php echo $d['driver_status']; ?>
                            </span>
                        </td>
                        <td style="padding: 15px; font-weight: 600;"><?php echo CURRENCY . ' ' . number_format($d['earnings']); ?></td>
                        <td style="padding: 15px;">
                            <div style="display: flex; gap: 10px;">
                                <button onclick="updateDriverStatus(<?php echo $d['id']; ?>, 'suspended')" style="background: none; border: none; color: var(--primary-color); cursor: pointer;" title="Suspend"><i class="fas fa-ban"></i></button>
                                <button onclick="updateDriverStatus(<?php echo $d['id']; ?>, 'available')" style="background: none; border: none; color: #2ecc71; cursor: pointer;" title="Activate"><i class="fas fa-check-circle"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    async function updateDriverStatus(id, status) {
        if (confirm(`Are you sure you want to set this driver to ${status}?`)) {
            try {
                const response = await fetch(`../api/admin_actions.php?action=update_driver&id=${id}&status=${status}`);
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
