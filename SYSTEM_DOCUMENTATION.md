# Ciudad De San Jose - Subdivision Management System
## System Documentation

---

## Table of Contents
1. [System Overview](#system-overview)
2. [System Purpose](#system-purpose)
3. [Key Features](#key-features)
4. [System Architecture](#system-architecture)
5. [User Roles](#user-roles)
6. [Core Functionality](#core-functionality)
7. [QR Code Implementation](#qr-code-implementation)
8. [Database Schema](#database-schema)
9. [Security Features](#security-features)
10. [User Workflows](#user-workflows)
11. [Technical Requirements](#technical-requirements)
12. [Future Enhancements](#future-enhancements)

---

## System Overview

**Ciudad De San Jose Subdivision Management System** is a comprehensive web-based platform designed to streamline access control and management for the Ciudad De San Jose residential subdivision. The system leverages QR code technology to provide secure, efficient, and contactless check-in and check-out processes for homeowners and authorized residents.

### Project Information
- **Project Name:** Ciudad De San Jose Management System
- **Type:** Web-based Subdivision Access Control System
- **Primary Technology:** QR Code-based Authentication
- **Target Users:** Subdivision Homeowners, Security Personnel, Administrators

---

## System Purpose

The primary objectives of the Ciudad De San Jose system are:

1. **Access Control:** Manage and monitor entry and exit of homeowners and residents
2. **Security Enhancement:** Provide a secure, verifiable method of identification using QR codes
3. **Record Keeping:** Maintain comprehensive logs of all check-in and check-out activities
4. **Efficiency:** Reduce wait times at subdivision gates through quick QR code scanning
5. **Transparency:** Provide real-time access to movement records for authorized personnel
6. **Contactless Operation:** Minimize physical contact through digital verification

---

## Key Features

### 1. QR Code Generation & Management
- Unique QR code generation for each registered homeowner
- QR codes contain encrypted homeowner information
- Ability to regenerate QR codes if lost or compromised
- QR code expiration and renewal capabilities
- Support for temporary QR codes for guests

### 2. Check-In/Check-Out System
- Quick QR code scanning at subdivision gates
- Real-time verification of homeowner credentials
- Automatic timestamp recording for all entries and exits
- Support for multiple entry/exit points
- Offline mode capability for system resilience

### 3. Homeowner Management
- Homeowner registration and profile management
- Household member registration
- Vehicle registration and tracking
- Contact information management
- Property/lot assignment

### 4. Security & Monitoring
- Real-time dashboard for security personnel
- Alert system for unauthorized access attempts
- Activity logs and audit trails
- Visitor management and pre-registration
- Emergency override capabilities

### 5. Reporting & Analytics
- Daily/weekly/monthly access reports
- Homeowner activity summaries
- Peak hours analysis
- Security incident reports
- Export capabilities (PDF, Excel)

---

## System Architecture

### Technology Stack

#### Frontend
- **HTML5:** Structure and markup
- **CSS3:** Styling and responsive design
- **JavaScript:** Client-side interactivity and QR code scanning
- **QR Code Library:** For generating and reading QR codes (e.g., QRCode.js, jsQR)

#### Backend
- **PHP:** Server-side logic and API endpoints
- **MySQL:** Database management system
- **Apache:** Web server (via XAMPP)

#### Additional Components
- **QR Code Scanner:** Mobile/tablet camera integration or dedicated scanner hardware
- **Session Management:** Secure user authentication
- **RESTful API:** For mobile app integration (future)

### System Components

```
┌─────────────────────────────────────────────────────────┐
│                    Presentation Layer                    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   Web UI     │  │  QR Scanner  │  │   Reports    │  │
│  │  (Browser)   │  │  Interface   │  │  Dashboard   │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                    Application Layer                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ User Mgmt    │  │  QR Code     │  │  Access      │  │
│  │ Module       │  │  Generator   │  │  Control     │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Check-In/Out │  │  Reporting   │  │  Security    │  │
│  │ Module       │  │  Module      │  │  Module      │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                      Data Layer                          │
│  ┌──────────────────────────────────────────────────┐  │
│  │              MySQL Database                       │  │
│  │  • Homeowners  • Access Logs  • QR Codes         │  │
│  │  • Vehicles    • Users        • Settings         │  │
│  └──────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

---

## User Roles

### 1. Administrator
**Responsibilities:**
- Full system access and configuration
- User account management (create, modify, delete)
- System settings and parameters
- Database backup and maintenance
- Generate comprehensive reports
- Manage security policies

**Permissions:**
- All CRUD operations on all modules
- System configuration access
- Audit log access
- User role assignment

### 2. Security Personnel
**Responsibilities:**
- Scan QR codes at gates
- Verify homeowner identity
- Process check-ins and check-outs
- Monitor real-time access dashboard
- Report security incidents
- Manage visitor entries

**Permissions:**
- QR code scanning and verification
- View homeowner basic information
- Create access logs
- View daily reports
- Flag suspicious activities

### 3. Homeowner
**Responsibilities:**
- Maintain personal profile information
- Download/access personal QR code
- Register household members
- Register vehicles
- View personal access history
- Pre-register guests

**Permissions:**
- View and update own profile
- Download personal QR code
- View personal access logs
- Manage household members
- Request QR code regeneration

### 4. Guest/Visitor (Optional)
**Responsibilities:**
- Use temporary QR code provided by homeowner
- Check-in at designated visitor entrance

**Permissions:**
- Limited access with temporary QR code
- Time-bound access rights

---

## Core Functionality

### 1. Homeowner Registration

**Process Flow:**
1. Administrator/Homeowner creates account
2. Personal information input (name, contact, address)
3. Property/lot assignment
4. Vehicle registration (optional)
5. Household member addition (optional)
6. System generates unique QR code
7. QR code delivered via email/downloadable from portal

**Required Information:**
- Full Name
- Contact Number
- Email Address
- Property/Lot Number
- Block/Phase
- Emergency Contact
- Valid ID (for verification)
- Vehicle Details (plate number, make, model)

### 2. QR Code Generation

**QR Code Contents:**
```json
{
  "homeowner_id": "unique_identifier",
  "name": "Juan Dela Cruz",
  "lot_number": "Block 5, Lot 12",
  "generated_date": "2026-01-29",
  "expiry_date": "2027-01-29",
  "qr_version": "1.0",
  "checksum": "encrypted_hash"
}
```

**QR Code Features:**
- Unique identifier for each homeowner
- Encrypted data for security
- Expiration date for periodic renewal
- Version control for system updates
- Error correction level: High (30% recovery)

### 3. Check-In Process

**Step-by-Step:**
1. Homeowner presents QR code at gate
2. Security personnel scans QR code
3. System validates QR code authenticity
4. System retrieves homeowner information
5. System displays homeowner details and photo
6. Security confirms identity
7. System records check-in with timestamp
8. Gate opens/access granted
9. Confirmation displayed on screen

**Validation Checks:**
- QR code format validation
- Homeowner active status
- QR code expiration check
- Duplicate check-in prevention
- Blacklist verification

### 4. Check-Out Process

**Step-by-Step:**
1. Homeowner presents QR code at exit gate
2. Security personnel scans QR code
3. System validates QR code
4. System checks for existing check-in record
5. System records check-out with timestamp
6. System calculates duration of stay
7. Gate opens/exit granted
8. Confirmation displayed on screen

**Additional Features:**
- Alert if no matching check-in found
- Option to manually create check-in record
- Duration tracking for analytics

### 5. Visitor Management

**Guest Pre-Registration:**
1. Homeowner logs into portal
2. Navigates to "Register Guest" section
3. Inputs guest details (name, contact, visit date/time)
4. System generates temporary QR code
5. QR code sent to guest via SMS/email
6. Guest presents QR code at visitor's gate
7. Security verifies and grants access
8. System logs visitor entry

**Temporary QR Code Features:**
- Time-bound validity (specific date/time range)
- Single-use or multiple-use options
- Linked to sponsoring homeowner
- Automatic expiration

---

## QR Code Implementation

### Technical Specifications

#### QR Code Generation (Backend - PHP)
```php
// Using PHP QR Code library
// Generate QR code with homeowner data
$data = json_encode([
    'id' => $homeowner_id,
    'name' => $homeowner_name,
    'lot' => $lot_number,
    'generated' => date('Y-m-d'),
    'expires' => date('Y-m-d', strtotime('+1 year')),
    'hash' => hash('sha256', $homeowner_id . SECRET_KEY)
]);

// Generate QR code image
QRcode::png($data, 'qrcodes/' . $homeowner_id . '.png', QR_ECLEVEL_H, 10);
```

#### QR Code Scanning (Frontend - JavaScript)
```javascript
// Using jsQR library for scanning
function scanQRCode() {
    const video = document.getElementById('qr-video');
    const canvas = document.getElementById('qr-canvas');
    const context = canvas.getContext('2d');
    
    // Capture video frame
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    
    // Decode QR code
    const code = jsQR(imageData.data, imageData.width, imageData.height);
    
    if (code) {
        // Send to backend for validation
        validateQRCode(code.data);
    }
}
```

### QR Code Security Measures

1. **Encryption:** QR code data is encrypted using SHA-256 hashing
2. **Expiration:** QR codes have validity periods
3. **Unique Identifiers:** Each QR code contains a unique, non-sequential ID
4. **Checksum Validation:** Prevents tampering and forgery
5. **Server-Side Validation:** All QR codes validated against database
6. **Rate Limiting:** Prevents brute force scanning attempts

---

## Database Schema

### Tables Structure

#### 1. homeowners
```sql
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
    INDEX idx_status (status)
);
```

#### 2. qr_codes
```sql
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
    INDEX idx_expiry (expiry_date)
);
```

#### 3. access_logs
```sql
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
    INDEX idx_action (action_type)
);
```

#### 4. users
```sql
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
    INDEX idx_role (role)
);
```

#### 5. vehicles
```sql
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
);
```

#### 6. household_members
```sql
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
);
```

#### 7. visitors
```sql
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
    INDEX idx_homeowner (homeowner_id)
);
```

#### 8. security_incidents
```sql
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
    INDEX idx_status (status)
);
```

---

## Security Features

### 1. Authentication & Authorization
- **Password Security:** Bcrypt hashing for all passwords
- **Session Management:** Secure PHP sessions with timeout
- **Role-Based Access Control (RBAC):** Different permissions per role
- **Two-Factor Authentication (Optional):** SMS/Email verification for sensitive operations

### 2. Data Protection
- **SQL Injection Prevention:** Prepared statements and parameterized queries
- **XSS Protection:** Input sanitization and output encoding
- **CSRF Protection:** Token-based validation for forms
- **Data Encryption:** Sensitive data encrypted at rest
- **SSL/TLS:** HTTPS for all communications

### 3. QR Code Security
- **Hash Verification:** SHA-256 checksums prevent forgery
- **Expiration Enforcement:** Automatic invalidation of expired codes
- **Rate Limiting:** Prevent scanning abuse
- **Audit Logging:** All QR scans logged with timestamp and user

### 4. Access Control
- **IP Whitelisting:** Restrict admin access to specific IPs (optional)
- **Login Attempt Limiting:** Lock accounts after failed attempts
- **Session Timeout:** Automatic logout after inactivity
- **Audit Trails:** Complete logging of all system actions

---

## User Workflows

### Workflow 1: New Homeowner Onboarding

```
1. Administrator receives homeowner information
   ↓
2. Admin logs into system
   ↓
3. Navigate to "Add New Homeowner"
   ↓
4. Input homeowner details (name, contact, lot number, etc.)
   ↓
5. Upload homeowner photo (optional)
   ↓
6. Register vehicles (if applicable)
   ↓
7. Add household members (if applicable)
   ↓
8. System generates unique QR code
   ↓
9. QR code saved to database and file system
   ↓
10. System sends email with QR code and login credentials
    ↓
11. Homeowner receives email and downloads QR code
    ↓
12. Homeowner can now use QR code for access
```

### Workflow 2: Daily Check-In/Check-Out

```
MORNING (Check-In):
1. Homeowner arrives at subdivision gate
   ↓
2. Homeowner presents QR code (printed or on phone)
   ↓
3. Security scans QR code using scanner/camera
   ↓
4. System validates QR code
   ↓
5. System displays homeowner info and photo
   ↓
6. Security verifies identity
   ↓
7. Security clicks "Approve Entry"
   ↓
8. System logs check-in with timestamp
   ↓
9. Gate opens, homeowner enters

EVENING (Check-Out):
1. Homeowner arrives at exit gate
   ↓
2. Homeowner presents QR code
   ↓
3. Security scans QR code
   ↓
4. System validates and retrieves check-in record
   ↓
5. Security clicks "Approve Exit"
   ↓
6. System logs check-out with timestamp
   ↓
7. Gate opens, homeowner exits
```

### Workflow 3: Visitor Pre-Registration

```
1. Homeowner logs into portal
   ↓
2. Navigate to "Register Visitor"
   ↓
3. Input visitor details:
   - Name
   - Contact number
   - Expected visit date
   - Expected time of arrival
   - Expected time of departure
   ↓
4. Submit visitor registration
   ↓
5. System generates temporary QR code
   ↓
6. System sends QR code to visitor via SMS/Email
   ↓
7. Visitor arrives at subdivision
   ↓
8. Visitor presents QR code at visitor's gate
   ↓
9. Security scans QR code
   ↓
10. System validates temporary QR code
    ↓
11. System displays visitor info and sponsoring homeowner
    ↓
12. Security approves entry
    ↓
13. System logs visitor check-in
    ↓
14. Visitor enters subdivision
```

### Workflow 4: Lost/Stolen QR Code

```
1. Homeowner reports lost/stolen QR code
   ↓
2. Homeowner logs into portal OR contacts admin
   ↓
3. Navigate to "Report Lost QR Code" OR admin accesses homeowner account
   ↓
4. Homeowner/Admin clicks "Revoke Current QR Code"
   ↓
5. System marks current QR code as "revoked" in database
   ↓
6. System generates new QR code with new hash
   ↓
7. New QR code sent to homeowner via email
   ↓
8. Old QR code no longer works for access
   ↓
9. Homeowner uses new QR code
```

---

## Technical Requirements

### Server Requirements
- **Web Server:** Apache 2.4+ (included in XAMPP)
- **PHP Version:** PHP 7.4 or higher (8.0+ recommended)
- **Database:** MySQL 5.7+ or MariaDB 10.3+
- **Storage:** Minimum 10GB for database and QR code images
- **RAM:** Minimum 4GB (8GB recommended)

### Client Requirements (Security Personnel)
- **Device:** Tablet or Desktop with camera
- **Browser:** Modern browser (Chrome 90+, Firefox 88+, Edge 90+)
- **Camera:** HD camera for QR code scanning
- **Internet:** Stable internet connection (minimum 5 Mbps)

### Client Requirements (Homeowners)
- **Device:** Smartphone, tablet, or computer
- **Browser:** Any modern browser
- **Internet:** For portal access and QR code download

### Software Dependencies
- **PHP Libraries:**
  - PHP QR Code (phpqrcode)
  - PDO for database connections
  - PHPMailer for email notifications
  
- **JavaScript Libraries:**
  - jsQR for QR code scanning
  - jQuery (optional, for UI interactions)
  - Chart.js (for analytics dashboard)

### Network Requirements
- **Local Network:** All devices on same network for optimal performance
- **Firewall:** Configure to allow HTTP/HTTPS traffic
- **Backup:** Regular database backups (daily recommended)

---

## Future Enhancements

### Phase 2 Features
1. **Mobile Application**
   - Native iOS and Android apps
   - Push notifications for access events
   - Digital wallet integration for QR codes

2. **Advanced Analytics**
   - Traffic pattern analysis
   - Predictive analytics for peak hours
   - Homeowner behavior insights

3. **Integration Capabilities**
   - Integration with subdivision billing system
   - Integration with amenity booking system
   - Integration with emergency alert systems

4. **Biometric Authentication**
   - Fingerprint scanning as backup verification
   - Facial recognition integration
   - Multi-factor authentication

5. **IoT Integration**
   - Automatic gate opening via Bluetooth/NFC
   - Smart home integration
   - License plate recognition (LPR) cameras

6. **Enhanced Visitor Management**
   - Visitor photo capture at gate
   - Visitor badge printing
   - Recurring visitor profiles

7. **Communication Module**
   - In-app messaging between homeowners and security
   - Broadcast announcements
   - Emergency notifications

8. **Parking Management**
   - Parking slot assignment
   - Parking availability tracking
   - Visitor parking management

### Scalability Considerations
- Cloud hosting migration for better scalability
- Load balancing for high-traffic periods
- Database sharding for large datasets
- CDN integration for faster QR code delivery

---

## System Maintenance

### Regular Maintenance Tasks

#### Daily
- Monitor system logs for errors
- Check QR code scanner functionality
- Verify database connectivity
- Review access logs for anomalies

#### Weekly
- Database backup
- Review security incident reports
- Update homeowner status (if needed)
- Clear temporary files and cache

#### Monthly
- Generate and review analytics reports
- Update expired QR codes
- System performance optimization
- Security audit

#### Quarterly
- Software updates and patches
- Database optimization and indexing
- User access review
- Disaster recovery testing

### Backup Strategy
- **Database Backup:** Daily automated backups
- **QR Code Images:** Weekly backups
- **System Configuration:** Monthly backups
- **Retention Period:** 6 months minimum
- **Backup Location:** Off-site storage recommended

---

## Support & Contact

### Technical Support
- **System Administrator:** [Contact Details]
- **IT Support Email:** support@ciudaddesanjose.com
- **Emergency Hotline:** [Phone Number]

### User Support
- **Homeowner Portal:** https://ciudaddesanjose.com/portal
- **Help Desk:** helpdesk@ciudaddesanjose.com
- **Operating Hours:** 24/7 for emergencies, 8AM-5PM for general inquiries

---

## Appendix

### Glossary
- **QR Code:** Quick Response code, a two-dimensional barcode
- **Check-In:** Process of entering the subdivision
- **Check-Out:** Process of exiting the subdivision
- **Homeowner:** Registered property owner in the subdivision
- **Visitor:** Guest pre-registered by a homeowner
- **Access Log:** Record of check-in/check-out activities

### References
- QR Code Standards: ISO/IEC 18004:2015
- PHP QR Code Library: https://phpqrcode.sourceforge.net/
- jsQR Library: https://github.com/cozmo/jsQR

---

**Document Version:** 1.0  
**Last Updated:** January 29, 2026  
**Prepared By:** System Development Team  
**Status:** Active

---

*This documentation is subject to updates as the system evolves. Please refer to the latest version for accurate information.*
