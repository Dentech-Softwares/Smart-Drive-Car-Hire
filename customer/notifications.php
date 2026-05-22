<?php 
require_once '../config/config.php';
require_once '../config/db.php';

if (!isLoggedIn() || !hasRole(ROLE_CUSTOMER)) {
    redirect('auth/login.php');
}

include_once '../includes/header.php'; 

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Mark all as read
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
?>

<section style="padding: 120px 0 60px;">
    <div class="container" style="max-width: 800px;">
        <h2 class="text-gradient" style="margin-bottom: 40px;">Your Notifications</h2>

        <?php if (empty($notifications)): ?>
            <div class="glass" style="padding: 60px; text-align: center;">
                <i class="fas fa-bell-slash" style="font-size: 3rem; color: var(--text-gray); margin-bottom: 20px;"></i>
                <p style="color: var(--text-gray);">No notifications yet.</p>
            </div>
        <?php else: ?>
            <div class="grid" style="gap: 20px;">
                <?php foreach ($notifications as $n): ?>
                    <div class="card glass <?php echo !$n['is_read'] ? 'new-notification' : ''; ?>" style="padding: 25px; border-left: 4px solid <?php echo !$n['is_read'] ? 'var(--primary-color)' : 'var(--glass-border)'; ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                            <h4 style="color: var(--primary-color);"><?php echo h($n['title']); ?></h4>
                            <span style="font-size: 0.8rem; color: var(--text-gray);"><?php echo date('M d, H:i', strtotime($n['created_at'])); ?></span>
                        </div>
                        <p style="color: var(--text-light);"><?php echo h($n['message']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.new-notification {
    background: rgba(230, 57, 70, 0.05);
}
</style>

<?php include_once '../includes/footer.php'; ?>
