-- CoreSkool Database Schema
-- School Management System

-- Schools table (for multi-tenancy/SaaS)
CREATE TABLE IF NOT EXISTS schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    logo VARCHAR(255),
    website VARCHAR(255),
    subdomain VARCHAR(100) UNIQUE,
    subscription_status ENUM('active', 'suspended', 'expired') DEFAULT 'active',
    subscription_end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table (unified for all user types)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT,
    username VARCHAR(100) UNIQUE,
    email VARCHAR(255),
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student', 'parent', 'exam_officer', 'cashier', 'staff') NOT NULL,
    matric_number VARCHAR(50) UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    other_names VARCHAR(100),
    gender ENUM('male', 'female') NOT NULL,
    date_of_birth DATE,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Nigeria',
    avatar VARCHAR(255),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    email_verified TINYINT(1) DEFAULT 0,
    phone_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_school_role (school_id, role),
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_matric (matric_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles and Permissions
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    module VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_permissions (
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (user_id, permission_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Academic Years and Terms
CREATE TABLE IF NOT EXISTS academic_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_current TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_current TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Classes
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    class_level INT,
    section VARCHAR(50),
    capacity INT DEFAULT 40,
    class_teacher_id INT,
    room_number VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (class_teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Class Teachers (1 class to many teachers, 1-3 teachers)
CREATE TABLE IF NOT EXISTS class_teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    teacher_id INT NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    assigned_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_class_teacher (class_id, teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student Class Assignment
CREATE TABLE IF NOT EXISTS student_classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    term_id INT,
    roll_number VARCHAR(50),
    status ENUM('active', 'promoted', 'repeated', 'transferred') DEFAULT 'active',
    assigned_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subjects
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50),
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Class Subjects
CREATE TABLE IF NOT EXISTS class_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT,
    academic_year_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    UNIQUE KEY unique_class_subject (class_id, subject_id, academic_year_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') NOT NULL,
    remarks TEXT,
    marked_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_attendance (student_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exams
CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    academic_year_id INT NOT NULL,
    term_id INT NOT NULL,
    exam_type ENUM('mid-term', 'final', 'quiz', 'assessment', 'mock') NOT NULL,
    start_date DATE,
    end_date DATE,
    status ENUM('scheduled', 'ongoing', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exam Timetable
CREATE TABLE IF NOT EXISTS exam_timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    exam_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(100),
    max_marks INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Results
CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    exam_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    term_id INT NOT NULL,
    ca_score DECIMAL(5,2) DEFAULT 0,
    exam_score DECIMAL(5,2) DEFAULT 0,
    total_score DECIMAL(5,2) AS (ca_score + exam_score) STORED,
    grade VARCHAR(5),
    remark VARCHAR(50),
    position INT,
    teacher_comment TEXT,
    entered_by INT,
    approved_by INT,
    status ENUM('draft', 'submitted', 'approved', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE,
    FOREIGN KEY (entered_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_result (student_id, subject_id, exam_id, term_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Report Cards
CREATE TABLE IF NOT EXISTS report_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    term_id INT NOT NULL,
    total_score DECIMAL(6,2),
    average_score DECIMAL(5,2),
    position INT,
    grade VARCHAR(5),
    teacher_comment TEXT,
    principal_comment TEXT,
    punctuality_rating INT DEFAULT 5,
    neatness_rating INT DEFAULT 5,
    attentiveness_rating INT DEFAULT 5,
    politeness_rating INT DEFAULT 5,
    attendance_present INT DEFAULT 0,
    attendance_absent INT DEFAULT 0,
    days_school_opened INT DEFAULT 0,
    next_term_begins DATE,
    status ENUM('draft', 'published') DEFAULT 'draft',
    generated_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_report_card (student_id, term_id, academic_year_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parent-Student Relationship
CREATE TABLE IF NOT EXISTS parent_student (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    student_id INT NOT NULL,
    relationship ENUM('father', 'mother', 'guardian', 'other') NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_parent_student (parent_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    sender_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    recipient_type ENUM('all', 'staff', 'teachers', 'students', 'parents', 'class', 'individual') NOT NULL,
    recipient_class_id INT,
    send_via ENUM('in-app', 'email', 'sms', 'all') NOT NULL,
    scheduled_time TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    status ENUM('draft', 'scheduled', 'sending', 'sent', 'failed') DEFAULT 'draft',
    total_recipients INT DEFAULT 0,
    sent_count INT DEFAULT 0,
    failed_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_class_id) REFERENCES classes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS message_recipients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at TIMESTAMP NULL,
    delivery_status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fees Management
CREATE TABLE IF NOT EXISTS fee_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    amount DECIMAL(10,2) NOT NULL,
    academic_year_id INT NOT NULL,
    term_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fee_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fee_type_id INT NOT NULL,
    class_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fee_type_id) REFERENCES fee_types(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    student_id INT NOT NULL,
    fee_type_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'card', 'cheque', 'online') NOT NULL,
    reference_number VARCHAR(100) UNIQUE,
    payment_date DATE NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    remarks TEXT,
    received_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_type_id) REFERENCES fee_types(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CBT (Computer-Based Testing) System
CREATE TABLE IF NOT EXISTS cbt_exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    duration INT NOT NULL COMMENT 'in minutes',
    total_marks INT NOT NULL,
    pass_marks INT NOT NULL,
    instructions TEXT,
    start_time TIMESTAMP NULL,
    end_time TIMESTAMP NULL,
    shuffle_questions TINYINT(1) DEFAULT 1,
    show_results TINYINT(1) DEFAULT 1,
    status ENUM('draft', 'published', 'completed') DEFAULT 'draft',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cbt_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cbt_exam_id INT NOT NULL,
    question TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'true_false', 'short_answer') NOT NULL,
    option_a VARCHAR(500),
    option_b VARCHAR(500),
    option_c VARCHAR(500),
    option_d VARCHAR(500),
    correct_answer VARCHAR(500) NOT NULL,
    marks INT DEFAULT 1,
    order_number INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cbt_exam_id) REFERENCES cbt_exams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cbt_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cbt_exam_id INT NOT NULL,
    student_id INT NOT NULL,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    score DECIMAL(5,2),
    total_questions INT,
    correct_answers INT,
    status ENUM('in_progress', 'submitted', 'abandoned') DEFAULT 'in_progress',
    ip_address VARCHAR(45),
    FOREIGN KEY (cbt_exam_id) REFERENCES cbt_exams(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cbt_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    student_answer TEXT,
    is_correct TINYINT(1),
    marks_obtained DECIMAL(5,2),
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attempt_id) REFERENCES cbt_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES cbt_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- LMS (Learning Management System)
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    thumbnail VARCHAR(255),
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS course_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    order_number INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS course_lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    video_url VARCHAR(500),
    document_url VARCHAR(500),
    duration INT COMMENT 'in minutes',
    order_number INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS course_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    progress DECIMAL(5,2) DEFAULT 0,
    completed_at TIMESTAMP NULL,
    status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (course_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Library Management
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255),
    isbn VARCHAR(50) UNIQUE,
    publisher VARCHAR(255),
    category VARCHAR(100),
    quantity INT DEFAULT 1,
    available_quantity INT DEFAULT 1,
    shelf_location VARCHAR(100),
    cover_image VARCHAR(255),
    status ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS book_issues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE,
    fine_amount DECIMAL(10,2) DEFAULT 0,
    status ENUM('issued', 'returned', 'overdue') DEFAULT 'issued',
    issued_by INT,
    returned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (returned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Timetable
CREATE TABLE IF NOT EXISTS timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    day_of_week ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(100),
    academic_year_id INT NOT NULL,
    term_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hostel Management
CREATE TABLE IF NOT EXISTS hostels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('boys', 'girls', 'mixed') NOT NULL,
    address TEXT,
    warden_id INT,
    capacity INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (warden_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hostel_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hostel_id INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    capacity INT DEFAULT 4,
    occupied INT DEFAULT 0,
    status ENUM('available', 'full', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hostel_allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    room_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    allocation_date DATE NOT NULL,
    vacate_date DATE,
    status ENUM('allocated', 'vacated') DEFAULT 'allocated',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES hostel_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50),
    link VARCHAR(500),
    is_read TINYINT(1) DEFAULT 0,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY unique_school_setting (school_id, setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default school for non-SaaS mode
INSERT INTO schools (name, code, email, subdomain, subscription_status) 
VALUES ('CoreSkool Academy', 'CS001', 'admin@coreskool.coinswipe.xyz', 'main', 'active');

-- Insert default admin user (password: admin123)
INSERT INTO users (school_id, email, phone, password, role, first_name, last_name, gender, status) 
VALUES (1, 'admin@coreskool.coinswipe.xyz', '08012345678', '$2y$10$oY1NSNGLF22bzdDhCbdxUuYEcTKV.ucL/8jPS/ICJXFIghvvRBaCO', 'admin', 'System', 'Administrator', 'male', 'active');
