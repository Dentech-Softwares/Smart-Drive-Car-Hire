<?php 
require_once '../config/db.php';
include_once 'includes/header.php'; 

$vehicles = $pdo->query("SELECT * FROM vehicles ORDER BY created_at DESC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <h2 class="text-gradient">Manage Vehicles</h2>
    <button onclick="openModal('addVehicleModal')" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Vehicle</button>
</div>

<div class="card" style="padding: 30px;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-gray);">
                    <th style="padding: 15px;">Vehicle</th>
                    <th style="padding: 15px;">Category</th>
                    <th style="padding: 15px;">Plate No.</th>
                    <th style="padding: 15px;">Price/Day</th>
                    <th style="padding: 15px;">Status</th>
                    <th style="padding: 15px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehicles as $v): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 15px;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <img src="<?php echo $v['main_image'] ?: BASE_URL . 'assets/images/car_placeholder.png'; ?>" style="width: 50px; height: 35px; border-radius: 5px; object-fit: cover;">
                                <span><?php echo $v['brand'] . ' ' . $v['model']; ?></span>
                            </div>
                        </td>
                        <td style="padding: 15px;"><?php echo $v['category']; ?></td>
                        <td style="padding: 15px;"><?php echo $v['plate_number']; ?></td>
                        <td style="padding: 15px; font-weight: 600;"><?php echo CURRENCY . ' ' . number_format($v['price_per_day']); ?></td>
                        <td style="padding: 15px;">
                            <?php 
                                $status_colors = [
                                    'Available' => '#2ecc71',
                                    'Booked' => '#e63946',
                                    'Under Maintenance' => '#f1c40f',
                                    'Reserved' => '#3498db',
                                    'Out of Service' => '#95a5a6'
                                ];
                                $color = $status_colors[$v['status']] ?? '#fff';
                            ?>
                            <span style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; background: <?php echo $color; ?>22; color: <?php echo $color; ?>;">
                                <?php echo $v['status']; ?>
                            </span>
                        </td>
                        <td style="padding: 15px;">
                            <div style="display: flex; gap: 10px;">
                                <button onclick="editVehicle(<?php echo $v['id']; ?>)" style="background: none; border: none; color: var(--accent-color); cursor: pointer;"><i class="fas fa-edit"></i></button>
                                <button onclick="deleteVehicle(<?php echo $v['id']; ?>)" style="background: none; border: none; color: var(--primary-color); cursor: pointer;"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Vehicle Modal -->
<div id="addVehicleModal" class="modal flex-center" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; padding: 20px;">
    <div class="glass" style="max-width: 800px; width: 100%; padding: 40px; position: relative; max-height: 90vh; overflow-y: auto;">
        <button onclick="closeModal('addVehicleModal')" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        <h2 class="text-gradient" style="margin-bottom: 30px;">Add New Vehicle</h2>
        
        <form id="addVehicleForm" enctype="multipart/form-data">
            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Brand</label>
                    <input type="text" name="brand" required class="glass">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Model</label>
                    <input type="text" name="model" required class="glass">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Plate Number</label>
                    <input type="text" name="plate_number" required class="glass">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Category</label>
                    <select name="category" required class="glass">
                        <option value="Economy">Economy</option>
                        <option value="SUV">SUV</option>
                        <option value="Luxury">Luxury</option>
                        <option value="Vans">Vans</option>
                        <option value="Electric">Electric</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Transmission</label>
                    <select name="transmission" required class="glass">
                        <option value="Automatic">Automatic</option>
                        <option value="Manual">Manual</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Fuel Type</label>
                    <select name="fuel_type" required class="glass">
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="Electric">Electric</option>
                        <option value="Hybrid">Hybrid</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Seating Capacity</label>
                    <input type="number" name="seating_capacity" required class="glass">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Price Per Day</label>
                    <input type="number" name="price_per_day" required class="glass">
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 20px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Vehicle Images (First image will be the Main Image)</label>
                <input type="file" name="vehicle_images[]" multiple required class="glass">
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Description</label>
                <textarea name="description" rows="4" class="glass"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 30px; justify-content: center;">Add Vehicle</button>
        </form>
    </div>
</div>

<!-- Edit Vehicle Modal -->
<div id="editVehicleModal" class="modal flex-center" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; padding: 20px;">
    <div class="glass" style="max-width: 800px; width: 100%; padding: 40px; position: relative; max-height: 90vh; overflow-y: auto;">
        <button onclick="closeModal('editVehicleModal')" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        <h2 class="text-gradient" style="margin-bottom: 30px;">Edit Vehicle</h2>
        
        <form id="editVehicleForm" enctype="multipart/form-data">
            <input type="hidden" name="id" id="edit_id">
            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Brand</label>
                    <input type="text" name="brand" id="edit_brand" required class="glass">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Model</label>
                    <input type="text" name="model" id="edit_model" required class="glass">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Plate Number</label>
                    <input type="text" name="plate_number" id="edit_plate_number" required class="glass">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Category</label>
                    <select name="category" id="edit_category" required class="glass">
                        <option value="Economy">Economy</option>
                        <option value="SUV">SUV</option>
                        <option value="Luxury">Luxury</option>
                        <option value="Vans">Vans</option>
                        <option value="Electric">Electric</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Transmission</label>
                    <select name="transmission" id="edit_transmission" required class="glass">
                        <option value="Automatic">Automatic</option>
                        <option value="Manual">Manual</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Fuel Type</label>
                    <select name="fuel_type" id="edit_fuel_type" required class="glass">
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="Electric">Electric</option>
                        <option value="Hybrid">Hybrid</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Seating Capacity</label>
                    <input type="number" name="seating_capacity" id="edit_seating_capacity" required class="glass">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Price Per Day</label>
                    <input type="number" name="price_per_day" id="edit_price_per_day" required class="glass">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Status</label>
                    <select name="status" id="edit_status" required class="glass">
                        <option value="Available">Available</option>
                        <option value="Booked">Booked</option>
                        <option value="Under Maintenance">Under Maintenance</option>
                        <option value="Reserved">Reserved</option>
                        <option value="Out of Service">Out of Service</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Update Images (Optional - Uploading new images will replace existing gallery)</label>
                <input type="file" name="vehicle_images[]" multiple class="glass">
                <div id="edit_image_preview" style="display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap;">
                    <!-- Existing images will be loaded here -->
                </div>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-gray);">Description</label>
                <textarea name="description" id="edit_description" rows="4" class="glass"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 30px; justify-content: center;">Update Vehicle</button>
        </form>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    // Wrap in DOMContentLoaded to ensure main.js is loaded
    document.addEventListener('DOMContentLoaded', () => {
        ajaxForm('addVehicleForm', '../api/vehicles.php?action=add', (data) => {
            if (data.status === 'success') {
                notify('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                notify('error', data.message);
            }
        });

        ajaxForm('editVehicleForm', '../api/vehicles.php?action=update', (data) => {
            if (data.status === 'success') {
                notify('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                notify('error', data.message);
            }
        });
    });

    async function editVehicle(id) {
        try {
            const response = await fetch(`../api/vehicles.php?action=get&id=${id}`);
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Server response:', text);
                throw new Error('Invalid server response');
            }

            if (data.status === 'success') {
                const v = data.data;
                document.getElementById('edit_id').value = v.id || '';
                document.getElementById('edit_brand').value = v.brand || '';
                document.getElementById('edit_model').value = v.model || '';
                document.getElementById('edit_plate_number').value = v.plate_number || '';
                document.getElementById('edit_category').value = v.category || 'Economy';
                document.getElementById('edit_transmission').value = v.transmission || 'Automatic';
                document.getElementById('edit_fuel_type').value = v.fuel_type || 'Petrol';
                document.getElementById('edit_seating_capacity').value = v.seating_capacity || '';
                document.getElementById('edit_price_per_day').value = v.price_per_day || '';
                document.getElementById('edit_status').value = v.status || 'Available';
                document.getElementById('edit_description').value = v.description || '';
                
                // Load image previews
                const preview = document.getElementById('edit_image_preview');
                preview.innerHTML = '';
                if (v.images && v.images.length > 0) {
                    v.images.forEach(img => {
                        const div = document.createElement('div');
                        div.style.width = '80px';
                        div.style.height = '60px';
                        div.style.borderRadius = '5px';
                        div.style.overflow = 'hidden';
                        div.style.border = '1px solid var(--glass-border)';
                        div.innerHTML = `<img src="${img}" style="width: 100%; height: 100%; object-fit: cover;">`;
                        preview.appendChild(div);
                    });
                } else if (v.main_image) {
                    const div = document.createElement('div');
                    div.style.width = '80px';
                    div.style.height = '60px';
                    div.style.borderRadius = '5px';
                    div.style.overflow = 'hidden';
                    div.style.border = '1px solid var(--glass-border)';
                    div.innerHTML = `<img src="${v.main_image}" style="width: 100%; height: 100%; object-fit: cover;">`;
                    preview.appendChild(div);
                }

                openModal('editVehicleModal');
            } else {
                notify('error', data.message);
            }
        } catch (error) {
            console.error('Edit error:', error);
            notify('error', 'Failed to fetch vehicle details');
        }
    }

    async function deleteVehicle(id) {
        if (confirm('Are you sure you want to delete this vehicle?')) {
            try {
                const response = await fetch(`../api/vehicles.php?action=delete&id=${id}`);
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
