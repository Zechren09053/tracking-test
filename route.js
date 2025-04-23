window.onload = function () {
    var pasigRiverRoute = [
        [14.5550, 121.0731],
        [14.5559, 121.0711],
        [14.5565, 121.0687],
        [14.5567, 121.0680],
        [14.5581, 121.0669],
        [14.5597, 121.0664],
        [14.5610, 121.0650],
        [14.5614, 121.0631],
        [14.5622, 121.0614],
        [14.5644, 121.0592],
        [14.5653, 121.0558],
        [14.5661, 121.0536],
        [14.5678, 121.0511],
        [14.5683, 121.0492],
        [14.5683, 121.0470]
    ];

    var pasigRiverRoute2 = [
        [14.5581, 121.0669],
        [14.5581, 121.0681],
        [14.5586, 121.0703],
        [14.5597, 121.0721],
        [14.5620, 121.0732],
        [14.5667, 121.0736],
        [14.5700, 121.0739],
        [14.5714, 121.0743],
        [14.5754, 121.0775],
        [14.5778, 121.0803]
    ];

    const riverRoute = L.polyline(pasigRiverRoute, {
        color: 'blue',
        weight: 4,
        opacity: 0.7,
        smoothFactor: 1
    }).addTo(map);

    const riverRoute2 = L.polyline(pasigRiverRoute2, {
        color: 'green',
        weight: 4,
        opacity: 0.7,
        smoothFactor: 1
    }).addTo(map);

    map.fitBounds([riverRoute.getBounds(), riverRoute2.getBounds()]);
};
