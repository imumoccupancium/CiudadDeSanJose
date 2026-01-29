# Authentication System - Ciudad De San Jose

## 📁 Files Overview

This folder contains all authentication-related files for the Ciudad De San Jose subdivision management system.

### Files Included:

1. **login.php** - Main login page with PHP backend
2. **forgot-password.php** - Password recovery page
3. **logout.php** - Session destruction and logout handler
4. **login.css** - Comprehensive stylesheet for all auth pages
5. **login.js** - Client-side validation and interactivity

---

## 🚀 Features

### Login Page (login.php)
- ✅ Modern, responsive design
- ✅ Session management
- ✅ Remember me functionality
- ✅ Password visibility toggle
- ✅ Real-time form validation
- ✅ Role-based redirection (Admin, Security, Homeowner)
- ✅ Demo authentication (for testing)
- ✅ Database integration ready

### Forgot Password (forgot-password.php)
- ✅ Email validation
- ✅ Token generation (placeholder)
- ✅ Email sending (placeholder)
- ✅ Success confirmation
- ✅ Security best practices (doesn't reveal if email exists)

### Logout (logout.php)
- ✅ Complete session destruction
- ✅ Cookie cleanup
- ✅ Redirect to login with success message

---

## 🎨 Design Features

- **Animated gradient background**
- **Glassmorphism effects**
- **Smooth transitions and animations**
- **Fully responsive (mobile, tablet, desktop)**
- **Accessibility compliant**
- **Dark mode ready**
- **Professional subdivision branding**

---

## 🔐 Demo Credentials

For testing purposes, the following credentials are available:

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Administrator |
| security | security123 | Security Personnel |
| homeowner | homeowner123 | Homeowner |

**⚠️ IMPORTANT:** Remove demo credentials before production deployment!

---

## 🗄️ Database Integration

### Current Status
The system currently uses **demo authentication** for testing. To integrate with your database:

### Steps to Connect Database:

1. **Uncomment the database code** in `login.php` (lines marked with TODO)
2. **Create the users table** using the schema from `SYSTEM_DOCUMENTATION.md`
3. **Update database configuration** in `../config/database.php`

### Example Database Query (Already in login.php):

```php
require_once '../config/database.php';
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'");
$stmt->execute([$username, $username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    // Set session and redirect
}
```

---

## 📋 Session Variables

When a user logs in successfully, the following session variables are set:

```php
$_SESSION['user_id']       // Unique user ID
$_SESSION['username']      // Username
$_SESSION['full_name']     // Full name
$_SESSION['role']          // User role (admin, security, homeowner)
$_SESSION['homeowner_id']  // Homeowner ID (if applicable)
```

---

## 🔄 User Flow

### Login Flow:
```
1. User visits login.php
2. Enters credentials
3. PHP validates and checks database
4. Session created
5. Redirect based on role:
   - Admin → ../admin/dashboard.php
   - Security → ../scanner/index.php
   - Homeowner → ../portal/dashboard.php
```

### Logout Flow:
```
1. User clicks logout
2. Redirected to logout.php
3. Session destroyed
4. Cookies cleared
5. Redirect to login.php with success message
```

### Password Recovery Flow:
```
1. User clicks "Forgot Password?"
2. Enters email address
3. System generates reset token
4. Email sent with reset link
5. User clicks link and resets password
```

---

## 🛠️ Customization

### Change Colors:
Edit CSS variables in `login.css`:

```css
:root {
    --primary-color: #2563eb;      /* Main brand color */
    --primary-dark: #1e40af;       /* Darker shade */
    --accent-color: #10b981;       /* Accent color */
}
```

### Change Branding:
Edit the branding section in `login.php`:

```html
<h1 class="brand-name">Your Subdivision Name</h1>
<p class="brand-tagline">Your Tagline</p>
```

### Change Redirect URLs:
Update the redirect logic in `login.php`:

```php
switch ($user['role']) {
    case 'admin':
        header('Location: /your-admin-path');
        break;
    // ... etc
}
```

---

## 🔒 Security Features

1. **Password Hashing**: Uses `password_verify()` for secure password checking
2. **SQL Injection Prevention**: Prepared statements with PDO
3. **XSS Protection**: `htmlspecialchars()` on all outputs
4. **Session Security**: Secure session handling
5. **CSRF Protection**: Can be added with tokens
6. **Rate Limiting**: Can be implemented for login attempts
7. **Remember Me**: Secure cookie implementation

---

## 📱 Responsive Breakpoints

- **Desktop**: > 1024px (Full layout with branding)
- **Tablet**: 640px - 1024px (Form only, no branding section)
- **Mobile**: < 640px (Optimized mobile layout)

---

## 🧪 Testing

### Test the Login Page:

1. **Start XAMPP** (Apache and MySQL)
2. **Navigate to**: `http://localhost/CiudadDeSanJose/auth/login.php`
3. **Try demo credentials** (see table above)
4. **Test features**:
   - Form validation
   - Password toggle
   - Remember me
   - Forgot password
   - Responsive design

### Test Logout:

1. Login with any demo account
2. Navigate to: `http://localhost/CiudadDeSanJose/auth/logout.php`
3. Should redirect to login with success message

---

## 🚧 TODO for Production

- [ ] Remove demo authentication code
- [ ] Implement actual database authentication
- [ ] Add CSRF token protection
- [ ] Implement rate limiting (prevent brute force)
- [ ] Set up email service for password recovery
- [ ] Add two-factor authentication (optional)
- [ ] Implement account lockout after failed attempts
- [ ] Add login activity logging
- [ ] Set up HTTPS/SSL
- [ ] Configure secure session settings
- [ ] Add reCAPTCHA (optional)

---

## 📝 File Structure

```
auth/
├── login.php              # Main login page (PHP backend)
├── login.html             # Original HTML version (for reference)
├── forgot-password.php    # Password recovery (PHP)
├── forgot-password.html   # Original HTML version (for reference)
├── logout.php             # Logout handler
├── login.css              # Comprehensive stylesheet
├── login.js               # Client-side JavaScript
└── README.md              # This file
```

---

## 🔗 Related Files

- **Database Config**: `../config/database.php`
- **System Config**: `../config/config.php`
- **Functions**: `../includes/functions.php`
- **Documentation**: `../SYSTEM_DOCUMENTATION.md`

---

## 💡 Tips

1. **Always use HTTPS in production**
2. **Never store passwords in plain text**
3. **Implement proper session timeout**
4. **Log all authentication attempts**
5. **Use strong password policies**
6. **Implement account lockout mechanisms**
7. **Keep session IDs secure**
8. **Validate on both client and server side**

---

## 🐛 Troubleshooting

### Issue: "Headers already sent" error
**Solution**: Make sure there's no output before `session_start()` or `header()` calls

### Issue: Session not persisting
**Solution**: Check if cookies are enabled and session configuration is correct

### Issue: Styles not loading
**Solution**: Verify the CSS file path is correct relative to the PHP file

### Issue: Database connection fails
**Solution**: Check database credentials in `../config/database.php`

---

## 📞 Support

For questions or issues:
- **Email**: support@ciudaddesanjose.com
- **Documentation**: See `../SYSTEM_DOCUMENTATION.md`
- **Implementation Guide**: See `../IMPLEMENTATION_GUIDE.md`

---

**Version**: 1.0  
**Last Updated**: January 29, 2026  
**Status**: Ready for database integration

---

*This authentication system is part of the Ciudad De San Jose Subdivision Management System*
