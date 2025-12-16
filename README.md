# CoreSkool - Comprehensive School Management System

A complete, enterprise-grade School Management System built with PHP, MySQL, HTML5, CSS3, and JavaScript. Designed for scalability, multi-tenancy (SaaS), and deployed on cPanel hosting.

## 🎯 Features

### Multi-Role User System
- **Administrator**: Full system access and management
- **Teachers**: Class management, attendance, results entry
- **Students**: View results, attendance, take CBT exams
- **Parents**: Monitor wards' performance and attendance
- **Exam Officers**: Exam creation and results management
- **Cashiers**: Fee collection and payment processing

### Core Modules

#### 📚 Academic Management
- **Student Management**: Add, edit, delete students with matric number generation
- **Teacher Management**: Assign teachers to classes and subjects (1 class → 1-3 teachers)
- **Class Management**: Create and manage classes with capacity limits
- **Subject Management**: Define and assign subjects per class
- **Timetable Management**: Create and manage class schedules

#### 📝 Examination System
- **Exam Management**: Create exams with multiple types (mid-term, final, quiz)
- **Results Management**: Comprehensive result entry, compilation, and reports
- **Result Templates**: Customizable report cards with ratings (punctuality, neatness, etc.)
- **CBT System**: Computer-Based Testing with multiple question types
- **Position Calculation**: Automatic ranking and grade calculation

#### 📊 Attendance & Monitoring
- **Daily Attendance**: Mark and track student attendance
- **Attendance Reports**: Generate class and individual attendance reports
- **Attendance Statistics**: Real-time attendance percentage tracking

#### 💰 Financial Management
- **Fee Types**: Define multiple fee categories
- **Fee Assignment**: Assign fees per class
- **Payment Recording**: Record payments with multiple methods (cash, bank transfer, card)
- **Payment Reports**: Generate daily, monthly, and custom financial reports
- **Outstanding Fees**: Track pending payments per student

#### 📧 Communication System
- **Bulk Messaging**: Send messages via Email, SMS, or In-app
- **Targeted Messaging**: Message specific groups (all users, staff, parents, students, specific class)
- **SMS Integration**: Enterprise SMS gateway support
- **Email Notifications**: Automated email notifications
- **In-app Notifications**: Real-time notification system

#### 🎓 Learning Management System (LMS)
- **Course Creation**: Create courses with modules and lessons
- **Content Management**: Upload videos, documents, and resources
- **Student Enrollment**: Enroll students in courses
- **Progress Tracking**: Monitor student learning progress

#### 📚 Library Management
- **Book Catalog**: Manage book inventory with ISBN tracking
- **Book Issuance**: Issue and return books
- **Fine Calculation**: Automatic fine calculation for late returns

#### 🏠 Hostel Management
- **Hostel Creation**: Manage hostels (boys, girls, mixed)
- **Room Allocation**: Allocate students to rooms
- **Capacity Management**: Track room occupancy

#### 🌐 Multi-Language Support
- **English**: Default language
- **Arabic**: Full Arabic translation for Islamic schools
- **Extensible**: Easy to add more languages

#### 📱 Responsive Design
- Mobile-friendly interface
- Works on tablets, phones, and desktops
- Modern gradient UI with purple and blue themes

### Advanced Features
- **Promotion System**: Automatic student promotion to next class
- **Report Card Generation**: Beautiful, printable report cards
- **Activity Logging**: Complete audit trail
- **Role-Based Permissions**: Granular permission system
- **Multi-tenancy**: SaaS-ready with school isolation
- **Data Export**: Export to CSV, PDF
- **Real-time Statistics**: Dashboard with live data

## 🚀 Technology Stack

- **Backend**: PHP 7.4+ (Pure Vanilla PHP, No Framework)
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Deployment**: cPanel Compatible
- **Email**: PHP Mail / SMTP (cPanel compatible)
- **SMS**: Configurable SMS Gateway Integration

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server (cPanel)
- PDO PHP Extension
- MB String PHP Extension
- cURL PHP Extension (for SMS/API features)

## 🔧 Installation

### Step 1: Clone Repository
```bash
git clone https://github.com/Corestreamng/coreskool.git
cd coreskool
```

### Step 2: Configure Database
Edit `config/database.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
```

### Step 3: Configure Email
Edit `config/config.php` with your email settings:
```php
define('SMTP_HOST', 'mail.yourdomain.com');
define('SMTP_USERNAME', 'noreply@yourdomain.com');
define('SMTP_PASSWORD', 'your_email_password');
```

### Step 4: Install Database
1. Upload all files to your cPanel public_html directory
2. Visit: `https://yourdomain.com/install.php`
3. The installer will create all necessary tables

### Step 5: Login
**Default Credentials:**
- **Email**: admin@coreskool.coinswipe.xyz
- **Password**: admin123

**⚠️ Important**: Change the default password immediately after first login!

## 🎨 User Interface

The system features a modern, gradient-based design with:
- Sky blue and purple color schemes
- Smooth animations and transitions
- Card-based layouts
- Responsive grid system
- Font Awesome icons
- Clean, professional appearance

## 📱 Login Methods

Different user roles have specific login methods:

| Role | Login Method |
|------|-------------|
| Admin | Email or Phone |
| Teacher | Phone Number |
| Student | Matric Number |
| Parent | Email or Phone |
| Exam Officer | Email or Phone |
| Cashier | Email or Phone |

## 🗂️ Project Structure

```
coreskool/
├── app/
│   ├── controllers/      # Application controllers
│   ├── models/          # Database models
│   ├── views/           # View templates
│   │   ├── admin/      # Admin views
│   │   ├── teacher/    # Teacher views
│   │   ├── student/    # Student views
│   │   ├── parent/     # Parent views
│   │   └── shared/     # Shared components
│   ├── middleware/      # Middleware (authentication, etc.)
│   └── helpers/         # Helper functions
├── config/              # Configuration files
│   ├── config.php      # Main configuration
│   ├── database.php    # Database configuration
│   └── email.php       # Email configuration
├── database/
│   ├── migrations/      # SQL migration files
│   └── seeds/          # Database seeders
├── public/              # Public web directory
│   ├── admin/          # Admin panel
│   ├── teacher/        # Teacher panel
│   ├── student/        # Student panel
│   ├── parent/         # Parent panel
│   ├── exam_officer/   # Exam officer panel
│   ├── cashier/        # Cashier panel
│   ├── assets/         # Static assets
│   │   ├── css/       # Stylesheets
│   │   ├── js/        # JavaScript files
│   │   └── images/    # Images
│   └── uploads/        # User uploads
├── services/            # External services
│   ├── sms/           # SMS service
│   ├── email/         # Email service
│   └── notification/  # Notification service
├── modules/             # Feature modules
│   ├── attendance/    # Attendance module
│   ├── results/       # Results module
│   ├── subjects/      # Subjects module
│   ├── classes/       # Classes module
│   ├── exams/         # Exams module
│   ├── fees/          # Fees module
│   ├── messages/      # Messages module
│   ├── cbt/           # CBT module
│   ├── lms/           # LMS module
│   ├── library/       # Library module
│   ├── hostel/        # Hostel module
│   └── timetable/     # Timetable module
├── languages/           # Language translations
│   ├── en/            # English
│   └── ar/            # Arabic
└── logs/               # Application logs
```

## 🔐 Security Features

- Password hashing with PHP's password_hash()
- CSRF token protection
- SQL injection prevention (PDO with prepared statements)
- XSS protection (input sanitization)
- Session management with timeout
- Role-based access control
- Activity logging
- Secure file uploads

## 📊 Database Schema

The system includes comprehensive database tables for:
- Users (unified table for all roles)
- Schools (multi-tenancy)
- Classes and Subjects
- Student-Class assignments
- Teacher-Class assignments
- Attendance records
- Exams and Results
- Report Cards
- Fees and Payments
- Messages and Notifications
- CBT Exams and Attempts
- LMS Courses and Enrollments
- Library Books and Issues
- Hostel Allocations
- Timetables
- Activity Logs

## 🔄 API Endpoints

The system includes internal APIs for:
- AJAX notifications
- Real-time data updates
- Language switching
- File uploads
- Data exports

## 🌍 Multi-Language Implementation

To add a new language:
1. Create a new folder in `languages/` (e.g., `fr/` for French)
2. Copy `translations.php` from `languages/en/`
3. Translate all keys
4. Add language option in topbar

## 📞 SMS Integration

Configure SMS gateway in `config/config.php`:
```php
define('SMS_GATEWAY_URL', 'https://your-sms-gateway.com/api');
define('SMS_API_KEY', 'your-api-key');
define('SMS_SENDER_ID', 'CoreSkool');
```

## 🎓 Academic Year Management

1. Create academic years in Settings
2. Create terms for each academic year
3. Set current academic year and term
4. System automatically uses current year/term for operations

## 👥 User Management

### Adding Students
1. Navigate to Admin → Students → Add New
2. Fill in student details
3. Matric number is auto-generated
4. Assign to a class
5. Student can login with matric number

### Adding Teachers
1. Navigate to Admin → Teachers → Add New
2. Fill in teacher details
3. Assign to classes (1-3 classes per teacher)
4. Assign subjects to teach
5. Teacher can login with phone number

### Adding Parents
1. Navigate to Admin → Parents → Add New
2. Fill in parent details
3. Link to student(s) (wards)
4. Parent can login with email or phone

## 📧 Messaging System

Send messages to:
- **All Users**: Everyone in the system
- **All Staff**: Teachers, exam officers, cashiers
- **All Teachers**: Only teachers
- **All Students**: Only students
- **All Parents**: Only parents
- **Specific Class**: Students in selected class
- **Individual**: Specific person

Delivery methods:
- **In-app**: Notification within system
- **Email**: Send via email
- **SMS**: Send via SMS
- **All**: Send via all channels

## 📈 Reports

Generate reports for:
- Student performance
- Attendance summary
- Fee collection
- Exam statistics
- Class performance
- Teacher performance
- Custom date ranges

## 🎨 Customization

### Changing Colors
Edit `public/assets/css/style.css`:
```css
:root {
    --primary-color: #667eea;  /* Change to your brand color */
    --secondary-color: #764ba2;
}
```

### Changing Logo
Replace `public/assets/images/logo.png` with your school logo

### Changing School Name
Edit `config/config.php`:
```php
define('SITE_NAME', 'Your School Name');
```

## 🐛 Troubleshooting

### Database Connection Failed
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists

### Email Not Sending
- Check email configuration in `config/config.php`
- Verify SMTP credentials
- Check cPanel email account is active

### Permission Denied
- Check file permissions (755 for directories, 644 for files)
- Ensure `public/uploads/` is writable (777)

### Session Issues
- Check PHP session configuration
- Ensure `/tmp` is writable
- Clear browser cookies

## 📝 License

Copyright © 2024 CoreSkool. All rights reserved.

## 👥 Support

For support and inquiries:
- **Email**: admin@coreskool.coinswipe.xyz
- **Website**: https://coreskool.coinswipe.xyz

## 🔄 Updates

Check for updates regularly. Future updates will include:
- Mobile app integration
- Advanced analytics
- Parent mobile app
- Teacher mobile app
- Biometric attendance
- Video conferencing integration
- AI-powered insights

## 🙏 Credits

Developed by: Corestream NG
Built with ❤️ for educational institutions worldwide

---

**Version**: 1.0.0
**Last Updated**: December 2024
