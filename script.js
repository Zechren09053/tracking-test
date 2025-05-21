// Wait for DOM to load
document.addEventListener("DOMContentLoaded", function () {
    // Toggle sidebar
    const hamburgerBtn = document.getElementById("hamburger");
    const sidebar = document.getElementById("mobileSidebar");
    const closeBtn = document.getElementById("closeBtn");
  
    if (hamburgerBtn && sidebar) {
      hamburgerBtn.addEventListener("click", () => {
        sidebar.classList.toggle("active");
      });
    }
    if (closeBtn && sidebar) {
  closeBtn.addEventListener("click", () => {
    sidebar.classList.remove("active");
  });
}
  
    // Navbar scroll logic
    let lastScrollTop = 0;
    const navbar = document.getElementById("navbar");
    const regBtn = document.getElementById("regBtn");
    const regBtn1 = document.getElementById("regBtn1");
  
    if (navbar && regBtn && regBtn1) {
      window.addEventListener('scroll', function () {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
  
        if (scrollTop > lastScrollTop) {
          navbar.style.top = "-70px";
          regBtn.style.opacity = "0";
          regBtn.style.transform = "translateY(100px)";
          regBtn1.style.opacity = "0";
          regBtn1.style.transform = "translateY(100px)";
        } else {
          navbar.style.top = "0";
          regBtn.style.opacity = "1";
          regBtn.style.transform = "translateY(0)";
          regBtn1.style.opacity = "1";
          regBtn1.style.transform = "translateY(0)";
        }
  
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
      });
    }
  
    // Dark mode toggle
    const toggleBtn = document.getElementById("toggle-dark");
    if (toggleBtn) {
      toggleBtn.addEventListener("click", () => {
        document.body.classList.toggle("dark-mode");
        localStorage.setItem("theme", document.body.classList.contains("dark-mode") ? "dark" : "light");
      });
    }
  
    if (localStorage.getItem("theme") === "dark") {
      document.body.classList.add("dark-mode");
    }
  
    // Image carousel swipe
    const carousel = document.querySelector('.image-carousel');
    if (carousel) {
      let isMouseDown = false;
      let startX;
      let scrollLeft;
  
      carousel.addEventListener('mousedown', (e) => {
        isMouseDown = true;
        startX = e.pageX - carousel.offsetLeft;
        scrollLeft = carousel.scrollLeft;
      });
  
      carousel.addEventListener('mouseleave', () => {
        isMouseDown = false;
      });
  
      carousel.addEventListener('mouseup', () => {
        isMouseDown = false;
      });
  
      carousel.addEventListener('mousemove', (e) => {
        if (!isMouseDown) return;
        e.preventDefault();
        const x = e.pageX - carousel.offsetLeft;
        const walk = (x - startX) * 1;
        carousel.scrollLeft = scrollLeft - walk;
      });
    }
  
    // Registration form validation
    const regForm = document.getElementById('registrationForm');
    if (regForm) {
      regForm.addEventListener('submit', function(event) {
        event.preventDefault();
  
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
  
        if (password !== confirmPassword) {
          alert('Passwords do not match!');
          return;
        }
  
        alert('Registration successful!');
      });
    }
  });
  

  document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const userBtn = document.getElementById('userBtn');
    const closeBtn = document.getElementById('closeBtn');
    const userSidebar = document.getElementById('userSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Show sidebar when user button is clicked
    userBtn.addEventListener('click', function() {
        userSidebar.classList.add('sidebar-active');
        sidebarOverlay.classList.add('overlay-active');
        console.log('Sidebar opened');
    });

    // Hide sidebar when close button is clicked
    closeBtn.addEventListener('click', function() {
        userSidebar.classList.remove('sidebar-active');
        sidebarOverlay.classList.remove('overlay-active');
        console.log('Sidebar closed via button');
    });

    // Hide sidebar when clicking outside (on the overlay)
    sidebarOverlay.addEventListener('click', function() {
        userSidebar.classList.remove('sidebar-active');
        sidebarOverlay.classList.remove('overlay-active');
        console.log('Sidebar closed via overlay');
    });
    
    console.log('Event listeners initialized');
});