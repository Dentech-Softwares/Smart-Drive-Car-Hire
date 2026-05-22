<?php 
require_once '../config/db.php';
include_once 'includes/header.php'; 

// Fetch stats
$total_vehicles = $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Paid'")->fetchColumn() ?: 0;
$total_drivers = $pdo->query("SELECT COUNT(*) FROM drivers")->fetchColumn();

// Fetch recent bookings
$stmt = $pdo->query("SELECT b.*, v.brand, v.model, u.full_name FROM bookings b 
                    JOIN vehicles v ON b.vehicle_id = v.id 
                    JOIN users u ON b.customer_id = u.id 
                    ORDER BY b.created_at DESC LIMIT 5");
$recent_bookings = $stmt->fetchAll();
?>

<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 40px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(230, 57, 70, 0.1); color: var(--primary-color);">
            <i class="fas fa-car"></i>
        </div>
        <div>
            <h3 style="font-size: 1.8rem;" id="stat_vehicles"><?php echo $total_vehicles; ?></h3>
            <p style="color: var(--text-gray);">Total Vehicles</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(69, 123, 157, 0.1); color: var(--accent-color);">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div>
            <h3 style="font-size: 1.8rem;" id="stat_bookings"><?php echo $total_bookings; ?></h3>
            <p style="color: var(--text-gray);">Total Bookings</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71;">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div>
            <h3 style="font-size: 1.8rem;" id="stat_revenue"><?php echo CURRENCY . ' ' . number_format($total_revenue); ?></h3>
            <p style="color: var(--text-gray);">Total Revenue</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(155, 89, 182, 0.1); color: #9b59b6;">
            <i class="fas fa-user-tie"></i>
        </div>
        <div>
            <h3 style="font-size: 1.8rem;" id="stat_drivers"><?php echo $total_drivers; ?></h3>
            <p style="color: var(--text-gray);">Active Drivers</p>
        </div>
    </div>
</div>

<div class="grid" style="grid-template-columns: 2fr 1.2fr; gap: 30px; margin-bottom: 40px; align-items: start;">
    <div class="card" style="padding: 25px; height: 350px;">
        <h3 style="margin-bottom: 20px; font-size: 1.1rem;">Revenue Overview (Real-time)</h3>
        <div style="height: 250px; position: relative;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    <div class="card" style="padding: 25px; height: 350px;">
        <h3 style="margin-bottom: 20px; font-size: 1.1rem;">Fleet Status</h3>
        <div style="height: 250px; position: relative;">
            <canvas id="fleetChart"></canvas>
        </div>
    </div>
</div>

<div class="card" style="padding: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h3>Recent Bookings</h3>
        <a href="bookings.php" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.9rem;">View All</a>
    </div>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-gray);">
                    <th style="padding: 15px;">Reference</th>
                    <th style="padding: 15px;">Customer</th>
                    <th style="padding: 15px;">Vehicle</th>
                    <th style="padding: 15px;">Date</th>
                    <th style="padding: 15px;">Status</th>
                    <th style="padding: 15px;">Action</th>
                </tr>
            </thead>
            <tbody id="recent_bookings_table">
                <?php foreach ($recent_bookings as $booking): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 15px; font-weight: 600;"><?php echo $booking['booking_reference']; ?></td>
                        <td style="padding: 15px;"><?php echo $booking['full_name']; ?></td>
                        <td style="padding: 15px;"><?php echo $booking['brand'] . ' ' . $booking['model']; ?></td>
                        <td style="padding: 15px;"><?php echo date('M d, Y', strtotime($booking['pickup_date'])); ?></td>
                        <td style="padding: 15px;">
                            <span style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; background: rgba(230, 57, 70, 0.1); color: var(--primary-color);">
                                <?php echo $booking['status']; ?>
                            </span>
                        </td>
                        <td style="padding: 15px;">
                            <a href="booking_details.php?id=<?php echo $booking['id']; ?>" style="color: var(--accent-color);"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    let revenueChart, fleetChart;

    async function updateStats() {
        try {
            const response = await fetch('../api/stats.php');
            const result = await response.json();
            
            if (result.status === 'success') {
                const data = result.data;
                
                // Update text stats
                document.getElementById('stat_vehicles').innerText = data.total_vehicles;
                document.getElementById('stat_bookings').innerText = data.total_bookings;
                document.getElementById('stat_revenue').innerText = '<?php echo CURRENCY; ?> ' + parseInt(data.total_revenue).toLocaleString();
                document.getElementById('stat_drivers').innerText = data.active_drivers;

                // Update Revenue Chart
                if (revenueChart) {
                    revenueChart.data.labels = data.revenue_chart.labels;
                    revenueChart.data.datasets[0].data = data.revenue_chart.data;
                    revenueChart.update();
                }

                // Update Fleet Chart
                if (fleetChart) {
                    const labels = Object.keys(data.fleet_status);
                    const counts = Object.values(data.fleet_status);
                    fleetChart.data.labels = labels;
                    fleetChart.data.datasets[0].data = counts;
                    fleetChart.update();
                }
            }
        } catch (error) {
            console.error('Failed to fetch realtime stats:', error);
        }
    }

    // Initial Chart setup with real data
    document.addEventListener('DOMContentLoaded', async () => {
        const response = await fetch('../api/stats.php');
        const result = await response.json();
        const data = result.data;

        // Revenue Chart
        const revCtx = document.getElementById('revenueChart').getContext('2d');
        revenueChart = new Chart(revCtx, {
            type: 'line',
            data: {
                labels: data.revenue_chart.labels,
                datasets: [{
                    label: 'Revenue',
                    data: data.revenue_chart.data,
                    borderColor: '#e63946',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(230, 57, 70, 0.1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#a8dadc' } },
                    x: { grid: { display: false }, ticks: { color: '#a8dadc' } }
                }
            }
        });

        // Fleet Chart
        const fleetCtx = document.getElementById('fleetChart').getContext('2d');
        const fleetLabels = Object.keys(data.fleet_status);
        const fleetCounts = Object.values(data.fleet_status);
        
        fleetChart = new Chart(fleetCtx, {
            type: 'doughnut',
            data: {
                labels: fleetLabels,
                datasets: [{
                    data: fleetCounts,
                    backgroundColor: ['#2ecc71', '#e63946', '#f1c40f', '#3498db', '#95a5a6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#a8dadc', padding: 20 } }
                }
            }
        });

        // Start polling every 10 seconds for "realtime" feel
        setInterval(updateStats, 10000);
    });
</script>

<?php include_once 'includes/footer.php'; ?>
