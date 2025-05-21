<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital QR ID System</title>
    <link rel="stylesheet" href="qr.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.4/html5-qrcode.min.js"></script>
    
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Digital QR ID System</h1>
            <p>Register, Generate and Scan QR ID Cards</p>
        </div>
        
        <div class="tabs">
            <div class="tab active" onclick="openTab('register')">Register</div>
            <div class="tab" onclick="openTab('view')">View IDs</div>
            <div class="tab" onclick="openTab('scan')">Scan QR Code</div>
        </div>
        
        <div id="register" class="tab-content active">
            <h2>Register New User</h2>
            <form id="registration-form" action="process_registration.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="birth_date">Date of Birth</label>
                    <input type="date" id="birth_date" name="birth_date" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                    <div id="email-error" class="error"></div>
                </div>
                
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number" required>
                </div>
                
                <div class="form-group">
                    <label for="profile_image">Profile Image</label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*">
                    <div class="image-preview" id="image-preview">
                        <span>No image selected</span>
                    </div>
                </div>
                
                <button type="submit">Register & Generate QR Code</button>
            </form>
            
            <div id="registration-result" class="card" style="display: none;">
                <h3>Registration Successful</h3>
                <div class="user-card">
                    <div class="user-info">
                        <h3 id="card-name"></h3>
                        <p><strong>Email:</strong> <span id="card-email"></span></p>
                        <p><strong>Phone:</strong> <span id="card-phone"></span></p>
                        <p><strong>DOB:</strong> <span id="card-dob"></span></p>
                        <p><strong>Issued:</strong> <span id="card-issued"></span></p>
                        <p><strong>Expires:</strong> <span id="card-expires"></span></p>
                        <div>
                            <span class="status status-active">ACTIVE</span>
                        </div>
                    </div>
                    <div class="qr-code">
                        <div id="qrcode"></div>
                    </div>
                </div>
                <button onclick="printCard()">Print ID Card</button>
                <button onclick="resetForm()">Register Another</button>
            </div>
        </div>
        
        <div id="view" class="tab-content">
            <h2>View Registered IDs</h2>
            <div class="search-box">
                <input type="text" id="search-input" placeholder="Search by name or email..." onkeyup="searchUsers()">
            </div>
            
            <div class="user-list" id="user-list">
                <!-- User items will be generated here -->
            </div>
            
            <div id="user-details" class="card" style="display: none;">
                <h3>User Details</h3>
                <div class="user-card">
                    <div class="user-info">
                        <h3 id="details-name"></h3>
                        <p><strong>Email:</strong> <span id="details-email"></span></p>
                        <p><strong>Phone:</strong> <span id="details-phone"></span></p>
                        <p><strong>DOB:</strong> <span id="details-dob"></span></p>
                        <p><strong>Issued:</strong> <span id="details-issued"></span></p>
                        <p><strong>Expires:</strong> <span id="details-expires"></span></p>
                        <p><strong>Last Used:</strong> <span id="details-last-used"></span></p>
                        <div id="details-status-container">
                            <span id="details-status" class="status"></span>
                        </div>
                    </div>
                    <div class="qr-code">
                        <div id="details-qrcode"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="scan" class="tab-content">
            <h2>Scan QR Code</h2>
            <div class="scanner-container">
                <div id="reader"></div>
                <button id="start-scanner" onclick="startScanner()">Start Scanner</button>
                
                <div class="result-container" id="scan-result" style="display: none;">
                    <h3>Scan Result</h3>
                    <div class="user-result">
                        <h3 id="result-name"></h3>
                        <p><strong>Email:</strong> <span id="result-email"></span></p>
                        <p><strong>Phone:</strong> <span id="result-phone"></span></p>
                        <p><strong>DOB:</strong> <span id="result-dob"></span></p>
                        <p><strong>Issued:</strong> <span id="result-issued"></span></p>
                        <p><strong>Expires:</strong> <span id="result-expires"></span></p>
                        <p><strong>Last Used:</strong> <span id="result-last-used"></span></p>
                        <div>
                            <span id="result-status" class="status"></span>
                        </div>
                    </div>
                    <button onclick="closeResult()">Scan Another</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let html5QrCode;
        
        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            // Check if there's a registration result in URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('registration')) {
                fetchUserAndDisplay(urlParams.get('registration'));
            }
            
            // Check if there's an email error in URL parameters
            if (urlParams.has('error') && urlParams.get('error') === 'email_exists') {
                document.getElementById('email-error').textContent = 'This email is already registered';
            }
            
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
            
            // If "view" tab is active, populate user list
            if (document.getElementById('view').classList.contains('active')) {
                populateUserList();
            }
            
            // Add click handler for tab navigation
            const tabs = document.getElementsByClassName('tab');
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].addEventListener('click', function() {
                    const tabName = this.getAttribute('onclick').match(/'([^']+)'/)[1];
                    if (tabName === 'view') {
                        populateUserList();
                    }
                });
            }
        });
        
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
        
        // Fetch user by ID and display their card
        function fetchUserAndDisplay(userId) {
            fetch('get_user.php?id=' + userId)
                .then(response => response.json())
                .then(user => {
                    if (user && !user.error) {
                        displayUserCard(user);
                    }
                })
                .catch(error => {
                    console.error('Error fetching user:', error);
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
            
            // If scan tab is selected, initialize scanner
            if (tabName === 'scan') {
                document.getElementById('scan-result').style.display = 'none';
                document.getElementById('start-scanner').style.display = 'block';
            }
            
            // If view tab is selected, populate the user list
            if (tabName === 'view') {
                populateUserList();
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
        
        // Display user card with QR code
        function displayUserCard(user) {
            // Hide the form
            document.getElementById('registration-form').style.display = 'none';
            
            // Show the result
            const resultCard = document.getElementById('registration-result');
            resultCard.style.display = 'block';
            
            // Fill in user details
            document.getElementById('card-name').textContent = user.full_name;
            document.getElementById('card-email').textContent = user.email;
            document.getElementById('card-phone').textContent = user.phone_number;
            document.getElementById('card-dob').textContent = formatDate(user.birth_date);
            document.getElementById('card-issued').textContent = formatDateTime(user.issued_at);
            document.getElementById('card-expires').textContent = formatDate(user.expires_at);
            
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
        
        // Reset the registration form
        function resetForm() {
            document.getElementById('registration-form').reset();
            document.getElementById('image-preview').innerHTML = '<span>No image selected</span>';
            document.getElementById('registration-form').style.display = 'block';
            document.getElementById('registration-result').style.display = 'none';
            
            // Clear URL parameters
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        // Populate the user list
        function populateUserList() {
            const userList = document.getElementById('user-list');
            userList.innerHTML = '<p>Loading users...</p>';
            
            fetch('get_users.php')
                .then(response => response.json())
                .then(users => {
                    userList.innerHTML = '';
                    
                    if (users.length === 0) {
                        userList.innerHTML = '<p>No users registered yet.</p>';
                        return;
                    }
                    
                    users.forEach(user => {
                        const userItem = document.createElement('div');
                        userItem.className = 'user-item';
                        userItem.onclick = () => showUserDetails(user.id);
                        
                        // Create profile image
                        const img = document.createElement('img');
                        img.className = 'user-item-img';
                        img.src = user.profile_image || '/api/placeholder/50/50';
                        img.alt = user.full_name;
                        
                        // Create user info
                        const infoDiv = document.createElement('div');
                        infoDiv.className = 'user-item-info';
                        
                        const nameDiv = document.createElement('div');
                        nameDiv.className = 'user-item-name';
                        nameDiv.textContent = user.full_name;
                        
                        const emailDiv = document.createElement('div');
                        emailDiv.textContent = user.email;
                        
                        infoDiv.appendChild(nameDiv);
                        infoDiv.appendChild(emailDiv);
                        
                        // Create status badge
                        const statusSpan = document.createElement('span');
                        const isActive = user.is_active && new Date(user.expires_at) > new Date();
                        statusSpan.className = `status ${isActive ? 'status-active' : 'status-expired'}`;
                        statusSpan.textContent = isActive ? 'ACTIVE' : 'EXPIRED';
                        
                        userItem.appendChild(img);
                        userItem.appendChild(infoDiv);
                        userItem.appendChild(statusSpan);
                        
                        userList.appendChild(userItem);
                    });
                })
                .catch(error => {
                    console.error('Error fetching users:', error);
                    userList.innerHTML = '<p>Error loading users. Please try again.</p>';
                });
        }
        
        // Show user details
        function showUserDetails(userId) {
            fetch('get_user.php?id=' + userId)
                .then(response => response.json())
                .then(user => {
                    if (user && !user.error) {
                        const detailsCard = document.getElementById('user-details');
                        detailsCard.style.display = 'block';
                        
                        // Fill in user details
                        document.getElementById('details-name').textContent = user.full_name;
                        document.getElementById('details-email').textContent = user.email;
                        document.getElementById('details-phone').textContent = user.phone_number;
                        document.getElementById('details-dob').textContent = formatDate(user.birth_date);
                        document.getElementById('details-issued').textContent = formatDateTime(user.issued_at);
                        document.getElementById('details-expires').textContent = formatDate(user.expires_at);
                        document.getElementById('details-last-used').textContent = formatDateTime(user.last_used);
                        
                        // Set status
                        const isActive = user.is_active && new Date(user.expires_at) > new Date();
                        const statusElement = document.getElementById('details-status');
                        statusElement.className = `status ${isActive ? 'status-active' : 'status-expired'}`;
                        statusElement.textContent = isActive ? 'ACTIVE' : 'EXPIRED';
                        
                        // Generate QR code
                        const qrCodeData = JSON.stringify({
                            id: user.id,
                            qr_code_data: user.qr_code_data
                        });
                        
                        const qrCodeElement = document.getElementById('details-qrcode');
                        qrCodeElement.innerHTML = '';
                        
                        new QRCode(qrCodeElement, {
                            text: qrCodeData,
                            width: 128,
                            height: 128
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching user details:', error);
                });
        }
        
        // Search users
        function searchUsers() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            
            if (searchTerm.length < 2) {
                populateUserList();
                return;
            }
            
            fetch('search_users.php?term=' + encodeURIComponent(searchTerm))
                .then(response => response.json())
                .then(users => {
                    const userList = document.getElementById('user-list');
                    userList.innerHTML = '';
                    
                    if (users.length === 0) {
                        userList.innerHTML = '<p>No matching users found.</p>';
                        return;
                    }
                    
                    users.forEach(user => {
                        const userItem = document.createElement('div');
                        userItem.className = 'user-item';
                        userItem.onclick = () => showUserDetails(user.id);
                        
                        // Create profile image
                        const img = document.createElement('img');
                        img.className = 'user-item-img';
                        img.src = user.profile_image || '/api/placeholder/50/50';
                        img.alt = user.full_name;
                        
                        // Create user info
                        const infoDiv = document.createElement('div');
                        infoDiv.className = 'user-item-info';
                        
                        const nameDiv = document.createElement('div');
                        nameDiv.className = 'user-item-name';
                        nameDiv.textContent = user.full_name;
                        
                        const emailDiv = document.createElement('div');
                        emailDiv.textContent = user.email;
                        
                        infoDiv.appendChild(nameDiv);
                        infoDiv.appendChild(emailDiv);
                        
                        // Create status badge
                        const statusSpan = document.createElement('span');
                        const isActive = user.is_active && new Date(user.expires_at) > new Date();
                        statusSpan.className = `status ${isActive ? 'status-active' : 'status-expired'}`;
                        statusSpan.textContent = isActive ? 'ACTIVE' : 'EXPIRED';
                        
                        userItem.appendChild(img);
                        userItem.appendChild(infoDiv);
                        userItem.appendChild(statusSpan);
                        
                        userList.appendChild(userItem);
                    });
                })
                .catch(error => {
                    console.error('Error searching users:', error);
                });
        }
        
        // Start the QR scanner
        function startScanner() {
            document.getElementById('start-scanner').style.display = 'none';
            
            html5QrCode = new Html5Qrcode("reader");
            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                // Stop scanning
                html5QrCode.stop().then(() => {
                    processQrCode(decodedText);
                }).catch((err) => {
                    console.error(err);
                });
            };
            

            const config = { fps: 10, qrbox: { width: 250, height: 250 } };
            
            html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
        }
        
        // Process QR code data
        function processQrCode(decodedText) {
            try {
                const qrData = JSON.parse(decodedText);
                
                // Send QR data to server for verification
                fetch('verify_qr.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: qrData.id,
                        qr_code_data: qrData.qr_code_data
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.valid && data.user) {
                        // Display user info
                        displayScanResult(data.user);
                    } else {
                        alert("Invalid QR Code! User not found.");
                        document.getElementById('start-scanner').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error verifying QR code:', error);
                    alert("Error verifying QR code. Please try again.");
                    document.getElementById('start-scanner').style.display = 'block';
                });
            } catch (error) {
                alert("Invalid QR Code format!");
                document.getElementById('start-scanner').style.display = 'block';
            }
        }
        
        // Display scan result
        function displayScanResult(user) {
            const scanResult = document.getElementById('scan-result');
            scanResult.style.display = 'block';
            
            // Fill in user details
            document.getElementById('result-name').textContent = user.full_name;
            document.getElementById('result-email').textContent = user.email;
            document.getElementById('result-phone').textContent = user.phone_number;
            document.getElementById('result-dob').textContent = formatDate(user.birth_date);
            document.getElementById('result-issued').textContent = formatDateTime(user.issued_at);
            document.getElementById('result-expires').textContent = formatDate(user.expires_at);
            document.getElementById('result-last-used').textContent = formatDateTime(user.last_used);
            
            // Set status
            const isActive = user.is_active && new Date(user.expires_at) > new Date();
            const statusElement = document.getElementById('result-status');
            statusElement.className = `status ${isActive ? 'status-active' : 'status-expired'}`;
            statusElement.textContent = isActive ? 'ACTIVE' : 'EXPIRED';
        }
        
        // Close the scan result
        function closeResult() {
            document.getElementById('scan-result').style.display = 'none';
            document.getElementById('start-scanner').style.display = 'block';
        }
        
        // Print user card
        function printCard() {
            const cardContent = document.getElementById('registration-result').cloneNode(true);
            
            // Remove buttons
            const buttons = cardContent.querySelectorAll('button');
            buttons.forEach(button => button.remove());
            
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
                        background-color: #2ecc71;
                        color: white;
                    }
                </style>
            `);
            
            printWindow.document.write('</head><body>');
            printWindow.document.write('<h2 style="text-align: center;">Official ID Card</h2>');
            printWindow.document.write(cardContent.innerHTML);
            printWindow.document.write('</body></html>');
            
            printWindow.document.close();
            printWindow.focus();
            
            // Print after a short delay to ensure content is loaded
            setTimeout(() => {
                printWindow.print();
            }, 500);
        }
    </script>
</body>
</html>