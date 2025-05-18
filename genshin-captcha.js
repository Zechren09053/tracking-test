// Genshin Impact Style Captcha Implementation

// Captcha configuration
const captchaConfig = {
    targetMargin: 5,           // Acceptable margin of error
    maxAttempts: 3,            // Maximum allowed attempts before reset
    showTargetDebug: false,    // Set to true during development to see target position
    addToForms: true           // Automatically add to login and registration forms
};

// Global variables for captcha
let isDragging = false;
let currentCaptchaId = null;
let captchas = {};

// Initialize captcha when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add captcha to both forms if configured
    if (captchaConfig.addToForms) {
        addCaptchaToForms();
    }
});

// Add captcha to login and registration forms
function addCaptchaToForms() {
    // Add to login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        addCaptchaToForm(loginForm, 'login-captcha');
        
        // Intercept login form submission
        loginForm.addEventListener('submit', function(event) {
            if (!validateCaptchaBeforeSubmit('login-captcha')) {
                event.preventDefault();
                return false;
            }
        });
    }
    
    // Add to registration form
    const registrationForm = document.getElementById('registration-form');
    if (registrationForm) {
        addCaptchaToForm(registrationForm, 'register-captcha');
        
        // Intercept registration form submission
        registrationForm.addEventListener('submit', function(event) {
            if (!validateCaptchaBeforeSubmit('register-captcha')) {
                event.preventDefault();
                return false;
            }
        });
    }
}

// Add captcha to a specific form
function addCaptchaToForm(formElement, captchaId) {
    // Create captcha container
    const captchaContainer = document.createElement('div');
    captchaContainer.className = 'captcha-container';
    captchaContainer.id = captchaId + '-container';
    
    // Add captcha HTML structure
    captchaContainer.innerHTML = `
        <div class="form-group">
            <label>Security Verification</label>
            <div class="genshin-captcha">
                <!-- Puzzle container -->
                <div class="puzzle-container">
                    <div class="puzzle-image">
                        <div class="target-position"></div>
                        <div class="puzzle-piece"></div>
                    </div>
                </div>
                
                <!-- Slider track -->
                <div class="slider-track">
                    <div class="slider-progress"></div>
                    <div class="slider-handle">→</div>
                </div>
                
                <div class="captcha-message"></div>
                <button type="button" class="reset-button" style="display: none;">Reset Verification</button>
            </div>
        </div>
    `;
    
    // Insert before form submit button
    const submitButton = formElement.querySelector('button[type="submit"]');
    if (submitButton) {
        formElement.insertBefore(captchaContainer, submitButton);
    } else {
        formElement.appendChild(captchaContainer);
    }
    
    // Initialize this captcha
    initializeCaptcha(captchaId);
    
    // Add the required CSS for the captcha
    addCaptchaStyles();
}

// Initialize a specific captcha
function initializeCaptcha(captchaId) {
    const container = document.getElementById(captchaId + '-container');
    if (!container) return;
    
    // Create captcha state
    captchas[captchaId] = {
        id: captchaId,
        sliderPosition: 0,
        targetPosition: Math.floor(Math.random() * 150) + 100, // Random position between 100-250px
        isVerified: false,
        attempts: 0,
        elements: {
            container: container,
            puzzleContainer: container.querySelector('.puzzle-container'),
            puzzlePiece: container.querySelector('.puzzle-piece'),
            targetPosition: container.querySelector('.target-position'),
            sliderTrack: container.querySelector('.slider-track'),
            sliderProgress: container.querySelector('.slider-progress'),
            sliderHandle: container.querySelector('.slider-handle'),
            message: container.querySelector('.captcha-message'),
            resetButton: container.querySelector('.reset-button')
        }
    };
    
    // Show target position during development if enabled
    if (captchaConfig.showTargetDebug) {
        captchas[captchaId].elements.targetPosition.style.left = captchas[captchaId].targetPosition + 'px';
        captchas[captchaId].elements.targetPosition.style.opacity = '0.5';
    }
    
    // Set up event listeners for this captcha
    setupCaptchaEventListeners(captchaId);
}

// Set up event listeners for a specific captcha
function setupCaptchaEventListeners(captchaId) {
    const captcha = captchas[captchaId];
    if (!captcha) return;
    
    // Mouse events
    captcha.elements.sliderHandle.addEventListener('mousedown', (e) => {
        startDragging(captchaId, e);
    });
    
    captcha.elements.puzzleContainer.addEventListener('mousemove', (e) => {
        handleDrag(captchaId, e.clientX);
    });
    
    document.addEventListener('mouseup', () => {
        if (currentCaptchaId === captchaId) {
            stopDragging(captchaId);
        }
    });
    
    document.addEventListener('mouseleave', () => {
        if (currentCaptchaId === captchaId) {
            stopDragging(captchaId);
        }
    });
    
    // Touch events for mobile
    captcha.elements.sliderHandle.addEventListener('touchstart', (e) => {
        startDragging(captchaId, e.touches[0]);
    });
    
    captcha.elements.puzzleContainer.addEventListener('touchmove', (e) => {
        e.preventDefault(); // Prevent scrolling while dragging
        handleDrag(captchaId, e.touches[0].clientX);
    });
    
    document.addEventListener('touchend', () => {
        if (currentCaptchaId === captchaId) {
            stopDragging(captchaId);
        }
    });
    
    // Reset button
    captcha.elements.resetButton.addEventListener('click', () => {
        resetCaptcha(captchaId);
    });
}

// Start dragging the slider
function startDragging(captchaId, event) {
    const captcha = captchas[captchaId];
    if (!captcha || captcha.isVerified) return;
    
    isDragging = true;
    currentCaptchaId = captchaId;
    captcha.elements.message.textContent = '';
    captcha.elements.sliderHandle.classList.add('dragging');
    captcha.elements.puzzlePiece.classList.add('dragging');
}

// Handle drag movement
function handleDrag(captchaId, clientX) {
    const captcha = captchas[captchaId];
    if (!captcha || !isDragging || currentCaptchaId !== captchaId) return;
    
    const containerRect = captcha.elements.sliderTrack.getBoundingClientRect();
    const newPosition = Math.max(0, Math.min(clientX - containerRect.left - 15, containerRect.width - 30));
    
    // Update slider position
    captcha.sliderPosition = newPosition;
    captcha.elements.sliderHandle.style.left = newPosition + 'px';
    captcha.elements.sliderProgress.style.width = newPosition + 'px';
    
    // Update puzzle piece position based on slider position
    const puzzleContainerWidth = captcha.elements.puzzleContainer.offsetWidth - 30;
    const puzzlePosition = (newPosition / (containerRect.width - 30)) * puzzleContainerWidth;
    captcha.elements.puzzlePiece.style.left = puzzlePosition + 'px';
}

// Stop dragging and verify position
function stopDragging(captchaId) {
    const captcha = captchas[captchaId];
    if (!captcha || !isDragging || currentCaptchaId !== captchaId) return;
    
    isDragging = false;
    currentCaptchaId = null;
    captcha.elements.sliderHandle.classList.remove('dragging');
    captcha.elements.puzzlePiece.classList.remove('dragging');
    
    // Check if puzzle is solved
    const difference = Math.abs(captcha.sliderPosition - captcha.targetPosition);
    if (difference <= captchaConfig.targetMargin) {
        // Success
        captcha.isVerified = true;
        captcha.elements.message.textContent = 'Verification successful!';
        captcha.elements.message.style.color = '#10b981';
        captcha.elements.sliderHandle.classList.add('success');
        captcha.elements.puzzlePiece.classList.add('success');
        captcha.elements.resetButton.style.display = 'block';
    } else {
        // Failed attempt
        captcha.attempts++;
        captcha.elements.message.style.color = '#ef4444';
        
        if (captcha.attempts >= captchaConfig.maxAttempts) {
            captcha.elements.message.textContent = 'Too many attempts. Please try again.';
            captcha.elements.resetButton.style.display = 'block';
        } else {
            captcha.elements.message.textContent = 'Try again';
            // Reset slider position but keep attempt count
            captcha.sliderPosition = 0;
            captcha.elements.sliderHandle.style.left = '0px';
            captcha.elements.sliderProgress.style.width = '0px';
            captcha.elements.puzzlePiece.style.left = '0px';
        }
    }
}

// Reset captcha
function resetCaptcha(captchaId) {
    const captcha = captchas[captchaId];
    if (!captcha) return;
    
    // Reset state
    captcha.sliderPosition = 0;
    captcha.targetPosition = Math.floor(Math.random() * 150) + 100;
    captcha.isVerified = false;
    captcha.attempts = 0;
    
    // Reset UI
    captcha.elements.sliderHandle.style.left = '0px';
    captcha.elements.sliderProgress.style.width = '0px';
    captcha.elements.puzzlePiece.style.left = '0px';
    captcha.elements.message.textContent = '';
    captcha.elements.resetButton.style.display = 'none';
    captcha.elements.sliderHandle.classList.remove('success');
    captcha.elements.puzzlePiece.classList.remove('success');
    
    // Update target position indicator if debug mode is on
    if (captchaConfig.showTargetDebug) {
        captcha.elements.targetPosition.style.left = captcha.targetPosition + 'px';
    }
}

// Validate captcha before form submission
function validateCaptchaBeforeSubmit(captchaId) {
    const captcha = captchas[captchaId];
    if (!captcha) return true; // If no captcha, allow submission
    
    if (!captcha.isVerified) {
        captcha.elements.message.textContent = 'Please complete the verification';
        captcha.elements.message.style.color = '#ef4444';
        return false;
    }
    
    return true;
}

// Add required CSS for captcha
function addCaptchaStyles() {
    // Check if styles already added
    if (document.getElementById('genshin-captcha-styles')) return;
    
    const styleEl = document.createElement('style');
    styleEl.id = 'genshin-captcha-styles';
    styleEl.textContent = `
        .genshin-captcha {
            margin-bottom: 15px;
        }
        
        .puzzle-container {
            position: relative;
            height: 100px;
            background-color: #f3f4f6;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 10px;
            cursor: pointer;
        }
        
        .puzzle-image {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #dbeafe, #ede9fe);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .puzzle-image::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M50 25L37.5 37.5 25 25 12.5 37.5 0 25 0 0h100v25l-12.5 12.5-12.5-12.5-12.5 12.5-12.5-12.5z' fill='%23bfdbfe' fill-opacity='0.4'/%3E%3C/svg%3E");
            opacity: 0.5;
        }
        
        .target-position {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: red;
            opacity: 0;
        }
        
        .puzzle-piece {
            position: absolute;
            height: 60px;
            width: 30px;
            top: 50%;
            transform: translateY(-50%);
            background-color: #3b82f6;
            border-radius: 4px;
            cursor: grab;
            left: 0;
            transition: background-color 0.3s;
        }
        
        .puzzle-piece::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60%;
            height: 60%;
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }
        
        .puzzle-piece.dragging {
            cursor: grabbing;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .puzzle-piece.success {
            background-color: #10b981;
        }
        
        .slider-track {
            position: relative;
            height: 40px;
            background-color: #e5e7eb;
            border-radius: 20px;
            margin-bottom: 5px;
        }
        
        .slider-progress {
            height: 100%;
            width: 0;
            background-color: #bfdbfe;
            border-radius: 20px;
            transition: width 0.1s;
        }
        
        .slider-handle {
            position: absolute;
            top: 0;
            left: 0;
            width: 40px;
            height: 40px;
            background-color: #3b82f6;
            color: white;
            border-radius: 50%;
            cursor: grab;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            z-index: 1;
            transition: background-color 0.3s;
        }
        
        .slider-handle.dragging {
            cursor: grabbing;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .slider-handle.success {
            background-color: #10b981;
        }
        
        .captcha-message {
            height: 20px;
            margin-top: 5px;
            text-align: center;
            font-size: 14px;
        }
        
        .reset-button {
            display: none;
            background-color: #e5e7eb;
            color: #374151;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 5px;
            width: 100%;
            transition: background-color 0.2s;
        }
        
        .reset-button:hover {
            background-color: #d1d5db;
        }
    `;
    
    document.head.appendChild(styleEl);
}