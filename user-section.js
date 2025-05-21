// Global variables for pagination
let currentPage = 1;
let totalPages = 1;
let pageSize = 10;
let currentUsers = [];
let currentSearchTerm = '';
let currentStatusFilter = 'all';
let selectedUserId = null;

// Load users from database with pagination, search and filtering
function loadUsers() {
    const searchTerm = currentSearchTerm;
    const statusFilter = currentStatusFilter;
    
    // Show loading indicator
    document.getElementById('users-table-body').innerHTML = '<tr><td colspan="7" class="table-loading">Loading users...</td></tr>';
    
    // Fetch users from the server
    $.ajax({
        url: 'api/get_users.php',
        type: 'GET',
        data: {
            page: currentPage,
            limit: pageSize,
            search: searchTerm,
            status: statusFilter
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                displayUsers(response.data);
                currentUsers = response.data.users;
                updatePagination(response.data.total, response.data.page, response.data.pages);
            } else {
                document.getElementById('users-table-body').innerHTML = `<tr><td colspan="7" class="error-message">${response.message}</td></tr>`;
            }
        },
        error: function() {
            document.getElementById('users-table-body').innerHTML = '<tr><td colspan="7" class="error-message">Failed to load users. Please try again.</td></tr>';
        }
    });
}

// Display users in the table
function displayUsers(data) {
    const tableBody = document.getElementById('users-table-body');
    tableBody.innerHTML = '';
    
    if (data.users.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="7" class="no-results">No users found</td></tr>';
        return;
    }
    
    data.users.forEach(user => {
        // Format dates
        const issuedDate = new Date(user.issued_at).toLocaleDateString();
        const expiryDate = new Date(user.expires_at).toLocaleDateString();
        
        // Check if expired
        const now = new Date();
        const expiry = new Date(user.expires_at);
        const isExpired = expiry < now || user.is_active == 0;
        
        // Create row
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.full_name}</td>
            <td>${user.email}</td>
            <td>${user.phone_number}</td>
            <td>${issuedDate}</td>
            <td>${expiryDate}</td>
            <td><span class="status-badge ${isExpired ? 'expired' : 'active'}">${isExpired ? 'Expired' : 'Active'}</span></td>
            <td class="actions">
                <button class="view-btn" onclick="viewUser(${user.id})"><i class="fas fa-eye"></i></button>
                <button class="edit-btn" onclick="openEditModal(${user.id})"><i class="fas fa-edit"></i></button>
                <button class="delete-btn" onclick="confirmDelete(${user.id}, '${user.full_name}')"><i class="fas fa-trash-alt"></i></button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Update pagination controls
function updatePagination(total, page, pages) {
    currentPage = page;
    totalPages = pages;
    
    document.getElementById('page-info').textContent = `Page ${page} of ${pages}`;
    document.getElementById('prev-page').disabled = page <= 1;
    document.getElementById('next-page').disabled = page >= pages;
}

// Go to next page
function nextPage() {
    if (currentPage < totalPages) {
        currentPage++;
        loadUsers();
    }
}

// Go to previous page
function prevPage() {
    if (currentPage > 1) {
        currentPage--;
        loadUsers();
    }
}

// Search users
function searchUsers() {
    const searchInput = document.getElementById('search-input');
    currentSearchTerm = searchInput.value.trim();
    currentPage = 1; // Reset to first page
    loadUsers();
}

// Filter users by status
function filterUsers() {
    const statusFilter = document.getElementById('status-filter');
    currentStatusFilter = statusFilter.value;
    currentPage = 1; // Reset to first page
    loadUsers();
}

// Refresh users list
function refreshUsers() {
    loadUsers();
}

// Switch between tabs
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
    
    // Show the selected tab content and set active class
    document.getElementById(tabName).classList.add('active');
    
    // Find and activate the clicked tab
    const tabButtons = document.getElementsByClassName('tab');
    for (let i = 0; i < tabButtons.length; i++) {
        if (tabButtons[i].textContent.toLowerCase().includes(tabName)) {
            tabButtons[i].classList.add('active');
        }
    }
}

// View user details
function viewUser(userId) {
    selectedUserId = userId;
    
    // Find user in current users array
    const user = currentUsers.find(u => u.id == userId);
    if (!user) return;
    
    // Populate modal with user data
    document.getElementById('modal-user-name').textContent = user.full_name;
    document.getElementById('modal-user-email').textContent = user.email;
    document.getElementById('modal-user-phone').textContent = user.phone_number;
    document.getElementById('modal-user-dob').textContent = new Date(user.birth_date).toLocaleDateString();
    document.getElementById('modal-user-issued').textContent = new Date(user.issued_at).toLocaleDateString();
    document.getElementById('modal-user-expires').textContent = new Date(user.expires_at).toLocaleDateString();
    document.getElementById('modal-user-last-used').textContent = user.last_used ? new Date(user.last_used).toLocaleDateString() : 'Never';
    
    // Set status
    const now = new Date();
    const expiry = new Date(user.expires_at);
    const isExpired = expiry < now || user.is_active == 0;
    const statusElem = document.getElementById('modal-user-status');
    statusElem.textContent = isExpired ? 'Expired' : 'Active';
    statusElem.className = 'status ' + (isExpired ? 'expired' : 'active');
    
    // Set toggle status button text
    const toggleBtn = document.getElementById('toggle-status-btn');
    const toggleText = document.getElementById('toggle-status-text');
    if (user.is_active == 1) {
        toggleText.textContent = 'Deactivate';
        toggleBtn.classList.remove('activate-btn');
        toggleBtn.classList.add('deactivate-btn');
    } else {
        toggleText.textContent = 'Activate';
        toggleBtn.classList.remove('deactivate-btn');
        toggleBtn.classList.add('activate-btn');
    }
    
    // Add toggle status event
    toggleBtn.onclick = function() {
        toggleUserStatus(userId, user.is_active == 1 ? 0 : 1);
    };
    
    // Set profile image
    const userImage = document.getElementById('modal-user-image');
    userImage.src = user.profile_image ? user.profile_image : 'uploads/default.png';
    
    // Generate QR code
    const qrcodeContainer = document.getElementById('user-qrcode');
    qrcodeContainer.innerHTML = '';
    new QRCode(qrcodeContainer, {
        text: user.qr_code_data,
        width: 128,
        height: 128
    });
    
    // Set delete button action
    document.getElementById('delete-user-btn').onclick = function() {
        confirmDelete(userId, user.full_name);
    };
    
    // Show modal
    document.getElementById('user-modal').style.display = 'block';
    
    // Add close modal events
    const closeButtons = document.getElementsByClassName('close');
    for (let i = 0; i < closeButtons.length; i++) {
        closeButtons[i].onclick = function() {
            document.getElementById('user-modal').style.display = 'none';
        };
    }
}

// Open edit user modal
function openEditModal(userId) {
    // Hide user details modal if open
    document.getElementById('user-modal').style.display = 'none';
    
    // Find user in current users array
    const user = currentUsers.find(u => u.id == userId);
    if (!user) return;
    
    // Set hidden user ID
    document.getElementById('edit-user-id').value = userId;
    
    // Fill form with user data
    document.getElementById('edit-full-name').value = user.full_name;
    document.getElementById('edit-email').value = user.email;
    document.getElementById('edit-phone').value = user.phone_number;
    document.getElementById('edit-birth-date').value = user.birth_date.split(' ')[0]; // Format date for input
    document.getElementById('edit-expires').value = user.expires_at.split(' ')[0]; // Format date for input
    document.getElementById('edit-status').value = user.is_active;
    
    // Clear password fields
    document.getElementById('edit-password').value = '';
    document.getElementById('edit-confirm-password').value = '';
    
    // Show profile image if exists
    const imagePreview = document.getElementById('edit-image-preview');
    if (user.profile_image) {
        imagePreview.innerHTML = `<img src="${user.profile_image}" alt="Profile Image">`;
    } else {
        imagePreview.innerHTML = `<img src="uploads/default.png" alt="Default Profile">`;
    }
    
    // Add image preview event
    document.getElementById('edit-profile-image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                imagePreview.innerHTML = `<img src="${event.target.result}" alt="Profile Preview">`;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Show edit modal
    document.getElementById('edit-modal').style.display = 'block';
    
    // Add close modal events
    const closeButtons = document.getElementsByClassName('close');
    for (let i = 0; i < closeButtons.length; i++) {
        closeButtons[i].onclick = function() {
            document.getElementById('edit-modal').style.display = 'none';
        };
    }
}

// Update user
function updateUser() {
    const userId = document.getElementById('edit-user-id').value;
    const fullName = document.getElementById('edit-full-name').value.trim();
    const email = document.getElementById('edit-email').value.trim();
    const phone = document.getElementById('edit-phone').value.trim();
    const birthDate = document.getElementById('edit-birth-date').value;
    const expiryDate = document.getElementById('edit-expires').value;
    const status = document.getElementById('edit-status').value;
    const password = document.getElementById('edit-password').value;
    const confirmPassword = document.getElementById('edit-confirm-password').value;
    
    // Basic validation
    if (!fullName || !email || !phone || !birthDate || !expiryDate) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Password validation if provided
    if (password) {
        if (password.length < 6) {
            document.getElementById('edit-password-error').textContent = 'Password must be at least 6 characters';
            return;
        }
        if (password !== confirmPassword) {
            document.getElementById('edit-password-error').textContent = 'Passwords do not match';
            return;
        }
    }
    
    // Clear error messages
    document.getElementById('edit-password-error').textContent = '';
    document.getElementById('edit-email-error').textContent = '';
    
    // Create form data for file upload
    const formData = new FormData();
    formData.append('id', userId);
    formData.append('full_name', fullName);
    formData.append('email', email);
    formData.append('phone_number', phone);
    formData.append('birth_date', birthDate);
    formData.append('expires_at', expiryDate);
    formData.append('is_active', status);
    
    if (password) {
        formData.append('password', password);
    }
    
    // Add profile image if selected
    const profileImage = document.getElementById('edit-profile-image').files[0];
    if (profileImage) {
        formData.append('profile_image', profileImage);
    }
    
    // Send AJAX request
    $.ajax({
        url: 'api/update_user.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.status === 'success') {
                    alert('User updated successfully');
                    document.getElementById('edit-modal').style.display = 'none';
                    loadUsers(); // Reload users
                } else {
                    if (result.field === 'email') {
                        document.getElementById('edit-email-error').textContent = result.message;
                    } else {
                        alert(result.message);
                    }
                }
            } catch (e) {
                alert('An error occurred. Please try again.');
            }
        },
        error: function() {
            alert('Failed to update user. Please try again.');
        }
    });
}

// Toggle user active status
function toggleUserStatus(userId, newStatus) {
    $.ajax({
        url: 'api/toggle_user_status.php',
        type: 'POST',
        data: {
            id: userId,
            is_active: newStatus
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                // Close modal
                document.getElementById('user-modal').style.display = 'none';
                
                // Reload users
                loadUsers();
            } else {
                alert(response.message || 'Failed to update user status');
            }
        },
        error: function() {
            alert('Failed to update user status. Please try again.');
        }
    });
}

// Confirm user deletion
function confirmDelete(userId, userName) {
    // Hide previous modals
    document.getElementById('user-modal').style.display = 'none';
    document.getElementById('edit-modal').style.display = 'none';
    
    // Set user name in confirmation
    document.getElementById('delete-user-name').textContent = userName;
    
    // Set up confirm button
    document.getElementById('confirm-delete-btn').onclick = function() {
        deleteUser(userId);
    };
    
    // Set up cancel button
    document.getElementById('cancel-delete-btn').onclick = function() {
        document.getElementById('delete-modal').style.display = 'none';
    };
    
    // Show deletion modal
    document.getElementById('delete-modal').style.display = 'block';
    
    // Add close modal events
    const closeButtons = document.getElementsByClassName('close');
    for (let i = 0; i < closeButtons.length; i++) {
        closeButtons[i].onclick = function() {
            document.getElementById('delete-modal').style.display = 'none';
        };
    }
}

// Delete user
function deleteUser(userId) {
    $.ajax({
        url: 'api/delete_user.php',
        type: 'POST',
        data: {
            id: userId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                // Close modal
                document.getElementById('delete-modal').style.display = 'none';
                
                // Reload users
                loadUsers();
            } else {
                alert(response.message || 'Failed to delete user');
            }
        },
        error: function() {
            alert('Failed to delete user. Please try again.');
        }
    });
}

// Submit new user form
function submitForm() {
    const fullName = document.getElementById('full_name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phoneNumber = document.getElementById('phone_number').value.trim();
    const birthDate = document.getElementById('birth_date').value;
    const expiryDate = document.getElementById('expires_at').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const isActive = document.getElementById('is_active').value;
    
    // Basic validation
    if (!fullName || !email || !phoneNumber || !birthDate || !expiryDate || !password) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Password validation
    if (password.length < 6) {
        document.getElementById('password-error').textContent = 'Password must be at least 6 characters';
        return;
    }
    
    if (password !== confirmPassword) {
        document.getElementById('password-error').textContent = 'Passwords do not match';
        return;
    }
    
    // Clear error messages
    document.getElementById('password-error').textContent = '';
    document.getElementById('email-error').textContent = '';
    
    // Create form data for file upload
    const formData = new FormData();
    formData.append('full_name', fullName);
    formData.append('email', email);
    formData.append('phone_number', phoneNumber);
    formData.append('birth_date', birthDate);
    formData.append('expires_at', expiryDate);
    formData.append('password', password);
    formData.append('is_active', isActive);
    
    // Add profile image if selected
    const profileImage = document.getElementById('profile_image').files[0];
    if (profileImage) {
        formData.append('profile_image', profileImage);
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submit-btn');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';
    
    // Send AJAX request
    $.ajax({
        url: 'api/add_user.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.status === 'success') {
                    alert('User added successfully');
                    resetForm();
                    // Switch to view tab and refresh list
                    openTab('view');
                    loadUsers();
                } else {
                    if (result.field === 'email') {
                        document.getElementById('email-error').textContent = result.message;
                    } else {
                        alert(result.message);
                    }
                }
            } catch (e) {
                alert('An error occurred. Please try again.');
            }
            
            // Reset button
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        },
        error: function() {
            alert('Failed to add user. Please try again.');
            
            // Reset button
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
}

// Reset form fields
function resetForm() {
    document.getElementById('user-form').reset();
    document.getElementById('password-error').textContent = '';
    document.getElementById('email-error').textContent = '';
    document.getElementById('image-preview').innerHTML = '<span>No image selected</span>';
}

// Print user ID card
function printUserCard() {
    const user = currentUsers.find(u => u.id == selectedUserId);
    if (!user) return;
    
    // Create new window for printing
    const printWindow = window.open('', '_blank');
    
    // Get QR code as SVG content
    const qrCodeSvg = document.getElementById('user-qrcode').innerHTML;
    
    // Create ID card HTML
    const cardHTML = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Ferry User ID Card</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                }
                .id-card {
                    width: 3.375in;
                    height: 2.125in;
                    margin: 0 auto;
                    border: 1px solid #000;
                    border-radius: 10px;
                    overflow: hidden;
                    page-break-inside: avoid;
                }
                .card-header {
                    background-color: #235A8C;
                    color: white;
                    padding: 10px;
                    text-align: center;
                    font-weight: bold;
                    font-size: 14px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .card-header img {
                    height: 20px;
                    margin-right: 10px;
                }
                .card-body {
                    padding: 10px;
                    display: flex;
                }
                .card-info {
                    flex: 1;
                }
                .card-qr {
                    width: 100px;
                    height: 100px;
                }
                .card-qr svg {
                    width: 100%;
                    height: 100%;
                }
                .card-photo {
                    width: 60px;
                    height: 60px;
                    border-radius: 50%;
                    object-fit: cover;
                    margin-bottom: 5px;
                }
                .card-name {
                    font-weight: bold;
                    font-size: 14px;
                    margin-bottom: 5px;
                }
                .card-details {
                    font-size: 10px;
                    margin-bottom: 3px;
                }
                .card-expiry {
                    font-size: 10px;
                    font-weight: bold;
                    color: #d32f2f;
                }
                .card-footer {
                    background-color: #f1f1f1;
                    text-align: center;
                    font-size: 8px;
                    padding: 5px;
                    border-top: 1px solid #ccc;
                }
                @media print {
                    @page {
                        size: 3.5in 2.5in;
                        margin: 0;
                    }
                    body {
                        margin: 0;
                    }
                    .print-btn {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <div class="print-btn" style="text-align: center; margin: 20px;">
                <button onclick="window.print()">Print ID Card</button>
            </div>
            <div class="id-card">
                <div class="card-header">
                    <img src="PasigRiverFerryServiceLogo.png" alt="Logo">
                    PASIG RIVER FERRY SERVICE
                </div>
                <div class="card-body">
                    <div class="card-info">
                        <img src="${user.profile_image || 'uploads/default.png'}" class="card-photo" alt="User Photo">
                        <div class="card-name">${user.full_name}</div>
                        <div class="card-details">ID: ${user.id}</div>
                        <div class="card-details">Phone: ${user.phone_number}</div>
                        <div class="card-expiry">Expires: ${new Date(user.expires_at).toLocaleDateString()}</div>
                    </div>
                    <div class="card-qr">${qrCodeSvg}</div>
                </div>
                <div class="card-footer">
                    This card is property of the Pasig River Ferry Service. If found, please return to the nearest ferry station.
                </div>
            </div>
        </body>
        </html>
    `;
    
    // Write to new window and trigger print
    printWindow.document.open();
    printWindow.document.write(cardHTML);
    printWindow.document.close();
    
    // Wait for resources to load then print
    printWindow.onload = function() {
        setTimeout(function() {
            printWindow.print();
        }, 500);
    };
}

// Add event listener for image preview on add user form
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('profile_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const imagePreview = document.getElementById('image-preview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                imagePreview.innerHTML = `<img src="${event.target.result}" alt="Profile Preview">`;
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.innerHTML = '<span>No image selected</span>';
        }
    });
    
    // Close modals when clicking outside
    window.onclick = function(event) {
        const modals = document.getElementsByClassName('modal');
        for (let i = 0; i < modals.length; i++) {
            if (event.target === modals[i]) {
                modals[i].style.display = 'none';
            }
        }
    };
    
    // Load users on page load
    loadUsers();
});
