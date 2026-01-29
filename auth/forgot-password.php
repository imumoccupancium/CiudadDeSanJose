<?php
// Start session
session_start();

// Initialize variables
$error = '';
$success = '';
$emailSent = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    // Validate email
    if (empty($email)) {
        $error = 'Email address is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // TODO: Replace with actual database check and email sending
        /*
        require_once '../config/database.php';
        $db = new Database();
        $conn = $db->getConnection();
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id, email, full_name FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expiry) VALUES (?, ?, ?)");
            $stmt->execute([$user['user_id'], hash('sha256', $token), $expiry]);
            
            // Send email with reset link
            $resetLink = "http://localhost/CiudadDeSanJose/auth/reset-password.php?token=" . $token;
            
            // Use PHPMailer or similar
            $subject = "Password Reset - Ciudad De San Jose";
            $message = "Hello " . $user['full_name'] . ",\n\n";
            $message .= "You requested a password reset. Click the link below to reset your password:\n\n";
            $message .= $resetLink . "\n\n";
            $message .= "This link will expire in 1 hour.\n\n";
            $message .= "If you didn't request this, please ignore this email.\n\n";
            $message .= "Best regards,\nCiudad De San Jose Team";
            
            mail($email, $subject, $message);
            
            $emailSent = true;
        } else {
            // Don't reveal if email exists or not (security best practice)
            $emailSent = true;
        }
        */
        
        // For demo purposes, always show success
        $emailSent = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Ciudad De San Jose</title>
    <link rel="stylesheet" href="login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all var(--transition-fast);
        }
        .back-link:hover {
            gap: 12px;
            color: var(--primary-dark);
        }
        .back-icon {
            width: 16px;
            height: 16px;
        }
        .success-message {
            text-align: center;
            padding: 40px 20px;
            display: none;
        }
        .success-message.show {
            display: block;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            color: var(--success-color);
        }
    </style>
</head>
<body>
    <div class="background-overlay"></div>
    
    <div class="login-container">
        <div class="branding-section" style="background-image: url('../assets/1.jpg'); background-size: cover; background-position: center; position: relative;">
            <div class="branding-content" style="position: relative; z-index: 1;">
                <div class="logo-container">
                    <svg class="logo-icon" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M50 10L20 30V70L50 90L80 70V30L50 10Z" stroke="currentColor" stroke-width="3" fill="none"/>
                        <path d="M50 30L35 40V60L50 70L65 60V40L50 30Z" fill="currentColor"/>
                        <circle cx="50" cy="50" r="8" fill="white"/>
                    </svg>
                    <h1 class="brand-name">Ciudad De San Jose</h1>
                </div>
                <p class="brand-tagline">Password Recovery</p>
            </div>
        </div>

        <div class="form-section">
            <div class="form-container">
                <a href="login.php" class="back-link">
                    <svg class="back-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M19 12H5M12 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Back to Login
                </a>

                <?php if (!$emailSent): ?>
                <div id="forgotForm">
                    <div class="form-header">
                        <h2>Forgot Password?</h2>
                        <p>Enter your email address and we'll send you instructions to reset your password</p>
                    </div>

                    <form method="POST" action="" class="login-form">
                        <?php if (!empty($error)): ?>
                            <div class="alert error" style="display: block;">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="email" class="form-label">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <polyline points="22,6 12,13 2,6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Email Address
                            </label>
                            <div class="input-wrapper">
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    class="form-input" 
                                    placeholder="Enter your email address"
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                    required
                                >
                                <span class="input-border"></span>
                            </div>
                            <span class="error-message" id="emailError"></span>
                        </div>

                        <button type="submit" class="btn-login">
                            <span class="btn-text">Send Reset Link</span>
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div id="successMessage" class="success-message show">
                    <svg class="success-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="22 4 12 14.01 9 11.01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <h3 style="color: var(--gray-900); margin-bottom: 10px;">Check Your Email</h3>
                    <p style="color: var(--gray-600); margin-bottom: 20px;">
                        If an account exists with the email address you provided, we've sent password reset instructions to that address.
                    </p>
                    <a href="login.php" class="btn-login" style="display: inline-flex; width: auto;">
                        <span class="btn-text">Return to Login</span>
                    </a>
                </div>
                <?php endif; ?>

                <div class="form-footer">
                    <p>&copy; <?php echo date('Y'); ?> Ciudad De San Jose. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Client-side email validation
        document.getElementById('email')?.addEventListener('input', function() {
            this.classList.remove('error');
            const errorElement = document.getElementById('emailError');
            if (errorElement) {
                errorElement.classList.remove('show');
            }
        });
    </script>
</body>
</html>
