<?php include_once '../includes/header.php'; ?>

<section class="auth-section flex-center" style="min-height: 100vh; padding-top: 100px;">
    <div class="container">
        <div class="auth-container glass animate-fade" style="max-width: 500px; margin: 0 auto; padding: 40px;">
            <h2 class="text-gradient" style="text-align: center; margin-bottom: 30px; font-size: 2rem;">Welcome Back</h2>
            
            <form id="loginForm">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Email Address</label>
                    <input type="email" name="email" required class="glass">
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Password</label>
                    <input type="password" name="password" required class="glass">
                </div>
                
                <div style="text-align: right; margin-bottom: 20px;">
                    <a href="#" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem;">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 1.1rem;">
                    Sign In <i class="fas fa-arrow-right"></i>
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 30px; color: var(--text-gray);">
                Don't have an account? <a href="register.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Register Now</a>
            </p>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    ajaxForm('loginForm', '../api/auth.php?action=login', (data) => {
        if (data.status === 'success') {
            notify('success', data.message);
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            notify('error', data.message);
        }
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>
