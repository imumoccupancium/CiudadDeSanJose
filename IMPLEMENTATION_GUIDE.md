# Ciudad De San Jose - Implementation Guide

## 🚀 Developer's Guide to Building the System

This guide provides step-by-step instructions for implementing the Ciudad De San Jose subdivision management system.

---

## Table of Contents
1. [Development Environment Setup](#development-environment-setup)
2. [Database Implementation](#database-implementation)
3. [Backend Development](#backend-development)
4. [Frontend Development](#frontend-development)
5. [QR Code Integration](#qr-code-integration)
6. [Security Implementation](#security-implementation)
7. [Testing Strategy](#testing-strategy)
8. [Deployment Guide](#deployment-guide)

---

## Development Environment Setup

### Prerequisites Installation

#### 1. Install XAMPP
```
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP to C:\xampp
3. Start Apache and MySQL services
4. Verify installation at http://localhost
```

#### 2. Configure PHP
Edit `C:\xampp\php\php.ini`:
```ini
; Enable required extensions
extension=gd2
extension=pdo_mysql
extension=openssl
extension=mbstring

; Set upload limits
upload_max_filesize = 10M
post_max_size = 10M

; Set timezone
date.timezone = Asia/Manila
```

#### 3. Configure MySQL
```sql
-- Create database
CREATE DATABASE ciudad_de_san_jose CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create admin user
CREATE USER 'cdsjadmin'@'localhost' IDENTIFIED BY 'SecurePassword123!';
GRANT ALL PRIVILEGES ON ciudad_de_san_jose.* TO 'cdsjadmin'@'localhost';
FLUSH PRIVILEGES;
```

### Project Structure

Create the following directory structure:
```
C:\xampp\htdocs\CiudadDeSanJose\
│
├── config/
│   ├── database.php          # Database configuration
│   ├── config.php             # General configuration
│   └── constants.php          # System constants
│
├── includes/
│   ├── header.php             # Common header
│   ├── footer.php             # Common footer
│   ├── navbar.php             # Navigation bar
│   └── functions.php          # Utility functions
│
├── assets/
│   ├── css/
│   │   ├── style.css          # Main stylesheet
│   │   ├── admin.css          # Admin panel styles
│   │   └── scanner.css        # Scanner interface styles
│   ├── js/
│   │   ├── main.js            # Main JavaScript
│   │   ├── qr-scanner.js      # QR scanning logic
│   │   └── validation.js      # Form validation
│   ├── images/
│   │   └── logo.png           # System logo
│   └── libs/
│       ├── jsQR.js            # QR code library
│       └── jquery.min.js      # jQuery (optional)
│
├── admin/
│   ├── index.php              # Admin dashboard
│   ├── homeowners.php         # Homeowner management
│   ├── add_homeowner.php      # Add new homeowner
│   ├── edit_homeowner.php     # Edit homeowner
│   ├── reports.php            # Reports page
│   └── settings.php           # System settings
│
├── scanner/
│   ├── index.php              # Scanner interface
│   ├── check-in.php           # Check-in page
│   └── check-out.php          # Check-out page
│
├── portal/
│   ├── index.php              # Homeowner dashboard
│   ├── profile.php            # Profile management
│   ├── qr-code.php            # QR code download
│   ├── visitors.php           # Visitor registration
│   └── vehicles.php           # Vehicle management
│
├── api/
│   ├── validate_qr.php        # QR code validation
│   ├── check_in.php           # Check-in API
│   ├── check_out.php          # Check-out API
│   └── generate_qr.php        # QR generation API
│
├── qrcodes/                   # Generated QR codes
├── uploads/                   # User uploads (photos)
├── database/
│   └── schema.sql             # Database schema
│
├── index.php                  # Landing page
├── login.php                  # Login page
├── logout.php                 # Logout handler
└── .htaccess                  # Apache configuration
```

---

## Database Implementation

### Step 1: Create Database Schema

Create `database/schema.sql`:

```sql
-- ============================================
-- Ciudad De San Jose Database Schema
-- ============================================

USE ciudad_de_san_jose;

-- Table: homeowners
CREATE TABLE homeowners (
    homeowner_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    lot_number VARCHAR(50) NOT NULL,
    block VARCHAR(50) NOT NULL,
    phase VARCHAR(50),
    address TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    photo_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_lot (lot_number),
    INDEX idx_status (status),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: users
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    role ENUM('admin', 'security', 'homeowner') NOT NULL,
    homeowner_id INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (homeowner_id) REFERENCES homeowners(homeowner_id) ON DELETE SET NULL,
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: qr_codes
CREATE TABLE qr_codes (
    qr_id INT PRIMARY KEY AUTO_INCREMENT,
    homeowner_id INT NOT NULL,
    qr_code_data TEXT NOT NULL,
    qr_hash VARCHAR(255) UNIQUE NOT NULL,
    qr_image_path VARCHAR(255),
    generated_date DATETIME NOT NULL,
    expiry_date DATETIME NOT NULL,
    status ENUM('active', 'expired', 'revoked') DEFAULT 'active',
    qr_type ENUM('permanent', 'temporary') DEFAULT 'permanent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (homeowner_id) REFERENCES homeowners(homeowner_id) ON DELETE CASCADE,
    INDEX idx_hash (qr_hash),
    INDEX idx_status (status),
    INDEX idx_expiry (expiry_date),
    INDEX idx_homeowner (homeowner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: access_logs
CREATE TABLE access_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    homeowner_id INT NOT NULL,
    qr_id INT NOT NULL,
    action_type ENUM('check-in', 'check-out') NOT NULL,
    timestamp DATETIME NOT NULL,
    gate_location VARCHAR(100),
    security_personnel_id INT,
    vehicle_plate VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (homeowner_id) REFERENCES homeowners(homeowner_id),
    FOREIGN KEY (qr_id) REFERENCES qr_codes(qr_id),
    FOREIGN KEY (security_personnel_id) REFERENCES users(user_id),
    INDEX idx_homeowner (homeowner_id),
    INDEX idx_timestamp (timestamp),
    INDEX idx_action (action_type),
    INDEX idx_date (DATE(timestamp))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: vehicles
CREATE TABLE vehicles (
    vehicle_id INT PRIMARY KEY AUTO_INCREMENT,
    homeowner_id INT NOT NULL,
    plate_number VARCHAR(20) UNIQUE NOT NULL,
    make VARCHAR(50),
    model VARCHAR(50),
    color VARCHAR(30),
    year INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (homeowner_id) REFERENCES homeowners(homeowner_id) ON DELETE CASCADE,
    INDEX idx_plate (plate_number),
    INDEX idx_homeowner (homeowner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: household_members
CREATE TABLE household_members (
    member_id INT PRIMARY KEY AUTO_INCREMENT,
    homeowner_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    relationship VARCHAR(50),
    age INT,
    contact_number VARCHAR(20),
    photo_path VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (homeowner_id) REFERENCES homeowners(homeowner_id) ON DELETE CASCADE,
    INDEX idx_homeowner (homeowner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: visitors
CREATE TABLE visitors (
    visitor_id INT PRIMARY KEY AUTO_INCREMENT,
    homeowner_id INT NOT NULL,
    visitor_name VARCHAR(150) NOT NULL,
    visitor_contact VARCHAR(20),
    visit_date DATE NOT NULL,
    visit_time_start TIME NOT NULL,
    visit_time_end TIME,
    qr_code_hash VARCHAR(255) UNIQUE,
    status ENUM('pending', 'approved', 'completed', 'cancelled') DEFAULT 'pending',
    actual_checkin DATETIME,
    actual_checkout DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (homeowner_id) REFERENCES homeowners(homeowner_id) ON DELETE CASCADE,
    INDEX idx_visit_date (visit_date),
    INDEX idx_homeowner (homeowner_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: security_incidents
CREATE TABLE security_incidents (
    incident_id INT PRIMARY KEY AUTO_INCREMENT,
    incident_type VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    reported_by INT NOT NULL,
    incident_date DATETIME NOT NULL,
    location VARCHAR(100),
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('open', 'investigating', 'resolved', 'closed') DEFAULT 'open',
    resolution_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reported_by) REFERENCES users(user_id),
    INDEX idx_date (incident_date),
    INDEX idx_status (status),
    INDEX idx_severity (severity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
INSERT INTO users (username, password_hash, full_name, email, role, status) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'System Administrator', 'admin@ciudaddesanjose.com', 'admin', 'active');
-- Default password: password (CHANGE THIS IN PRODUCTION!)
```

### Step 2: Import Database

```bash
# Via MySQL command line
mysql -u root -p ciudad_de_san_jose < database/schema.sql

# Or via phpMyAdmin
# 1. Open http://localhost/phpmyadmin
# 2. Select ciudad_de_san_jose database
# 3. Click Import
# 4. Choose schema.sql file
# 5. Click Go
```

---

## Backend Development

### Step 1: Database Configuration

Create `config/database.php`:

```php
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'cdsjadmin');
define('DB_PASS', 'SecurePassword123!');
define('DB_NAME', 'ciudad_de_san_jose');

// Create database connection
class Database {
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}
?>
```

### Step 2: System Configuration

Create `config/config.php`:

```php
<?php
session_start();

// System configuration
define('SITE_NAME', 'Ciudad De San Jose');
define('SITE_URL', 'http://localhost/CiudadDeSanJose');
define('ADMIN_EMAIL', 'admin@ciudaddesanjose.com');

// File paths
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('QR_CODE_PATH', __DIR__ . '/../qrcodes/');

// Security settings
define('SECRET_KEY', 'your-secret-key-here-change-in-production');
define('SESSION_TIMEOUT', 3600); // 1 hour

// QR Code settings
define('QR_CODE_VALIDITY_DAYS', 365);
define('QR_CODE_SIZE', 10);
define('QR_CODE_MARGIN', 2);

// Timezone
date_default_timezone_set('Asia/Manila');

// Include database
require_once 'database.php';
?>
```

### Step 3: Utility Functions

Create `includes/functions.php`:

```php
<?php
// Sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Redirect function
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

// Generate secure hash
function generateHash($data) {
    return hash('sha256', $data . SECRET_KEY);
}

// Format date
function formatDate($date) {
    return date('F d, Y', strtotime($date));
}

// Format datetime
function formatDateTime($datetime) {
    return date('F d, Y h:i A', strtotime($datetime));
}

// Generate random string
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Upload file
function uploadFile($file, $destination) {
    $target_dir = UPLOAD_PATH . $destination . '/';
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $destination . '/' . $new_filename;
    }
    
    return false;
}

// Check session timeout
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            redirect('/login.php?timeout=1');
        }
    }
    $_SESSION['last_activity'] = time();
}
?>
```

### Step 4: Authentication System

Create `login.php`:

```php
<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['homeowner_id'] = $user['homeowner_id'];
        $_SESSION['last_activity'] = time();
        
        // Update last login
        $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        
        // Redirect based on role
        switch ($user['role']) {
            case 'admin':
                redirect('/admin/index.php');
                break;
            case 'security':
                redirect('/scanner/index.php');
                break;
            case 'homeowner':
                redirect('/portal/index.php');
                break;
        }
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1><?php echo SITE_NAME; ?></h1>
            <h2>Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
```

---

## QR Code Integration

### Step 1: Install PHP QR Code Library

Download phpqrcode from: https://sourceforge.net/projects/phpqrcode/

Extract to: `C:\xampp\htdocs\CiudadDeSanJose\libs\phpqrcode\`

### Step 2: QR Code Generation API

Create `api/generate_qr.php`:

```php
<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../libs/phpqrcode/qrlib.php';

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $homeowner_id = intval($_POST['homeowner_id']);
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get homeowner details
    $stmt = $conn->prepare("SELECT * FROM homeowners WHERE homeowner_id = ?");
    $stmt->execute([$homeowner_id]);
    $homeowner = $stmt->fetch();
    
    if (!$homeowner) {
        http_response_code(404);
        echo json_encode(['error' => 'Homeowner not found']);
        exit();
    }
    
    // Revoke existing QR codes
    $stmt = $conn->prepare("UPDATE qr_codes SET status = 'revoked' WHERE homeowner_id = ? AND status = 'active'");
    $stmt->execute([$homeowner_id]);
    
    // Generate QR code data
    $qr_data = [
        'id' => $homeowner_id,
        'name' => $homeowner['first_name'] . ' ' . $homeowner['last_name'],
        'lot' => $homeowner['lot_number'],
        'block' => $homeowner['block'],
        'generated' => date('Y-m-d H:i:s'),
        'expires' => date('Y-m-d H:i:s', strtotime('+' . QR_CODE_VALIDITY_DAYS . ' days'))
    ];
    
    $qr_json = json_encode($qr_data);
    $qr_hash = generateHash($qr_json);
    
    // Add hash to data
    $qr_data['hash'] = $qr_hash;
    $qr_json_final = json_encode($qr_data);
    
    // Generate QR code image
    $qr_filename = 'qr_' . $homeowner_id . '_' . time() . '.png';
    $qr_path = QR_CODE_PATH . $qr_filename;
    
    QRcode::png($qr_json_final, $qr_path, QR_ECLEVEL_H, QR_CODE_SIZE, QR_CODE_MARGIN);
    
    // Save to database
    $stmt = $conn->prepare("
        INSERT INTO qr_codes (homeowner_id, qr_code_data, qr_hash, qr_image_path, generated_date, expiry_date, status)
        VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), 'active')
    ");
    $stmt->execute([
        $homeowner_id,
        $qr_json_final,
        $qr_hash,
        $qr_filename,
        QR_CODE_VALIDITY_DAYS
    ]);
    
    echo json_encode([
        'success' => true,
        'qr_id' => $conn->lastInsertId(),
        'qr_path' => SITE_URL . '/qrcodes/' . $qr_filename,
        'expires' => $qr_data['expires']
    ]);
}
?>
```

### Step 3: QR Code Validation API

Create `api/validate_qr.php`:

```php
<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qr_data = $_POST['qr_data'];
    
    // Decode QR data
    $data = json_decode($qr_data, true);
    
    if (!$data) {
        echo json_encode(['valid' => false, 'error' => 'Invalid QR code format']);
        exit();
    }
    
    // Verify hash
    $received_hash = $data['hash'];
    unset($data['hash']);
    $expected_hash = generateHash(json_encode($data));
    
    if ($received_hash !== $expected_hash) {
        echo json_encode(['valid' => false, 'error' => 'QR code tampered or invalid']);
        exit();
    }
    
    // Check expiration
    if (strtotime($data['expires']) < time()) {
        echo json_encode(['valid' => false, 'error' => 'QR code expired']);
        exit();
    }
    
    // Verify in database
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT qr.*, h.*, h.photo_path
        FROM qr_codes qr
        JOIN homeowners h ON qr.homeowner_id = h.homeowner_id
        WHERE qr.qr_hash = ? AND qr.status = 'active' AND h.status = 'active'
    ");
    $stmt->execute([$received_hash]);
    $result = $stmt->fetch();
    
    if (!$result) {
        echo json_encode(['valid' => false, 'error' => 'QR code not found or inactive']);
        exit();
    }
    
    // Get vehicles
    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE homeowner_id = ? AND status = 'active'");
    $stmt->execute([$result['homeowner_id']]);
    $vehicles = $stmt->fetchAll();
    
    echo json_encode([
        'valid' => true,
        'homeowner' => [
            'id' => $result['homeowner_id'],
            'name' => $result['first_name'] . ' ' . $result['last_name'],
            'lot' => $result['lot_number'],
            'block' => $result['block'],
            'photo' => $result['photo_path'] ? SITE_URL . '/uploads/' . $result['photo_path'] : null,
            'phone' => $result['phone']
        ],
        'vehicles' => $vehicles,
        'qr_id' => $result['qr_id']
    ]);
}
?>
```

---

## Frontend Development

### Step 1: Main Stylesheet

Create `assets/css/style.css`:

```css
/* ============================================
   Ciudad De San Jose - Main Stylesheet
   ============================================ */

:root {
    --primary-color: #2563eb;
    --secondary-color: #1e40af;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
    --dark-color: #1f2937;
    --light-color: #f3f4f6;
    --white: #ffffff;
    --border-radius: 8px;
    --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: var(--dark-color);
    background-color: var(--light-color);
}

/* Login Page */
.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
}

.login-box {
    background: var(--white);
    padding: 40px;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    width: 100%;
    max-width: 400px;
}

.login-box h1 {
    text-align: center;
    color: var(--primary-color);
    margin-bottom: 10px;
}

.login-box h2 {
    text-align: center;
    margin-bottom: 30px;
    color: var(--dark-color);
}

/* Forms */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: var(--border-radius);
    font-size: 14px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
}

/* Buttons */
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: var(--primary-color);
    color: var(--white);
    width: 100%;
}

.btn-primary:hover {
    background-color: var(--secondary-color);
}

.btn-success {
    background-color: var(--success-color);
    color: var(--white);
}

.btn-danger {
    background-color: var(--danger-color);
    color: var(--white);
}

/* Alerts */
.alert {
    padding: 15px;
    border-radius: var(--border-radius);
    margin-bottom: 20px;
}

.alert-error {
    background-color: #fee2e2;
    color: var(--danger-color);
    border: 1px solid var(--danger-color);
}

.alert-success {
    background-color: #d1fae5;
    color: var(--success-color);
    border: 1px solid var(--success-color);
}

/* Scanner Interface */
.scanner-container {
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
}

#qr-video {
    width: 100%;
    max-width: 500px;
    border-radius: var(--border-radius);
}

.homeowner-info {
    background: var(--white);
    padding: 20px;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    margin-top: 20px;
}

.homeowner-photo {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
}

/* Responsive */
@media (max-width: 768px) {
    .login-box {
        margin: 20px;
    }
}
```

### Step 2: QR Scanner JavaScript

Create `assets/js/qr-scanner.js`:

```javascript
// QR Code Scanner Implementation
let videoStream = null;
let scanning = false;

// Initialize scanner
function initScanner() {
    const video = document.getElementById('qr-video');
    const canvas = document.getElementById('qr-canvas');
    const ctx = canvas.getContext('2d');
    
    // Request camera access
    navigator.mediaDevices.getUserMedia({ 
        video: { facingMode: 'environment' } 
    })
    .then(stream => {
        videoStream = stream;
        video.srcObject = stream;
        video.setAttribute('playsinline', true);
        video.play();
        scanning = true;
        requestAnimationFrame(tick);
    })
    .catch(err => {
        console.error('Camera access denied:', err);
        alert('Please allow camera access to scan QR codes');
    });
    
    function tick() {
        if (!scanning) return;
        
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.height = video.videoHeight;
            canvas.width = video.videoWidth;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            
            if (code) {
                // QR code detected
                handleQRCode(code.data);
            }
        }
        
        requestAnimationFrame(tick);
    }
}

// Handle scanned QR code
function handleQRCode(qrData) {
    scanning = false; // Stop scanning
    
    // Validate QR code via API
    fetch('api/validate_qr.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'qr_data=' + encodeURIComponent(qrData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            displayHomeownerInfo(data);
        } else {
            alert('Error: ' + data.error);
            scanning = true; // Resume scanning
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to validate QR code');
        scanning = true;
    });
}

// Display homeowner information
function displayHomeownerInfo(data) {
    const infoDiv = document.getElementById('homeowner-info');
    
    let vehiclesHTML = '';
    if (data.vehicles && data.vehicles.length > 0) {
        vehiclesHTML = '<h4>Registered Vehicles:</h4><ul>';
        data.vehicles.forEach(v => {
            vehiclesHTML += `<li>${v.plate_number} - ${v.make} ${v.model} (${v.color})</li>`;
        });
        vehiclesHTML += '</ul>';
    }
    
    infoDiv.innerHTML = `
        <div class="homeowner-card">
            ${data.homeowner.photo ? `<img src="${data.homeowner.photo}" class="homeowner-photo" alt="Photo">` : ''}
            <h3>${data.homeowner.name}</h3>
            <p><strong>Lot:</strong> ${data.homeowner.lot}</p>
            <p><strong>Block:</strong> ${data.homeowner.block}</p>
            <p><strong>Phone:</strong> ${data.homeowner.phone}</p>
            ${vehiclesHTML}
            <div class="action-buttons">
                <button class="btn btn-success" onclick="approveEntry(${data.homeowner.id}, ${data.qr_id})">
                    Approve Entry
                </button>
                <button class="btn btn-danger" onclick="denyEntry()">
                    Deny
                </button>
            </div>
        </div>
    `;
    
    infoDiv.style.display = 'block';
}

// Approve entry
function approveEntry(homeownerId, qrId) {
    // Implementation for check-in/check-out
    console.log('Approving entry for homeowner:', homeownerId);
    // Add AJAX call to record access log
}

// Stop scanner
function stopScanner() {
    scanning = false;
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('qr-video')) {
        initScanner();
    }
});
```

---

## Testing Strategy

### Unit Testing
- Test QR code generation
- Test hash validation
- Test database queries
- Test authentication functions

### Integration Testing
- Test complete check-in flow
- Test complete check-out flow
- Test visitor registration flow
- Test QR code regeneration

### User Acceptance Testing
- Admin panel functionality
- Scanner interface usability
- Homeowner portal features
- Mobile responsiveness

---

## Deployment Guide

### Production Checklist

1. **Security**
   - [ ] Change default admin password
   - [ ] Update SECRET_KEY in config
   - [ ] Enable HTTPS
   - [ ] Configure firewall
   - [ ] Set proper file permissions

2. **Database**
   - [ ] Create production database
   - [ ] Set strong database password
   - [ ] Configure automated backups
   - [ ] Optimize indexes

3. **Performance**
   - [ ] Enable PHP OPcache
   - [ ] Configure Apache caching
   - [ ] Optimize images
   - [ ] Minify CSS/JS

4. **Monitoring**
   - [ ] Set up error logging
   - [ ] Configure email alerts
   - [ ] Monitor disk space
   - [ ] Track system performance

---

**Document Version:** 1.0  
**Last Updated:** January 29, 2026  
**For Developers:** This guide provides the foundation for building the system

---

*For additional support, refer to SYSTEM_DOCUMENTATION.md and QUICK_REFERENCE.md*
