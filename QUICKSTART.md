# CoreSkool Quick Start Guide

Get CoreSkool up and running in minutes!

## ⚡ Quick Installation (5 Minutes)

### Step 1: Upload Files (2 minutes)
1. Download or clone the repository
2. Upload all files to your cPanel `public_html` directory
3. Ensure `public/uploads/` directory exists and is writable

### Step 2: Configure Database (1 minute)
Edit `config/database.php`:
```php
define('DB_NAME', 'coinswipe_coreskool');
define('DB_USER', 'coinswipe_usercoreskool');
define('DB_PASS', 'Allen@780');
```

### Step 3: Configure Email (1 minute)
Edit `config/config.php`:
```php
define('BASE_URL', 'https://coreskool.coinswipe.xyz/');
define('SMTP_USERNAME', 'noreply@coreskool.coinswipe.xyz');
define('SMTP_PASSWORD', 'C01ne$w1pe');
```

### Step 4: Install Database (1 minute)
1. Visit: `https://coreskool.coinswipe.xyz/install.php`
2. Wait for tables to be created
3. Note the default credentials shown
4. **Delete install.php after installation**

### Step 5: Login & Start Using
1. Visit: `https://coreskool.coinswipe.xyz/`
2. Login with:
   - Email: `admin@coreskool.coinswipe.xyz`
   - Password: `admin123`
3. **Change password immediately!**

## 🎯 First Steps After Login

### 1. Change Admin Password (Mandatory)
- Click your profile
- Go to Settings
- Change password

### 2. Update School Information
- Go to Settings > School Settings
- Add school name, logo, contact details

### 3. Create Academic Year
- Settings > Academic Years
- Create current academic year
- Create terms (1st, 2nd, 3rd)
- Set as current

### 4. Add Classes
- Admin > Classes > Add New
- Create all your classes (e.g., JSS1, JSS2, SS1, etc.)

### 5. Add Subjects
- Admin > Subjects > Add New
- Add all subjects taught

### 6. Add Teachers
- Admin > Teachers > Add New
- Assign to classes and subjects

### 7. Add Students
- Admin > Students > Add New
- Matric numbers are auto-generated
- Assign to classes

### 8. Add Parents
- Admin > Parents > Add New
- Link to their children (wards)

## 📱 User Roles & Login Methods

### Admin
- **Login**: Email or Phone
- **Default**: admin@coreskool.coinswipe.xyz / admin123
- **Access**: Full system access

### Teacher
- **Login**: Phone number only
- **Access**: Classes, subjects, attendance, results

### Student
- **Login**: Matric number only
- **Example**: CS/2024/0001
- **Access**: Results, attendance, CBT, courses

### Parent
- **Login**: Email or Phone
- **Access**: View wards' performance, attendance, fees

### Exam Officer
- **Login**: Email or Phone
- **Access**: Exam management, results approval

### Cashier
- **Login**: Email or Phone
- **Access**: Fee collection, payment management

## 🚀 Key Features to Try

### Send a Message
1. Admin > Messages > Send Message
2. Select recipients (All, Staff, Students, Parents, Class)
3. Choose delivery method (Email, SMS, In-app, All)
4. Write message and send

### Add a Student
1. Admin > Students > Add New
2. Fill in student details
3. Assign to a class
4. Set a password
5. Save - Matric number auto-generated!

### Mark Attendance
1. Teacher > Attendance > Mark
2. Select class and date
3. Mark students as Present/Absent/Late
4. Save

### Enter Results
1. Teacher > Results > Enter Results
2. Select class, subject, and exam
3. Enter CA and Exam scores
4. Grades calculated automatically
5. Submit for approval

## 📊 Dashboard Overview

Each role has a custom dashboard with:
- **Welcome Banner**: With current date, time, and key metric
- **Statistics Cards**: 4 colorful cards with important numbers
- **Quick Actions**: Frequently used functions
- **Recent Activity**: Latest updates relevant to the role

## 💡 Pro Tips

### For Administrators
- Regular backups are essential (daily recommended)
- Review activity logs monthly
- Update school information seasonally
- Send bulk messages to keep everyone informed

### For Teachers
- Mark attendance daily
- Enter results before deadlines
- Use messaging to communicate with parents
- Keep class information updated

### For Students
- Check dashboard daily for updates
- Complete CBT tests on time
- Monitor your attendance percentage
- Review your results regularly

### For Parents
- Monitor ward attendance weekly
- Check results when published
- Pay fees on time
- Respond to school messages promptly

## 🔧 Quick Troubleshooting

### Can't Login?
- Check you're using the correct login method for your role
- Verify credentials are correct
- Clear browser cache and cookies
- Contact administrator

### Features Not Working?
- Refresh the page
- Clear browser cache
- Check internet connection
- Report to administrator

### Email Not Sending?
- Verify email settings in config
- Check cPanel email account is active
- Test with a simple message first

### Upload Fails?
- Check file size (max 5MB)
- Verify file type is allowed
- Ensure uploads directory is writable
- Contact administrator

## 📞 Getting Help

### Documentation
- **README.md**: Complete system overview
- **DEPLOYMENT.md**: Detailed deployment guide
- **FEATURES.md**: All features explained
- **SECURITY.md**: Security guidelines

### Support
- **Email**: admin@coreskool.coinswipe.xyz
- **Website**: https://coreskool.coinswipe.xyz

## 🎓 Learning Resources

### For First-Time Users
1. Watch the dashboard tour (coming soon)
2. Read the user manual for your role
3. Try the sample data (if available)
4. Ask your administrator for training

### For Administrators
1. Read DEPLOYMENT.md completely
2. Review SECURITY.md for best practices
3. Understand all user roles
4. Plan your data structure before bulk import

## ⚠️ Important Reminders

1. **Change Default Password**: First thing after installation
2. **Delete install.php**: After successful installation
3. **Enable HTTPS**: In production environment
4. **Regular Backups**: Schedule daily backups
5. **Update Information**: Keep school details current
6. **Test Email**: Before sending to everyone
7. **Monitor Logs**: Check weekly for issues
8. **User Training**: Train staff before go-live

## 🎉 You're Ready!

Your CoreSkool system is now set up and ready to use. Start by:
1. Adding your school's basic data (classes, subjects)
2. Creating user accounts (teachers, students, parents)
3. Testing core features (attendance, messaging)
4. Going live with your school!

## 📈 Next Steps

After initial setup:
- [ ] Import bulk student data (if available)
- [ ] Set up fee structure
- [ ] Create exam timetable
- [ ] Configure SMS gateway
- [ ] Train all users
- [ ] Go live!

---

**Need More Help?**
- Full documentation: See README.md
- Deployment issues: See DEPLOYMENT.md
- Contributing: See CONTRIBUTING.md

**Have Feedback?**
We'd love to hear from you! Contact us at admin@coreskool.coinswipe.xyz

---

**Version**: 1.0.0
**Last Updated**: December 2024

Happy School Managing! 🎓✨
