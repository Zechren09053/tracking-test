document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const startButton = document.getElementById('startButton');
    const stopButton = document.getElementById('stopButton');
    const switchCameraButton = document.getElementById('switchCameraButton');
    const userInfoSection = document.getElementById('userInfo');
    const ticketForm = document.getElementById('ticketForm');
    const createTicketForm = document.getElementById('createTicketForm');
    const loadingSection = document.getElementById('loading');
    const receiptSection = document.getElementById('receipt');
    const printReceiptButton = document.getElementById('printReceipt');
    const newTicketButton = document.getElementById('newTicket');
    const manualCodeInput = document.getElementById('manualCode');
    const submitManualCodeButton = document.getElementById('submitManualCode');
    const scannerStatus = document.getElementById('scannerStatus');
    const originSelect = document.getElementById('originSelect');
    const destinationSelect = document.getElementById('destinationSelect');
    
    // QR Scanner instance
    let html5QrCode = null;
    let currentCamera = 'environment'; // Start with back camera
    
    // Check for camera support
    Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length > 1) {
            switchCameraButton.style.display = 'inline-block';
        }
    }).catch(err => {
        console.warn("Error getting cameras", err);
    });
    
    // Ferry and Route data
    let ferries = [];
    let routes = [];
    let stations = [];
    
    // Load ferry and route data when page loads
    fetchFerryData();
    fetchRouteData();
    fetchStationData();
    
    // Set default valid until date (24 hours from now)
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('validUntilDate').value = formatDatetimeLocal(tomorrow);
    
    // Initialize QR Scanner
    startButton.addEventListener('click', function() {
        initializeScanner();
    });
    
    stopButton.addEventListener('click', function() {
        stopScanner();
    });
    
    switchCameraButton.addEventListener('click', function() {
        toggleCamera();
    });
    
    // Handle form submission
    createTicketForm.addEventListener('submit', function(e) {
        e.preventDefault();
        saveTicket();
    });
    
   // Print receipt - with enhanced styling
printReceiptButton.addEventListener('click', function() {
    // Create a new window for printing just the receipt
    const printWindow = window.open('', '_blank');
    
    // Get the receipt content
    const receiptSection = document.getElementById('receipt');
    
    // Create a deep clone of the receipt content to modify
    const receiptClone = receiptSection.cloneNode(true);
    
    // Remove any buttons or non-receipt elements from the clone
    const buttonsToRemove = receiptClone.querySelectorAll('button, .no-print, .print-button, .action-button');
    buttonsToRemove.forEach(button => button.remove());
    
    // Create a complete HTML document with enhanced styling
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Ferry Ticket Receipt</title>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
                
                body {
                    font-family: 'Roboto', Arial, sans-serif;
                    padding: 30px;
                    max-width: 800px;
                    margin: 0 auto;
                    color: #333;
                    background-color: #f9f9f9;
                }
                
                .receipt-container {
                    border: 1px solid #ddd;
                    padding: 25px;
                    border-radius: 8px;
                    background-color: #fff;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }
                
                .receipt-header {
                    text-align: center;
                    margin-bottom: 25px;
                    padding-bottom: 15px;
                    border-bottom: 2px solid #eaeaea;
                }
                
                .receipt-header h2 {
                    color: #1a73e8;
                    margin-bottom: 5px;
                    font-weight: 500;
                }
                
                .receipt-header p {
                    color: #666;
                    font-size: 0.9em;
                }
                
                .receipt-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 12px;
                    padding-bottom: 8px;
                    border-bottom: 1px dotted #eaeaea;
                }
                
                .receipt-label {
                    font-weight: 500;
                    color: #555;
                    width: 40%;
                }
                
                .receipt-value {
                    text-align: right;
                    width: 60%;
                }
                
                .receipt-total {
                    margin-top: 20px;
                    padding-top: 15px;
                    border-top: 2px solid #eaeaea;
                    font-weight: 700;
                    font-size: 1.1em;
                }
                
                .receipt-footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 0.85em;
                    color: #777;
                    padding-top: 20px;
                    border-top: 1px solid #eaeaea;
                }
                
                .receipt-barcode {
                    text-align: center;
                    margin: 20px 0;
                }
                
                .receipt-logo {
                    max-height: 60px;
                    margin-bottom: 15px;
                }
                
                .thank-you {
                    text-align: center;
                    font-weight: 500;
                    margin-top: 20px;
                    color: #1a73e8;
                }
                
                /* Hide any remaining buttons or controls */
                button, .print-button, .action-button, .no-print {
                    display: none !important;
                }
                
                @media print {
                    body {
                        width: 100%;
                        margin: 0;
                        padding: 15px;
                        background-color: white;
                    }
                    
                    .receipt-container {
                        box-shadow: none;
                        border: none;
                    }
                }
            </style>
        </head>
        <body>
            ${receiptClone.outerHTML}
        </body>
        </html>
    `);
    
    // Wait for content to load then print
    printWindow.document.close();
    printWindow.focus();
    
    // Print after a short delay to ensure content is fully loaded
    setTimeout(function() {
        printWindow.print();
        // Close the window after printing (optional)
        printWindow.close();
    }, 500); // Increased delay for better reliability
});
    // New ticket button
    newTicketButton.addEventListener('click', function() {
        resetForm();
    });
    
    // Manual code entry
    submitManualCodeButton.addEventListener('click', function() {
        const manualCode = manualCodeInput.value.trim();
        if (manualCode) {
            processQRCode(manualCode);
            manualCodeInput.value = '';
        } else {
            showStatusMessage('Please enter a QR code value', 'error');
        }
    });
    
    // Initialize QR Scanner function
    function initializeScanner() {
        // Clear any previous instances
        if (html5QrCode && html5QrCode.isScanning) {
            stopScanner();
            return;
        }
        
        // Create new instance if needed
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }
        
        showStatusMessage('Starting camera...', 'info');
        
        // Improved scanner configuration
        const config = {
            fps: 10,
            qrbox: {
                width: 240,
                height: 240
            },
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true
            },
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.DATA_MATRIX
            ]
        };
        
        html5QrCode.start(
            { facingMode: currentCamera },
            config,
            onScanSuccess,
            onScanFailure
        ).then(() => {
            // Scanner started successfully
            startButton.style.display = 'none';
            stopButton.style.display = 'inline-block';
            showStatusMessage('Scanner ready! Point at a QR code.', 'success');
        }).catch(err => {
            console.error("QR Scanner initialization failed", err);
            showStatusMessage('Camera access failed. Please check permissions.', 'error');
            
            // Reset scanner state
            html5QrCode = null;
            startButton.style.display = 'inline-block';
            stopButton.style.display = 'none';
        });
    }
    
    // Stop scanner
    function stopScanner() {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(() => {
                startButton.style.display = 'inline-block';
                stopButton.style.display = 'none';
                showStatusMessage('Scanner stopped', 'info');
            }).catch(err => {
                console.error("Error stopping scanner:", err);
            });
        }
    }
    
    // Toggle between front and back camera
    function toggleCamera() {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(() => {
                currentCamera = currentCamera === 'environment' ? 'user' : 'environment';
                showStatusMessage(`Switching to ${currentCamera === 'environment' ? 'back' : 'front'} camera...`, 'info');
                initializeScanner();
            }).catch(err => {
                console.error("Error stopping scanner:", err);
            });
        } else {
            currentCamera = currentCamera === 'environment' ? 'user' : 'environment';
            initializeScanner();
        }
    }
    
    // Display status message
    function showStatusMessage(message, type) {
        scannerStatus.textContent = message;
        scannerStatus.className = 'status-message';
        scannerStatus.classList.add(`status-${type}`);
        scannerStatus.style.display = 'block';
        
        // Hide message after 5 seconds if it's just informational
        if (type === 'info' || type === 'success') {
            setTimeout(() => {
                scannerStatus.style.display = 'none';
            }, 5000);
        }
    }
    
    // QR Code success callback
    function onScanSuccess(qrCodeMessage) {
        // Play success sound
        const successSound = new Audio('data:audio/mp3;base64,SUQzBAAAAAAAI1RTU0UAAAAPAAADTGF2ZjU4Ljc2LjEwMAAAAAAAAAAAAAAA/+M4wAAAAAAAAAAAAEluZm8AAAAPAAAAAwAAAbAAuLi4uLi4uLi4uLi4uLi4uLjV1dXV1dXV1dXV1dXV1dXV1fLy8vLy8vLy8vLy8vLy8vLy////////////////////////////////////////AAAAAExhdmM1OC4xMwAAAAAAAAAAAAAAACQDgAAAAAAAAAGwsqjgzAAAAAAAAAAAAAAAAAAAAP/jOMQAA+gBAASgAAAAQQjKCC3EAQBAEAwMDFYYNCw0NTi6pKS9ywsLDX//////9xcXFzMzMz09PUhIT2tra4SEhI+QkJ+fn6Ojo7Ozs8zMzMzMzMzMzMzMzAAAAAAAAAAAAAD/4zjOABOkgQAAhAAAAEGIihgxxAEAQBAMDFYYNCw0NTi6pKS9ywsDa4sLC31//////9xcXFzMzMz09PUhIT2tra4SEhI+QkJ+fn6Ojo7Ozs8zMzMzMzMzMzMzMzAAAAAAAAAAAAD/4zjMAAr0AQABgAAAAEFQyogtxAEAQBAMDFYYNCw0NTi6urq3jgItkywxLDQvjgItkywxLDQv');
        successSound.play().catch(err => {
            // Ignore error - some browsers block autoplay
        });
        
        // Stop scanning once we get a result
        stopScanner();
        
        showStatusMessage('QR code detected!', 'success');
        
        // Process the QR code data
        processQRCode(qrCodeMessage);
    }
    
    // QR Code failure callback
    function onScanFailure(error) {
        // We don't need to show errors for each frame
        // Only show serious errors
        if (error && error.toString().includes('NotAllowedError')) {
            showStatusMessage('Camera permission denied. Please allow camera access.', 'error');
            stopScanner();
        }
    }
    
    // Process QR Code data
    function processQRCode(qrData) {
        // Show loading
        loadingSection.style.display = 'block';
        showStatusMessage('Processing QR code...', 'info');
        
        // Fetch user data based on QR code
        fetchUserData(qrData)
            .then(userData => {
                if (userData) {
                    displayUserInfo(userData);
                    document.getElementById('userId').value = userData.id;
                    
                    // Show ticket form
                    ticketForm.style.display = 'block';
                    
                    // Hide loading
                    loadingSection.style.display = 'none';
                    showStatusMessage('User found!', 'success');
                }
            })
            .catch(error => {
                console.error("Error processing QR code:", error);
                showStatusMessage('Error processing QR code: ' + error.message, 'error');
                loadingSection.style.display = 'none';
            });
    }
    
    // Fetch user data using QR code
    function fetchUserData(qrData) {
        return new Promise((resolve, reject) => {
            try {
                fetch('get_user_by_qr.php?action=get_user&qr_code=' + encodeURIComponent(qrData))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Server error: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            resolve(data.user);
                        } else {
                            showStatusMessage(data.message || "Error retrieving user data", 'error');
                            reject(new Error(data.message || "Error retrieving user data"));
                        }
                    })
                    .catch(error => {
                        console.error("API call failed:", error);
                        reject(error);
                    });
            } catch (error) {
                reject(error);
            }
        });
    }
    
    // Display user information
    function displayUserInfo(userData) {
        document.getElementById('fullName').textContent = userData.full_name;
        document.getElementById('userEmail').textContent = userData.email;
        document.getElementById('userPhone').textContent = userData.phone_number;
        document.getElementById('validUntil').textContent = formatDate(new Date(userData.expires_at));
        
        userInfoSection.style.display = 'block';
    }
    
    // Save ticket function - updated version to match API changes
    function saveTicket() {
        // Show loading
        ticketForm.style.display = 'none';
        loadingSection.style.display = 'block';
        
        // Get form data
        const formData = new FormData(createTicketForm);
        
        // Get passenger type data
        const passengerType = document.getElementById('passengerType').value;
        const baseAmount = document.getElementById('baseAmount').value;
        const discountRate = discountRates[passengerType] || 0;
        
        formData.append('passenger_type', passengerType);
        formData.append('base_amount', baseAmount);
        formData.append('discount_rate', discountRate);
        
        // Get the route information
        const routeSelect = document.getElementById('routeSelect');
        const selectedRouteId = parseInt(routeSelect.value);
        
        // Find the selected route object from our routes array
        const selectedRoute = routes.find(route => route.id == selectedRouteId);
        
        if (!selectedRoute) {
            showStatusMessage("Please select a valid route", 'error');
            loadingSection.style.display = 'none';
            ticketForm.style.display = 'block';
            return;
        }
        
        // Get origin and destination station IDs
        const originId = parseInt(originSelect.value);
        const destinationId = parseInt(destinationSelect.value);
        
        if (!originId || !destinationId) {
            showStatusMessage("Please select origin and destination stations", 'error');
            loadingSection.style.display = 'none';
            ticketForm.style.display = 'block';
            return;
        }
        
        console.log("Selected route:", selectedRoute);
        
        // Add route information to form data
        formData.append('route_id', selectedRouteId);
        formData.append('origin_id', originId);
        formData.append('destination_id', destinationId);
        
        console.log("Form data prepared, sending to server...");
        
        // Send ticket data to server  
        fetch('get_user_by_qr.php?action=save_ticket', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log("Server response received");
            if (!response.ok) {
                throw new Error('Server error: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log("Response data:", data);
            
            if (data.success) {
                const ticketDetails = data.ticket_details;
                
                // Update receipt information
                document.getElementById('receiptTicketId').textContent = data.ticket_id;
                document.getElementById('receiptName').textContent = ticketDetails.full_name;
                
                // Add passenger type to receipt if the element exists
                if (document.getElementById('receiptPassengerType')) {
                    document.getElementById('receiptPassengerType').textContent = passengerType;
                }
                
                document.getElementById('receiptFerry').textContent = ticketDetails.ferry_name + ' (' + ticketDetails.ferry_code + ')';
                document.getElementById('receiptRoute').textContent = ticketDetails.route_name;
                
                // Add origin and destination stations if they exist in the receipt
                if (document.getElementById('receiptOrigin') && ticketDetails.origin_name) {
                    document.getElementById('receiptOrigin').textContent = ticketDetails.origin_name;
                }
                
                if (document.getElementById('receiptDestination') && ticketDetails.destination_name) {
                    document.getElementById('receiptDestination').textContent = ticketDetails.destination_name;
                }
                
                document.getElementById('receiptTicketType').textContent = ticketDetails.ticket_type;
                document.getElementById('receiptAmount').textContent = 'PHP ' + parseFloat(ticketDetails.amount).toFixed(2);
                document.getElementById('receiptDate').textContent = formatDate(new Date(ticketDetails.purchase_date));
                document.getElementById('receiptValidUntil').textContent = formatDate(new Date(ticketDetails.valid_until));
                
                // Show discount information if a discount was applied and elements exist
                if (document.getElementById('receiptDiscountRow') && 
                    document.getElementById('receiptDiscount') && 
                    discountRate > 0) {
                    document.getElementById('receiptDiscount').textContent = `${(discountRate * 100).toFixed(0)}% off`;
                    document.getElementById('receiptDiscountRow').style.display = 'flex';
                } else if (document.getElementById('receiptDiscountRow')) {
                    document.getElementById('receiptDiscountRow').style.display = 'none';
                }
                
                // Hide loading and show receipt
                loadingSection.style.display = 'none';
                receiptSection.style.display = 'block';
                showStatusMessage('Ticket issued successfully!', 'success');
            } else {
                // Show error
                showStatusMessage(data.message || "Error saving ticket", 'error');
                loadingSection.style.display = 'none';
                ticketForm.style.display = 'block';
            }
        })
        .catch(error => {
            console.error("Error saving ticket:", error);
            showStatusMessage("Error saving ticket. Please try again.", 'error');
            loadingSection.style.display = 'none';
            ticketForm.style.display = 'block';
        });
    }
    
    // Reset form for new ticket
    function resetForm() {
        // Reset form fields
        createTicketForm.reset();
        
        // Set default valid until date
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('validUntilDate').value = formatDatetimeLocal(tomorrow);
        
        // Hide receipt and show starting sections
        receiptSection.style.display = 'none';
        userInfoSection.style.display = 'none';
        ticketForm.style.display = 'none';
        
        // Show start scanner button
        startButton.style.display = 'inline-block';
        stopButton.style.display = 'none';
        
        // Clear manual code input
        manualCodeInput.value = '';
        
        // Clear status message
        scannerStatus.style.display = 'none';
    }
    
    // Fetch ferry data
    function fetchFerryData() {
        // Fetch ferries from API
        fetch('get_user_by_qr.php?action=get_ferries')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    ferries = data.ferries;
                    
                    // Populate ferry select
                    const ferrySelect = document.getElementById('ferrySelect');
                    ferrySelect.innerHTML = '<option value="">-- Select Ferry --</option>';
                    
                    ferries.forEach(ferry => {
                        const option = document.createElement('option');
                        option.value = ferry.id;
                        // Use ferry_code instead of code for better compatibility with API
                        const ferryName = ferry.name || 'Unknown';
                        const ferryCode = ferry.ferry_code || '';
                        option.textContent = ferryCode ? `${ferryName} (${ferryCode})` : ferryName;
                        ferrySelect.appendChild(option);
                    });
                } else {
                    console.error("Error fetching ferries:", data.message);
                    showStatusMessage("Error loading ferry data", 'error');
                }
            })
            .catch(error => {
                console.error("Error fetching ferries:", error);
                showStatusMessage("Error loading ferry data", 'error');
            });
    }
    
    // Fetch station data
    function fetchStationData() {
        // Fetch stations from API
        fetch('get_user_by_qr.php?action=get_stations')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    stations = data.stations;
                    
                    // Update both origin and destination dropdowns
                    populateStationDropdowns();
                } else {
                    console.error("Error fetching stations:", data.message);
                    showStatusMessage("Error loading station data", 'error');
                }
            })
            .catch(error => {
                console.error("Error fetching stations:", error);
                showStatusMessage("Error loading station data", 'error');
            });
    }
    
    // Populate station dropdowns (origin and destination)
    function populateStationDropdowns() {
        // Clear existing options first
        originSelect.innerHTML = '<option value="">-- Select Origin --</option>';
        destinationSelect.innerHTML = '<option value="">-- Select Destination --</option>';
        
        // Add station options to both dropdowns
        stations.forEach(station => {
            // For origin dropdown
            const originOption = document.createElement('option');
            originOption.value = station.id;
            originOption.textContent = station.station_name;
            originSelect.appendChild(originOption);
            
            // For destination dropdown
            const destOption = document.createElement('option');
            destOption.value = station.id;
            destOption.textContent = station.station_name;
            destinationSelect.appendChild(destOption);
        });
    }
    
    // Route select change handler
    document.getElementById('routeSelect').addEventListener('change', function() {
        const routeId = parseInt(this.value);
        const selectedRoute = routes.find(route => route.id == routeId);
        
        if (selectedRoute) {
            // If the route has origin_id and destination_id, auto-select them in dropdowns
            if (selectedRoute.origin_id && selectedRoute.destination_id) {
                originSelect.value = selectedRoute.origin_id;
                destinationSelect.value = selectedRoute.destination_id;
            }
            
            // Update prices based on the selected route
            updatePriceBasedOnRoute(selectedRoute);
        }
        
        // Trigger the ticket type change event to recalculate prices
        const event = new Event('change');
        document.getElementById('ticketType').dispatchEvent(event);
    });
    
    // Update price based on selected route
    function updatePriceBasedOnRoute(route) {
        const ticketType = document.getElementById('ticketType').value;
        
        // Set default prices based on ticket type
        let defaultPrice = 0;
        
        switch(ticketType) {
            case 'One-way':
                defaultPrice = 5.00;
                break;
            case 'Round-trip':
                defaultPrice = 30.00;
                break;
            case 'Multi-pass':
                defaultPrice = 30.00;
                break;
            case 'Special':
                defaultPrice = 75.00;
                break;
        }
        
        // Apply route price multiplier if available
        if (route && route.price_multiplier) {
            defaultPrice *= parseFloat(route.price_multiplier);
        }
        
        // Update base amount field
        document.getElementById('baseAmount').value = defaultPrice.toFixed(2);
        
        // Calculate the discounted price
        calculateDiscountedPrice();
    }
    
    // Fetch route data with improved handling for the API changes
    function fetchRouteData() {
        // Fetch routes from API
        fetch('get_user_by_qr.php?action=get_routes')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log("Route data received:", data);
                
                if (data.success) {
                    routes = data.routes;
                    
                    // Populate route select
                    const routeSelect = document.getElementById('routeSelect');
                    if (!routeSelect) {
                        console.error("Element with ID 'routeSelect' not found");
                        return;
                    }
                    
                    routeSelect.innerHTML = '<option value="">-- Select Route --</option>';
                    
                    // Check if routes is an array
                    if (!Array.isArray(routes)) {
                        console.error("Routes data is not an array:", routes);
                        showStatusMessage("Error: Invalid route data format", 'error');
                        return;
                    }
                    
                    routes.forEach(route => {
                        const option = document.createElement('option');
                        option.value = route.id;
                        
                        // Format the route display based on the API structure
                        if (route.name && route.from && route.to) {
                            // New format from API
                            option.textContent = `${route.name} (${route.from} - ${route.to})`;
                        } else if (route.route_name && route.origin_name && route.destination_name) {
                            // Alternative new format from API
                            option.textContent = `${route.route_name} (${route.origin_name} - ${route.destination_name})`;
                        } else if (route.route_name) {
                            // Fallback to just showing route name
                            option.textContent = route.route_name;
                        } else {
                            option.textContent = `Route ${route.id}`;
                        }
                        
                        routeSelect.appendChild(option);
                    });
                    
                    console.log("Route select populated with options:", routeSelect.options.length - 1);
                } else {
                    console.error("Error fetching routes:", data.message);
                    showStatusMessage("Error loading route data: " + (data.message || "Unknown error"), 'error');
                }
            })
            .catch(error => {
                console.error("Error fetching routes:", error);
                showStatusMessage("Error loading route data: " + error.message, 'error');
            });
    }
    
    // Format date for display
    function formatDate(date) {
        if (!(date instanceof Date) || isNaN(date)) {
            return 'Invalid Date';
        }
        
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        
        return date.toLocaleDateString('en-US', options);
    }
    
    // Format date for datetime-local input
    function formatDatetimeLocal(date) {
        if (!(date instanceof Date) || isNaN(date)) {
            return '';
        }
        
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }
    
    // Set up ticket type change handler to update price
    document.getElementById('ticketType').addEventListener('change', function() {
        const ticketType = this.value;
        const routeId = document.getElementById('routeSelect').value;
        
        // Set default prices based on ticket type
        let defaultPrice = 0;
        
        switch(ticketType) {
            case 'One-way':
                defaultPrice = 50.00;
                break;
            case 'Round-trip':
                defaultPrice = 90.00;
                break;
            case 'Multi-pass':
                defaultPrice = 200.00;
                break;
            case 'Special':
                defaultPrice = 75.00;
                break;
        }
        
        // If route is selected, adjust price based on route
        if (routeId) {
            const selectedRoute = routes.find(route => route.id == routeId);
            if (selectedRoute && selectedRoute.price_multiplier) {
                defaultPrice *= parseFloat(selectedRoute.price_multiplier);
            }
        }
        
        // Update base amount field
        document.getElementById('baseAmount').value = defaultPrice.toFixed(2);
        
        // Calculate the discounted price
        calculateDiscountedPrice();
    });
    
    // Show welcome message
    showStatusMessage('Welcome! Press "Start Scanner" to begin scanning QR codes.', 'info');
});

// Define discount rates for different passenger types
const discountRates = {
    'Regular': 0,
    'Student': 0.20,  // 20% discount
    'Senior': 0.20,   // 20% discount
    'PWD': 0.20,      // 20% discount for Persons with Disability
    'Government': 0.10, // 10% discount
    'Child': 1.00     // 100% discount (free)
};

// Add passenger type change handler
document.getElementById('passengerType').addEventListener('change', function() {
    calculateDiscountedPrice();
});

// Add event listener for amount changes
document.getElementById('amount').addEventListener('input', function() {
    // Store the direct input value as the base amount
    document.getElementById('baseAmount').value = this.value;
    calculateDiscountedPrice();
});

// Add event listener for base amount changes
document.getElementById('baseAmount').addEventListener('input', function() {
    calculateDiscountedPrice();
});

// Calculate discounted price based on passenger type and base amount
function calculateDiscountedPrice() {
    const passengerType = document.getElementById('passengerType').value;
    const baseAmount = parseFloat(document.getElementById('baseAmount').value) || 0;
    const discountRate = discountRates[passengerType] || 0;
    
    // Calculate the final amount
    const finalAmount = baseAmount * (1 - discountRate);
    
    // Update the final amount input
    document.getElementById('amount').value = finalAmount.toFixed(2);
    
    // Show discount info if a discount is applied
    const discountInfoDiv = document.getElementById('discountInfo');
    
    if (discountRate > 0) {
        document.getElementById('originalPrice').textContent = `₱${baseAmount.toFixed(2)}`;
        document.getElementById('finalPrice').textContent = `₱${finalAmount.toFixed(2)}`;
        document.getElementById('discountPercentage').textContent = `${(discountRate * 100).toFixed(0)}%`;
        discountInfoDiv.style.display = 'block';
    } else {
        discountInfoDiv.style.display = 'none';
    }
}