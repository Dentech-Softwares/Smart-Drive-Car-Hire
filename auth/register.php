<?php include_once '../includes/header.php'; ?>

<section class="auth-section flex-center" style="min-height: 100vh; padding: 120px 0 60px;">
    <div class="container">
        <div class="auth-container glass animate-fade" style="max-width: 600px; margin: 0 auto; padding: 40px;">
            <h2 class="text-gradient" style="text-align: center; margin-bottom: 30px; font-size: 2rem;">Join Smart Drive Car Rental</h2>
            
            <form id="registerForm">
                <div class="role-selector" style="display: flex; gap: 10px; margin-bottom: 30px;">
                    <label class="glass" style="flex: 1; padding: 15px; cursor: pointer; text-align: center; border: 1px solid var(--glass-border); border-radius: 10px; transition: var(--transition);">
                        <input type="radio" name="role" value="customer" checked style="display: none;">
                        <i class="fas fa-user" style="display: block; margin-bottom: 5px; font-size: 1.2rem;"></i>
                        Customer
                    </label>
                    <label class="glass" style="flex: 1; padding: 15px; cursor: pointer; text-align: center; border: 1px solid var(--glass-border); border-radius: 10px; transition: var(--transition);">
                        <input type="radio" name="role" value="driver" style="display: none;">
                        <i class="fas fa-id-card" style="display: block; margin-bottom: 5px; font-size: 1.2rem;"></i>
                        Driver
                    </label>
                    <label class="glass" style="flex: 1; padding: 15px; cursor: pointer; text-align: center; border: 1px solid var(--glass-border); border-radius: 10px; transition: var(--transition);">
                        <input type="radio" name="role" value="admin" style="display: none;">
                        <i class="fas fa-user-shield" style="display: block; margin-bottom: 5px; font-size: 1.2rem;"></i>
                        Admin
                    </label>
                </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Full Name</label>
                        <input type="text" name="full_name" required class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px; outline: none;">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Phone Number</label>
                        <input type="text" name="phone" required class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px; outline: none;">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Email Address</label>
                    <input type="email" name="email" required class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px; outline: none;">
                </div>
                
                <div class="grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Password</label>
                        <input type="password" name="password" required class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px; outline: none;">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Confirm Password</label>
                        <input type="password" name="confirm_password" required class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px; outline: none;">
                    </div>
                </div>

                <!-- Driver specific fields (hidden by default) -->
                <div id="driverFields" style="display: none;">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Driving License Number</label>
                        <input type="text" name="license_number" class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px; outline: none;">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 1.1rem; margin-top: 20px;">
                    Create Account <i class="fas fa-user-plus"></i>
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 30px; color: var(--text-gray);">
                Already have an account? <a href="login.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Login Here</a>
            </p>
        </div>
    </div>
</section>

<style>
.role-selector label:has(input:checked) {
    border-color: var(--primary-color);
    background: rgba(230, 57, 70, 0.1);
    color: var(--primary-color);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const roleInputs = document.querySelectorAll('input[name="role"]');
    const driverFields = document.getElementById('driverFields');

    roleInputs.forEach(input => {
        input.addEventListener('change', () => {
            if (input.value === 'driver') {
                driverFields.style.display = 'block';
                driverFields.querySelectorAll('input').forEach(i => i.required = true);
            } else {
                driverFields.style.display = 'none';
                driverFields.querySelectorAll('input').forEach(i => i.required = false);
            }
        });
    });

    ajaxForm('registerForm', '../api/auth.php?action=register', (data) => {
        if (data.status === 'success') {
            notify('success', data.message);
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        } else {
            notify('error', data.message);
        }
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>
