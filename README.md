# Ciudad De San Jose - Subdivision Management System

![System Status](https://img.shields.io/badge/status-active-success)
![Version](https://img.shields.io/badge/version-1.0-blue)
![License](https://img.shields.io/badge/license-proprietary-red)

## 🏘️ Overview

**Ciudad De San Jose** is a modern, QR code-based subdivision management system designed to streamline access control for homeowners and residents. The system provides secure, efficient, and contactless check-in/check-out processes using QR code technology.

## ✨ Key Features

- 🔐 **QR Code Authentication** - Unique QR codes for each homeowner
- ⚡ **Quick Access Control** - Fast check-in/check-out processing
- 👥 **Visitor Management** - Pre-register guests with temporary QR codes
- 📊 **Real-time Dashboard** - Monitor subdivision access in real-time
- 📈 **Analytics & Reports** - Comprehensive reporting capabilities
- 🚗 **Vehicle Registration** - Track homeowner vehicles
- 👨‍👩‍👧‍👦 **Household Management** - Register family members
- 🔒 **Security Features** - Encrypted QR codes with expiration dates

## 📋 Quick Start

### For Administrators
1. Access the admin panel at `http://localhost/CiudadDeSanJose/admin`
2. Login with your credentials
3. Start adding homeowners and generating QR codes

### For Security Personnel
1. Access the scanner interface at `http://localhost/CiudadDeSanJose/scanner`
2. Login with your credentials
3. Scan homeowner QR codes for check-in/check-out

### For Homeowners
1. Access the homeowner portal at `http://localhost/CiudadDeSanJose/portal`
2. Login with credentials provided by admin
3. Download your QR code and manage your profile

## 📚 Documentation

For complete system documentation, please refer to:
- **[SYSTEM_DOCUMENTATION.md](SYSTEM_DOCUMENTATION.md)** - Complete system documentation
- **[QUICK_REFERENCE.md](QUICK_REFERENCE.md)** - Quick reference guide

## 🛠️ Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Server:** Apache (XAMPP)
- **QR Code:** phpqrcode, jsQR

## 📦 Installation

### Prerequisites
- XAMPP (or similar LAMP/WAMP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser with camera support

### Setup Steps
1. Clone/copy project to `C:\xampp\htdocs\CiudadDeSanJose`
2. Import database schema from `database/schema.sql`
3. Configure database connection in `config/database.php`
4. Set up QR code directory permissions
5. Access the system at `http://localhost/CiudadDeSanJose`

## 👥 User Roles

| Role | Description | Key Permissions |
|------|-------------|-----------------|
| **Administrator** | Full system access | All CRUD operations, system configuration |
| **Security Personnel** | Gate operations | QR scanning, access logging |
| **Homeowner** | Self-service portal | Profile management, QR code access |

## 🔐 Security Features

- SHA-256 encrypted QR codes
- Password hashing with bcrypt
- SQL injection prevention
- XSS protection
- CSRF token validation
- Session management
- Role-based access control

## 📊 System Architecture

```
┌─────────────┐
│   Browser   │ ← Homeowners, Security, Admin
└──────┬──────┘
       │
┌──────▼──────┐
│  Apache     │ ← Web Server (XAMPP)
│  + PHP      │
└──────┬──────┘
       │
┌──────▼──────┐
│   MySQL     │ ← Database
└─────────────┘
```

## 📱 QR Code System

### QR Code Contents
Each QR code contains encrypted JSON data:
```json
{
  "homeowner_id": "unique_id",
  "name": "Homeowner Name",
  "lot_number": "Block X, Lot Y",
  "generated_date": "2026-01-29",
  "expiry_date": "2027-01-29",
  "checksum": "encrypted_hash"
}
```

### QR Code Security
- Unique identifier per homeowner
- SHA-256 encryption
- Expiration dates
- Server-side validation
- Rate limiting

## 🚀 Future Enhancements

- [ ] Mobile application (iOS/Android)
- [ ] Biometric authentication
- [ ] License plate recognition
- [ ] Smart gate integration
- [ ] Advanced analytics dashboard
- [ ] Cloud deployment
- [ ] Multi-language support

## 📞 Support

- **Technical Support:** support@ciudaddesanjose.com
- **Emergency Hotline:** [Phone Number]
- **Documentation:** See SYSTEM_DOCUMENTATION.md

## 📄 License

Proprietary - Ciudad De San Jose Subdivision

## 👨‍💻 Development Team

**Ciudad De San Jose Development Team**  
Version 1.0 - January 2026

---

For detailed information about system features, database schema, workflows, and technical specifications, please refer to the [complete documentation](SYSTEM_DOCUMENTATION.md).
