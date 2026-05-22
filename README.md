# Smart Drive Car Rental Management System

A premium, professional, and responsive Car Rental Management System built with PHP, MySQL, and Modern Web Technologies.

## Features
- **Modern UI/UX**: Premium dark theme with reddish accents, glassmorphism, and smooth animations.
- **Role-Based Authentication**: Secure login and registration for Customers, Drivers, and Admins.
- **Fleet Management**: Admin can manage vehicles, categories, and availability.
- **Booking Workflow**: Seamless booking process with Self-Drive and Chauffeur options.
- **Real-time Analytics**: Interactive charts for revenue and fleet utilization.
- **Driver Management**: Track driver assignments, trips, and earnings.
- **Maintenance Tracking**: Log vehicle services and set next service reminders.
- **Reporting System**: Generate detailed transaction reports and export to PDF.
- **Fully Responsive**: Optimized for Desktop, Tablet, and Mobile.

## Tech Stack
- **Frontend**: HTML5, CSS3 (Flexbox/Grid), JavaScript (ES6+), GSAP, AOS.
- **Backend**: PHP (Modular structure), PDO for secure DB operations.
- **Database**: MySQL with foreign keys and triggers.
- **Icons & UI**: Font Awesome, Google Fonts, SweetAlert2, Chart.js.

## Installation
1. Clone the repository to your local server (e.g., XAMPP/htdocs).
2. Import the `database/db.sql` file into your MySQL database.
3. Update `config/db.php` with your database credentials.
4. Access the system via `http://localhost/carhire/`.

## Admin Accounts
The system restricts admins to only 2 accounts. Admins can register themselves through the standard registration page by selecting the **Admin** role.

Once 2 admins have registered, the system will block any further admin registrations.

## Folder Structure
- `admin/`: Admin dashboard and management pages.
- `customer/`: Customer-specific features.
- `driver/`: Driver dashboard and trip management.
- `api/`: AJAX endpoints for backend operations.
- `assets/`: CSS, JS, Images, and Animations.
- `config/`: Global configuration and DB connection.
- `includes/`: Shared templates (Header/Footer).
- `vehicles/`: Vehicle browsing and booking logic.

## Security Features
- Password hashing (Bcrypt).
- Prepared statements (PDO) to prevent SQL Injection.
- Input sanitization and XSS protection.
- Role-based access control (RBAC).
