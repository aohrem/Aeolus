// create new map for the home page
var map = L.map('map_small', { keyboard: false }).setView([35, -22, ], 2);

// show copyright notes and set the maximum zoom level
L.tileLayer('http://{s}.tile.cloudmade.com/cc2b230c7e24424eb2d4b2928fceba79/997/256/{z}/{x}/{y}.png', {
    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a>, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Image data &copy; <a href="http://cloudmade.com">CloudMade</a>',
    maxZoom: 18
}).addTo(map);

// function to an air quality egg to the map using latitude, logitude and the feed id of the egg
function addEgg(lat, lon, feedID){
    var eggIcon = L.icon({
                iconUrl:     'img/map_eggs/eggicon_noval.png',
                iconSize:    [33, 35],
                iconAnchor:    [16.5, 17.5]
            });
    var eggMarker = L.marker([lat, lon], {icon: eggIcon}, {title: feedID}).addTo(map);
    eggMarker.on('click', openMap);
}

// function opens the big map on click on the small map and saves the current center and zoom level to a cookie
function openMap(){
    var x, y, zoom;
    x = map.getCenter().lat;
    y = map.getCenter().lng;
    zoom = map.getZoom();
    
    document.cookie = "x=" + escape(x);
    document.cookie = "y=" + escape(y);
    document.cookie = "zoom=" + escape(zoom);

    loading();
    location.href = 'index.php?s=map&lang={lang}';
}
map.on('click', openMap);