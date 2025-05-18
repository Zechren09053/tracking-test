
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Ticket System - Login & Registration</title>
    <link rel="stylesheet" href="cll.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="clientlogin.js" defer></script>
    
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
</body>
</html>