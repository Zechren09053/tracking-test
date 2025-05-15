<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ferry Ticket System - QR Scanner</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.4/html5-qrcode.min.js"></script>
    <link rel="stylesheet" href="ticket.css">
    <style>
        /* Additional styles for passenger type discounts */
        .discount-info {
            padding: 8px;
            background-color: #f0f8ff;
            border-radius: 4px;
            margin-top: 10px;
            border-left: 3px solid #0275d8;
        }
        
        .discount-amount {
            font-weight: bold;
            color: #28a745;
        }
        
        .original-price {
            text-decoration: line-through;
            color: #6c757d;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <header>
        <h1><i class="fas fa-ship"></i> Ferry Ticket System</h1>
    </header>

    <div class="container">
        <div class="row">
            <div class="col">
                <div class="card">
                    <h2><i class="fas fa-qrcode"></i> Scan QR Code</h2>
                    <div class="scanner-container">
                        <div id="reader"></div>
                    </div>
                    <div id="scannerStatus" class="status-message"></div>
                    <div style="margin-top: 10px;">
                        <button id="startButton" class="btn"><i class="fas fa-play"></i> Start Scanner</button>
                        <button id="stopButton" class="btn btn-danger" style="display: none;"><i class="fas fa-stop"></i> Stop Scanner</button>
                        <button id="switchCameraButton" class="btn" style="display: none;"><i class="fas fa-sync"></i> Switch Camera</button>
                    </div>
                </div>
                
                <div id="userInfo" class="card user-info">
                    <h2><i class="fas fa-user"></i> User Information</h2>
                    <table>
                        <tr>
                            <th>Full Name:</th>
                            <td id="fullName"></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td id="userEmail"></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td id="userPhone"></td>
                        </tr>
                        <tr>
                            <th>ID Valid Until:</th>
                            <td id="validUntil"></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="col">
                <div id="ticketForm" class="card" style="display: none;">
                    <h2><i class="fas fa-ticket-alt"></i> Issue Ticket</h2>
                    <form id="createTicketForm">
                        <input type="hidden" id="userId" name="userId">
                        
                        <div class="form-group">
                            <label for="ferrySelect">Select Ferry:</label>
                            <select id="ferrySelect" name="ferryId" class="form-control" required>
                                <option value="">-- Select Ferry --</option>
                                <!-- This will be populated dynamically -->
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="ticketType">Ticket Type:</label>
                            <select id="ticketType" name="ticketType" class="form-control" required>
                                <option value="">-- Select Ticket Type --</option>
                                <option value="One-way">One-way</option>
                                <option value="Round-trip">Round-trip</option>
                                <option value="Multi-pass">Multi-pass</option>
                                <option value="Special">Special Event</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="passengerType">Passenger Type:</label>
                            <select id="passengerType" name="passengerType" class="form-control" required>
                                <option value="Regular">Regular</option>
                                <option value="Student">Student (20% off)</option>
                                <option value="Senior">Senior Citizen (20% off)</option>
                                <option value="PWD">PWD (20% off)</option>
                                <option value="Government">Government Employee (10% off)</option>
                                <option value="Child">Child under 7 (Free)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
    <label for="originSelect">Origin:</label>
    <select id="originSelect" name="origin" class="form-control" required>
        <option value="">-- Select Origin --</option>
        <!-- Will be populated dynamically -->
    </select>
</div>

<div class="form-group">
    <label for="destinationSelect">Destination:</label>
    <select id="destinationSelect" name="destination" class="form-control" required>
        <option value="">-- Select Destination --</option>
        <!-- Will be populated dynamically -->
    </select>
</div>

           
                        <div class="form-group">
                            <label for="baseAmount">Base Amount (PHP):</label>
                            <input type="number" id="baseAmount" name="baseAmount" class="form-control" step="0.01" min="0" required>
                        </div>
                        
                        <div id="discountInfo" class="discount-info" style="display: none;">
                            <p>Original Price: <span class="original-price" id="originalPrice">₱0.00</span> 
                               Final Price: <span class="discount-amount" id="finalPrice">₱0.00</span>
                               (<span id="discountPercentage">0%</span> discount applied)</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="amount">Final Amount (PHP):</label>
                            <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" required readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="validUntilDate">Valid Until:</label>
                            <input type="datetime-local" id="validUntilDate" name="validUntil" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Ticket</button>
                    </form>
                </div>
                
                <div id="loading" class="card loading">
                    <div class="spinner"></div>
                    <p>Processing your request...</p>
                </div>
                
                <div id="receipt" class="card receipt">
                    <div class="receipt-header">
                        <h2>Ferry Ticket Receipt</h2>
                        <p>PASIG RIVER FERRY SERVICE</p>
                    </div>
                    
                    <div class="receipt-body">
                        <div class="receipt-item">
                            <span>Ticket ID:</span>
                            <span id="receiptTicketId"></span>
                        </div>
                        <div class="receipt-item">
                            <span>Passenger:</span>
                            <span id="receiptName"></span>
                        </div>
                        <div class="receipt-item">
                            <span>Passenger Type:</span>
                            <span id="receiptPassengerType"></span>
                        </div>
                        <div class="receipt-item">
                            <span>Ferry:</span>
                            <span id="receiptFerry"></span>
                        </div>
                        <div class="receipt-item">
                            <span>Route:</span>
                            <span id="receiptRoute"></span>
                        </div>
                        <div class="receipt-item">
                            <span>Ticket Type:</span>
                            <span id="receiptTicketType"></span>
                        </div>
                        <div class="receipt-item" id="receiptDiscountRow" style="display: none;">
                            <span>Discount Applied:</span>
                            <span id="receiptDiscount"></span>
                        </div>
                        <div class="receipt-item">
                            <span>Amount:</span>
                            <span id="receiptAmount"></span>
                        </div>
                        <div class="receipt-item">
                            <span>Purchase Date:</span>
                            <span id="receiptDate"></span>
                        </div>
                        <div class="receipt-item">
                            <span>Valid Until:</span>
                            <span id="receiptValidUntil"></span>
                        </div>
                    </div>
                    
                    <div class="receipt-footer">
                        <p>Thank you for choosing Pasig River Ferry Service!</p>
                    </div>
                    
                    <button id="printReceipt" class="btn"><i class="fas fa-print"></i> Print Receipt</button>
                    <button id="newTicket" class="btn btn-success"><i class="fas fa-plus"></i> New Ticket</button>
                </div>
                
                <!-- Manual code entry as fallback -->
                <div class="card">
                    <h2><i class="fas fa-keyboard"></i> Manual Code Entry</h2>
                    <div class="form-group">
                        <label for="manualCode">QR Code Value:</label>
                        <input type="text" id="manualCode" class="form-control" placeholder="Enter QR code value manually">
                    </div>
                    <button id="submitManualCode" class="btn btn-success"><i class="fas fa-check"></i> Submit</button>
                </div>
            </div>
        </div>
    </div>

    <script src="tickets.js"></script>
</body>
</html>