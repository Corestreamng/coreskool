# CoreSkool Deployment Guide

## Deploying to cPanel Hosting

This guide will help you deploy CoreSkool to your cPanel hosting account.

### Prerequisites

Before deployment, ensure you have:
- cPanel hosting account with PHP 7.4+ and MySQL 5.7+
- FTP/SFTP access or cPanel File Manager access
- Database credentials (provided by hosting provider)
- Email account created in cPanel for system emails

### Step 1: Prepare Your cPanel Account

#### 1.1 Create Database
1. Log into cPanel
2. Navigate to **MySQL Databases**
3. Create a new database (e.g., `coinswipe_coreskool`)
4. Create a database user (e.g., `coinswipe_usercoreskool`)
5. Set a strong password
6. Add user to database with **ALL PRIVILEGES**
7. Note down:
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost`)

#### 1.2 Create Email Account
1. In cPanel, go to **Email Accounts**
2. Create email: `noreply@yourdomain.com`
3. Set a strong password
4. Note down email credentials

### Step 2: Upload Files

#### Option A: Using File Manager (Recommended for beginners)
1. In cPanel, open **File Manager**
2. Navigate to `public_html` directory
3. Click **Upload** and select all CoreSkool files
4. Wait for upload to complete
5. Extract if you uploaded as a ZIP file

#### Option B: Using FTP/SFTP
1. Connect to your server using FileZilla or similar FTP client
2. Navigate to `public_html` directory
3. Upload all CoreSkool files
4. Ensure file permissions are correct:
   - Directories: 755
   - Files: 644
   - `public/uploads/`: 777 (writable)

### Step 3: Configure the System

#### 3.1 Update Database Configuration
1. Navigate to `config/database.php`
2. Update with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'coinswipe_coreskool');
define('DB_USER', 'coinswipe_usercoreskool');
define('DB_PASS', 'your_database_password');
```

#### 3.2 Update Main Configuration
1. Open `config/config.php`
2. Update BASE_URL:
```php
define('BASE_URL', 'https://coreskool.coinswipe.xyz/');
```
3. Update email settings:
```php
define('SMTP_USERNAME', 'noreply@coreskool.coinswipe.xyz');
define('SMTP_PASSWORD', 'your_email_password');
define('SMTP_HOST', 'mail.coreskool.coinswipe.xyz');
```

### Step 4: Install Database

1. Open your browser and navigate to: `https://yourdomain.com/install.php`
2. The installer will create all database tables
3. Wait for completion message
4. Note the default admin credentials shown
5. **Important**: After successful installation, delete or rename `install.php` for security

### Step 5: First Login

1. Navigate to: `https://yourdomain.com`
2. Login with default credentials:
   - **Email**: admin@coreskool.coinswipe.xyz
   - **Password**: admin123
3. **Immediately change the default password!**

### Step 6: Post-Installation Configuration

#### 6.1 Change Admin Password
1. Click on your profile
2. Go to Settings
3. Change password to a strong, unique password

#### 6.2 Update School Information
1. Go to Settings > School Settings
2. Update:
   - School name
   - Address
   - Contact information
   - Upload school logo

#### 6.3 Configure Academic Year
1. Go to Settings > Academic Years
2. Create the current academic year
3. Create terms for the year
4. Set as current

#### 6.4 Setup Email & SMS
1. Test email sending from Messages
2. Configure SMS gateway if needed in `config/config.php`

### Step 7: Security Hardening

#### 7.1 File Permissions
Ensure correct permissions:
```bash
# Directories
chmod 755 app/ config/ public/ database/
chmod 777 public/uploads/ logs/

# Files
chmod 644 *.php
chmod 644 config/*.php
```

#### 7.2 Protect Configuration Files
Add to your `.htaccess` (already included):
```apache
<FilesMatch "^(config|database|email)\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

#### 7.3 Enable HTTPS (Important!)
1. In cPanel, go to **SSL/TLS**
2. Enable AutoSSL or install Let's Encrypt certificate
3. After SSL is active, uncomment these lines in `.htaccess`:
```apache
<IfModule mod_rewrite.c>
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

#### 7.4 Hide Sensitive Files
Ensure `.env`, `.git`, and configuration files are protected (done automatically by `.htaccess`)

### Step 8: Initial Data Setup

#### 8.1 Create Classes
1. Go to Admin > Classes
2. Create your school classes (e.g., JSS1, JSS2, SS1, etc.)

#### 8.2 Add Subjects
1. Go to Admin > Subjects
2. Add all subjects taught in your school

#### 8.3 Create Fee Types
1. Go to Admin > Fees
2. Define fee types (Tuition, Books, Uniform, etc.)
3. Assign fees to classes

#### 8.4 Add Users
1. Add Teachers
2. Add Students (with class assignments)
3. Add Parents (link to students)
4. Add Exam Officers
5. Add Cashiers

### Troubleshooting Common Issues

#### Database Connection Failed
**Error**: "Database connection failed"
**Solution**:
- Verify database credentials in `config/database.php`
- Ensure database user has proper privileges
- Check if database exists
- Confirm database host (usually `localhost`)

#### Email Not Sending
**Error**: Emails not being delivered
**Solution**:
- Verify email credentials in `config/config.php`
- Check SMTP settings (use mail.yourdomain.com)
- Ensure email account is not suspended
- Check cPanel email logs

#### Permission Denied
**Error**: "Permission denied" when uploading files
**Solution**:
- Set `public/uploads/` to 777
- Ensure web server has write access
- Check file ownership (should be your cPanel user)

#### White Screen / Blank Page
**Error**: White screen appears
**Solution**:
- Enable error display in `config/config.php` (temporarily):
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```
- Check PHP error logs in cPanel
- Verify PHP version is 7.4+
- Ensure all required PHP extensions are installed

#### 404 Errors for Assets
**Error**: CSS/JS files not loading
**Solution**:
- Check BASE_URL is correct in `config/config.php`
- Verify files uploaded to correct directory
- Clear browser cache
- Check `.htaccess` is present in root and public folders

#### Session Issues
**Error**: Keeps logging out or session errors
**Solution**:
- Check PHP session settings
- Ensure `/tmp` directory is writable
- Increase session timeout in `config/config.php`
- Clear browser cookies

### Performance Optimization

#### Enable Caching
1. In cPanel, enable **OPcache** under PHP settings
2. Enable browser caching (already in `.htaccess`)
3. Enable Gzip compression (already in `.htaccess`)

#### Database Optimization
1. In cPanel, go to **phpMyAdmin**
2. Select your database
3. Run **Optimize table** on all tables monthly

#### CDN Integration (Optional)
For better performance, consider using a CDN for static assets:
1. Upload CSS/JS/images to CDN
2. Update asset URLs in templates

### Backup Strategy

#### Automated Backups (Recommended)
1. In cPanel, enable automatic backups
2. Schedule daily database backups
3. Schedule weekly full backups

#### Manual Backup
1. **Database**: Use phpMyAdmin to export database
2. **Files**: Download entire directory via FTP
3. Store backups off-site (Google Drive, Dropbox, etc.)

### Maintenance

#### Regular Tasks
- **Daily**: Check error logs
- **Weekly**: Review user activity logs
- **Monthly**: Database optimization
- **Quarterly**: Update admin passwords
- **Yearly**: Review and archive old data

#### Monitoring
1. Monitor disk space usage
2. Check database size
3. Review email sending logs
4. Monitor user login attempts

### Scaling for Growth

#### When to Upgrade Hosting
Upgrade when:
- Users exceed 5,000 active users
- Database size exceeds 5GB
- Response time is slow (>3 seconds)
- Regular timeout errors occur

#### Upgrade Path
1. **Shared Hosting** → VPS (Virtual Private Server)
2. **VPS** → Dedicated Server
3. **Dedicated** → Cloud Infrastructure (AWS, Azure, GCP)

### Multi-School (SaaS) Deployment

If deploying as SaaS for multiple schools:

1. Enable multi-tenancy in `config/config.php`:
```php
define('MULTI_TENANT_MODE', true);
```

2. Setup subdomain wildcard in cPanel:
   - Add wildcard DNS record: `*.yourdomain.com`
   - Configure subdomain routing

3. Each school gets unique subdomain:
   - `school1.yourdomain.com`
   - `school2.yourdomain.com`

### Support

For deployment assistance:
- **Email**: admin@coreskool.coinswipe.xyz
- **Website**: https://coreskool.coinswipe.xyz

### Checklist

Use this checklist during deployment:

- [ ] Database created
- [ ] Database user created with privileges
- [ ] Email account created
- [ ] Files uploaded to public_html
- [ ] File permissions set correctly
- [ ] config/database.php updated
- [ ] config/config.php updated
- [ ] install.php executed successfully
- [ ] Default password changed
- [ ] SSL certificate installed
- [ ] HTTPS redirect enabled
- [ ] School information updated
- [ ] Academic year configured
- [ ] Classes created
- [ ] Subjects added
- [ ] Fee types configured
- [ ] Test users added
- [ ] Email sending tested
- [ ] Backup system configured
- [ ] Error logging checked

### Conclusion

Your CoreSkool system should now be fully deployed and operational on cPanel hosting. For ongoing support and updates, refer to the main README.md file.

**Remember**: Always keep your system updated, maintain regular backups, and monitor security logs.

---

**Version**: 1.0.0
**Last Updated**: December 2024
