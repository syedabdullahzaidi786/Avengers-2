# Gym Management System

A complete, professional PHP-based Gym Management System built with core PHP, MySQL, Bootstrap 5, and jQuery AJAX.

## Features

### 1. Authentication System
- Secure admin login/logout
- Session-based authentication
- Password hashing with PHP's password_hash()
- Automatic session timeout (30 minutes)
- Protect direct file access

### 2. Dashboard
- **Total Members** count
- **Active Members** count
- **Expired Members** count
- **Monthly Revenue** display
- **Expiring Soon Members** (next 7 days alert)
- **Recent Payments** list
- **Revenue Chart** (monthly breakdown with Chart.js)

### 3. Members Management (CRUD)
- Add new members
- View all members with search & filter
- Edit member details
- Delete members
- Auto-calculate membership end dates
- Track member status (active/expired)
- View individual member profiles
- Payment history per member
- Responsive DataTable listing

### 4. Membership Plans (CRUD)
- Create membership plans
- Edit plans
- Delete plans (with validation)
- View all plans
- Plan duration and pricing
- AJAX-based forms

### 5. Payments Module
- Record payments
- Payment method tracking (cash/card/online)
- Receipt generation & printing
- Payment history
- Search payments
- Automatic receipt numbering

### 6. Reports
- Monthly revenue reports
- Year-wise revenue breakdown
- Revenue analysis charts
- Export to CSV functionality
- Printable reports

### 7. Security Features
- Prepared statements (PDO)
- SQL injection prevention
- XSS protection (output escaping)
- Session security
- Input validation
- CSRF protection

## System Requirements

### Server
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server

### Software
- XAMPP (for Windows/Mac) or similar AMP stack
- Modern web browser
- Git (optional, for version control)

## Installation Instructions

### Step 1: Setup XAMPP

1. Download and install XAMPP from https://www.apachefriends.org/
2. Install XAMPP to `C:\xampp` (Windows) or `/Applications/xampp` (Mac)
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Navigate to htdocs

```bash
cd C:\xampp\htdocs
```

The project folder `Gym System` should already be in this directory.

### Step 3: Create Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click on "Import" tab
3. Select the `gym_database.sql` file from the project root folder
4. Click "Import" button

**OR** Use MySQL Command Line:

```bash
mysql -u root -p < "C:\xampp\htdocs\Gym System\gym_database.sql"
```

(Press Enter when prompted for password - default is empty)

### Step 4: Verify Configuration

Open `config/database.php` and ensure these settings:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Empty for default XAMPP
define('DB_NAME', 'gym_management');
```

### Step 5: Access the Application

1. Open your browser
2. Navigate to: `http://localhost/Gym%20System`
3. You'll be redirected to login page

### Step 6: Login with Demo Credentials

```
Username: admin
Password: admin123
```

## Database Structure

### Users Table
- id (Primary Key)
- username (Unique)
- email (Unique)
- password (hashed)
- full_name
- created_at
- updated_at

### Membership Plans Table
- id (Primary Key)
- name (Unique)
- duration (days)
- price
- description
- is_active
- created_at
- updated_at

### Members Table
- id (Primary Key)
- full_name
- phone
- email
- age
- gender
- address
- plan_id (Foreign Key)
- start_date
- end_date (auto-calculated)
- status (active/expired/suspended)
- created_at
- updated_at

### Payments Table
- id (Primary Key)
- member_id (Foreign Key)
- amount
- payment_method (cash/card/online)
- payment_date
- description
- receipt_number
- created_at
- updated_at

## File Structure

```
Gym System/
├── config/
│   └── database.php           # Database configuration & helpers
│
├── controllers/
│   └── DashboardController.php
│
├── models/
│   ├── User.php              # User authentication model
│   ├── Member.php            # Member CRUD operations
│   ├── Plan.php              # Plan CRUD operations
│   └── Payment.php           # Payment operations
│
├── views/
│   ├── layout/
│   │   └── header.php        # Main layout template
│   ├── dashboard/            # Dashboard views
│   ├── members/              # Members CRUD views
│   │   ├── members.php
│   │   └── member_profile.php
│   ├── plans/                # Plans CRUD views
│   │   └── plans.php
│   ├── payments/             # Payments views
│   │   ├── payments.php
│   │   └── receipt.php
│   └── reports/              # Reports views
│       └── revenue.php
│
├── ajax/                      # AJAX handlers
│   ├── members_add.php
│   ├── members_get.php
│   ├── members_get_single.php
│   ├── members_update.php
│   ├── members_delete.php
│   ├── plans_add.php
│   ├── plans_get.php
│   ├── plans_get_single.php
│   ├── plans_update.php
│   ├── plans_delete.php
│   ├── payments_add.php
│   └── payments_get.php
│
├── assets/
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   └── js/
│       ├── main.js           # Global functions
│       ├── members.js
│       ├── plans.js
│       └── payments.js
│
├── index.php                 # Dashboard (main entry point)
├── login.php                 # Login page
├── logout.php                # Logout handler
└── gym_database.sql          # Database SQL file
```

## Key Features Implementation

### 1. Session Management
- Automatic logout after 30 minutes of inactivity
- Secure session configuration
- User role management (admin)

### 2. Form Validation
- Client-side validation (HTML5)
- Server-side validation (PHP)
- AJAX error handling

### 3. AJAX Implementation
- No page refresh for CRUD operations
- Real-time search and filtering
- Dynamic table updates
- Error handling with SweetAlert2

### 4. Security Implementation
- PDO prepared statements
- Input sanitization
- Output escaping (htmlspecialchars)
- SQL injection prevention
- XSS protection

### 5. Responsive Design
- Bootstrap 5 grid system
- Mobile-friendly layout
- Collapsible sidebar on small screens
- Responsive tables with overflow

### 6. Data Export
- CSV export for reports
- Printable receipts
- PDF-friendly styling

## Usage Guide

### Adding a Member

1. Click "Members" in sidebar
2. Click "Add Member" button
3. Fill in member details:
   - Full name (required)
   - Email (required)
   - Phone (required)
   - Age, Gender, Address (optional)
   - Plan (required)
   - Start Date (required)
4. Click "Save Member"
5. End date is automatically calculated based on plan duration

### Recording a Payment

1. Click "Payments" in sidebar
2. Click "Add Payment" button
3. Select member
4. Enter amount
5. Select payment method
6. Confirm payment date
7. Click "Save Payment"
8. Receipt is automatically generated

### Viewing Reports

1. Click "Reports" in sidebar
2. Select year (2024-2026)
3. View revenue breakdown
4. Export to CSV or print

### Managing Plans

1. Click "Plans" in sidebar
2. Click "Add Plan" to create new plan
3. Edit or delete existing plans
4. Plans show member count to prevent deletion of active plans

## Customization Guide

### Change App Name
Edit `config/database.php`:
```php
define('APP_NAME', 'Your Gym Name');
```

### Change Colors
Edit `assets/css/style.css`:
```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    /* ...other colors */
}
```

### Change Session Timeout
Edit `config/database.php`:
```php
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
```

### Add New Admin User
Create a new user in the `users` table:
```php
$password = password_hash('password123', PASSWORD_DEFAULT);
// Insert into database
```

## Troubleshooting

### "Database connection failed"
- Check MySQL is running
- Verify database name in `config/database.php`
- Ensure database is imported correctly

### Login not working
- Clear browser cache and cookies
- Check database has `users` table with sample data
- Verify admin credentials in database

### AJAX requests not working
- Check browser console for errors
- Verify jQuery is loaded
- Check file paths in AJAX calls
- Ensure all AJAX files exist

### Charts not displaying
- Verify Chart.js is loaded
- Check browser console for JavaScript errors
- Ensure data format is correct

## Performance Tips

1. **Database Optimization**
   - Add appropriate indexes (already included)
   - Regular backup of data
   - Archive old payment records

2. **Caching**
   - Cache frequently accessed plans and users
   - Implement Redis for sessions (optional)

3. **Code Optimization**
   - Use pagination for large datasets
   - Lazy load images
   - Minify CSS and JS in production

## Backup & Recovery

### Backup Database
```bash
mysqldump -u root -p gym_management > backup_$(date +%Y%m%d).sql
```

### Restore Database
```bash
mysql -u root -p gym_management < backup_20260216.sql
```

## Support & Updates

- Regular database maintenance recommended
- Update PHP and MySQL regularly
- Review logs for errors
- Monitor disk space usage

## License

This project is provided as-is for the Gym Management System.

## Author

Built with ❤️ for Professional Gym Management

---

**Version**: 1.0.0  
**Last Updated**: February 2026  
**Status**: Production Ready

## Contact

For issues or questions, please contact the development team.

---

**Thank you for using Gym Management System!**
"# GYM-Management-" 
"# Avengers-2" 
