<?php 
require_once '../config/db.php';
include_once 'includes/header.php'; 

$enquiries = $pdo->query("SELECT e.*, v.brand, v.model FROM enquiries e 
                         JOIN vehicles v ON e.vehicle_id = v.id 
                         ORDER BY e.created_at DESC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <h2 class="text-gradient">Vehicle Enquiries</h2>
</div>

<div class="card" style="padding: 30px;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-gray);">
                    <th style="padding: 15px;">Date</th>
                    <th style="padding: 15px;">Vehicle</th>
                    <th style="padding: 15px;">Customer</th>
                    <th style="padding: 15px;">Subject</th>
                    <th style="padding: 15px;">Status</th>
                    <th style="padding: 15px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enquiries as $e): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 15px; font-size: 0.9rem;"><?php echo date('M d, Y', strtotime($e['created_at'])); ?></td>
                        <td style="padding: 15px;"><?php echo $e['brand'] . ' ' . $e['model']; ?></td>
                        <td style="padding: 15px;">
                            <div style="font-weight: 600;"><?php echo $e['full_name']; ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-gray);"><?php echo $e['email']; ?></div>
                        </td>
                        <td style="padding: 15px;"><?php echo $e['subject']; ?></td>
                        <td style="padding: 15px;">
                            <?php 
                                $status_colors = [
                                    'Pending' => '#f1c40f',
                                    'Replied' => '#3498db',
                                    'Closed' => '#95a5a6'
                                ];
                                $color = $status_colors[$e['status']] ?? '#fff';
                            ?>
                            <span style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; background: <?php echo $color; ?>22; color: <?php echo $color; ?>;">
                                <?php echo $e['status']; ?>
                            </span>
                        </td>
                        <td style="padding: 15px;">
                            <div style="display: flex; gap: 10px;">
                                <button onclick="viewEnquiry(<?php echo htmlspecialchars(json_encode($e)); ?>)" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem;"><i class="fas fa-eye"></i></button>
                                <button onclick="updateEnquiryStatus(<?php echo $e['id']; ?>, 'Closed')" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem; border-color: #666; color: #666;"><i class="fas fa-times-circle"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Enquiry Details Modal -->
<div id="enquiryModal" class="modal flex-center" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; padding: 20px;">
    <div class="glass" style="max-width: 600px; width: 100%; padding: 40px; position: relative;">
        <button onclick="closeModal('enquiryModal')" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        <h3 class="text-gradient" style="margin-bottom: 10px;" id="modal_subject">Enquiry Details</h3>
        <p style="color: var(--text-gray); margin-bottom: 30px;" id="modal_vehicle"></p>
        
        <div style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid var(--glass-border);">
            <h4 style="margin-bottom: 10px; color: var(--primary-color);">Customer Information</h4>
            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <p style="font-size: 0.8rem; color: var(--text-gray);">Name</p>
                    <p id="modal_name" style="font-weight: 600;"></p>
                </div>
                <div>
                    <p style="font-size: 0.8rem; color: var(--text-gray);">Phone</p>
                    <p id="modal_phone" style="font-weight: 600;"></p>
                </div>
                <div>
                    <p style="font-size: 0.8rem; color: var(--text-gray);">Email</p>
                    <p id="modal_email" style="font-weight: 600;"></p>
                </div>
            </div>
        </div>

        <div style="margin-bottom: 30px;">
            <h4 style="margin-bottom: 10px; color: var(--primary-color);">Message</h4>
            <div class="glass" style="padding: 20px; border-radius: 10px; font-size: 0.95rem; line-height: 1.6;" id="modal_message"></div>
        </div>

        <div style="display: flex; gap: 15px;">
            <button onclick="updateEnquiryStatus(current_enquiry_id, 'Replied')" class="btn btn-primary" style="flex: 1; justify-content: center;">Mark as Replied</button>
            <a id="modal_reply_btn" href="" class="btn btn-outline" style="flex: 1; justify-content: center;"><i class="fas fa-envelope"></i> Reply via Email</a>
        </div>
    </div>
</div>

<script>
    let current_enquiry_id = null;

    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function viewEnquiry(enquiry) {
        current_enquiry_id = enquiry.id;
        document.getElementById('modal_subject').innerText = enquiry.subject;
        document.getElementById('modal_vehicle').innerText = `Regarding: ${enquiry.brand} ${enquiry.model}`;
        document.getElementById('modal_name').innerText = enquiry.full_name;
        document.getElementById('modal_phone').innerText = enquiry.phone || 'N/A';
        document.getElementById('modal_email').innerText = enquiry.email;
        document.getElementById('modal_message').innerText = enquiry.message;
        document.getElementById('modal_reply_btn').href = `mailto:${enquiry.email}?subject=RE: ${enquiry.subject}`;
        
        openModal('enquiryModal');
    }

    async function updateEnquiryStatus(id, status) {
        if (confirm(`Update enquiry status to ${status}?`)) {
            try {
                const response = await fetch(`../api/enquiries.php?action=update_status&id=${id}&status=${status}`);
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
