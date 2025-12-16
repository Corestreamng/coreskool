# Admin Password Fix Guide

## Problem
The admin login credentials `admin@coreskool.coinswipe.xyz` / `admin123` were showing "Invalid credentials" error.

## Root Cause
The database migration file contained an incorrect bcrypt password hash. The hash used was:
```
$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```

This is a well-known Laravel framework test hash that corresponds to the password **"password"** instead of **"admin123"**.

## Solution
The password hash has been corrected to:
```
$2y$10$oY1NSNGLF22bzdDhCbdxUuYEcTKV.ucL/8jPS/ICJXFIghvvRBaCO
```

This hash correctly corresponds to the password **"admin123"**.

## How to Apply the Fix

### Option 1: Fresh Installation (Recommended)
If you haven't deployed to production yet or can reinstall:

1. Delete the `.installed` file if it exists
2. Run the installation script: `https://coreskool.coinswipe.xyz/install.php`
3. The correct password hash is now in the migration file

### Option 2: Using the Fix Script (For Existing Installations)
If the database is already installed:

1. Access the fix script: `https://coreskool.coinswipe.xyz/fix_admin_password.php`
2. The script will update the admin password to "admin123"
3. **Important**: Delete the `fix_admin_password.php` file after use for security

### Option 3: Manual SQL Update
If you have direct database access:

```sql
UPDATE users 
SET password = '$2y$10$oY1NSNGLF22bzdDhCbdxUuYEcTKV.ucL/8jPS/ICJXFIghvvRBaCO'
WHERE email = 'admin@coreskool.coinswipe.xyz' AND role = 'admin';
```

Or run the migration file:
```bash
mysql -u username -p database_name < database/migrations/002_fix_admin_password.sql
```

## Verification

After applying the fix, you should be able to login with:
- **Email**: admin@coreskool.coinswipe.xyz
- **Password**: admin123

## Security Recommendations

1. **Change the default password immediately** after first login
2. Delete the `fix_admin_password.php` file after use
3. Never commit actual database credentials to version control
4. Use environment variables for sensitive configuration in production

## Technical Details

The authentication system uses PHP's `password_verify()` function which compares the provided password against the stored bcrypt hash. The incorrect hash was causing all login attempts with "admin123" to fail because:

```php
password_verify('admin123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi') // Returns false
password_verify('password', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi') // Returns true
```

After the fix:
```php
password_verify('admin123', '$2y$10$oY1NSNGLF22bzdDhCbdxUuYEcTKV.ucL/8jPS/ICJXFIghvvRBaCO') // Returns true
```

## Files Modified

1. `database/migrations/001_create_tables.sql` - Updated admin user password hash
2. `database/migrations/002_fix_admin_password.sql` - New migration for updating existing installations
3. `fix_admin_password.php` - Web-accessible script for easy password fix

## Contact

If you continue to experience login issues after applying this fix, please contact:
- Email: admin@coreskool.coinswipe.xyz
- Website: https://coreskool.coinswipe.xyz
