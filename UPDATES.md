# System Updates - February 2026

## Changes Made

### 1. ✅ Password Hashing Removed
- **User Model**: Updated `authenticate()` method to use direct string comparison instead of `password_verify()`
- **User Model**: Updated `createUser()` method to store plain text passwords instead of hashing
- **Database**: Admin password changed from hashed to plain text: `admin123`
- **Login**: Works with plain text passwords
- **Note**: Passwords are now stored as plain text in the database

### 2. ✅ New Payment Methods Added
Pakistani digital payment methods now supported:
- **Cash** (پیسے)
- **Easy Paisa** (ای پیسہ)
- **Jazz Cash** (جاز کیش)
- **Naya Pay** (نیا پے)
- **Sada Pay** (سادہ پے)
- **Bank Transfer** (بینک ٹرانسفر)

**Updated Files:**
- `gym_database.sql`: Payment method ENUM updated
- `views/payments/payments.php`: Form dropdown with all 6 methods
- `views/payments/payments.php`: Color-coded badges for each method

### 3. ✅ Member Profile Pictures
Members can now upload profile photos when adding/editing members.

**Features:**
- Image upload in Add/Edit Member form
- Supported formats: JPG, JPEG, PNG, GIF
- Max file size: 5MB
- Photos stored in: `uploads/members/` directory
- Profile pic displayed in Member Profile page
- Full name and default avatar fallback if no image

**Updated Files:**
- `gym_database.sql`: Added `profile_picture` field to members table
- `models/Member.php`: Updated `createMember()` and `updateMember()` with picture handling
- `ajax/members_add.php`: File upload handling, validation, and storage
- `ajax/members_update.php`: Update existing pictures, delete old files
- `views/members/members.php`: Image input field in form (multipart/form-data)
- `views/members/member_profile.php`: Display profile picture as circular avatar (200x200px)

**Security:**
- File type validation (whitelist: jpg, jpeg, png, gif)
- File size limit (5MB)
- Unique file names (using uniqid())
- Old pictures deleted when replaced

### 4. ✅ Email, Address, Age Removed from Members
Simplified member data collection by removing non-essential fields.

**Removed Fields:**
- Email address
- Physical address
- Age

**Required Information Now:**
- Full Name (required)
- Phone Number (required)
- Gender (optional)
- Profile Picture (optional)
- Plan (required)
- Start Date (required)

**Updated Files:**
- `gym_database.sql`: Removed `email`, `address`, `age` columns and their indexes
- `models/Member.php`: 
  - `getAllMembers()`: Updated search to use only name and phone
  - `getTotalMembers()`: Updated search validation
  - `createMember()`: Removed email, address, age from INSERT
  - `updateMember()`: Removed email, address, age from UPDATE
  - `validateMember()`: Only validates name, phone, plan, start_date
- `ajax/members_add.php`: Removed email, age, address from form data
- `ajax/members_update.php`: Removed email, age, address from form data
- `views/members/members.php`:
  - Updated form to remove email, age, address input fields
  - Updated table columns (removed email column)
  - Updated search placeholder (removed email mention)
  - Updated `editMember()` function to not set removed fields
  - Table now shows: Name, Phone, Plan, End Date, Status, Actions
- `views/members/member_profile.php`:
  - Removed email and address from display
  - Updated section title to "Member Information"
  - Profile card now shows phone number instead of email
  - Personal Information now only shows: Name, Phone, Gender
- `views/payments/payments.php`:
  - Updated member dropdown to show phone instead of email
  - Updated receipt display to show phone instead of email
- `views/payments/receipt.php`: Removed email row from receipt table

## Database Changes

### Members Table - Current Schema (Updated)
```sql
CREATE TABLE `members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL UNIQUE,
  `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
  `profile_picture` VARCHAR(255),
  `plan_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('active', 'expired', 'suspended') DEFAULT 'active',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`plan_id`) REFERENCES `membership_plans`(`id`) ON DELETE RESTRICT,
  INDEX `idx_status` (`status`),
  INDEX `idx_phone` (`phone`),
  INDEX `idx_end_date` (`end_date`),
  INDEX `idx_created_at` (`created_at`)
);
```

### Payments Table
```sql
MODIFY COLUMN payment_method ENUM('cash', 'easypaisa', 'jazzcash', 'nayapay', 'sadapay', 'bank_transfer');
```

### Users Table
```sql
-- Admin password changed from hashed to plain text
UPDATE users SET password = 'admin123' WHERE username = 'admin';
```

## Testing Instructions

### Test 1: Plain Text Password Login
1. Open: `http://localhost/Gym System/login.php`
2. Login with: 
   - Username: `admin`
   - Password: `admin123`

## Removed Database Columns
- `members.email` - No longer needed
- `members.address` - No longer needed
- `members.age` - No longer needed
- `members.idx_email` index - Removed with email column

### Test 2: Member Picture Upload
1. Go to Members page
2. Click "Add Member"
3. Fill in form fields
4. Upload a JPG/PNG photo in "Profile Picture" field
5. Save member
6. Click eye icon to view profile - photo should display

### Test 3: New Payment Methods
1. Go to Payments page
2. Click "Add Payment"
3. Select different payment methods - see colored badges:
   - Cash (Yellow)
   - Easy Paisa (Blue)
   - Jazz Cash (Primary Blue)
   - Naya Pay (Green)
   - Sada Pay (Gray)
   - Bank Transfer (Dark)
4. Complete payment and view it in the table

## File Permissions

Ensure `uploads/` directory is writable:
```bash
chmod -R 755 uploads/
```

## Backwards Compatibility

- ✅ All existing data preserved
- ✅ Existing members work without pictures (NULL allowed)
- ✅ Existing payments still visible in new payment methods list
- ✅ Dashboard still calculates revenue correctly

## Future Enhancements

- Add Whatsapp payment method
- Add image compression before upload
- Add image cropping tool
- Add payment receipt with method-specific details
- Add SMS confirmation for digital payments
