<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Ticket System - Login & Registration</title>
    <link rel="stylesheet" href="cll.css">
  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="tabs">
            <div class="tab active" onclick="openTab('login')">Login</div>
            <div class="tab" onclick="openTab('register')">Register</div>
            <div class="tab" id="dashboard-tab" style="display: none;" onclick="openTab('dashboard')">Dashboard</div>
        </div>
        
        <div id="login" class="tab-content active">
            <h2>Login to Your Account</h2>
            <form id="login-form">
                <div class="form-group">
                    <label for="login-username">Email or Phone Number</label>
                    <input type="text" id="login-username" name="username" required placeholder="Enter your email or phone number">
                    <div id="login-username-error" class="error"></div>
                </div>
                
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" required placeholder="Enter your password">
                    <div id="login-password-error" class="error"></div>
                </div>
                
                <button type="submit">Login</button>
                <div id="login-general-error" class="error" style="margin-top: 15px;"></div>
            </form>
        </div>
        
        <div id="register" class="tab-content">
            <h2>Create New Account</h2>
            <form id="registration-form">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required placeholder="Enter your full name">
                    <div id="name-error" class="error"></div>
                </div>
                
                <div class="form-group">
                    <label for="birth_date">Date of Birth</label>
                    <input type="date" id="birth_date" name="birth_date" required>
                    <div id="birth-date-error" class="error"></div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email address">
                    <div id="email-error" class="error"></div>
                </div>
                
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number" required placeholder="Enter your phone number (e.g., +1234567890)">
                    <div id="phone-error" class="error"></div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="At least 8 characters">
                    <div id="password-error" class="error"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
                    <div id="confirm-password-error" class="error"></div>
                </div>
                
                <div class="form-group">
                    <label for="profile_image">Profile Image (Optional)</label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*">
                    <div class="image-preview" id="image-preview">
                        <span>No image selected</span>
                    </div>
                    <div id="image-error" class="error"></div>
                </div>
                
                <button type="submit">Register Account</button>
                <div id="register-general-error" class="error" style="margin-top: 15px;"></div>
            </form>
            
            <div id="registration-result" class="card" style="display: none;">
                <h3>Registration Successful</h3>
                <p>Your account has been created successfully. You can now log in with your credentials.</p>
                <button onclick="openTab('login')">Go to Login</button>
            </div>
        </div>
        
        <div id="dashboard" class="tab-content">
            <div class="dashboard-header">
                <h2>Dashboard</h2>
                <button onclick="logout()">Logout</button>
            </div>
            
            <div class="user-profile">
                <img id="user-avatar" src="/api/placeholder/80/80" alt="Profile" class="user-profile-img">
                <div class="user-profile-info">
                    <h3 id="user-fullname">User Name</h3>
                    <p id="user-email">user@example.com</p>
                </div>
            </div>
            
            <div class="user-stats">
                <div class="stat-card">
                    <h3 id="days-remaining">0</h3>
                    <p>Days Until Expiration</p>
                </div>
                <div class="stat-card">
                    <h3 id="last-login">Never</h3>
                    <p>Last Access</p>
                </div>
                <div class="stat-card">
                    <h3 id="account-status">Unknown</h3>
                    <p>Account Status</p>
                </div>
            </div>
            
            <div class="card">
                <h3>Your Digital ID</h3>
                <div class="user-card">
                    <div class="user-info">
                        <h3 id="card-name"></h3>
                        <p><strong>Email:</strong> <span id="card-email"></span></p>
                        <p><strong>Phone:</strong> <span id="card-phone"></span></p>
                        <p><strong>DOB:</strong> <span id="card-dob"></span></p>
                        <p><strong>Issued:</strong> <span id="card-issued"></span></p>
                        <p><strong>Expires:</strong> <span id="card-expires"></span></p>
                        <div>
                            <span id="card-status" class="status"></span>
                        </div>
                    </div>
                    <div class="qr-code">
                        <div id="qrcode"></div>
                    </div>
                </div>
                <button onclick="printCard()">Print ID Card</button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let sessionCheckInterval;
        let sessionLastChecked = 0;
        let currentUserId = null;
        const SESSION_CHECK_INTERVAL = 5000; // Check every 5 seconds
        
        // Check session state on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize session sync for cross-tab communication
            initSessionSync();
            
            // Handle image preview
            document.getElementById('profile_image').addEventListener('change', function(e) {
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
            });
            
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
            document.getElementById('dashboard-tab').style.display = 'block';
            openTab('dashboard');
            loadUserData(userId);
        }
        
        // Update UI for logout state
        function updateUIForLogout() {
            document.getElementById('dashboard-tab').style.display = 'none';
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
    </script>
</body>
</html>