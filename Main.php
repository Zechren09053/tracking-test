<?php
// Include the announcements file
require_once 'get_announcements.php';

// Include the simplified schedule functions
require_once 'simple_schedule_functions.php';

// Get the announcements
$announcements = getActiveAnnouncements();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pasig River Ferry Service</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="clstyle.css">
</head>
<body>
  <!-- Mobile sidebar navigation -->
  <div class="mobile-sidebar" id="mobileSidebar">
    <div class="sidebar-header">
      <h2>Menu</h2>
      <button class="close-btn" onclick="toggleSidebar('mobileSidebar')">×</button>
    </div>
    <div class="search-container">
      <input type="text" placeholder="Search..." aria-label="Search">
      <button type="submit" aria-label="Submit search">🔍</button>
    </div>
    <nav class="mobile-nav">
      <a href="#schedules">Schedules</a>
      <a href="#routes">Routes</a>
      <a href="#gallery">Gallery</a>
      <a href="#">Community</a>
    </nav>
  </div>
  
  <!-- Overlay for sidebars -->
  <div class="overlay" id="sidebarOverlay"></div>

  <!-- User sidebar -->
  <div class="sidebar" id="userSidebar">
    <div class="sidebar-header">
      <h2>User Profile</h2>
      <button class="close-btn" onclick="toggleSidebar('userSidebar')">×</button>
    </div>
    <div class="user-info">
      <div class="user-avatar">JP</div>
      
      <div class="user-details">
        <div class="user-detail">
          <div class="detail-label">Name</div>
          <div class="detail-value">John Peterson</div>
        </div>
        
        <div class="user-detail">
          <div class="detail-label">Username</div>
          <div class="detail-value">john_p</div>
        </div>
        
        <div class="user-detail">
          <div class="detail-label">Account Number</div>
          <div class="detail-value">ACC-12345678</div>
        </div>
        
        <div class="user-detail">
          <div class="detail-label">Email</div>
          <div class="detail-value">john.peterson@example.com</div>
        </div>
        
        <div class="user-detail">
          <div class="detail-label">Contact Number</div>
          <div class="detail-value">+1 (555) 123-4567</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main navigation -->
  <nav id="navbar">
    <div class="logo-container">
      <img src="logongprsf.jpg" alt="Pasig River Ferry Service Logo" class="logo">
      <div class="title">Pasig River Ferry Service</div>
    </div>

    <!-- Desktop nav links -->
    <div class="nav-links">
      <a href="#schedules">Schedules</a>
      <a href="#routes">Routes</a>
      <a href="#gallery">Gallery</a>
      <a href="#">Community</a>
    </div>

    <div class="nav-buttons">
      <div class="search-container">
        <input type="text" placeholder="Search..." aria-label="Search">
        <button type="submit" aria-label="Submit search">🔍</button>
      </div>
      
      <button id="userBtn" class="btn btn-outline" aria-label="User profile">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
          <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c0-.001 0-.002 0-.004 0-.993-1.319-3-5-3s-5 2.007-5 3v.004c0 .002 0 .003 0 .004 0 .006.001.016.004.022C3.008 14.982 3.08 15 3.5 15h9c.42 0 .492-.018.996-.982.003-.006.004-.016.004-.022z"/>
        </svg>
        Profile
      </button>
      
      <a href="clientlogin.html" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
          <path d="M8.5 10c-.276 0-.5-.224-.5-.5v-4c0-.276.224-.5.5-.5s.5.224.5.5v4c0 .276-.224.5-.5.5z"/>
          <path d="M10.828 10.828a.5.5 0 0 1-.707 0L8 8.707l-2.121 2.121a.5.5 0 0 1-.707-.707l2.828-2.828a.5.5 0 0 1 .707 0l2.828 2.828a.5.5 0 0 1 0 .707z"/>
          <path d="M8 16A8 8 0 1 1 8 0a8 8 0 0 1 0 16zm0-1A7 7 0 1 0 8 1a7 7 0 0 0 0 14z"/>
        </svg>
        Login
      </a>
      
      <button id="toggleDarkMode" class="btn-icon" aria-label="Toggle dark mode">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
          <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
        </svg>
      </button>
    </div>
    
    <!-- Mobile hamburger menu -->
    <button class="hamburger" id="hamburger" aria-label="Menu" aria-expanded="false">☰</button>
  </nav>

  <!-- Hero Section -->
  <section id="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1 class="hero-title">Navigate Manila's Historic Waterway</h1>
      <p class="hero-subtitle">Experience a faster, eco-friendly transportation alternative while enjoying Manila's scenic river views.</p>
      <div class="cta-buttons">
        <a href="#schedules" class="btn btn-primary">View Schedules</a>
        <a href="#routes" class="btn btn-outline light">Explore Routes</a>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="container">
    <h2 class="section-title">Why Choose Pasig River Ferry</h2>
    <div class="feature-cards">
      <div class="feature-card">
        <div class="feature-icon">⏱️</div>
        <h3 class="feature-title">Efficient Transportation</h3>
        <p class="feature-description">Beat Manila's notorious traffic with our streamlined river commute, saving valuable time on your daily journeys.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🌱</div>
        <h3 class="feature-title">Eco-Friendly Journey</h3>
        <p class="feature-description">Reduce your carbon footprint by choosing our environmentally sustainable transportation alternative.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🚢</div>
        <h3 class="feature-title">Modern Ferries</h3>
        <p class="feature-description">Experience comfort and safety with our fleet of modern, well-maintained ferry vessels designed for passenger satisfaction.</p>
      </div>
    </div>
  </section>

  <!-- Announcements Section -->
  <section id="announcements" class="container">
    <h2 class="section-title">Latest Announcements</h2>
    <div class="announcements-container">
      <?php if (!empty($announcements)): ?>
        <?php foreach ($announcements as $announcement): ?>
          <div class="announcement-card">
            <span class="announcement-date"><?php echo date('M d, Y', strtotime($announcement['created_at'])); ?></span>
            <h3 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h3>
            <p class="announcement-text"><?php echo htmlspecialchars($announcement['message']); ?></p>
            <a href="#" class="announcement-link">Learn more</a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="announcement-card">
          <span class="announcement-date">May 10, 2025</span>
          <h3 class="announcement-title">No Current Announcements</h3>
          <p class="announcement-text">There are no active announcements at this time. Please check back later.</p>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Schedule Section with Tabs -->
  <section id="schedules" class="schedule-section">
    <div class="container">
      <div class="schedule-header">
        <h2 class="section-title">Ferry Routes and Schedules</h2>
        <div id="clock" class="current-time"></div>
      </div>
      
      <!-- Schedule Direction Tabs -->
      <div class="schedule-tabs">
        <button class="tab-btn active" data-target="upstream-schedule">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 0 1H7.465A4 4 0 0 1 0 8z"/>
          </svg>
          Upstream: Escolta → Kalawaan
        </button>
        <button class="tab-btn" data-target="downstream-schedule">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
          </svg>
          Downstream: Pinagbuhatan → Escolta
        </button>
      </div>

      <div class="schedule-content">
        <!-- Upstream Table -->
        <div class="schedule-table-container active" id="upstream-schedule">
          <div class="table-responsive">
            <table class="schedule-table" id="upstream-table">
              <thead>
                <tr>
                  <th class="trip-column">Trip</th>
                  <?php foreach($upstreamData['stations'] as $station): ?>
                    <th><?php echo htmlspecialchars($station); ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php for($i = 1; $i <= $maxUpstreamTrips; $i++): ?>
                  <tr>
                    <td class="trip-number">Trip <?php echo $i; ?></td>
                    <?php foreach(array_keys($upstreamData['stations']) as $colId): ?>
                      <td><?php echo isset($upstreamData['data'][$i][$colId]) ? 
                        htmlspecialchars($upstreamData['data'][$i][$colId]) : '—'; ?></td>
                    <?php endforeach; ?>
                  </tr>
                <?php endfor; ?>
              </tbody>
            </table>
          </div>
          <div class="scroll-indicator">
            <span>Scroll to see more stations →</span>
          </div>
        </div>

        <!-- Downstream Table -->
        <div class="schedule-table-container" id="downstream-schedule">
          <div class="table-responsive">
            <table class="schedule-table" id="downstream-table">
              <thead>
                <tr>
                  <th class="trip-column">Trip</th>
                  <?php foreach($downstreamData['stations'] as $station): ?>
                    <th><?php echo htmlspecialchars($station); ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php for($i = 1; $i <= $maxDownstreamTrips; $i++): ?>
                  <tr>
                    <td class="trip-number">Trip <?php echo $i; ?></td>
                    <?php foreach(array_keys($downstreamData['stations']) as $colId): ?>
                      <td><?php echo isset($downstreamData['data'][$i][$colId]) ? 
                        htmlspecialchars($downstreamData['data'][$i][$colId]) : '—'; ?></td>
                    <?php endforeach; ?>
                  </tr>
                <?php endfor; ?>
              </tbody>
            </table>
          </div>
          <div class="scroll-indicator">
            <span>Scroll to see more stations →</span>
          </div>
        </div>
      </div>
      
      <div class="schedule-info">
        <div class="schedule-notes">
          <h4>Important Notes:</h4>
          <ul>
            <li>Schedules may vary during holidays and special events</li>
            <li>Last trip departure depends on passenger volume and weather conditions</li>
            <li>Children below 3 feet in height must be accompanied by an adult</li>
            <li>Please arrive at least 15 minutes before scheduled departure</li>
          </ul>
        </div>
        
        <div class="fare-info">
          <h4>Fare Information</h4>
          <p>Single journey fare: ₱25 for first 3 stations, ₱35 for end-to-end trips</p>
          <a href="#" class="btn btn-outline">View Complete Fare Matrix</a>
        </div>
      </div>
    </div>
  </section>

 <!-- Routes Section -->
<section id="routes" class="container">
  <h2 class="section-title">Ferry Routes</h2>
  
  <!-- Interactive Ferry Route Map -->
  <div class="route-description">
    <p>The Pasig River Ferry Service operates along the historic Pasig River, connecting various points in Metro Manila with 13 stations strategically located along the waterway. Explore our routes on the interactive map below.</p>
  </div>
  
  <!-- Insert the map here -->
  <iframe src="ferry_map.html" width="100%" height="570px" style="border:none; margin-top:20px;"></iframe>
  
  <div class="route-info-cards">
    <div class="info-card">
      <h3>Upstream Route</h3>
      <p>From Escolta to Kalawaan, passing through the central business districts of Manila and into the eastern residential areas.</p>
      <p><strong>Estimated Travel Time:</strong> 45-60 minutes (end-to-end)</p>
    </div>
    
    <div class="info-card">
      <h3>Downstream Route</h3>
      <p>From Pinagbuhatan to Escolta, connecting the eastern residential areas to Manila's historic districts.</p>
      <p><strong>Estimated Travel Time:</strong> 45-60 minutes (end-to-end)</p>
    </div>
  </div>
</section>

<!-- Gallery Section -->
<section id="gallery" class="container">
  <h2 class="section-title">Experience the Pasig River Ferry</h2>
  
  <div class="gallery-filters">
    <button class="filter-btn active" data-filter="all">All</button>
    <button class="filter-btn" data-filter="ferries">Ferries</button>
    <button class="filter-btn" data-filter="stations">Stations</button>
    <button class="filter-btn" data-filter="landmarks">River Landmarks</button>
    <button class="filter-btn" data-filter="experiences">Passenger Experiences</button>
  </div>
  
  <div class="gallery-grid">
    <!-- Ferry Images -->
    <div class="gallery-item" data-category="ferries">
      <div class="gallery-image">
        <img src="BOAT1.jpg" alt="Modern ferry boat cruising along Pasig River" />
      </div>
      <div class="gallery-caption">
        <h4>Modern Ferry Vessels</h4>
        <p>Our fleet features comfortable air-conditioned vessels for a pleasant journey</p>
      </div>
    </div>
    
    <div class="gallery-item" data-category="ferries">
      <div class="gallery-image">
        <img src="BOAT.jpg" alt="Ferry boat interior showing comfortable seating" />
      </div>
      <div class="gallery-caption">
        <h4>Comfortable Interiors</h4>
        <p>Spacious seating arrangements for a relaxing commute</p>
      </div>
    </div>
    
    <!-- Station Images -->
    <div class="gallery-item" data-category="stations">
      <div class="gallery-image">
        <img src="ESCOL.jpg" alt="Escolta Ferry Terminal" />
      </div>
      <div class="gallery-caption">
        <h4>Escolta Terminal</h4>
        <p>Our well-maintained terminal in the historic district of Manila</p>
      </div>
    </div>
    
    <div class="gallery-item" data-category="stations">
      <div class="gallery-image">
        <img src="PRFS.jpg" alt="Guadalupe Ferry Station" />
      </div>
      <div class="gallery-caption">
        <h4>Guadalupe Station</h4>
        <p>Convenient access point near the Guadalupe Bridge</p>
      </div>
    </div>
    
    <!-- Landmark Images -->
    <div class="gallery-item" data-category="landmarks">
      <div class="gallery-image">
        <img src="mala.jpg" alt="View of Malacañang Palace from the river" />
      </div>
      <div class="gallery-caption">
        <h4>Malacañang View</h4>
        <p>Glimpse the historic presidential palace from the river</p>
      </div>
    </div>
    
    <div class="gallery-item" data-category="landmarks">
      <div class="gallery-image">
        <img src="BRIDGE.jpg" alt="Historic bridges spanning the Pasig River" />
      </div>
      <div class="gallery-caption">
        <h4>Historic Bridges</h4>
        <p>Pass under Manila's iconic bridges with unique vantage points</p>
      </div>
    </div>
    
    <!-- Experience Images -->
    <div class="gallery-item" data-category="experiences">
      <div class="gallery-image">
        <img src="passenger.jpg" alt="Passengers enjoying the ferry ride" />
      </div>
      <div class="gallery-caption">
        <h4>Commuter Experience</h4>
        <p>Daily commuters enjoy a traffic-free journey to work</p>
      </div>
    </div>
    
    <div class="gallery-item" data-category="experiences">
      <div class="gallery-image">
        <img src="live.jpg" alt="Tourist taking photos from the ferry" />
      </div>
      <div class="gallery-caption">
        <h4>Tourist Explorations</h4>
        <p>A unique way to experience Manila's waterway heritage</p>
      </div>
    </div>
    
    <!-- More Items -->
    <div class="gallery-item" data-category="landmarks">
      <div class="gallery-image">
        <img src="sunset.jpg" alt="Sunset over the Pasig River" />
      </div>
      <div class="gallery-caption">
        <h4>Sunset Views</h4>
        <p>Experience stunning sunset views along your journey</p>
      </div>
    </div>
    
    <div class="gallery-item" data-category="stations">
      <div class="gallery-image">
        <img src="pup.jpg" alt="PUP Ferry Station" />
      </div>
      <div class="gallery-caption">
        <h4>PUP Station</h4>
        <p>Convenient access for university students and faculty</p>
      </div>
    </div>
    
    <div class="gallery-item" data-category="ferries">
      <div class="gallery-image">
        <img src="cap.jpg" alt="Ferry captain at the helm" />
      </div>
      <div class="gallery-caption">
        <h4>Expert Navigation</h4>
        <p>Our experienced captains ensure safe and efficient journeys</p>
      </div>
    </div>
    
    <div class="gallery-item" data-category="experiences">
      <div class="gallery-image">
        <img src="fam.jpg" alt="Family enjoying the ferry service" />
      </div>
      <div class="gallery-caption">
        <h4>Family Outings</h4>
        <p>A fun alternative for weekend family adventures</p>
      </div>
    </div>
  </div>
  
  <!-- Lightbox container for full-size image viewing -->
  <div class="gallery-lightbox" id="galleryLightbox">
    <span class="lightbox-close">&times;</span>
    <img class="lightbox-content" id="lightboxImage">
    <div class="lightbox-caption" id="lightboxCaption"></div>
    <button class="lightbox-nav prev">&lsaquo;</button>
    <button class="lightbox-nav next">&rsaquo;</button>
  </div>
  
  <div class="gallery-cta">
    <p>Want to share your Pasig River Ferry experience with us?</p>
    <a href="#" class="btn btn-primary">Submit Your Photos</a>
  </div>
</section>

  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="footer-content">
        <div class="footer-logo">
          <img src="logongprsf.jpg" alt="Pasig River Ferry Service Logo" class="logo">
          <p>Pasig River Ferry Service</p>
        </div>
        <div class="footer-links">
          <div class="footer-column">
            <h4>Quick Links</h4>
            <ul>
              <li><a href="#schedules">Schedules</a></li>
              <li><a href="#routes">Routes</a></li>
              <li><a href="#gallery">Gallery</a></li>
              <li><a href="#">Community</a></li>
            </ul>
          </div>
          <div class="footer-column">
            <h4>Information</h4>
            <ul>
              <li><a href="#">About Us</a></li>
              <li><a href="#">Contact</a></li>
              <li><a href="#">Terms of Service</a></li>
              <li><a href="#">Privacy Policy</a></li>
            </ul>
          </div>
          <div class="footer-column">
            <h4>Contact Us</h4>
            <address>
              <p>Pasig River Ferry Service</p>
              <p>Manila, Philippines</p>
              <p>Email: info@prfs.gov.ph</p>
              <p>Phone: +63 (2) 8888-1234</p>
            </address>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2025 Pasig River Ferry Service. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <script>
    // Gallery section JavaScript

// Handle gallery filtering
function initGalleryFilters() {
  const filterButtons = document.querySelectorAll('.filter-btn');
  const galleryItems = document.querySelectorAll('.gallery-item');
  
  filterButtons.forEach(button => {
    button.addEventListener('click', function() {
      // Remove active class from all buttons
      filterButtons.forEach(btn => btn.classList.remove('active'));
      
      // Add active class to clicked button
      this.classList.add('active');
      
      // Get the filter value
      const filterValue = this.getAttribute('data-filter');
      
      // Filter the gallery items
      galleryItems.forEach(item => {
        if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
          item.style.display = '';
          
          // Add a slight delay for smoother appearance
          setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
          }, 50);
        } else {
          item.style.opacity = '0';
          item.style.transform = 'translateY(20px)';
          
          // Hide after fade out animation
          setTimeout(() => {
            item.style.display = 'none';
          }, 300);
        }
      });
    });
  });
}

// Handle gallery lightbox
function initGalleryLightbox() {
  const galleryItems = document.querySelectorAll('.gallery-item');
  const lightbox = document.getElementById('galleryLightbox');
  const lightboxImg = document.getElementById('lightboxImage');
  const lightboxCaption = document.getElementById('lightboxCaption');
  const closeBtn = document.querySelector('.lightbox-close');
  const nextBtn = document.querySelector('.lightbox-nav.next');
  const prevBtn = document.querySelector('.lightbox-nav.prev');
  
  let currentIndex = 0;
  const visibleItems = [];
  
  function updateVisibleItems() {
    visibleItems.length = 0;
    galleryItems.forEach((item, index) => {
      if (item.style.display !== 'none') {
        visibleItems.push({
          index: index,
          item: item
        });
      }
    });
  }
  
  function showImage(index) {
    updateVisibleItems();
    if (visibleItems.length === 0) return;
    
    currentIndex = index;
    
    // Ensure currentIndex is within bounds
    if (currentIndex >= visibleItems.length) currentIndex = 0;
    if (currentIndex < 0) currentIndex = visibleItems.length - 1;
    
    const currentItem = visibleItems[currentIndex].item;
    const imgSrc = currentItem.querySelector('img').src;
    const captionText = currentItem.querySelector('.gallery-caption').innerHTML;
    
    lightboxImg.src = imgSrc;
    lightboxCaption.innerHTML = captionText;
    lightbox.style.display = 'block';
    
    // Prevent body scrolling when lightbox is open
    document.body.style.overflow = 'hidden';
  }
  
  // Set up click events for gallery items
  galleryItems.forEach((item, index) => {
    item.addEventListener('click', function() {
      showImage(index);
    });
  });
  
  // Close lightbox
  closeBtn.addEventListener('click', function() {
    lightbox.style.display = 'none';
    document.body.style.overflow = '';
  });
  
  // Outside click to close
  lightbox.addEventListener('click', function(e) {
    if (e.target === lightbox) {
      lightbox.style.display = 'none';
      document.body.style.overflow = '';
    }
  });
  
  // Next image
  nextBtn.addEventListener('click', function() {
    showImage(currentIndex + 1);
  });
  
  // Previous image
  prevBtn.addEventListener('click', function() {
    showImage(currentIndex - 1);
  });
  
  // Keyboard navigation
  document.addEventListener('keydown', function(e) {
    if (lightbox.style.display === 'block') {
      if (e.key === 'ArrowRight') {
        showImage(currentIndex + 1);
      } else if (e.key === 'ArrowLeft') {
        showImage(currentIndex - 1);
      } else if (e.key === 'Escape') {
        lightbox.style.display = 'none';
        document.body.style.overflow = '';
      }
    }
  });
}

// Initialize gallery functionality
document.addEventListener('DOMContentLoaded', function() {
  initGalleryFilters();
  initGalleryLightbox();
  
  // Apply initial staggered animation to gallery items
  const galleryItems = document.querySelectorAll('.gallery-item');
  galleryItems.forEach((item, index) => {
    item.style.animationDelay = `${index * 0.1}s`;
  });
});
    // Update the current time
    function updateClock() {
      const now = new Date();
      const timeElement = document.getElementById('clock');
      if (timeElement) {
        timeElement.textContent = now.toLocaleTimeString('en-PH', { 
          hour: '2-digit', 
          minute: '2-digit',
          hour12: true 
        });
      }
    }

    // Toggle any sidebar by ID
    function toggleSidebar(sidebarId) {
      const sidebar = document.getElementById(sidebarId);
      const overlay = document.getElementById('sidebarOverlay');
      
      if (sidebar) {
        sidebar.classList.toggle('active');
      }
      
      if (overlay) {
        overlay.classList.toggle('active');
        
        // If any sidebar is active, make overlay active
        const sidebars = document.querySelectorAll('.sidebar, .mobile-sidebar');
        let anySidebarActive = false;
        
        sidebars.forEach(sb => {
          if (sb.classList.contains('active')) {
            anySidebarActive = true;
          }
        });
        
        if (anySidebarActive) {
          overlay.classList.add('active');
        } else {
          overlay.classList.remove('active');
        }
      }
    }
    
    // Schedule Tabs Functionality
    function initScheduleTabs() {
      const tabButtons = document.querySelectorAll('.tab-btn');
      tabButtons.forEach(button => {
        button.addEventListener('click', function() {
          // Remove active class from all tabs
          tabButtons.forEach(btn => btn.classList.remove('active'));
          
          // Add active class to clicked tab
          this.classList.add('active');
          
          // Hide all schedule containers
          const scheduleContainers = document.querySelectorAll('.schedule-table-container');
          scheduleContainers.forEach(container => container.classList.remove('active'));
          
          // Show the selected schedule container
          const targetId = this.getAttribute('data-target');
          const targetContainer = document.getElementById(targetId);
          if (targetContainer) {
            targetContainer.classList.add('active');
          }
        });
      });
    }

    // Horizontal scroll indicator for tables
    function initScrollIndicators() {
      const tableContainers = document.querySelectorAll('.table-responsive');
      
      tableContainers.forEach(container => {
        container.addEventListener('scroll', function() {
          const indicator = this.parentElement.querySelector('.scroll-indicator');
          
          if (this.scrollWidth > this.clientWidth) {
            if (this.scrollLeft > 0) {
              this.classList.add('scrolled-start');
            } else {
              this.classList.remove('scrolled-start');
            }
            
            if (this.scrollLeft + this.clientWidth >= this.scrollWidth - 10) {
              this.classList.add('scrolled-end');
              if (indicator) indicator.style.opacity = '0';
            } else {
              this.classList.remove('scrolled-end');
              if (indicator) indicator.style.opacity = '1';
            }
          }
        });
        
        // Trigger the scroll event initially
        container.dispatchEvent(new Event('scroll'));
      });
    }

    // Initialize everything when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
      // Set up clock
      updateClock();
      setInterval(updateClock, 60000); // Update every minute
      
      // Initialize tabs
      initScheduleTabs();
      
      // Initialize scroll indicators
      initScrollIndicators();
      
      // Set up hamburger menu
      document.getElementById('hamburger').addEventListener('click', function() {
        toggleSidebar('mobileSidebar');
      });
      
      // Set up user profile button
      document.getElementById('userBtn').addEventListener('click', function() {
        toggleSidebar('userSidebar');
      });
      
      // Set up overlay to close sidebars
      document.getElementById('sidebarOverlay').addEventListener('click', function() {
        const sidebars = document.querySelectorAll('.sidebar.active, .mobile-sidebar.active');
        sidebars.forEach(sidebar => {
          sidebar.classList.remove('active');
        });
        this.classList.remove('active');
      });
      
      // Set up dark mode toggle
      document.getElementById('toggleDarkMode').addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        
        // Optional: Save preference to localStorage
        const isDarkMode = document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', isDarkMode ? 'enabled' : 'disabled');
      });
      
      // Check for saved dark mode preference
      if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
      }
    });
  </script>
</body>
</html>