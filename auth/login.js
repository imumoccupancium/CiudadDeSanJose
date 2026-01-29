// ============================================
// Ciudad De San Jose - Login Page JavaScript
// Form Validation & Interactivity
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const loginForm = document.getElementById('loginForm');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const rememberMeCheckbox = document.getElementById('rememberMe');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const loginButton = document.getElementById('loginButton');
    const alertMessage = document.getElementById('alertMessage');

    // Error message elements
    const usernameError = document.getElementById('usernameError');
    const passwordError = document.getElementById('passwordError');

    // ============================================
    // Password Toggle Functionality
    // ============================================
    togglePasswordBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Update icon
        const eyeIcon = this.querySelector('.eye-icon');
        if (type === 'text') {
            eyeIcon.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="1" y1="1" x2="23" y2="23" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            `;
        } else {
            eyeIcon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            `;
        }
    });

    // ============================================
    // Input Validation Functions
    // ============================================
    
    function validateUsername() {
        const username = usernameInput.value.trim();
        
        if (username === '') {
            showError(usernameInput, usernameError, 'Username or email is required');
            return false;
        }
        
        if (username.length < 3) {
            showError(usernameInput, usernameError, 'Username must be at least 3 characters');
            return false;
        }
        
        // If it looks like an email, validate email format
        if (username.includes('@')) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(username)) {
                showError(usernameInput, usernameError, 'Please enter a valid email address');
                return false;
            }
        }
        
        clearError(usernameInput, usernameError);
        return true;
    }

    function validatePassword() {
        const password = passwordInput.value;
        
        if (password === '') {
            showError(passwordInput, passwordError, 'Password is required');
            return false;
        }
        
        if (password.length < 6) {
            showError(passwordInput, passwordError, 'Password must be at least 6 characters');
            return false;
        }
        
        clearError(passwordInput, passwordError);
        return true;
    }

    function showError(input, errorElement, message) {
        input.classList.add('error');
        errorElement.textContent = message;
        errorElement.classList.add('show');
    }

    function clearError(input, errorElement) {
        input.classList.remove('error');
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    }

    function showAlert(message, type = 'error') {
        alertMessage.textContent = message;
        alertMessage.className = `alert ${type}`;
        alertMessage.style.display = 'block';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            alertMessage.style.display = 'none';
        }, 5000);
    }

    function hideAlert() {
        alertMessage.style.display = 'none';
    }

    // ============================================
    // Real-time Validation
    // ============================================
    
    usernameInput.addEventListener('blur', validateUsername);
    passwordInput.addEventListener('blur', validatePassword);
    
    // Clear errors on input
    usernameInput.addEventListener('input', function() {
        if (this.classList.contains('error')) {
            clearError(this, usernameError);
        }
        hideAlert();
    });
    
    passwordInput.addEventListener('input', function() {
        if (this.classList.contains('error')) {
            clearError(this, passwordError);
        }
        hideAlert();
    });

    // ============================================
    // Form Submission
    // ============================================
    
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Hide any existing alerts
        hideAlert();
        
        // Validate all fields
        const isUsernameValid = validateUsername();
        const isPasswordValid = validatePassword();
        
        if (!isUsernameValid || !isPasswordValid) {
            showAlert('Please fix the errors before submitting', 'error');
            return;
        }
        
        // Show loading state
        setLoadingState(true);
        
        // Get form data
        const formData = {
            username: usernameInput.value.trim(),
            password: passwordInput.value,
            rememberMe: rememberMeCheckbox.checked
        };
        
        try {
            // Simulate API call (replace with actual API endpoint)
            await simulateLogin(formData);
            
            // Success
            showAlert('Login successful! Redirecting...', 'success');
            
            // Redirect after 1.5 seconds
            setTimeout(() => {
                // Replace with actual redirect logic based on user role
                // window.location.href = '/dashboard';
                console.log('Redirecting to dashboard...');
            }, 1500);
            
        } catch (error) {
            // Error handling
            setLoadingState(false);
            showAlert(error.message || 'Login failed. Please check your credentials.', 'error');
        }
    });

    // ============================================
    // Loading State Management
    // ============================================
    
    function setLoadingState(isLoading) {
        if (isLoading) {
            loginButton.classList.add('loading');
            loginButton.disabled = true;
            loginButton.querySelector('.btn-text').style.opacity = '0';
            loginButton.querySelector('.btn-icon').style.opacity = '0';
            loginButton.querySelector('.btn-loader').style.display = 'block';
        } else {
            loginButton.classList.remove('loading');
            loginButton.disabled = false;
            loginButton.querySelector('.btn-text').style.opacity = '1';
            loginButton.querySelector('.btn-icon').style.opacity = '1';
            loginButton.querySelector('.btn-loader').style.display = 'none';
        }
    }

    // ============================================
    // Simulated Login Function
    // Replace this with actual API call
    // ============================================
    
    function simulateLogin(formData) {
        return new Promise((resolve, reject) => {
            // Simulate network delay
            setTimeout(() => {
                // Demo credentials (remove in production)
                const validCredentials = [
                    { username: 'admin', password: 'admin123', role: 'admin' },
                    { username: 'security', password: 'security123', role: 'security' },
                    { username: 'homeowner', password: 'homeowner123', role: 'homeowner' },
                    { username: 'demo@ciudaddesanjose.com', password: 'demo123', role: 'homeowner' }
                ];
                
                const user = validCredentials.find(
                    cred => (cred.username === formData.username || cred.username === formData.username) 
                    && cred.password === formData.password
                );
                
                if (user) {
                    // Store user info (in production, use secure session/token)
                    if (formData.rememberMe) {
                        localStorage.setItem('rememberedUser', formData.username);
                    }
                    sessionStorage.setItem('userRole', user.role);
                    resolve({ success: true, user: user });
                } else {
                    reject({ message: 'Invalid username or password' });
                }
            }, 1500);
        });
    }

    // ============================================
    // Remember Me Functionality
    // ============================================
    
    // Check if there's a remembered user
    const rememberedUser = localStorage.getItem('rememberedUser');
    if (rememberedUser) {
        usernameInput.value = rememberedUser;
        rememberMeCheckbox.checked = true;
    }

    // ============================================
    // Keyboard Shortcuts
    // ============================================
    
    document.addEventListener('keydown', function(e) {
        // Alt + L to focus on login button
        if (e.altKey && e.key === 'l') {
            e.preventDefault();
            loginButton.focus();
        }
        
        // Escape to clear form
        if (e.key === 'Escape') {
            if (document.activeElement === usernameInput || document.activeElement === passwordInput) {
                document.activeElement.blur();
            }
        }
    });

    // ============================================
    // Input Auto-focus Enhancement
    // ============================================
    
    // Auto-focus on username field when page loads
    setTimeout(() => {
        usernameInput.focus();
    }, 500);

    // ============================================
    // Prevent Multiple Submissions
    // ============================================
    
    let isSubmitting = false;
    
    loginForm.addEventListener('submit', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }
    });

    // ============================================
    // Browser Autofill Detection
    // ============================================
    
    // Detect browser autofill and clear errors
    setTimeout(() => {
        if (usernameInput.value !== '') {
            clearError(usernameInput, usernameError);
        }
        if (passwordInput.value !== '') {
            clearError(passwordInput, passwordError);
        }
    }, 500);

    // ============================================
    // Console Welcome Message
    // ============================================
    
    console.log('%c🏘️ Ciudad De San Jose', 'font-size: 20px; font-weight: bold; color: #2563eb;');
    console.log('%cSubdivision Management System', 'font-size: 14px; color: #6b7280;');
    console.log('%c\nDemo Credentials:', 'font-size: 12px; font-weight: bold; color: #10b981;');
    console.log('%cAdmin: admin / admin123', 'font-size: 11px; color: #374151;');
    console.log('%cSecurity: security / security123', 'font-size: 11px; color: #374151;');
    console.log('%cHomeowner: homeowner / homeowner123', 'font-size: 11px; color: #374151;');
    console.log('%cEmail: demo@ciudaddesanjose.com / demo123', 'font-size: 11px; color: #374151;');
});

// ============================================
// Additional Utility Functions
// ============================================

// Sanitize input to prevent XSS
function sanitizeInput(input) {
    const div = document.createElement('div');
    div.textContent = input;
    return div.innerHTML;
}

// Check password strength (optional enhancement)
function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    if (strength <= 2) return 'weak';
    if (strength <= 4) return 'medium';
    return 'strong';
}

// Format error messages
function formatErrorMessage(error) {
    const errorMessages = {
        'network_error': 'Network error. Please check your connection.',
        'server_error': 'Server error. Please try again later.',
        'invalid_credentials': 'Invalid username or password.',
        'account_locked': 'Your account has been locked. Please contact support.',
        'account_inactive': 'Your account is inactive. Please contact your administrator.'
    };
    
    return errorMessages[error] || 'An unexpected error occurred.';
}

// ============================================
// Export for testing (if needed)
// ============================================
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        sanitizeInput,
        checkPasswordStrength,
        formatErrorMessage
    };
}
