<?php
// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    switch ($_SESSION['user_role']) {
        case 'admin':
        case 'hoa':
        case 'supervisor':
            header('Location: ../admin/dashboard.php');
            break;
        case 'guard':
            header('Location: ../scanner/index.php');
            break;
        default:
            header('Location: ../index.php');
    }
    exit();
}

// Initialize variables
$error = '';
$success = '';

// Check for logout message
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success = 'You have been successfully logged out.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['rememberMe']);
    
    // Basic validation
    if (empty($username)) {
        $error = 'Username or email is required';
    } elseif (empty($password)) {
        $error = 'Password is required';
    } else {
        require_once '../config/database.php';
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                
                // Update last login
                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Set remember me cookie
                if ($rememberMe) {
                    setcookie('remembered_user', $username, time() + (86400 * 30), '/');
                }
                
                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                    case 'hoa':
                    case 'supervisor':
                        header('Location: ../admin/dashboard.php');
                        break;
                    case 'guard':
                        header('Location: ../scanner/index.php');
                        break;
                    default:
                        header('Location: ../index.php');
                }
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get remembered username from cookie
$rememberedUser = $_COOKIE['remembered_user'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ciudad De San Jose</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="../assets/vendor/fonts/inter/inter.css">
</head>
<body>
    <!-- Background with animated gradient -->
    <div class="background-overlay"></div>
    
    <div class="login-container">
        <!-- Left side - Branding with Photo -->
        <div class="branding-section" style="background-image: url('../assets/1.jpg'); background-size: cover; background-position: center; position: relative;">
            <div class="branding-content" style="position: relative; z-index: 1;">
                <div class="logo-container">
                    <img src="../assets/logo.png" alt="Ciudad De San Jose Logo" class="brand-logo">
                    <h1 class="brand-name">Ciudad De San Jose</h1>
                </div>
                <p class="brand-tagline">Secure Access Management System</p>
                <div class="features-list">
                    <div class="feature-item">
                        <svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M20 6L9 17L4 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>QR Code Access Control</span>
                    </div>
                    <div class="feature-item">
                        <svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M20 6L9 17L4 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Real-time Monitoring</span>
                    </div>
                    <div class="feature-item">
                        <svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M20 6L9 17L4 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Secure & Reliable</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side - Login Form -->
        <div class="form-section">
            <div class="form-container">
                <div class="form-header">
                    <img src="../assets/logo.png" alt="Ciudad De San Jose Logo" class="login-page-logo">
                    <h2>Welcome to Ciudad De San Jose</h2>
                    <p>Please login to your account</p>
                </div>

                <form method="POST" action="" class="login-form" novalidate>
                    <!-- Alert messages -->
                    <?php if (!empty($error)): ?>
                        <div class="alert error" style="display: block;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert success" style="display: block;">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Username/Email Field -->
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="7" r="4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Username or Email
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="form-input" 
                                placeholder="Enter your username or email"
                                value="<?php echo htmlspecialchars($rememberedUser); ?>"
                                required
                                autocomplete="username"
                            >
                            <span class="input-border"></span>
                        </div>
                        <span class="error-message" id="usernameError"></span>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Password
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input password-input" 
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="toggle-password" id="togglePassword" aria-label="Toggle password visibility">
                                <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <span class="input-border"></span>
                        </div>
                        <span class="error-message" id="passwordError"></span>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="form-options">
                        <label class="checkbox-container">
                            <input type="checkbox" id="rememberMe" name="rememberMe" <?php echo !empty($rememberedUser) ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            <span class="checkbox-label">Remember me</span>
                        </label>
                        <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn-login" id="loginButton">
                        <span class="btn-text">Login</span>
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M5 12h14M12 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>

                <!-- Footer -->
                <div class="form-footer">
                    <p>&copy; <?php echo date('Y'); ?> Ciudad De San Jose. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="login.js"></script>
    <script>
        // Disable client-side form submission handling since we're using PHP
        document.getElementById('loginForm')?.addEventListener('submit', function(e) {
            // Let PHP handle the submission
            // Remove the preventDefault from login.js for PHP version
        });
    </script>
</body>
</html>
