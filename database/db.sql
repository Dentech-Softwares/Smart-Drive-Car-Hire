CREATE DATABASE IF NOT EXISTS carhire_db;
USE carhire_db;

-- Drop trigger if exists
DROP TRIGGER IF EXISTS before_admin_insert;

-- Drop tables in order to avoid foreign key constraints
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS maintenance;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS vehicle_images;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS drivers;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS users;

-- Users table for authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer', 'driver') NOT NULL,
    profile_image VARCHAR(255) DEFAULT 'default_profile.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customers table for extra details
CREATE TABLE customers (
    user_id INT PRIMARY KEY,
    id_passport_number VARCHAR(50),
    driving_license_number VARCHAR(50),
    id_image VARCHAR(255),
    license_image VARCHAR(255),
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Drivers table for extra details
CREATE TABLE drivers (
    user_id INT PRIMARY KEY,
    license_number VARCHAR(50) NOT NULL,
    license_image VARCHAR(255),
    experience_years INT DEFAULT 0,
    status ENUM('available', 'on_trip', 'suspended', 'inactive') DEFAULT 'available',
    rating DECIMAL(3, 2) DEFAULT 5.00,
    earnings DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Admins table (Max 2 enforced via application logic and trigger)
CREATE TABLE admins (
    user_id INT PRIMARY KEY,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Vehicles table
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    plate_number VARCHAR(20) UNIQUE NOT NULL,
    category ENUM('Economy', 'SUV', 'Luxury', 'Vans', 'Electric') NOT NULL,
    transmission ENUM('Manual', 'Automatic') NOT NULL,
    fuel_type ENUM('Petrol', 'Diesel', 'Electric', 'Hybrid') NOT NULL,
    seating_capacity INT NOT NULL,
    price_per_day DECIMAL(10, 2) NOT NULL,
    status ENUM('Available', 'Booked', 'Under Maintenance', 'Reserved', 'Out of Service') DEFAULT 'Available',
    insurance_status VARCHAR(100),
    maintenance_status VARCHAR(100),
    description TEXT,
    main_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vehicle images table (Multiple images)
CREATE TABLE vehicle_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT,
    image_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT,
    vehicle_id INT,
    driver_id INT NULL, -- NULL for self-drive
    pickup_date DATETIME NOT NULL,
    return_date DATETIME NOT NULL,
    rental_mode ENUM('Self Drive', 'With Driver') NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    security_deposit DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Awaiting Verification', 'Approved', 'Rejected', 'Ongoing', 'Completed', 'Cancelled') DEFAULT 'Pending',
    id_document VARCHAR(255), -- Uploaded for this specific booking if needed
    license_document VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    payment_method ENUM('M-Pesa', 'Cash', 'Card') NOT NULL,
    transaction_id VARCHAR(100) UNIQUE,
    amount DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('Pending', 'Paid', 'Failed', 'Refunded') DEFAULT 'Pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    invoice_path VARCHAR(255),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Maintenance table
CREATE TABLE maintenance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT,
    service_type VARCHAR(100) NOT NULL,
    service_date DATE NOT NULL,
    mileage INT,
    cost DECIMAL(10, 2),
    notes TEXT,
    next_service_date DATE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    customer_id INT,
    vehicle_rating INT CHECK (vehicle_rating BETWEEN 1 AND 5),
    driver_rating INT CHECK (driver_rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Trigger to prevent more than 2 admins
DELIMITER //
CREATE TRIGGER before_admin_insert
BEFORE INSERT ON admins
FOR EACH ROW
BEGIN
    DECLARE admin_count INT;
    SELECT COUNT(*) INTO admin_count FROM admins;
    IF admin_count >= 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Maximum number of admins (2) reached.';
    END IF;
END;
//
DELIMITER ;

-- Note: Admin accounts must be manually inserted into the 'users' table with role 'admin',
-- followed by an entry in the 'admins' table. Only 2 admins are allowed.
