<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="style.css">
</head>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            height: 100vh;
            display: flex;
        }
    </style>
</head>
<body>
    <div class="split-container">
        <div class="image-section">
            <div class="slideshow">
                <div class="slide active">
                    <img src="BOAT.jpg" alt="Login Background Image 1">
                </div>
                <div class="slide">
                    <img src="ESCOL.jpg" alt="Login Background Image 2">
                </div>
                <div class="slide">
                    <img src="guada Image.webp" alt="Login Background Image 3">
                </div>
            </div>
        </div>
        <div class="form-section">
            <div class="login-container">
                <div class="login-header">
                    <div class="logo-title">
                    <img src="logo1.jpg" alt="Logo" class="logo" />
                    <h1>Pasig River Ferry Service</h1>
                    <img src="logongprsf.jpg" alt="Logo" class="logo1" />
                    </div>
                    <p>Please log in to your account</p>
                    
                    
                </div>
                <form>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="remember-forgot">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <div class="forgot-password">
                            <a href="#">Forgot password?</a>
                        </div>
                    </div>
                    <button type="submit" class="login-button">Log In</button>
                    <div class="signup-link">
                        <p>Don't have an account? <a href="register.html">Sign up</a></p>
                    </div>
                </form>
            </div>
            <div class="reg-btn-container">
            <button id="toggle-dark" class="toggle" style="margin-left: 15px; padding: 5px 10px; border-radius: 5px;">
                🌙 / ☀️
              </button>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
    <script>
        // Slideshow functionality
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.slide');
            let currentSlide = 0;
            
            function nextSlide() {
                // Remove active class from current slide
                slides[currentSlide].classList.remove('active');
                
                // Move to next slide or back to first slide
                currentSlide = (currentSlide + 1) % slides.length;
                
                // Add active class to new current slide
                slides[currentSlide].classList.add('active');
            }
            
            // Change slide every 5 seconds
            setInterval(nextSlide, 3000);
        });
    </script>
</body>
</html>