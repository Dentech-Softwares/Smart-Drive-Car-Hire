<?php 
require_once 'config/config.php';
require_once 'config/db.php';
include_once 'includes/header.php'; 

// Fetch featured vehicles (Show all to display status tags)
$stmt = $pdo->query("SELECT * FROM vehicles ORDER BY created_at DESC LIMIT 6");
$featured_vehicles = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero flex-center" style="height: 100vh; background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80&w=1920') center/cover no-repeat; position: relative;">
    <div class="container" style="text-align: center;">
        <h1 class="animate-fade" style="font-size: 4rem; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 2px;">Premium <span class="text-gradient">Car Rental</span> Experience</h1>
        <p class="animate-fade" style="font-size: 1.2rem; margin-bottom: 40px; color: var(--text-gray); max-width: 800px; margin-inline: auto;">Drive your dreams with our exclusive fleet of luxury, economy, and electric vehicles. Best prices guaranteed.</p>
        
        <div class="booking-search glass animate-fade" style="padding: 30px; margin-top: 40px;">
            <form action="vehicles/browse.php" method="GET" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); align-items: end;">
                <div class="form-group">
                    <label style="display: block; text-align: left; margin-bottom: 10px;">Vehicle Category</label>
                    <select name="category" class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px; outline: none;">
                        <option value="">All Categories</option>
                        <option value="Economy">Economy</option>
                        <option value="SUV">SUV</option>
                        <option value="Luxury">Luxury</option>
                        <option value="Electric">Electric</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display: block; text-align: left; margin-bottom: 10px;">Pickup Date</label>
                    <input type="date" name="pickup" class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px; outline: none;">
                </div>
                <div class="form-group">
                    <label style="display: block; text-align: left; margin-bottom: 10px;">Return Date</label>
                    <input type="date" name="return" class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px; outline: none;">
                </div>
                <button type="submit" class="btn btn-primary" style="height: 50px; justify-content: center;">Search Vehicles <i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
</section>

<!-- Featured Vehicles -->
<section id="vehicles" style="padding: 100px 0;">
    <div class="container">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 style="font-size: 2.5rem; margin-bottom: 15px;">Featured <span class="text-gradient">Vehicles</span></h2>
            <p style="color: var(--text-gray);">Hand-picked selection of our top-rated cars</p>
        </div>

        <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
            <?php if (empty($featured_vehicles)): ?>
                <div class="glass" style="grid-column: 1 / -1; padding: 40px; text-align: center;">
                    <p style="color: var(--text-gray);">Our fleet is currently being updated. Please check back shortly!</p>
                </div>
            <?php else: ?>
                <?php foreach ($featured_vehicles as $vehicle): ?>
                    <div class="card animate-fade" data-aos="fade-up" style="padding: 15px; display: flex; flex-direction: column;">
                        <div style="height: 180px; overflow: hidden; border-radius: 10px; margin-bottom: 15px; position: relative;">
                            <img src="<?php echo $vehicle['main_image'] ?: 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&q=80&w=800'; ?>" alt="<?php echo $vehicle['brand']; ?>" style="width: 100%; height: 100%; object-fit: cover; transition: var(--transition);">
                            <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); padding: 4px 10px; border-radius: 15px; backdrop-filter: blur(5px); border: 1px solid var(--glass-border);">
                                <span style="color: var(--primary-color); font-weight: 700; font-size: 0.9rem;"><?php echo CURRENCY . ' ' . number_format($vehicle['price_per_day']); ?></span><span style="font-size: 0.7rem; color: var(--text-gray);">/day</span>
                            </div>
                        </div>
                        <div style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="background: var(--primary-color); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.65rem; text-transform: uppercase;"><?php echo $vehicle['category']; ?></span>
                                <h3 style="font-size: 1.1rem; margin-top: 5px;"><?php echo $vehicle['brand'] . ' ' . $vehicle['model']; ?></h3>
                            </div>
                            <span style="padding: 4px 10px; border-radius: 15px; font-size: 0.7rem; font-weight: 600; background: <?php echo $vehicle['status'] == 'Available' ? 'rgba(46, 204, 113, 0.1)' : 'rgba(230, 57, 70, 0.1)'; ?>; color: <?php echo $vehicle['status'] == 'Available' ? '#2ecc71' : 'var(--primary-color)'; ?>;">
                                <?php echo $vehicle['status'] ?: 'Available'; ?>
                            </span>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 15px; color: var(--text-gray); font-size: 0.8rem;">
                            <span><i class="fas fa-cog" style="color: var(--primary-color); margin-right: 5px;"></i> <?php echo $vehicle['transmission']; ?></span>
                            <span><i class="fas fa-gas-pump" style="color: var(--primary-color); margin-right: 5px;"></i> <?php echo $vehicle['fuel_type']; ?></span>
                            <span><i class="fas fa-users" style="color: var(--primary-color); margin-right: 5px;"></i> <?php echo $vehicle['seating_capacity']; ?> Seats</span>
                            <span><i class="fas fa-check-circle" style="color: var(--primary-color); margin-right: 5px;"></i> <?php echo $vehicle['status']; ?></span>
                        </div>
                        <a href="vehicles/details.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-outline" style="width: 100%; justify-content: center; padding: 8px; font-size: 0.9rem; margin-top: auto;">View Details</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 50px;">
            <a href="vehicles/browse.php" class="btn btn-primary">Browse All Vehicles <i class="fas fa-chevron-right"></i></a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section style="padding: 80px 0; background: var(--bg-card);">
    <div class="container">
        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); text-align: center;">
            <div data-aos="zoom-in">
                <h2 class="text-gradient" style="font-size: 3rem; margin-bottom: 10px;">500+</h2>
                <p style="color: var(--text-gray);">Happy Customers</p>
            </div>
            <div data-aos="zoom-in" data-aos-delay="100">
                <h2 class="text-gradient" style="font-size: 3rem; margin-bottom: 10px;">120+</h2>
                <p style="color: var(--text-gray);">Luxury Cars</p>
            </div>
            <div data-aos="zoom-in" data-aos-delay="200">
                <h2 class="text-gradient" style="font-size: 3rem; margin-bottom: 10px;">50+</h2>
                <p style="color: var(--text-gray);">Expert Drivers</p>
            </div>
            <div data-aos="zoom-in" data-aos-delay="300">
                <h2 class="text-gradient" style="font-size: 3rem; margin-bottom: 10px;">15+</h2>
                <p style="color: var(--text-gray);">Years Experience</p>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section id="services" style="padding: 100px 0;">
    <div class="container">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 style="font-size: 2.5rem; margin-bottom: 15px;">Why Choose <span class="text-gradient">Smart Drive Car Rental</span></h2>
            <p style="color: var(--text-gray);">We offer the best services for our customers</p>
        </div>

        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
            <div class="card" style="text-align: center; padding: 40px;" data-aos="fade-up">
                <i class="fas fa-shield-alt" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 20px;"></i>
                <h3 style="margin-bottom: 15px;">Fully Insured</h3>
                <p style="color: var(--text-gray);">All our vehicles come with comprehensive insurance coverage for your peace of mind.</p>
            </div>
            <div class="card" style="text-align: center; padding: 40px;" data-aos="fade-up" data-aos-delay="100">
                <i class="fas fa-headset" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 20px;"></i>
                <h3 style="margin-bottom: 15px;">24/7 Support</h3>
                <p style="color: var(--text-gray);">Our support team is available round the clock to assist you with any queries or issues.</p>
            </div>
            <div class="card" style="text-align: center; padding: 40px;" data-aos="fade-up" data-aos-delay="200">
                <i class="fas fa-tags" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 20px;"></i>
                <h3 style="margin-bottom: 15px;">Best Prices</h3>
                <p style="color: var(--text-gray);">We offer competitive pricing with no hidden charges. Premium quality at affordable rates.</p>
            </div>
            <div class="card" style="text-align: center; padding: 40px;" data-aos="fade-up" data-aos-delay="300">
                <i class="fas fa-map-marked-alt" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 20px;"></i>
                <h3 style="margin-bottom: 15px;">Anywhere Pickup</h3>
                <p style="color: var(--text-gray);">Choose your preferred pickup and drop-off locations. We make it convenient for you.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section style="padding: 100px 0; background: var(--bg-card);">
    <div class="container">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 style="font-size: 2.5rem; margin-bottom: 15px;">Customer <span class="text-gradient">Testimonials</span></h2>
            <p style="color: var(--text-gray);">What our clients say about us</p>
        </div>

        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <div class="glass" style="padding: 30px;" data-aos="fade-right">
                <div style="display: flex; gap: 5px; color: #ffc107; margin-bottom: 15px;">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p style="margin-bottom: 20px; font-style: italic;">"The best car rental experience I've ever had. The process was smooth and the car was in perfect condition."</p>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: #444;"></div>
                    <div>
                        <h4 style="margin-bottom: 2px;">John Doe</h4>
                        <span style="font-size: 0.8rem; color: var(--text-gray);">Business Executive</span>
                    </div>
                </div>
            </div>
            <div class="glass" style="padding: 30px;" data-aos="fade-up">
                <div style="display: flex; gap: 5px; color: #ffc107; margin-bottom: 15px;">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p style="margin-bottom: 20px; font-style: italic;">"Amazing service! The chauffeur was very professional and the luxury sedan was extremely comfortable."</p>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: #444;"></div>
                    <div>
                        <h4 style="margin-bottom: 2px;">Sarah Smith</h4>
                        <span style="font-size: 0.8rem; color: var(--text-gray);">Travel Blogger</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" style="padding: 100px 0;">
    <div class="container">
        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;">
            <div data-aos="fade-right">
                <h2 style="font-size: 2.5rem; margin-bottom: 20px;">Get In <span class="text-gradient">Touch</span></h2>
                <p style="color: var(--text-gray); margin-bottom: 40px;">Have questions or need a custom booking? Contact us today and our team will get back to you shortly.</p>
                
                <div style="margin-bottom: 30px;">
                    <div style="display: flex; gap: 20px; margin-bottom: 25px;">
                        <div class="flex-center" style="width: 50px; height: 50px; background: var(--primary-color); border-radius: 10px;">
                            <i class="fas fa-map-marker-alt" style="color: white; font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 5px;">Our Location</h4>
                            <p style="color: var(--text-gray);">123 Luxury Drive, Nairobi, Kenya</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 20px; margin-bottom: 25px;">
                        <div class="flex-center" style="width: 50px; height: 50px; background: var(--primary-color); border-radius: 10px;">
                            <i class="fas fa-phone" style="color: white; font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 5px;">Phone Number</h4>
                            <p style="color: var(--text-gray);">+254 700 000 000</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 20px;">
                        <div class="flex-center" style="width: 50px; height: 50px; background: var(--primary-color); border-radius: 10px;">
                            <i class="fas fa-envelope" style="color: white; font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 5px;">Email Address</h4>
                            <p style="color: var(--text-gray);">info@smartdrive.com</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="glass" style="padding: 40px;" data-aos="fade-left">
                <form>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <input type="text" placeholder="Your Name" class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px; outline: none;">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <input type="email" placeholder="Your Email" class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px; outline: none;">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <textarea placeholder="Your Message" rows="5" class="glass" style="width: 100%; padding: 12px; color: white; border: 1px solid var(--glass-border); border-radius: 8px; outline: none;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>
