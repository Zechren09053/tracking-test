var map = L.map('map').setView([14.5896, 121.0360], 13);
    var markers = {};

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const pasigRiverRoute = [
  [14.5528,121.0875],[14.5528,121.0847],[14.5533,121.0806],[14.5533,121.0764],[14.5542,121.0750],
  [14.5550,121.0731],[14.5559,121.0711],[14.5565,121.0687],[14.5567,121.0680],[14.5581,121.0669],
  [14.5597,121.0664],[14.5610,121.0650],[14.5614,121.0631],[14.5622,121.0614],[14.5644,121.0592],
  [14.5653,121.0558],[14.5661,121.0536],[14.5678,121.0511],[14.5683,121.0492],[14.5683,121.0470],
  [14.5686,121.0447],[14.5686,121.0422],[14.5675,121.0378],[14.5672,121.0347],[14.5683,121.0325],
  [14.5744,121.0256],[14.5792,121.0175],[14.5808,121.0169],[14.5831,121.0190],[14.5856,121.0203],
  [14.5870,121.0190],[14.5856,121.0142],[14.5828,121.0114],[14.5817,121.0092],[14.5828,121.0075],
  [14.5844,121.0067],[14.5864,121.0070],[14.5878,121.0086],[14.5881,121.0114],[14.5911,121.0156],
  [14.5925,121.0158],[14.5939,121.0122],[14.5964,121.0075],[14.5972,121.0044],[14.5956,121.0017],
  [14.5967,120.9983],[14.5939,120.9950],[14.5911,120.9928],[14.5897,120.9900],[14.5897,120.9872],
  [14.5911,120.9858],[14.5928,120.9836],[14.5961,120.9814],[14.5967,120.9794],[14.5953,120.9761],
  [14.5956,120.9703],[14.5958,120.9636]
];

const pasigRiverRoute2 = [
  [14.5581,121.0669],[14.5581,121.0681],[14.5586,121.0703],[14.5597,121.0721],[14.5620,121.0732],
  [14.5667,121.0736],[14.5700,121.0739],[14.5714,121.0743],[14.5754,121.0775],[14.5778,121.0803],
  [14.5803,121.0819],[14.5833,121.0828],[14.5833,121.0828],[14.5872,121.0833],[14.5928,121.0822],
  [14.5978,121.0825],[14.6025,121.0825]
];


    const riverRoute = L.polyline(pasigRiverRoute, {
        color: 'blue',
        weight: 4,
        opacity: 0.7,
        smoothFactor: 1
    }).addTo(map);

    const riverRoute2 = L.polyline(pasigRiverRoute2, {
    color: 'blue',
    weight: 4,
    opacity: 0.7,
    smoothFactor: 1
}).addTo(map);

    // Only fit once when map initializes
    map.fitBounds([riverRoute.getBounds(), riverRoute2.getBounds()]);

    function fetchFerryData() {
    const ferryList = $('#ferry-list');
    const scrollPos = ferryList.scrollTop(); // Save scroll position

    $.ajax({
        url: 'getFerries.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            ferryList.empty();

            if (data.length === 0) {
                ferryList.append('<p>No ferries are currently available.</p>');
            }

            data.forEach(function(ferry) {
                const statusClass = ferry.status.toLowerCase() === 'active' ? 'status-active' : 'status-inactive';
                const ferryElement = `
                    <div class="boat-card" data-lat="${ferry.latitude}" data-lng="${ferry.longitude}">
                        <div class="status-indicator ${statusClass}"></div>
                        <div class="top"><strong>${ferry.name}</strong></div>
                        
                        <div class="middle-row">
                            <div class="left-info">
                                Active Time: ${ferry.active_time} mins<br>
                                Status: ${ferry.status}<br>
                                Operator: ${ferry.operator}
                            </div>
                            <div class="capacity-info">
                                Capacity: ${ferry.current_capacity} / ${ferry.max_capacity}
                            </div>
                        </div>

                        <div class="coordinates">
                            Longitude: ${ferry.longitude} | Latitude: ${ferry.latitude}
                        </div>
                    </div>
                `;

                ferryList.append(ferryElement);

                if (ferry.latitude && ferry.longitude) {
                    if (!markers[ferry.name]) {
                        addFerryMarker(ferry.latitude, ferry.longitude, ferry.name);
                    } else {
                        markers[ferry.name].setLatLng([ferry.latitude, ferry.longitude]);
                    }
                }
            });

            ferryList.scrollTop(scrollPos); // Restore scroll position
        },
        error: function() {
            ferryList.html('<p>Sorry, there was an error loading the ferry data.</p>');
        }
    });
}


    setInterval(fetchFerryData, 5000);
    fetchFerryData();

    function addFerryMarker(latitude, longitude, ferryName) {
    const ferryIcon = L.icon({
        iconUrl: 'ship.png', // Replace with your own icon path
        iconSize: [32, 32], // Resize to fit your style
        iconAnchor: [16, 16], // Anchor in the center
        popupAnchor: [0, -16]
    });

    const marker = L.marker([latitude, longitude], { icon: ferryIcon }).addTo(map)
        .bindTooltip(ferryName, {
            permanent: true,
            direction: 'top',
            className: 'ferry-label'
        });

    markers[ferryName] = marker;
}


    $(document).on('click', '.boat-card', function() {
        const lat = $(this).data('lat');
        const lng = $(this).data('lng');
        const name = $(this).find('.top strong').text();

        map.setView([lat, lng], 15);

        if (!markers[name]) {
            addFerryMarker(lat, lng, name);
        }
    });

    document.getElementById('map').style.borderRadius = '24px';
    document.getElementById('map').style.overflow = 'hidden';

    const navItems = document.querySelectorAll('.nav li');

    navItems.forEach(item => {
        item.addEventListener('click', function() {
            navItems.forEach(item => item.classList.remove('active'));
            item.classList.add('active');
            const page = item.getAttribute('data-page');
            if (page === 'dashboard') {
                window.location.href = 'Dashboard.php';
            } else if (page === 'analytics') {
                window.location.href = 'analytics.php';
            } else if (page === 'tracking') {
                window.location.href = 'tracking.php';
            } else if (page === 'ferrymngt') {
                window.location.href = 'ferrymngt.php';
            } else if (page === 'routeschedules') {
                window.location.href = 'template.php';
            } else if (page === 'Users') {
                window.location.href = 'template.php';
            }
        });
    });
    

    function fetchStatsData() {
    $.ajax({
        url: 'getStats.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            const statBoxes = document.querySelectorAll('.stat-box');

            if (statBoxes.length >= 4) {
                statBoxes[0].querySelector('p').textContent = data.total_passengers;
                statBoxes[1].querySelector('p').textContent = data.active_passes;
                statBoxes[2].querySelector('p').textContent = data.active_ferries;
                statBoxes[3].querySelector('p').textContent = data.occupancy_percentage + '%';
            }
        },
        error: function() {
            console.error('Failed to fetch stats');
        }
    });
}

setInterval(fetchStatsData, 1000); // every 5 seconds
fetchStatsData(); // also run immediately
