/**
 * Pasig River Ferry Service Website
 * Main JavaScript functionality
 */

// Global initialization when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Login/Logout functionality
  updateLoginLogoutButton();

  // Set up session polling (check login status every 0.5 seconds)
  initSessionPolling();

  // Set up clock
  updateClock();
  setInterval(updateClock, 60000); // Update every minute

  // Initialize sidebars and mobile menu
  initSidebars();

  // Initialize dark mode toggle
  initDarkMode();

  // Initialize tabs for schedule section
  initScheduleTabs();

  // Initialize scroll indicators for tables
  initScrollIndicators();

  // Initialize gallery features
  initGallery();
});

// ===== Session Polling Functionality =====

// Variable to track previous login state
let previousLoginState = null;

// Initialize session polling
function initSessionPolling() {
  // Check session status immediately
  checkSessionStatus();
  
  // Set up interval to check every 0.5 seconds
  setInterval(checkSessionStatus, 500);
}

// Function to check session status via AJAX
function checkSessionStatus() {
  fetch('check_session.php')
    .then(response => response.json())
    .then(data => {
      // Only update UI if login state has changed
      if (previousLoginState === null || previousLoginState !== data.logged_in) {
        previousLoginState = data.logged_in;
        
        // Update login/logout button based on session status
        updateLoginLogoutButton(data.logged_in);
        
        // Update user profile button visibility if it exists
        const userBtn = document.getElementById('userBtn');
        if (userBtn) {
          userBtn.style.display = data.logged_in ? 'flex' : 'none';
        }
        
        // Show notification if state changed from logged in to logged out (but not on initial load)
        if (previousLoginState !== null && !data.logged_in && previousLoginState === true) {
          showNotification('You have been logged out', 'info');
        }
      }
    })
    .catch(error => {
      console.error('Error checking session status:', error);
    });
}

// ===== Login/Logout Functionality =====

// Function to check if user is logged in and update login/logout button
function updateLoginLogoutButton(isLoggedIn = null) {
  // If isLoggedIn is not provided, fetch the status
  if (isLoggedIn === null) {
    fetch('check_session.php')
      .then(response => response.json())
      .then(data => {
        updateLoginButton(data.logged_in);
      })
      .catch(error => {
        console.error('Error checking session:', error);
      });
  } else {
    // If isLoggedIn status is provided, use it directly
    updateLoginButton(isLoggedIn);
  }
}

// Helper function to update the login button based on login status
function updateLoginButton(isLoggedIn) {
  const loginBtn = document.getElementById('loginBtn');
  
  if (loginBtn) {
    if (isLoggedIn) {
      // User is logged in - show logout button
      loginBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
          <path d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
          <path d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
        </svg>
        Logout
      `;
      loginBtn.classList.remove('btn-primary');
      loginBtn.classList.add('btn-danger');
      loginBtn.removeEventListener('click', openLoginModal);
      loginBtn.addEventListener('click', handleLogout);
    } else {
      // User is not logged in - show login button
      loginBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
          <path d="M8.5 10c-.276 0-.5-.224-.5-.5v-4c0-.276.224-.5.5-.5s.5.224.5.5v4c0 .276-.224.5-.5.5z"/>
          <path d="M10.828 10.828a.5.5 0 0 1-.707 0L8 8.707l-2.121 2.121a.5.5 0 0 1-.707-.707l2.828-2.828a.5.5 0 0 1 .707 0l2.828 2.828a.5.5 0 0 1 0 .707z"/>
          <path d="M8 16A8 8 0 1 1 8 0a8 8 0 0 1 0 16zm0-1A7 7 0 1 0 8 1a7 7 0 0 0 0 14z"/>
        </svg>
        Login
      `;
      loginBtn.classList.add('btn-primary');
      loginBtn.classList.remove('btn-danger');
      loginBtn.removeEventListener('click', handleLogout);
      loginBtn.addEventListener('click', openLoginModal);
    }
  }
}

// Handle logout - modified to only show the existing confirmation modal
function handleLogout() {
  // Open the existing logout confirmation modal
  const modal = document.getElementById('logoutConfirmModal');
  if (modal) {
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
  }
}

// Show notification function
function showNotification(message, type = 'info') {
  // Create notification element
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
    <div class="notification-content">
      <p>${message}</p>
      <button class="notification-close">&times;</button>
    </div>
  `;
  
  // Add to the document
  document.body.appendChild(notification);
  
  // Add event listener to close button
  const closeBtn = notification.querySelector('.notification-close');
  closeBtn.addEventListener('click', function() {
    notification.remove();
  });
  
  // Auto-remove after 5 seconds
  setTimeout(() => {
    notification.remove();
  }, 5000);
}

// Function to open login modal
function openLoginModal() {
  const modal = document.getElementById('loginModal');
  if (modal) {
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
  }
}

// Function to close login modal
function closeLoginModal() {
  const modal = document.getElementById('loginModal');
  if (modal) {
    modal.style.display = 'none';
    document.body.classList.remove('modal-open');
  }
}

// Message listener for iframe communication
window.addEventListener('message', function(event) {
  // Check origin for security
  if (event.data.type === 'loginSuccess') {
    // User has logged in successfully in the iframe
    closeLoginModal();
    // Session polling will handle button state automatically
    
    // Show success notification
    showNotification('You have been logged in successfully', 'success');
  }
});

// ===== Sidebar Functionality =====

// Initialize sidebar functionality
function initSidebars() {
  // Set up hamburger menu
  const hamburgerBtn = document.getElementById('hamburger');
  if (hamburgerBtn) {
    hamburgerBtn.addEventListener('click', function() {
      toggleSidebar('mobileSidebar');
    });
  }
  
  // Set up user profile button
  const userBtn = document.getElementById('userBtn');
  if (userBtn) {
    userBtn.addEventListener('click', function() {
      toggleSidebar('userSidebar');
    });
  }
  
  // Set up overlay to close sidebars
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', function() {
      const sidebars = document.querySelectorAll('.sidebar.active, .mobile-sidebar.active');
      sidebars.forEach(sidebar => {
        sidebar.classList.remove('active');
      });
      this.classList.remove('active');
      document.body.classList.remove('sidebar-open');
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
      document.body.classList.add('sidebar-open');
    } else {
      overlay.classList.remove('active');
      document.body.classList.remove('sidebar-open');
    }
  }
}

// ===== Dark Mode Functionality =====

// Initialize dark mode based on user preferences
function initDarkMode() {
  // Set up dark mode toggle
  const darkModeToggle = document.getElementById('toggleDarkMode');
  if (darkModeToggle) {
    darkModeToggle.addEventListener('click', toggleDarkMode);
  }
  
  // Check for saved dark mode preference
  if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
  }
}

// Toggle dark mode functionality
function toggleDarkMode() {
  document.body.classList.toggle('dark-mode');
  // Save preference to localStorage
  if (document.body.classList.contains('dark-mode')) {
    localStorage.setItem('darkMode', 'enabled');
  } else {
    localStorage.setItem('darkMode', 'disabled');
  }
}

// ===== Schedule Tabs Functionality =====

// Initialize schedule tabs
function initScheduleTabs() {
  const tabButtons = document.querySelectorAll('.schedule-tabs .tab-btn');
  tabButtons.forEach(button => {
    button.addEventListener('click', function() {
      const target = this.getAttribute('data-target');
      switchScheduleTab(target);
    });
  });
}

// Switch schedule tabs
function switchScheduleTab(target) {
  // Hide all schedule tables
  const tables = document.querySelectorAll('.schedule-table-container');
  tables.forEach(table => {
    table.classList.remove('active');
  });
  
  // Show the selected table
  document.getElementById(target).classList.add('active');
  
  // Update active state on buttons
  const buttons = document.querySelectorAll('.schedule-tabs .tab-btn');
  buttons.forEach(button => {
    button.classList.remove('active');
    if (button.getAttribute('data-target') === target) {
      button.classList.add('active');
    }
  });
}

// ===== Scroll Indicators =====

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

// ===== Gallery Functionality =====

// Initialize gallery functionality
function initGallery() {
  initGalleryFilters();
  initGalleryLightbox();
  
  // Apply initial staggered animation to gallery items
  const galleryItems = document.querySelectorAll('.gallery-item');
  galleryItems.forEach((item, index) => {
    item.style.animationDelay = `${index * 0.1}s`;
  });
}

// Handle gallery filtering
function initGalleryFilters() {
  const filterButtons = document.querySelectorAll('.gallery-filters .filter-btn');
  
  filterButtons.forEach(button => {
    button.addEventListener('click', function() {
      const filter = this.getAttribute('data-filter');
      filterGallery(filter);
      
      // Toggle active class
      document.querySelectorAll('.gallery-filters .filter-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      this.classList.add('active');
    });
  });
}

// Filter gallery
function filterGallery(filter) {
  const items = document.querySelectorAll('.gallery-item');
  
  items.forEach(item => {
    if (filter === 'all' || item.getAttribute('data-category') === filter) {
      item.style.display = 'block';
      
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
}

// Handle gallery lightbox
function initGalleryLightbox() {
  const galleryItems = document.querySelectorAll('.gallery-item .gallery-image img');
  const lightbox = document.getElementById('galleryLightbox');
  const lightboxImg = document.getElementById('lightboxImage');
  const lightboxCaption = document.getElementById('lightboxCaption');
  const closeBtn = document.querySelector('.lightbox-close');
  const nextBtn = document.querySelector('.lightbox-nav.next');
  const prevBtn = document.querySelector('.lightbox-nav.prev');
  
  let currentIndex = 0;
  let currentImageIndex = 0;
  const galleryImages = document.querySelectorAll('.gallery-item .gallery-image img');
  
  // Set up click events for gallery items
  galleryItems.forEach(item => {
    item.addEventListener('click', function() {
      openLightbox(this);
    });
  });
  
  // Close lightbox
  if (closeBtn) {
    closeBtn.addEventListener('click', closeLightbox);
  }
  
  // Outside click to close
  if (lightbox) {
    lightbox.addEventListener('click', function(e) {
      if (e.target === lightbox) {
        closeLightbox();
      }
    });
  }
  
  // Next image
  if (nextBtn) {
    nextBtn.addEventListener('click', function() {
      navigateLightbox('next');
    });
  }
  
  // Previous image
  if (prevBtn) {
    prevBtn.addEventListener('click', function() {
      navigateLightbox('prev');
    });
  }
  
  // Keyboard navigation
  document.addEventListener('keydown', function(e) {
    if (lightbox && lightbox.classList.contains('active')) {
      if (e.key === 'ArrowRight') {
        navigateLightbox('next');
      } else if (e.key === 'ArrowLeft') {
        navigateLightbox('prev');
      } else if (e.key === 'Escape') {
        closeLightbox();
      }
    }
  });
}

// Lightbox functionality
function openLightbox(imageElement) {
  const lightbox = document.getElementById('galleryLightbox');
  const lightboxImage = document.getElementById('lightboxImage');
  const lightboxCaption = document.getElementById('lightboxCaption');
  
  // Set the image source
  lightboxImage.src = imageElement.src;
  
  // Get the caption from the gallery item
  const galleryItem = imageElement.closest('.gallery-item');
  const captionElement = galleryItem.querySelector('.gallery-caption h4');
  const caption = captionElement ? captionElement.textContent : '';
  lightboxCaption.textContent = caption;
  
  // Find index of current image for navigation
  const galleryImages = document.querySelectorAll('.gallery-item .gallery-image img');
  currentImageIndex = Array.from(galleryImages).indexOf(imageElement);
  
  // Show the lightbox
  lightbox.classList.add('active');
  document.body.classList.add('lightbox-open');
}

function closeLightbox() {
  const lightbox = document.getElementById('galleryLightbox');
  if (lightbox) {
    lightbox.classList.remove('active');
    document.body.classList.remove('lightbox-open');
    document.body.style.overflow = '';
  }
}

function navigateLightbox(direction) {
  const galleryImages = document.querySelectorAll('.gallery-item .gallery-image img');
  const imagesArray = Array.from(galleryImages);
  const totalImages = imagesArray.length;
  
  if (direction === 'prev') {
    currentImageIndex = (currentImageIndex - 1 + totalImages) % totalImages;
  } else {
    currentImageIndex = (currentImageIndex + 1) % totalImages;
  }
  
  const newImage = imagesArray[currentImageIndex];
  openLightbox(newImage);
}

// ===== Clock Functionality =====

// Update the current time
function updateClock() {
  const now = new Date();
  const clockElement = document.getElementById('clock');
  if (clockElement) {
    clockElement.textContent = now.toLocaleTimeString('en-US', { 
      hour: '2-digit', 
      minute: '2-digit',
      hour12: true 
    });
  }
}