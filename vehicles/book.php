<?php 
require_once '../config/config.php';
require_once '../config/db.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    redirect('auth/login.php');
}

include_once '../includes/header.php'; 

$vehicle_id = $_GET['id'] ?? '';
if (!$vehicle_id) redirect('vehicles/browse.php');

$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ? AND status = 'Available'");
$stmt->execute([$vehicle_id]);
$vehicle = $stmt->fetch();

if (!$vehicle) redirect('vehicles/browse.php');
?>

<section style="padding: 120px 0 60px;">
    <div class="container">
        <div class="grid" style="grid-template-columns: 1.5fr 1fr; gap: 40px;">
            <div>
                <h2 class="text-gradient" style="margin-bottom: 30px;">Complete Your Booking</h2>
                
                <div class="card glass" style="padding: 40px; margin-bottom: 30px;">
                    <form id="bookingForm" enctype="multipart/form-data">
                        <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                        
                        <div style="margin-bottom: 30px;">
                            <h4 style="margin-bottom: 20px; color: var(--primary-color);">Rental Mode</h4>
                            <div class="role-selector" style="display: flex; gap: 15px;">
                                <label class="glass" style="flex: 1; padding: 20px; cursor: pointer; text-align: center; border: 1px solid var(--glass-border); border-radius: 12px; transition: var(--transition);">
                                    <input type="radio" name="rental_mode" value="Self Drive" checked style="display: none;">
                                    <i class="fas fa-user-ninja" style="display: block; margin-bottom: 10px; font-size: 1.5rem;"></i>
                                    Self Drive
                                </label>
                                <label class="glass" style="flex: 1; padding: 20px; cursor: pointer; text-align: center; border: 1px solid var(--glass-border); border-radius: 12px; transition: var(--transition);">
                                    <input type="radio" name="rental_mode" value="With Driver" style="display: none;">
                                    <i class="fas fa-user-tie" style="display: block; margin-bottom: 10px; font-size: 1.5rem;"></i>
                                    With Driver
                                </label>
                            </div>
                        </div>

                        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Pickup Date & Time</label>
                                <input type="datetime-local" name="pickup_date" id="pickup_date" required class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Number of Days</label>
                                <input type="number" name="rental_days_input" id="rental_days_input" min="1" value="1" required class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                                <input type="hidden" name="return_date" id="return_date">
                            </div>
                        </div>

                        <div id="returnTimeInfo" class="glass" style="padding: 15px; border-radius: 8px; margin-bottom: 30px; border: 1px dashed var(--accent-color); display: none;">
                            <p style="font-size: 0.9rem; color: var(--text-gray);">Expected Return Time:</p>
                            <h4 id="calculatedReturnTime" style="color: var(--accent-color); margin-top: 5px;">-</h4>
                        </div>

                        <div id="selfDriveFields" style="margin-bottom: 30px;">
                            <h4 style="margin-bottom: 20px; color: var(--primary-color);">Required Documents</h4>
                            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Upload ID/Passport</label>
                                    <input type="file" name="id_document" class="glass" style="width: 100%; padding: 10px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                                </div>
                                <div class="form-group">
                                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Driving License</label>
                                    <input type="file" name="license_document" class="glass" style="width: 100%; padding: 10px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom: 30px;">
                            <h4 style="margin-bottom: 20px; color: var(--primary-color);">Payment Method</h4>
                            <select name="payment_method" class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                                <option value="M-Pesa">M-Pesa</option>
                                <option value="Card">Credit/Debit Card</option>
                                <option value="Cash">Cash on Pickup</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 1.2rem; padding: 15px;">Confirm Booking & Pay</button>
                    </form>
                </div>

                <div class="card glass" style="padding: 30px;">
                    <h4 style="margin-bottom: 20px; color: var(--primary-color);"><i class="fas fa-calendar-check"></i> Vehicle Availability Schedule</h4>
                    <p style="color: var(--text-gray); font-size: 0.9rem; margin-bottom: 20px;">The following dates are already booked for this vehicle:</p>
                    <div id="availabilitySchedule" style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <p style="color: var(--text-gray); font-style: italic;">Loading schedule...</p>
                    </div>
                </div>
            </div>

            <div>
                <div class="card glass" style="padding: 30px; position: sticky; top: 120px;">
                    <h3 style="margin-bottom: 20px;">Booking Summary</h3>
                    <div style="display: flex; gap: 20px; margin-bottom: 25px;">
                        <img src="<?php echo $vehicle['main_image']; ?>" style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px;">
                        <div>
                            <h4 style="margin-bottom: 5px;"><?php echo $vehicle['brand'] . ' ' . $vehicle['model']; ?></h4>
                            <p style="color: var(--text-gray); font-size: 0.9rem;"><?php echo $vehicle['category']; ?> • <?php echo $vehicle['transmission']; ?></p>
                        </div>
                    </div>

                    <div style="border-top: 1px solid var(--glass-border); padding-top: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <span style="color: var(--text-gray);">Price per day</span>
                            <span><?php echo CURRENCY . ' ' . number_format($vehicle['price_per_day']); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <span style="color: var(--text-gray);">Rental Days</span>
                            <span id="rentalDays">0</span>
                        </div>
                        <div id="driverFeeRow" style="display: none; justify-content: space-between; margin-bottom: 15px;">
                            <span style="color: var(--text-gray);">Driver Fee (<span id="driverDays">0</span> days)</span>
                            <span id="driverTotal"><?php echo CURRENCY; ?> 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 20px; padding-top: 20px; border-top: 1px dashed var(--glass-border); font-size: 1.3rem; font-weight: 700;">
                            <span>Total</span>
                            <span class="text-gradient" id="totalPrice"><?php echo CURRENCY; ?> 0</span>
                        </div>
                    </div>
                </div>
            </div>
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
    const pickupInput = document.getElementById('pickup_date');
    const daysInput = document.getElementById('rental_days_input');
    const returnInput = document.getElementById('return_date');
    const rentalDaysText = document.getElementById('rentalDays');
    const driverDaysText = document.getElementById('driverDays');
    const driverTotalText = document.getElementById('driverTotal');
    const totalPriceText = document.getElementById('totalPrice');
    const driverFeeRow = document.getElementById('driverFeeRow');
    const selfDriveFields = document.getElementById('selfDriveFields');
    const rentalModeInputs = document.querySelectorAll('input[name="rental_mode"]');
    const returnTimeInfo = document.getElementById('returnTimeInfo');
    const calculatedReturnTime = document.getElementById('calculatedReturnTime');
    const scheduleContainer = document.getElementById('availabilitySchedule');

    const basePrice = <?php echo $vehicle['price_per_day']; ?>;
    const driverDailyFee = 1000;

    // Fetch and display booked dates
    async function loadSchedule() {
        try {
            const response = await fetch(`../api/bookings.php?action=get_booked_dates&vehicle_id=<?php echo $vehicle['id']; ?>`);
            const data = await response.json();
            
            if (data.length === 0) {
                scheduleContainer.innerHTML = '<p style="color: #2ecc71; font-weight: 600;"><i class="fas fa-check-circle"></i> This vehicle is available for all future dates.</p>';
                return;
            }

            scheduleContainer.innerHTML = '';
            data.forEach(booking => {
                const pickup = new Date(booking.pickup_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
                const returnD = new Date(booking.return_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
                
                const badge = document.createElement('div');
                badge.className = 'glass';
                badge.style.padding = '8px 15px';
                badge.style.borderRadius = '20px';
                badge.style.fontSize = '0.85rem';
                badge.style.border = '1px solid rgba(230, 57, 70, 0.3)';
                badge.style.background = 'rgba(230, 57, 70, 0.05)';
                badge.style.color = 'var(--primary-color)';
                badge.innerHTML = `<i class="fas fa-ban" style="margin-right: 5px;"></i> ${pickup} - ${returnD}`;
                scheduleContainer.appendChild(badge);
            });
        } catch (error) {
            scheduleContainer.innerHTML = '<p style="color: var(--primary-color);">Failed to load schedule.</p>';
        }
    }

    loadSchedule();

    function calculateTotal() {
        const pickup = new Date(pickupInput.value);
        const days = parseInt(daysInput.value) || 0;

        if (pickup && days > 0) {
            // Calculate return date
            const returnDate = new Date(pickup.getTime() + (days * 24 * 60 * 60 * 1000));
            
            // Format for hidden input (MySQL format)
            const year = returnDate.getFullYear();
            const month = String(returnDate.getMonth() + 1).padStart(2, '0');
            const day = String(returnDate.getDate()).padStart(2, '0');
            const hours = String(returnDate.getHours()).padStart(2, '0');
            const mins = String(returnDate.getMinutes()).padStart(2, '0');
            returnInput.value = `${year}-${month}-${day} ${hours}:${mins}:00`;

            // Display return time
            returnTimeInfo.style.display = 'block';
            calculatedReturnTime.innerText = returnDate.toLocaleString('en-GB', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            rentalDaysText.innerText = days;

            let total = (days * basePrice);
            
            const mode = document.querySelector('input[name="rental_mode"]:checked').value;
            if (mode === 'With Driver') {
                const driverFee = (days * driverDailyFee);
                total += driverFee;
                driverDaysText.innerText = days;
                driverTotalText.innerText = '<?php echo CURRENCY; ?> ' + driverFee.toLocaleString();
                driverFeeRow.style.display = 'flex';
                selfDriveFields.style.display = 'none';
            } else {
                driverFeeRow.style.display = 'none';
                selfDriveFields.style.display = 'block';
            }

            totalPriceText.innerText = '<?php echo CURRENCY; ?> ' + total.toLocaleString();
        }
    }

    pickupInput.addEventListener('change', calculateTotal);
    daysInput.addEventListener('input', calculateTotal);
    rentalModeInputs.forEach(input => input.addEventListener('change', calculateTotal));

    ajaxForm('bookingForm', '../api/bookings.php?action=create', (data) => {
        if (data.status === 'success') {
            notify('success', data.message);
            setTimeout(() => {
                window.location.href = '../customer/bookings.php';
            }, 2000);
        } else {
            notify('error', data.message);
        }
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>
