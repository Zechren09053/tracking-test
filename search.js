// Search functionality for Pasig River Ferry Service website

// Define the searchable content structure
const searchableContent = [
    // Schedules section
    {
      title: "Ferry Schedules",
      description: "View upstream and downstream ferry schedules",
      url: "#schedules",
      keywords: ["schedule", "timetable", "departure", "arrival", "upstream", "downstream", "ferry times", "trips"]
    },
    // Routes section
    {
      title: "Ferry Routes",
      description: "Map and information about ferry routes along Pasig River",
      url: "#routes",
      keywords: ["route", "map", "stations", "stops", "locations", "terminals", "waterway"]
    },
    // Stations (generated from the route data)
    {
      title: "Escolta Station",
      description: "Ferry terminal located in the historic district of Manila",
      url: "#routes",
      keywords: ["escolta", "terminal", "station", "manila", "downtown"]
    },
    {
      title: "PUP Station",
      description: "Ferry station serving Polytechnic University of the Philippines",
      url: "#routes",
      keywords: ["pup", "polytechnic", "university", "station", "terminal", "student"]
    },
    {
      title: "Guadalupe Station",
      description: "Ferry terminal near Guadalupe Bridge",
      url: "#routes",
      keywords: ["guadalupe", "bridge", "terminal", "station", "edsa"]
    },
    {
      title: "Pinagbuhatan Station",
      description: "Eastern terminal of the Pasig River Ferry Service",
      url: "#routes",
      keywords: ["pinagbuhatan", "pasig", "terminal", "station", "east"]
    },
    {
      title: "Kalawaan Station",
      description: "Ferry terminal in Kalawaan, Pasig City",
      url: "#routes",
      keywords: ["kalawaan", "pasig", "terminal", "station"]
    },
    // Gallery section
    {
      title: "Ferry Gallery",
      description: "Photos of ferries, stations, landmarks, and passenger experiences",
      url: "#gallery",
      keywords: ["gallery", "photos", "images", "pictures", "ferries", "stations", "landmarks", "experiences"]
    },
    // Features section
    {
      title: "Ferry Service Features",
      description: "Benefits of using the Pasig River Ferry Service",
      url: "#features",
      keywords: ["features", "benefits", "efficient", "eco-friendly", "modern ferries", "transportation"]
    },
    // Fare information
    {
      title: "Fare Information",
      description: "Ticket prices for the Pasig River Ferry Service",
      url: "#schedules",
      keywords: ["fare", "ticket", "price", "cost", "fee", "payment", "peso", "₱25", "₱35"]
    },
    // Important notes
    {
      title: "Important Notes",
      description: "Important information for ferry passengers",
      url: "#schedules",
      keywords: ["notes", "information", "rules", "holidays", "special events", "weather", "children", "arrival time"]
    }
  ];
  
  // Search results display container (to be inserted in the DOM)
  const searchResultsTemplate = `
    <div id="searchResults" class="search-results">
      <div class="search-results-header">
        <h3>Search Results</h3>
        <button class="close-search-btn">&times;</button>
      </div>
      <div class="search-results-content">
        <div class="results-list"></div>
        <div class="no-results-message" style="display: none;">
          <p>No results found. Please try different keywords.</p>
        </div>
      </div>
    </div>
  `;
  
  // Function to initialize the search functionality
  function initializeSearch() {
    // Insert the search results container into the DOM
    document.body.insertAdjacentHTML('beforeend', searchResultsTemplate);
    
    // Get reference to search elements
    const searchContainers = document.querySelectorAll('.search-container');
    const searchResults = document.getElementById('searchResults');
    const resultsContent = searchResults.querySelector('.results-list');
    const noResultsMessage = searchResults.querySelector('.no-results-message');
    const closeSearchBtn = searchResults.querySelector('.close-search-btn');
    
    // Handle search input and submission
    searchContainers.forEach(container => {
      const searchInput = container.querySelector('input');
      const searchButton = container.querySelector('button');
      
      // Search button click handler
      searchButton.addEventListener('click', function() {
        performSearch(searchInput.value);
      });
      
      // Enter key press in search input
      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          performSearch(searchInput.value);
        }
      });
    });
    
    // Close search results
    closeSearchBtn.addEventListener('click', function() {
      searchResults.classList.remove('active');
      document.getElementById('sidebarOverlay').classList.remove('active');
    });
    
    // Close search results when clicking overlay
    document.getElementById('sidebarOverlay').addEventListener('click', function() {
      searchResults.classList.remove('active');
    });
    
    // Function to perform the search
    function performSearch(query) {
      if (!query.trim()) return;
      
      // Clear previous results
      resultsContent.innerHTML = '';
      
      // Convert query to lowercase for case-insensitive matching
      const searchQuery = query.toLowerCase();
      
      // Filter searchable content based on the query
      const results = searchableContent.filter(item => {
        // Check if query is in title, description, or keywords
        return (
          item.title.toLowerCase().includes(searchQuery) ||
          item.description.toLowerCase().includes(searchQuery) ||
          item.keywords.some(keyword => keyword.toLowerCase().includes(searchQuery))
        );
      });
      
      // Display results or "no results" message
      if (results.length > 0) {
        resultsContent.innerHTML = results.map(result => `
          <div class="search-result-item">
            <a href="${result.url}" class="result-link">
              <h4>${result.title}</h4>
              <p>${result.description}</p>
            </a>
          </div>
        `).join('');
        
        // Add click event to result links
        document.querySelectorAll('.result-link').forEach(link => {
          link.addEventListener('click', function() {
            // Close search results when a result is clicked
            searchResults.classList.remove('active');
            document.getElementById('sidebarOverlay').classList.remove('active');
            
            // Close mobile sidebar if open
            const mobileSidebar = document.getElementById('mobileSidebar');
            if (mobileSidebar && mobileSidebar.classList.contains('active')) {
              mobileSidebar.classList.remove('active');
            }
          });
        });
        
        resultsContent.style.display = 'block';
        noResultsMessage.style.display = 'none';
      } else {
        resultsContent.style.display = 'none';
        noResultsMessage.style.display = 'block';
      }
      
      // Show the search results and overlay
      searchResults.classList.add('active');
      document.getElementById('sidebarOverlay').classList.add('active');
    }
  }
  
  // Initialize search when the DOM is loaded
  document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
  });