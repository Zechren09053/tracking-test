// Initialize the map
var map = L.map('map').setView([40.712776, -74.005974], 13);  // Set initial map view (New York)

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Function to fetch ferry data from the PHP endpoint and update the map
function fetchFerryData() {
    $.ajax({
        url: 'getFerries.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#ferry-list').empty();  // Clear the previous ferry list

            if (data.length === 0) {
                $('#ferry-list').append('<p>No ferries are currently available.</p>');
            }

            // Loop through each ferry and place a marker
            data.forEach(function(ferry) {
                var ferryLat = ferry.latitude;
                var ferryLng = ferry.longitude;
                var ferryName = ferry.name;

                // Add a marker for the ferry on the map
                L.marker([ferryLat, ferryLng]).addTo(map)
                    .bindPopup('<b>' + ferryName + '</b><br>Location: (' + ferryLat + ', ' + ferryLng + ')')
                    .openPopup();

                // Display ferry details in the list
                const ferryElement = `
                    <div class="ferry">
                        <h3>${ferryName}</h3>
                        <p class="location">Location: Latitude ${ferryLat}, Longitude ${ferryLng}</p>
                        <p class="last-updated">Last Updated: ${ferry.last_updated}</p>
                    </div>
                `;
                $('#ferry-list').append(ferryElement);
            });
        },
        error: function() {
            $('#ferry-list').html('<p>Sorry, there was an error loading the ferry data.</p>');
        }
    });
}

// Call the function to fetch ferry data
fetchFerryData();

// Set interval to refresh ferry location every 5 seconds
setInterval(fetchFerryData, 5000);
