<?php 
require_once '../config/config.php';
require_once '../config/db.php';
include_once '../includes/header.php'; 

$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM vehicles WHERE (status = 'Available' OR status IS NULL OR status = '')";
$params = [];

if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
}
if ($search) {
    $query .= " AND (brand LIKE ? OR model LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$vehicles = $stmt->fetchAll();
?>

<section style="padding: 120px 0 60px; background: var(--bg-dark);">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 50px;">
            <div>
                <h2 class="text-gradient" style="font-size: 2.5rem; margin-bottom: 10px;">Browse Our Fleet</h2>
                <p style="color: var(--text-gray);">Find the perfect car for your next journey</p>
            </div>
            
            <form action="browse.php" method="GET" style="display: flex; gap: 15px;">
                <input type="text" name="search" value="<?php echo h($search); ?>" placeholder="Search brand or model..." class="glass" style="padding: 12px 20px; color: white; border: 1px solid var(--glass-border); border-radius: 8px; width: 300px;">
                <select name="category" class="glass" onchange="this.form.submit()" style="padding: 12px 20px; color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                    <option value="">All Categories</option>
                    <option value="Economy" <?php echo $category == 'Economy' ? 'selected' : ''; ?>>Economy</option>
                    <option value="SUV" <?php echo $category == 'SUV' ? 'selected' : ''; ?>>SUV</option>
                    <option value="Luxury" <?php echo $category == 'Luxury' ? 'selected' : ''; ?>>Luxury</option>
                    <option value="Electric" <?php echo $category == 'Electric' ? 'selected' : ''; ?>>Electric</option>
                </select>
            </form>
        </div>

        <?php if (empty($vehicles)): ?>
            <div class="glass" style="padding: 60px; text-align: center;">
                <i class="fas fa-car-side" style="font-size: 4rem; color: var(--text-gray); margin-bottom: 20px;"></i>
                <h3>No vehicles found matching your criteria.</h3>
                <p style="color: var(--text-gray); margin-top: 10px;">Try adjusting your filters or search term.</p>
                <a href="browse.php" class="btn btn-primary" style="margin-top: 20px;">View All Vehicles</a>
            </div>
        <?php else: ?>
            <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                <?php foreach ($vehicles as $vehicle): ?>
                    <div class="card animate-fade" style="padding: 15px; display: flex; flex-direction: column;">
                        <div style="height: 180px; overflow: hidden; border-radius: 10px; margin-bottom: 15px; position: relative;">
                            <img src="<?php echo $vehicle['main_image'] ?: 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&q=80&w=800'; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); padding: 4px 10px; border-radius: 15px; backdrop-filter: blur(5px); border: 1px solid var(--glass-border);">
                                <span style="color: var(--primary-color); font-weight: 700; font-size: 0.9rem;"><?php echo CURRENCY . ' ' . number_format($vehicle['price_per_day']); ?></span><span style="font-size: 0.7rem; color: var(--text-gray);">/day</span>
                            </div>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <span style="font-size: 0.7rem; color: var(--text-gray); text-transform: uppercase; letter-spacing: 1px;"><?php echo $vehicle['category']; ?></span>
                            <h3 style="font-size: 1.1rem; margin-top: 3px;"><?php echo $vehicle['brand'] . ' ' . $vehicle['model']; ?></h3>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 15px; color: var(--text-gray); font-size: 0.8rem;">
                            <span><i class="fas fa-cog" style="color: var(--primary-color); margin-right: 5px;"></i> <?php echo $vehicle['transmission']; ?></span>
                            <span><i class="fas fa-gas-pump" style="color: var(--primary-color); margin-right: 5px;"></i> <?php echo $vehicle['fuel_type']; ?></span>
                            <span><i class="fas fa-users" style="color: var(--primary-color); margin-right: 5px;"></i> <?php echo $vehicle['seating_capacity']; ?> Seats</span>
                            <span><i class="fas fa-snowflake" style="color: var(--primary-color); margin-right: 5px;"></i> A/C</span>
                        </div>
                        <div style="display: flex; gap: 8px; margin-top: auto;">
                            <a href="details.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-outline" style="flex: 1; justify-content: center; padding: 8px; font-size: 0.9rem;">Details</a>
                            <a href="book.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-primary" style="flex: 1; justify-content: center; padding: 8px; font-size: 0.9rem;">Book</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include_once '../includes/footer.php'; ?>
