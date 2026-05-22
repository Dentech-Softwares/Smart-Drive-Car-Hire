<?php 
require_once '../config/config.php';
require_once '../config/db.php';

if (!isLoggedIn()) {
    redirect('auth/login.php');
}

include_once '../includes/header.php'; 

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT u.*, c.id_passport_number, c.driving_license_number, c.emergency_contact_name, c.emergency_contact_phone 
                    FROM users u 
                    LEFT JOIN customers c ON u.id = c.user_id 
                    WHERE u.id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<section style="padding: 120px 0 60px;">
    <div class="container">
        <h2 class="text-gradient" style="margin-bottom: 40px;">My Profile</h2>

        <div class="grid" style="grid-template-columns: 1fr 2fr; gap: 40px;">
            <div>
                <div class="card glass" style="padding: 30px; text-align: center;">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=e63946&color=fff" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary-color); margin-bottom: 20px;">
                    <h3 style="margin-bottom: 10px;"><?php echo $user['full_name']; ?></h3>
                    <p style="color: var(--text-gray); font-size: 0.9rem; margin-bottom: 20px;"><?php echo $user['role']; ?></p>
                    <button class="btn btn-outline" style="width: 100%; justify-content: center;">Change Avatar</button>
                </div>
            </div>

            <div>
                <div class="card glass" style="padding: 40px;">
                    <form id="profileForm">
                        <h4 style="margin-bottom: 25px; color: var(--primary-color);">Personal Information</h4>
                        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Full Name</label>
                                <input type="text" name="full_name" value="<?php echo $user['full_name']; ?>" class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Phone Number</label>
                                <input type="text" name="phone" value="<?php echo $user['phone']; ?>" class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Email Address</label>
                                <input type="email" value="<?php echo $user['email']; ?>" class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;" readonly>
                            </div>
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">ID/Passport Number</label>
                                <input type="text" name="id_passport_number" value="<?php echo $user['id_passport_number']; ?>" class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                            </div>
                        </div>

                        <h4 style="margin-bottom: 25px; color: var(--primary-color);">Emergency Contact</h4>
                        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Contact Name</label>
                                <input type="text" name="emergency_contact_name" value="<?php echo $user['emergency_contact_name']; ?>" class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Contact Phone</label>
                                <input type="text" name="emergency_contact_phone" value="<?php echo $user['emergency_contact_phone']; ?>" class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    ajaxForm('profileForm', '../api/user_actions.php?action=update_profile', (data) => {
        if (data.status === 'success') {
            notify('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            notify('error', data.message);
        }
    });
</script>

<?php include_once '../includes/footer.php'; ?>
