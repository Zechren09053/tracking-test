// Global variables
let sessionCheckInterval;
let sessionLastChecked = 0;
let currentUserId = null;
const SESSION_CHECK_INTERVAL = 5000; // Check every 5 seconds

// Document ready function
document.addEventListener('DOMContentLoaded', function() {
    // Initialize session sync for cross-tab communication
    initSessionSync();
    
    // Handle image preview
    document.getElementById('profile_image').addEventListener('change', handleImagePreview);
    
    // Check if email exists when typing
    document.getElementById('email').addEventListener('blur', function() {
        const email = this.value;
        if (email) {
            checkEmailExists(email);
        }
    });
    
    // Check if phone number exists when typing
    document.getElementById('phone_number').addEventListener('blur', function() {
        const phone = this.value;
        if (phone) {
            checkPhoneExists(phone);
        }
    });
    
    // Set up form submission handlers
    document.getElementById('login-form').addEventListener('submit', handleLogin);
    document.getElementById('registration-form').addEventListener('submit', handleRegistration);
    
    // Listen for storage events for cross-tab communication
    window.addEventListener('storage', handleStorageChange);
});

// Handle profile image preview
function handleImagePreview(e) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);
        }
        reader.readAsDataURL(this.files[0]);
    } else {
        preview.innerHTML = '<span>No image selected</span>';
    }
}

// Initialize session synchronization
function initSessionSync() {
    // Check session immediately
    checkSession();
    
    // Set up interval for periodic checks
    sessionCheckInterval = setInterval(() => {
        // Only check if the tab is visible/active
        if (!document.hidden) {
            checkSession();
        }
    }, SESSION_CHECK_INTERVAL);
    
    // Add visibility change listener to pause/resume checking when tab is hidden/visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Tab is hidden, clear the interval
            clearInterval(sessionCheckInterval);
        } else {
            // Tab is visible again, check immediately and restart interval
            checkSession();
            sessionCheckInterval = setInterval(checkSession, SESSION_CHECK_INTERVAL);
        }
    });
}

// Handle localStorage changes from other tabs
function handleStorageChange(event) {
    // Listen for specific login/logout events
    if (event.key === 'userLoginState') {
        const loginState = JSON.parse(event.newValue);
        
        if (loginState && loginState.loggedIn) {
            // Another tab logged in, update this tab's UI
            currentUserId = loginState.userId;
            updateUIForLogin(loginState.userId);
        } else {
            // Another tab logged out, update this tab's UI
            currentUserId = null;
            clearInputFields();  // Add this line to clear inputs
            updateUIForLogout();
        }
    }
}

// Check if user is logged in
function checkSession() {
    // Add timestamp to prevent caching
    const timestamp = new Date().getTime();
    fetch(`check_session.php?_=${timestamp}`)
        .then(response => response.json())
        .then(data => {
            // Update last checked time
            sessionLastChecked = timestamp;
            
            if (data.logged_in) {
                // User is logged in
                if (currentUserId !== data.user_id) {
                    // Login state changed or initial load
                    currentUserId = data.user_id;
                    updateUIForLogin(data.user_id);
                    
                    // Update localStorage to notify other tabs
                    localStorage.setItem('userLoginState', JSON.stringify({
                        loggedIn: true,
                        userId: data.user_id,
                        timestamp: Date.now()
                    }));
                }
            } else {
                // User is not logged in
                if (currentUserId !== null) {
                    // Logout detected
                    currentUserId = null;
                    updateUIForLogout();
                    
                    // Update localStorage to notify other tabs
                    localStorage.setItem('userLoginState', JSON.stringify({
                        loggedIn: false,
                        timestamp: Date.now()
                    }));
                }
            }
        })
        .catch(error => {
            console.error('Error checking session:', error);
        });
}

// Update UI for login state
function updateUIForLogin(userId) {
    // Show dashboard tab
    document.getElementById('dashboard-tab').style.display = 'block';
    
    // Hide or disable login and register tabs
    const loginTab = document.querySelector('.tab[onclick*="openTab(\'login\')"]');
    const registerTab = document.querySelector('.tab[onclick*="openTab(\'register\')"]');
    
    if (loginTab) {
        // Option 1: Hide the login tab completely
        loginTab.style.display = 'none';
        
        // Option 2: Or disable it but keep it visible (uncomment if preferred)
        // loginTab.classList.add('disabled');
        // loginTab.style.opacity = '0.5';
        // loginTab.style.pointerEvents = 'none';
    }
    
    if (registerTab) {
        // Hide the register tab completely 
        registerTab.style.display = 'none';
        
        // Option 2: Or disable it but keep it visible (uncomment if preferred)
        // registerTab.classList.add('disabled');
        // registerTab.style.opacity = '0.5';
        // registerTab.style.pointerEvents = 'none';
    }
    
    // Switch to dashboard tab
    openTab('dashboard');
    
    // Load user data
    loadUserData(userId);
}

// Update UI for logout state
function updateUIForLogout() {
    // Hide dashboard tab
    document.getElementById('dashboard-tab').style.display = 'none';
    
    // Show/enable login and register tabs
    const loginTab = document.querySelector('.tab[onclick*="openTab(\'login\')"]');
    const registerTab = document.querySelector('.tab[onclick*="openTab(\'register\')"]');
    
    if (loginTab) {
        // Show the login tab
        loginTab.style.display = '';
        
        // Or if using the disabled approach
        // loginTab.classList.remove('disabled');
        // loginTab.style.opacity = '';
        // loginTab.style.pointerEvents = '';
    }
    
    if (registerTab) {
        // Show the register tab
        registerTab.style.display = '';
        
        // Or if using the disabled approach
        // registerTab.classList.remove('disabled');
        // registerTab.style.opacity = '';
        // registerTab.style.pointerEvents = '';
    }
    
    // Switch to login tab
    openTab('login');
    
    // Display logout message if on login tab
    if (document.getElementById('login').classList.contains('active')) {
        showLogoutMessage();
    }
}   
// Show logout success message
function showLogoutMessage() {
    // Remove any existing logout message
    const existingMessage = document.querySelector('.success-message');
    if (existingMessage) {
        existingMessage.parentNode.removeChild(existingMessage);
    }
    
    document.getElementById('login-general-error').textContent = '';
    const logoutMessage = document.createElement('div');
    logoutMessage.className = 'success-message';
    logoutMessage.textContent = 'You have been logged out.';
    logoutMessage.style.color = '#10b981';
    logoutMessage.style.padding = '10px';
    logoutMessage.style.marginTop = '15px';
    logoutMessage.style.textAlign = 'center';
    logoutMessage.style.fontWeight = 'bold';
    
    // Insert message before the login form
    const loginForm = document.getElementById('login-form');
    loginForm.parentNode.insertBefore(logoutMessage, loginForm);
    
    // Remove message after 5 seconds
    setTimeout(() => {
        logoutMessage.style.opacity = '0';
        logoutMessage.style.transition = 'opacity 1s';
        setTimeout(() => {
            if (logoutMessage.parentNode) {
                logoutMessage.parentNode.removeChild(logoutMessage);
            }
        }, 1000);
    }, 5000);
}

// Handle login form submission
function handleLogin(event) {
    event.preventDefault();
    
    // Clear previous errors
    document.getElementById('login-username-error').textContent = '';
    document.getElementById('login-password-error').textContent = '';
    document.getElementById('login-general-error').textContent = '';
    
    // Get form data
    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;
    
    // Basic validation
    if (!username) {
        document.getElementById('login-username-error').textContent = 'Email or phone number is required';
        return;
    }
    
    if (!password) {
        document.getElementById('login-password-error').textContent = 'Password is required';
        return;
    }
    
    // Send login request
    fetch('cllogin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username: username,
            password: password
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Login successful
            currentUserId = data.user_id;
            
            // Update localStorage to notify other tabs
            localStorage.setItem('userLoginState', JSON.stringify({
                loggedIn: true,
                userId: data.user_id,
                timestamp: Date.now()
            }));
            
            // Update UI
            updateUIForLogin(data.user_id);
        } else {
            // Login failed
            document.getElementById('login-general-error').textContent = data.message || 'Invalid credentials';
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        document.getElementById('login-general-error').textContent = 'An error occurred. Please try again.';
    });
}

// Handle registration form submission
function handleRegistration(event) {
    event.preventDefault();
    
    // Clear previous errors
    const errorFields = ['name', 'birth-date', 'email', 'phone', 'password', 'confirm-password', 'image', 'register-general'];
    errorFields.forEach(field => {
        document.getElementById(`${field}-error`).textContent = '';
    });
    
    // Get form data
    const fullName = document.getElementById('full_name').value;
    const birthDate = document.getElementById('birth_date').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone_number').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const profileImage = document.getElementById('profile_image').files[0];
    
    // Basic validation
    let hasErrors = false;
    
    if (!fullName) {
        document.getElementById('name-error').textContent = 'Full name is required';
        hasErrors = true;
    }
    
    if (!birthDate) {
        document.getElementById('birth-date-error').textContent = 'Date of birth is required';
        hasErrors = true;
    } else {
        // Check if user is at least 13 years old
        const today = new Date();
        const birthDateObj = new Date(birthDate);
        const age = today.getFullYear() - birthDateObj.getFullYear();
        const monthDiff = today.getMonth() - birthDateObj.getMonth();
        
        // If birth month is after current month or same month but birth day is after current day, subtract one year
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDateObj.getDate())) {
            if (age - 1 < 13) {
                document.getElementById('birth-date-error').textContent = 'You must be at least 13 years old';
                hasErrors = true;
            }
        } else if (age < 13) {
            document.getElementById('birth-date-error').textContent = 'You must be at least 13 years old';
            hasErrors = true;
        }
    }
    
    if (!email) {
        document.getElementById('email-error').textContent = 'Email is required';
        hasErrors = true;
    } else if (!isValidEmail(email)) {
        document.getElementById('email-error').textContent = 'Please enter a valid email address';
        hasErrors = true;
    }
    
    if (!phone) {
        document.getElementById('phone-error').textContent = 'Phone number is required';
        hasErrors = true;
    } else if (!isValidPhoneNumber(phone)) {
        document.getElementById('phone-error').textContent = 'Please enter a valid phone number';
        hasErrors = true;
    }
    
    if (!password) {
        document.getElementById('password-error').textContent = 'Password is required';
        hasErrors = true;
    } else if (password.length < 8) {
        document.getElementById('password-error').textContent = 'Password must be at least 8 characters';
        hasErrors = true;
    }
    
    if (password !== confirmPassword) {
        document.getElementById('confirm-password-error').textContent = 'Passwords do not match';
        hasErrors = true;
    }
    
    if (hasErrors) {
        return;
    }
    
    // Generate a random QR code data (normally this would be done server-side)
    const qrCodeData = generateQRCodeData();
    
    // Calculate expiration date (1 year from now by default)
    const expiryDate = new Date();
    expiryDate.setFullYear(expiryDate.getFullYear() + 1);
    
    // Create form data object
    const formData = new FormData();
    formData.append('full_name', fullName);
    formData.append('birth_date', birthDate);
    formData.append('email', email);
    formData.append('phone_number', phone);
    formData.append('password', password);
    formData.append('qr_code_data', qrCodeData);
    formData.append('expires_at', expiryDate.toISOString().split('T')[0]);
    
    if (profileImage) {
        formData.append('profile_image', profileImage);
    }
    
    // Send registration request
    fetch('user_register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Registration successful
            document.getElementById('registration-form').style.display = 'none';
            document.getElementById('registration-result').style.display = 'block';
        } else {
            // Registration failed
            if (data.errors) {
                // Display specific errors
                for (const [field, message] of Object.entries(data.errors)) {
                    const errorElement = document.getElementById(`${field}-error`);
                    if (errorElement) {
                        errorElement.textContent = message;
                    }
                }
            } else {
                // Display general error
                document.getElementById('register-general-error').textContent = data.message || 'Registration failed';
            }
        }
    })
    .catch(error => {
        console.error('Registration error:', error);
        document.getElementById('register-general-error').textContent = 'An error occurred. Please try again.';
    });
}

// Generate random QR code data
function generateQRCodeData() {
    return 'TIX-' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
}

// Validate email format
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Validate phone number format (basic validation)
function isValidPhoneNumber(phone) {
    // This is a basic validation, you can enhance it as needed
    const phoneRegex = /^\+?[0-9]{6,15}$/;
    return phoneRegex.test(phone);
}

// Check if email exists
function checkEmailExists(email) {
    fetch('check_email.php?email=' + encodeURIComponent(email))
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                document.getElementById('email-error').textContent = 'This email is already registered';
            } else {
                document.getElementById('email-error').textContent = '';
            }
        })
        .catch(error => {
            console.error('Error checking email:', error);
        });
}

// Check if phone number exists
function checkPhoneExists(phone) {
    fetch('check_phone.php?phone=' + encodeURIComponent(phone))
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                document.getElementById('phone-error').textContent = 'This phone number is already registered';
            } else {
                document.getElementById('phone-error').textContent = '';
            }
        })
        .catch(error => {
            console.error('Error checking phone:', error);
        });
}

// Load user data
function loadUserData(userId) {
    fetch('get_user.php?id=' + userId)
        .then(response => response.json())
        .then(user => {
            if (user && !user.error) {
                displayUserDashboard(user);
            }
        })
        .catch(error => {
            console.error('Error fetching user:', error);
        });
}

// Display user dashboard
function displayUserDashboard(user) {
    // Set user profile info
    document.getElementById('user-fullname').textContent = user.full_name;
    document.getElementById('user-email').textContent = user.email;
    
    // Set user avatar if available
    if (user.profile_image) {
        document.getElementById('user-avatar').src = user.profile_image;
    }
    
    // Calculate days until expiration
    const today = new Date();
    const expiryDate = new Date(user.expires_at);
    const daysRemaining = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));
    document.getElementById('days-remaining').textContent = daysRemaining > 0 ? daysRemaining : 0;
    
    // Set last login
    document.getElementById('last-login').textContent = user.last_used ? formatDateTime(user.last_used).split(' ')[0] : 'Never';
    
    // Set account status
    const isActive = user.is_active && new Date(user.expires_at) > new Date();
    document.getElementById('account-status').textContent = isActive ? 'Active' : 'Expired';
    document.getElementById('account-status').style.color = isActive ? '#10b981' : '#ef4444';
    
    // Fill in user details for ID card
    document.getElementById('card-name').textContent = user.full_name;
    document.getElementById('card-email').textContent = user.email;
    document.getElementById('card-phone').textContent = user.phone_number;
    document.getElementById('card-dob').textContent = formatDate(user.birth_date);
    document.getElementById('card-issued').textContent = formatDateTime(user.issued_at);
    document.getElementById('card-expires').textContent = formatDate(user.expires_at);
    
    // Set status
    const statusElement = document.getElementById('card-status');
    statusElement.className = `status ${isActive ? 'status-active' : 'status-expired'}`;
    statusElement.textContent = isActive ? 'ACTIVE' : 'EXPIRED';
    
    // Generate QR code
    const qrCodeData = JSON.stringify({
        id: user.id,
        qr_code_data: user.qr_code_data
    });
    
    const qrCodeElement = document.getElementById('qrcode');
    qrCodeElement.innerHTML = '';
    
    new QRCode(qrCodeElement, {
        text: qrCodeData,
        width: 128,
        height: 128
    });
}

// Open tab
function openTab(tabName) {
    // Hide all tab content
    const tabContents = document.getElementsByClassName('tab-content');
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove('active');
    }
    
    // Remove active class from all tabs
    const tabs = document.getElementsByClassName('tab');
    for (let i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }
    
    // Show the selected tab content
    document.getElementById(tabName).classList.add('active');
    
    // Find and activate the selected tab
    const selectedTab = Array.from(document.getElementsByClassName('tab')).find(tab => {
        return tab.getAttribute('onclick').includes(tabName);
    });
    
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
}

// Format date for display
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString();
}

// Format datetime for display
function formatDateTime(dateString) {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

// Print user card
function printCard() {
    const cardContent = document.querySelector('.user-card').cloneNode(true);
    
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>ID Card</title>');
    
    // Add styles
    printWindow.document.write(`
        <style>
            body {
                font-family: Arial, sans-serif;
                padding: 20px;
            }
            .card {
                border: 2px solid #000;
                border-radius: 10px;
                padding: 20px;
                width: 400px;
                margin: 0 auto;
            }
            .user-card {
                display: flex;
            }
            .user-info {
                flex: 2;
            }
            .qr-code {
                flex: 1;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .status {
                padding: 5px 10px;
                border-radius: 4px;
                display: inline-block;
                margin-top: 5px;
            }
            .status-active {
                background-color: #10b981;
                color: white;
            }
            .status-expired {
                background-color: #ef4444;
                color: white;
            }
        </style>
    `);
    
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h2 style="text-align: center;">PRFS Official ID Card</h2>');
    printWindow.document.write('<div class="card">');
    printWindow.document.write(cardContent.outerHTML);
    printWindow.document.write('</div>');
    printWindow.document.write('</body></html>');
    
    printWindow.document.close();
    printWindow.focus();
    
    // Print after a short delay to ensure content is loaded
    setTimeout(() => {
        printWindow.print();
    }, 500);
}

// Logout function
function logout() {
    fetch('cllogout.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update localStorage to notify other tabs
                localStorage.setItem('userLoginState', JSON.stringify({
                    loggedIn: false,
                    timestamp: Date.now()
                }));
                
                // Clear all input fields
                clearInputFields();
                
                // Update current tab's UI
                currentUserId = null;
                updateUIForLogout();
            }
        })
        .catch(error => {
            console.error('Error logging out:', error);
            // Display error message
            document.getElementById('login-general-error').textContent = 'An error occurred while logging out. Please try again.';
        });
}
// Global variables for inactivity tracking
let inactivityTimeout;
const INACTIVITY_TIMEOUT = 3 * 60 * 1000; // 1 minutes in milliseconds

// Initialize inactivity tracking
function initInactivityTracking() {
    // Reset the timer when the page loads
    resetInactivityTimer();
    
    // Add event listeners for user activity
    const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
    activityEvents.forEach(event => {
        document.addEventListener(event, resetInactivityTimer);
    });
    
    // Special handling for tab visibility changes
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && currentUserId !== null) {
            // When tab becomes visible again and user is logged in, reset the timer
            resetInactivityTimer();
        }
    });
}

// Reset the inactivity timer
function resetInactivityTimer() {
    // Only set timer if user is logged in
    if (currentUserId !== null) {
        // Clear any existing timeout
        clearTimeout(inactivityTimeout);
        
        // Set a new timeout
        inactivityTimeout = setTimeout(handleInactivity, INACTIVITY_TIMEOUT);
    }
}

// Handle user inactivity
function handleInactivity() {
    // Only proceed if user is still logged in
    if (currentUserId !== null) {
        console.log('User inactive for 3 minutes, logging out...');
        
        // Show a warning message before logout
        showInactivityWarning();
        
        // Clear input fields before logout
        clearInputFields();
        
        // Perform logout after a brief delay to allow user to see the message
        setTimeout(function() {
            logout();
        }, 2000);
    }
}

// Show inactivity warning message
function showInactivityWarning() {
    // Create a warning overlay
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
    overlay.style.display = 'flex';
    overlay.style.justifyContent = 'center';
    overlay.style.alignItems = 'center';
    overlay.style.zIndex = '1000';
    
    // Create message box
    const messageBox = document.createElement('div');
    messageBox.style.backgroundColor = 'white';
    messageBox.style.padding = '20px';
    messageBox.style.borderRadius = '5px';
    messageBox.style.maxWidth = '400px';
    messageBox.style.textAlign = 'center';
    
    // Add message content
    messageBox.innerHTML = `
        <h3 style="margin-top: 0; color: #ef4444;">Session Timeout</h3>
        <p>Your session has expired due to inactivity.</p>
        <p>You are being logged out for security reasons.</p>
    `;
    
    // Add message box to overlay
    overlay.appendChild(messageBox);
    
    // Add overlay to document
    document.body.appendChild(overlay);
    
    // Remove overlay after logout completes
    setTimeout(() => {
        if (overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
        }
    }, 3000);
}

// Add to document ready function
document.addEventListener('DOMContentLoaded', function() {
    // Existing initializations...
    initSessionSync();
    
    // Add inactivity tracking initialization
    initInactivityTracking();
    
    // Rest of your existing code...
});
// CAPTCHA Implementation
// This will add a custom CAPTCHA to the login and registration forms

// Global variables for CAPTCHA
let captchaValue = null;
let loginCaptchaRequired = true;      // Set to true to always require CAPTCHA on login
let registerCaptchaRequired = true;   // Set to true to always require CAPTCHA on registration
let loginFailCount = 0;               // Count failed login attempts

// Add this to your document ready function
document.addEventListener('DOMContentLoaded', function() {
    // Initialize CAPTCHA
    initCaptcha();
    
    // Generate initial CAPTCHAs
    generateCaptcha('login-captcha');
    generateCaptcha('register-captcha');
    
    // Add refresh CAPTCHA event listeners
    document.getElementById('refresh-login-captcha').addEventListener('click', function(e) {
        e.preventDefault();
        generateCaptcha('login-captcha');
    });
    
    document.getElementById('refresh-register-captcha').addEventListener('click', function(e) {
        e.preventDefault();
        generateCaptcha('register-captcha');
    });
});

// Initialize CAPTCHA elements in the forms
function initCaptcha() {
    // Create login form CAPTCHA
    const loginCaptchaHTML = `
        <div class="form-group captcha-container">
            <label for="login-captcha-input">Verify you're human</label>
            <div class="captcha-box">
                <canvas id="login-captcha" width="200" height="50"></canvas>
                <button id="refresh-login-captcha" class="captcha-refresh" title="Refresh CAPTCHA">↻</button>
            </div>
            <input type="text" id="login-captcha-input" name="captcha" required placeholder="Enter the code above">
            <div id="login-captcha-error" class="error"></div>
        </div>
    `;
    
    // Create registration form CAPTCHA
    const registerCaptchaHTML = `
        <div class="form-group captcha-container">
            <label for="register-captcha-input">Verify you're human</label>
            <div class="captcha-box">
                <canvas id="register-captcha" width="200" height="50"></canvas>
                <button id="refresh-register-captcha" class="captcha-refresh" title="Refresh CAPTCHA">↻</button>
            </div>
            <input type="text" id="register-captcha-input" name="captcha" required placeholder="Enter the code above">
            <div id="register-captcha-error" class="error"></div>
        </div>
    `;
    
    // Insert login CAPTCHA before the login button
    const loginForm = document.getElementById('login-form');
    const loginButton = loginForm.querySelector('button[type="submit"]');
    loginForm.insertBefore(createElementFromHTML(loginCaptchaHTML), loginButton);
    
    // Insert registration CAPTCHA before the register button
    const registerForm = document.getElementById('registration-form');
    const registerButton = registerForm.querySelector('button[type="submit"]');
    registerForm.insertBefore(createElementFromHTML(registerCaptchaHTML), registerButton);
    
    // Add styles for CAPTCHA
    addCaptchaStyles();
}

// Helper function to create element from HTML string
function createElementFromHTML(htmlString) {
    const div = document.createElement('div');
    div.innerHTML = htmlString.trim();
    return div.firstChild;
}

// Generate a new CAPTCHA and display it on the canvas
function generateCaptcha(canvasId) {
    const canvas = document.getElementById(canvasId);
    const ctx = canvas.getContext('2d');
    
    // Clear the canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Set background
    ctx.fillStyle = '#f0f0f0';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Generate random alphanumeric string (exclude confusing characters like 0, O, 1, I, etc.)
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    let captchaText = '';
    for (let i = 0; i < 6; i++) {
        captchaText += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    // Store the CAPTCHA value in a way that's accessible for validation
    // Using a data attribute on the canvas
    canvas.dataset.captchaValue = captchaText;
    
    // Draw the text with distortion
    ctx.font = 'bold 28px Arial';
    
    // Add noise (dots)
    for (let i = 0; i < 100; i++) {
        ctx.fillStyle = getRandomColor(0.6);
        ctx.beginPath();
        ctx.arc(
            Math.random() * canvas.width,
            Math.random() * canvas.height,
            Math.random() * 2,
            0,
            Math.PI * 2
        );
        ctx.fill();
    }
    
    // Add lines
    for (let i = 0; i < 5; i++) {
        ctx.strokeStyle = getRandomColor(0.5);
        ctx.beginPath();
        ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
        ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
        ctx.stroke();
    }
    
    // Draw each character with slight rotation and position variation
    for (let i = 0; i < captchaText.length; i++) {
        ctx.save();
        ctx.translate(30 + i * 25, 35);
        ctx.rotate((Math.random() - 0.5) * 0.3);
        ctx.fillStyle = getRandomColor(0.8);
        ctx.fillText(captchaText[i], 0, 0);
        ctx.restore();
    }
}

// Get a random color with specified opacity
function getRandomColor(opacity) {
    const r = Math.floor(Math.random() * 100);
    const g = Math.floor(Math.random() * 100);
    const b = Math.floor(Math.random() * 100);
    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
}

// Add styles for CAPTCHA elements
function addCaptchaStyles() {
    const styleElement = document.createElement('style');
    styleElement.textContent = `
        .captcha-container {
            margin-bottom: 20px;
        }
        
        .captcha-box {
            position: relative;
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .captcha-refresh {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.7);
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }
        
        .captcha-refresh:hover {
            background: rgba(255, 255, 255, 0.9);
        }
        
        #login-captcha, #register-captcha {
            width: 100%;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
    `;
    document.head.appendChild(styleElement);
}

// Validate CAPTCHA
function validateCaptcha(formType) {
    const canvasId = `${formType}-captcha`;
    const inputId = `${formType}-captcha-input`;
    const errorId = `${formType}-captcha-error`;
    
    const canvas = document.getElementById(canvasId);
    const input = document.getElementById(inputId);
    const errorElement = document.getElementById(errorId);
    
    // Get the expected value from the canvas data attribute
    const expectedValue = canvas.dataset.captchaValue;
    const inputValue = input.value.trim();
    
    // Clear previous error
    errorElement.textContent = '';
    
    // Check if CAPTCHA is required based on form type
    const isRequired = formType === 'login' ? loginCaptchaRequired : registerCaptchaRequired;
    
    // For login, also check failed attempts
    const requireDueToFailures = formType === 'login' && loginFailCount >= 3;
    
    if ((isRequired || requireDueToFailures) && !inputValue) {
        errorElement.textContent = 'Please enter the CAPTCHA code';
        return false;
    }
    
    if ((isRequired || requireDueToFailures) && inputValue !== expectedValue) {
        errorElement.textContent = 'CAPTCHA code is incorrect';
        // Generate a new CAPTCHA
        generateCaptcha(canvasId);
        input.value = '';
        return false;
    }
    
    return true;
}

// Update the handleLogin function
// Modify your existing handleLogin function by adding CAPTCHA validation
function handleLogin(event) {
    event.preventDefault();
    
    // Clear previous errors
    document.getElementById('login-username-error').textContent = '';
    document.getElementById('login-password-error').textContent = '';
    document.getElementById('login-general-error').textContent = '';
    document.getElementById('login-captcha-error').textContent = '';
    
    // Get form data
    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;
    
    // Basic validation
    if (!username) {
        document.getElementById('login-username-error').textContent = 'Email or phone number is required';
        return;
    }
    
    if (!password) {
        document.getElementById('login-password-error').textContent = 'Password is required';
        return;
    }
    
    // Validate CAPTCHA
    if (!validateCaptcha('login')) {
        return;
    }
    
    // Send login request
    fetch('cllogin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username: username,
            password: password
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Login successful
            currentUserId = data.user_id;
            loginFailCount = 0; // Reset fail counter on success
            
            // Update localStorage to notify other tabs
            localStorage.setItem('userLoginState', JSON.stringify({
                loggedIn: true,
                userId: data.user_id,
                timestamp: Date.now()
            }));
            
            // Update UI
            updateUIForLogin(data.user_id);
        } else {
            // Login failed
            loginFailCount++; // Increment fail counter
            
            // Show CAPTCHA after 3 failed attempts if not already shown
            if (loginFailCount >= 3) {
                document.querySelector('.form-group.captcha-container').style.display = 'block';
                // Generate a new CAPTCHA
                generateCaptcha('login-captcha');
            }
            
            document.getElementById('login-general-error').textContent = data.message || 'Invalid credentials';
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        document.getElementById('login-general-error').textContent = 'An error occurred. Please try again.';
    });
}

// Update the handleRegistration function
// Modify your existing handleRegistration function by adding CAPTCHA validation
function handleRegistration(event) {
    event.preventDefault();
    
    // Clear previous errors
    const errorFields = ['name', 'birth-date', 'email', 'phone', 'password', 'confirm-password', 'image', 'register-general', 'register-captcha'];
    errorFields.forEach(field => {
        const element = document.getElementById(`${field}-error`);
        if (element) element.textContent = '';
    });
    
    // Get form data
    const fullName = document.getElementById('full_name').value;
    const birthDate = document.getElementById('birth_date').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone_number').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const profileImage = document.getElementById('profile_image').files[0];
    
    // Basic validation
    let hasErrors = false;
    
    if (!fullName) {
        document.getElementById('name-error').textContent = 'Full name is required';
        hasErrors = true;
    }
    
    if (!birthDate) {
        document.getElementById('birth-date-error').textContent = 'Date of birth is required';
        hasErrors = true;
    } else {
        // Check if user is at least 13 years old
        const today = new Date();
        const birthDateObj = new Date(birthDate);
        const age = today.getFullYear() - birthDateObj.getFullYear();
        const monthDiff = today.getMonth() - birthDateObj.getMonth();
        
        // If birth month is after current month or same month but birth day is after current day, subtract one year
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDateObj.getDate())) {
            if (age - 1 < 13) {
                document.getElementById('birth-date-error').textContent = 'You must be at least 13 years old';
                hasErrors = true;
            }
        } else if (age < 13) {
            document.getElementById('birth-date-error').textContent = 'You must be at least 13 years old';
            hasErrors = true;
        }
    }
    
    if (!email) {
        document.getElementById('email-error').textContent = 'Email is required';
        hasErrors = true;
    } else if (!isValidEmail(email)) {
        document.getElementById('email-error').textContent = 'Please enter a valid email address';
        hasErrors = true;
    }
    
    if (!phone) {
        document.getElementById('phone-error').textContent = 'Phone number is required';
        hasErrors = true;
    } else if (!isValidPhoneNumber(phone)) {
        document.getElementById('phone-error').textContent = 'Please enter a valid phone number';
        hasErrors = true;
    }
    
    if (!password) {
        document.getElementById('password-error').textContent = 'Password is required';
        hasErrors = true;
    } else if (password.length < 8) {
        document.getElementById('password-error').textContent = 'Password must be at least 8 characters';
        hasErrors = true;
    }
    
    if (password !== confirmPassword) {
        document.getElementById('confirm-password-error').textContent = 'Passwords do not match';
        hasErrors = true;
    }
    
    // Validate CAPTCHA
    if (!validateCaptcha('register')) {
        hasErrors = true;
    }
    
    if (hasErrors) {
        return;
    }
    
    // Generate a random QR code data (normally this would be done server-side)
    const qrCodeData = generateQRCodeData();
    
    // Calculate expiration date (1 year from now by default)
    const expiryDate = new Date();
    expiryDate.setFullYear(expiryDate.getFullYear() + 1);
    
    // Create form data object
    const formData = new FormData();
    formData.append('full_name', fullName);
    formData.append('birth_date', birthDate);
    formData.append('email', email);
    formData.append('phone_number', phone);
    formData.append('password', password);
    formData.append('qr_code_data', qrCodeData);
    formData.append('expires_at', expiryDate.toISOString().split('T')[0]);
    
    if (profileImage) {
        formData.append('profile_image', profileImage);
    }
    
    // Send registration request
    fetch('user_register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Registration successful
            document.getElementById('registration-form').style.display = 'none';
            document.getElementById('registration-result').style.display = 'block';
        } else {
            // Registration failed
            if (data.errors) {
                // Display specific errors
                for (const [field, message] of Object.entries(data.errors)) {
                    const errorElement = document.getElementById(`${field}-error`);
                    if (errorElement) {
                        errorElement.textContent = message;
                    }
                }
            } else {
                // Display general error
                document.getElementById('register-general-error').textContent = data.message || 'Registration failed';
            }
            
            // Generate a new CAPTCHA
            generateCaptcha('register-captcha');
            document.getElementById('register-captcha-input').value = '';
        }
    })
    .catch(error => {
        console.error('Registration error:', error);
        document.getElementById('register-general-error').textContent = 'An error occurred. Please try again.';
        
        // Generate a new CAPTCHA
        generateCaptcha('register-captcha');
        document.getElementById('register-captcha-input').value = '';
    });
}
// Function to clear all input fields after logout
function clearInputFields() {
    // Clear login form inputs
    if (document.getElementById('login-username')) {
        document.getElementById('login-username').value = '';
    }
    if (document.getElementById('login-password')) {
        document.getElementById('login-password').value = '';
    }
    
    // Clear CAPTCHA input if it exists
    const loginCaptchaInput = document.getElementById('login-captcha-input');
    if (loginCaptchaInput) {
        loginCaptchaInput.value = '';
    }
    
    // Clear registration form inputs
    const registrationInputs = [
        'full_name',
        'birth_date',
        'email',
        'phone_number',
        'password',
        'confirm_password'
    ];
    
    registrationInputs.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.value = '';
        }
    });
    
    // Clear registration CAPTCHA input if it exists
    const registerCaptchaInput = document.getElementById('register-captcha-input');
    if (registerCaptchaInput) {
        registerCaptchaInput.value = '';
    }
    
    // Reset image preview
    const imagePreview = document.getElementById('image-preview');
    if (imagePreview) {
        imagePreview.innerHTML = '<span>No image selected</span>';
    }
    
    // Reset the file input for profile image
    const profileImage = document.getElementById('profile_image');
    if (profileImage) {
        profileImage.value = '';
    }
    
    // Clear all error messages
    const errorElements = document.querySelectorAll('.error');
    errorElements.forEach(element => {
        element.textContent = '';
    });
    
    // Generate new CAPTCHAs
    if (typeof generateCaptcha === 'function') {
        generateCaptcha('login-captcha');
        generateCaptcha('register-captcha');
    }
}