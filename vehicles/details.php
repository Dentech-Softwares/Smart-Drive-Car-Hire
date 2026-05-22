<?php 
require_once '../config/config.php';
require_once '../config/db.php';
include_once '../includes/header.php'; 

$id = $_GET['id'] ?? '';
if (!$id) redirect('vehicles/browse.php');

$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->execute([$id]);
$v = $stmt->fetch();

if (!$v) redirect('vehicles/browse.php');

// Fetch additional images
$stmt = $pdo->prepare("SELECT * FROM vehicle_images WHERE vehicle_id = ?");
$stmt->execute([$id]);
$gallery = $stmt->fetchAll();
?>

<section style="padding: 120px 0 60px;">
    <div class="container">
        <div class="grid" style="grid-template-columns: 1.2fr 0.8fr; gap: 60px;">
            <div data-aos="fade-right">
                <div style="height: 450px; border-radius: 20px; overflow: hidden; margin-bottom: 30px; border: 1px solid var(--glass-border);">
                    <img id="mainVehicleImage" src="<?php echo $v['main_image']; ?>" style="width: 100%; height: 100%; object-fit: cover; transition: var(--transition);">
                </div>
                <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 15px;">
                    <div class="glass" style="height: 100px; border-radius: 10px; overflow: hidden; cursor: pointer; border: 2px solid var(--primary-color);">
                        <img src="<?php echo $v['main_image']; ?>" onclick="switchImage(this.src)" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <?php foreach ($gallery as $img): ?>
                        <div class="glass" style="height: 100px; border-radius: 10px; overflow: hidden; cursor: pointer;">
                            <img src="<?php echo $img['image_path']; ?>" onclick="switchImage(this.src)" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.7; transition: var(--transition);" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

<script>
    function switchImage(src) {
        const mainImg = document.getElementById('mainVehicleImage');
        mainImg.style.opacity = '0';
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = '1';
        }, 200);
        
        // Highlight active thumbnail
        document.querySelectorAll('.grid img').forEach(img => {
            img.parentElement.style.borderColor = 'transparent';
        });
        event.target.parentElement.style.borderColor = 'var(--primary-color)';
    }
</script>

            <div data-aos="fade-left">
                <div style="margin-bottom: 30px;">
                    <span style="background: var(--primary-color); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; text-transform: uppercase;"><?php echo $v['category']; ?></span>
                    <h1 style="font-size: 3rem; margin-top: 15px;"><?php echo $v['brand'] . ' ' . $v['model']; ?></h1>
                    <p style="color: var(--text-gray); font-size: 1.1rem; margin-top: 10px;"><?php echo $v['plate_number']; ?></p>
                </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px;">
                    <div class="card glass" style="padding: 15px; display: flex; align-items: center; gap: 15px;">
                        <i class="fas fa-cog" style="color: var(--primary-color); font-size: 1.5rem;"></i>
                        <div>
                            <p style="color: var(--text-gray); font-size: 0.8rem;">Transmission</p>
                            <p style="font-weight: 600;"><?php echo $v['transmission']; ?></p>
                        </div>
                    </div>
                    <div class="card glass" style="padding: 15px; display: flex; align-items: center; gap: 15px;">
                        <i class="fas fa-gas-pump" style="color: var(--primary-color); font-size: 1.5rem;"></i>
                        <div>
                            <p style="color: var(--text-gray); font-size: 0.8rem;">Fuel Type</p>
                            <p style="font-weight: 600;"><?php echo $v['fuel_type']; ?></p>
                        </div>
                    </div>
                    <div class="card glass" style="padding: 15px; display: flex; align-items: center; gap: 15px;">
                        <i class="fas fa-users" style="color: var(--primary-color); font-size: 1.5rem;"></i>
                        <div>
                            <p style="color: var(--text-gray); font-size: 0.8rem;">Capacity</p>
                            <p style="font-weight: 600;"><?php echo $v['seating_capacity']; ?> Seats</p>
                        </div>
                    </div>
                    <div class="card glass" style="padding: 15px; display: flex; align-items: center; gap: 15px;">
                        <i class="fas fa-check-circle" style="color: var(--primary-color); font-size: 1.5rem;"></i>
                        <div>
                            <p style="color: var(--text-gray); font-size: 0.8rem;">Status</p>
                            <p style="font-weight: 600;"><?php echo $v['status']; ?></p>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 40px;">
                    <h3 style="margin-bottom: 15px;">Description</h3>
                    <p style="color: var(--text-gray); line-height: 1.8;"><?php echo $v['description'] ?: 'No description available for this vehicle. Experience the ultimate comfort and performance with our premium car rental service.'; ?></p>
                </div>

                <div class="glass" style="padding: 30px; border: 1px solid var(--primary-color); margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <span style="color: var(--text-gray);">Price per day</span>
                        <h2 class="text-gradient" style="font-size: 2rem;"><?php echo CURRENCY . ' ' . number_format($v['price_per_day']); ?></h2>
                    </div>
                    <a href="book.php?id=<?php echo $v['id']; ?>" class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 1.2rem; padding: 15px; margin-bottom: 15px;">Book This Car Now</a>
                    <button onclick="openModal('enquiryModal')" class="btn btn-outline" style="width: 100%; justify-content: center; padding: 12px;">Make an Enquiry</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Enquiry Modal -->
<div id="enquiryModal" class="modal flex-center" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; padding: 20px;">
    <div class="glass" style="max-width: 600px; width: 100%; padding: 40px; position: relative;">
        <button onclick="closeModal('enquiryModal')" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        <h2 class="text-gradient" style="margin-bottom: 10px;">Vehicle Enquiry</h2>
        <p style="color: var(--text-gray); margin-bottom: 30px;">Have questions about the <?php echo $v['brand'] . ' ' . $v['model']; ?>? Send us a message.</p>
        
        <form id="enquiryForm">
            <input type="hidden" name="vehicle_id" value="<?php echo $v['id']; ?>">
            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Full Name</label>
                    <input type="text" name="full_name" value="<?php echo $_SESSION['full_name'] ?? ''; ?>" required class="glass">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Email Address</label>
                    <input type="email" name="email" value="<?php echo $_SESSION['email'] ?? ''; ?>" required class="glass">
                </div>
            </div>
            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Phone Number</label>
                    <input type="text" name="phone" value="<?php echo $_SESSION['phone'] ?? ''; ?>" class="glass">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Subject</label>
                    <input type="text" name="subject" value="Enquiry for <?php echo $v['brand'] . ' ' . $v['model']; ?>" required class="glass">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 30px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Message</label>
                <textarea name="message" rows="5" required class="glass" placeholder="Type your questions here..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Send Enquiry</button>
        </form>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    document.addEventListener('DOMContentLoaded', () => {
        ajaxForm('enquiryForm', '../api/enquiries.php?action=create', (data) => {
            if (data.status === 'success') {
                notify('success', data.message);
                closeModal('enquiryModal');
                document.getElementById('enquiryForm').reset();
            } else {
                notify('error', data.message);
            }
        });
    });
</script>

<?php include_once '../includes/footer.php'; ?>
