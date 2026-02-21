# QUICK START GUIDE - Gym Management System

## Installation (5 Minutes)

### Prerequisites
- XAMPP installed and running (Apache + MySQL)
- Project folder: `C:\xampp\htdocs\Gym System`

---

## Step 1: Start Services

1. Open XAMPP Control Panel
2. Click "Start" for Apache
3. Click "Start" for MySQL
4. Wait for both to show "Running"

---

## Step 2: Import Database

### Option A: Using phpMyAdmin (Easiest)

1. Open: http://localhost/phpmyadmin
2. Click "Import" tab
3. Click "Choose File"
4. Select: `Gym System/gym_database.sql`
5. Click "Import" button
6. You should see: "Import has been executed successfully"

### Option B: Using Command Line

```bash
cd C:\xampp\htdocs\Gym%20System
mysql -u root < gym_database.sql
```

---

## Step 3: Access the Application

1. Open browser
2. Go to: **http://localhost/Gym%20System**
3. Login with:
   - **Username**: admin
   - **Password**: admin123

---

## Step 4: Explore Features

### Dashboard
- View total members, revenue, and metrics
- See expiring memberships alerts
- Check recent payments

### Members
- Add/Edit/Delete members
- Search and filter members
- View member profiles and payment history

### Plans
- Create membership plans
- Set duration and price
- Edit existing plans

### Payments
- Record member payments
- Print receipts
- Track payment methods

### Reports
- View monthly revenue
- Export to CSV
- Analyze yearly trends

---

## Default Login

```
Username: admin
Email: admin@gym.local
Password: admin123
```

**Note**: Change password immediately in production!

---

## Common Tasks

### Add a New Admin User

```php
// Use phpMyAdmin > gym_management > users > Insert

$password = password_hash('newpassword', PASSWORD_DEFAULT);

INSERT INTO users (username, email, password, full_name) 
VALUES ('newadmin', 'admin@example.com', '$2y$10$...', 'Admin Name');
```

### Change Database Credentials

1. Edit: `config/database.php`
2. Update these constants:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Your MySQL password
   define('DB_NAME', 'gym_management');
   ```

### Reset Database

```bash
mysql -u root -e "DROP DATABASE gym_management;"
mysql -u root < gym_database.sql
```

---

## File Locations

| Purpose | Location |
|---------|----------|
| Database Setup | `gym_database.sql` |
| Configuration | `config/database.php` |
| Main Entry | `index.php` (Dashboard) |
| Login Page | `login.php` |
| Logout Handler | `logout.php` |
| Documentation | `README.md` |

---

## Troubleshooting

### Problem: Cannot access http://localhost/Gym%20System

**Solution**: 
- Verify Apache is running (XAMPP Control Panel)
- Check URL spelling (it has a space: "Gym System")
- Clear browser cache (Ctrl+Shift+Delete)

### Problem: "Database connection failed"

**Solution**:
- Verify MySQL is running
- Check `config/database.php` settings
- Verify database was imported correctly
  ```bash
  mysql -u root -e "SHOW DATABASES;"
  ```
  Should show: `gym_management`

### Problem: Login page but cannot login

**Solution**:
- Check admin user exists:
  ```bash
  mysql -u root gym_management -e "SELECT * FROM users;"
  ```
- Reset password in phpMyAdmin if needed

### Problem: AJAX operations not working

**Solution**:
- Check browser console (F12 > Console)
- Verify jQuery is loaded
- Check network tab for AJAX requests
- Ensure .htaccess is not blocking access

---

## Performance Notes

### Initial Load
- First page load: ~2-3 seconds (normal)
- Dashboard loads: ~1 second
- Tables load: ~0.5 seconds

### Optimization Tips
1. Use modern browser (Chrome, Firefox, Edge)
2. Clear browser cache monthly
3. Optimize images if added
4. Archive old payments annually

---

## Security Reminders

✓ Change admin password immediately  
✓ Set up database backups  
✓ Use HTTPS in production  
✓ Restrict database user permissions  
✓ Keep PHP and MySQL updated  

---

## Next Steps

1. **Customize App Name**
   - Edit `config/database.php`
   - Change: `define('APP_NAME', 'Your Gym Name');`

2. **Add More Admin Users**
   - Use phpMyAdmin
   - Create users in `users` table

3. **Customize Colors**
   - Edit `assets/css/style.css`
   - Change color variables in `:root`

4. **Set Up Backups**
   - Export database weekly
   - Store in safe location

---

## Support Resources

### Official Documentation
- Bootstrap 5: https://getbootstrap.com/
- PHP PDO: https://www.php.net/manual/en/book.pdo.php
- Chart.js: https://www.chartjs.org/
- DataTables: https://datatables.net/

### Useful Commands

```bash
# Check MySQL connection
mysql -u root -p

# Verify database
mysql -u root -e "SHOW DATABASES;"

# View gym_management tables
mysql -u root gym_management -e "SHOW TABLES;"

# Backup database
mysqldump -u root gym_management > backup.sql

# Restore database
mysql -u root gym_management < backup.sql
```

---

## That's It! 🎉

Your Gym Management System is now ready to use!

**For full documentation**: See `README.md`

---

**Version**: 1.0.0  
**Created**: February 2026  
**Status**: Production Ready  

Enjoy managing your gym! 💪🏋️
