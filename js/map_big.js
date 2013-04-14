var lat, lon, zoom;
var bgMarker, downloadMarker, diagMarker, tableMarker, menuDisplayed = false;
var bgDiagMarker, coMarker, no2Marker, humMarker, tempMarker, diagMenuDisplayed  = false;
var lanuvCluster = new L.MarkerClusterGroup({showCoverageOnHover: false});

var map = L.map('map_big', { keyboard: false });
centermap();

L.tileLayer('http://{s}.tile.cloudmade.com/cc2b230c7e24424eb2d4b2928fceba79/997/256/{z}/{x}/{y}.png', {
    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a>, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Image data &copy; <a href="http://cloudmade.com">CloudMade</a>',
    maxZoom: 18
}).addTo(map);

function addEgg(lat, lon, feedID, color, sensor, coValue, no2Value, tempValue, humValue, title){
    var eggIcon = L.icon({
                iconUrl:     'img/map_eggs/eggicon' + sensor + '_' + color + '.png',
                iconSize:    [33, 35],
                iconAnchor:    [16.5, 17.5]
            });
    var eggMarker = L.marker([lat, lon], {icon: eggIcon, zIndexOffset: 20, title: feedID}).addTo(map);
    eggMarker.on('click', function(e){
            writeCookie();
            openCircleMenu(lat, lon, feedID);
            document.getElementById("eggValueFeedId").innerHTML = title;
            document.getElementById("eggValueCo").innerHTML = coValue + " ppm";
            document.getElementById("eggValueNo2").innerHTML = no2Value + " ppm";
            document.getElementById("eggValueTemp").innerHTML = tempValue + " &deg;C";
            document.getElementById("eggValueHum").innerHTML = humValue + " %";
            document.getElementById("eggValue").style.visibility = "visible";
            document.getElementById("lanuvValue").style.visibility = "hidden";
        });    
}

function openCircleMenu(lat, lon, feedID){
        if (menuDisplayed == true){
            removeCircleMenu();
            menuDisplayed = false;
        }
        var bgIcon = L.icon({
            iconUrl:    'img/kreismenu/kreismenu.png',
            iconAnchor:    [75, 73],
            iconSize:    [150, 150]
        });
        var downloadIcon = L.icon({
            iconUrl:    'img/kreismenu/kreismenu_download.png',
            iconAnchor:    [30, -17],
            iconSize:    [60, 60]
        });
        var tableIcon = L.icon({
            iconUrl:    'img/kreismenu/kreismenu_tabelle.png',
            iconAnchor:    [-25, 73],
            iconSize:    [50, 90]
        });
        var diagIcon = L.icon({
            iconUrl:    'img/kreismenu/kreismenu_diagramm.png',
            iconAnchor:    [75, 73],
            iconSize:    [50, 90]
        });
        bgMarker = new L.Marker([lat, lon], {
                icon: bgIcon,
                zIndexOffset: 200
            }).addTo(map);
        downloadMarker = new L.Marker([lat, lon], {
                icon: downloadIcon,
                zIndexOffset: 200
            }).addTo(map);
        tableMarker = new L.Marker([lat, lon], {
                icon: tableIcon,
                zIndexOffset: 200
            }).addTo(map);
        diagMarker = new L.Marker([lat, lon], {
                icon: diagIcon,
                zIndexOffset: 200
            }).addTo(map);
        
        menuDisplayed = true;
        
        downloadMarker.on('click', function (e) { location.href = 'index.php?s=download&fid=' + feedID + '&timeframe=6h&interpolateoutliers=false&sensitivity=2&lang={lang}'; loading(); });
        diagMarker.on('click', function(e){ openDiagMenu(lat, lon, feedID) });
        tableMarker.on('click', function (e) { location.href = 'index.php?s=table&fid=' + feedID + '&timeframe=6h&interpolateoutliers=false&sensitivity=2&lang={lang}'; loading(); });
}

function removeCircleMenu(){
    map.removeLayer(bgMarker);
    map.removeLayer(downloadMarker);
    map.removeLayer(diagMarker);
    map.removeLayer(tableMarker);
    document.getElementById("eggValue").style.visibility = "hidden";
    if (diagMenuDisplayed == true){
        removeDiagMenu();
        diagMenuDisplayed = false;
    }
}

function openDiagMenu(lat, lon, feedID){
        if (diagMenuDisplayed == true){
            removeDiagMenu();
            diagMenuDisplayed = false;
        }
        else{
            var bgDiagIcon = L.icon({
                iconUrl:    'img/kreismenu/kreismenu_diagramm_aussen.png',
                iconAnchor:    [150, 150],
                iconSize:    [147, 223]
            });
            var coIcon = L.icon({
                iconUrl:    'img/kreismenu/kreismenu_diagramm_aussen_co.png',
                iconAnchor:    [60, 150],
                iconSize:    [57, 50]
            });
            var no2Icon = L.icon({
                iconUrl:    'img/kreismenu/kreismenu_diagramm_aussen_no2.png',
                iconAnchor:    [115, 100],
                iconSize:    [55, 35]
            });
            var tempIcon = L.icon({
                iconUrl:    'img/kreismenu/kreismenu_diagramm_aussen_temp.png',
                iconAnchor:    [140, 50],
                iconSize:    [50, 45]
            });
            var humIcon = L.icon({
                iconUrl:    'img/kreismenu/kreismenu_diagramm_aussen_hum.png',
                iconAnchor:    [140, -5],
                iconSize:    [50, 50]
            });
            bgDiagMarker = new L.Marker([lat, lon], {
                    icon: bgDiagIcon,
                    zIndexOffset: 190
                }).addTo(map);
            coMarker = new L.Marker([lat, lon], {
                    icon: coIcon,
                    zIndexOffset: 200
                }).addTo(map);
            no2Marker = new L.Marker([lat, lon], {
                    icon: no2Icon,
                    zIndexOffset: 200
                }).addTo(map);
            humMarker = new L.Marker([lat, lon], {
                    icon: humIcon,
                    zIndexOffset: 200
                }).addTo(map);
            tempMarker = new L.Marker([lat, lon], {
                    icon: tempIcon,
                    zIndexOffset: 200
                }).addTo(map);
            
            diagMenuDisplayed = true;
            
            coMarker.on('click', function (e) { location.href = 'index.php?s=diagram&fid=' + feedID + '&lang={lang}&sensor=co'; loading(); });
            no2Marker.on('click', function (e) { location.href = 'index.php?s=diagram&fid=' + feedID + '&lang={lang}&sensor=no2'; loading(); });
            humMarker.on('click', function (e) { location.href = 'index.php?s=diagram&fid=' + feedID + '&lang={lang}&sensor=humidity'; loading(); });
            tempMarker.on('click', function (e) { location.href = 'index.php?s=diagram&fid=' + feedID + '&lang={lang}&sensor=temperature'; loading(); });
        }
}

function addLanuv(lat, lon, code, city, street, temp, no2, no, so2, pm10, ozone){
     var lanuvIcon = L.icon({
                iconUrl:     'img/lanuv-marker.png',
                iconSize:    [35, 37],
                iconAnchor:    [17.5, 26]
            });
    var lanuvMarker = L.marker([lat, lon], {icon: lanuvIcon, zIndexOffset: 10, title: code});
    lanuvCluster.addLayer(lanuvMarker);
    map.addLayer(lanuvCluster);
    lanuvMarker.on('click', function(e){
        writeCookie();
        loading();
        location.href = 'index.php?s=map&lang={lang}&lanuv=true&lanuvStation=' + code;
        });    
}

function removeLanuvTable(){
        document.getElementById("lanuvValue").style.visibility = "hidden";
}

function removeDiagMenu(){
    map.removeLayer(bgDiagMarker);
    map.removeLayer(coMarker);
    map.removeLayer(no2Marker);
    map.removeLayer(humMarker);
    map.removeLayer(tempMarker);
}

function centermap(){
    //read from cookie:
    lat = readCookie("x");
    if (lat == null){
        lat = 35;
    }
    lon = readCookie("y");
    if (lon == null){
        lon = -22;
    }
    zoom = readCookie("zoom");
    if (zoom == null){
        zoom = 2;
    }
    
    center = new L.LatLng(lat, lon);
    map.setView(center, zoom)
}

function readCookie(tag) {
   var cookie = document.cookie;

   var posTag = cookie.indexOf("; " + tag + "=");
   if (posTag == -1) {
      if (cookie.indexOf(tag + "=") == 0) posTag = 0;
      else return null;
   }

   var valueStart = cookie.indexOf("=", posTag)+1;
   var valueEnd = cookie.indexOf(";", posTag+1);
   if (valueEnd == -1) valueEnd = cookie.length;

   var value = cookie.substring(valueStart, valueEnd);
   return unescape(value);
}

function writeCookie(){
    var x, y, zoom;
    x = map.getCenter().lat;
    y = map.getCenter().lng;
    zoom = map.getZoom();
    
    document.cookie = "x=" + escape(x);
    document.cookie = "y=" + escape(y);
    document.cookie = "zoom=" + escape(zoom);
}

map.on('click', removeLanuvTable);
map.on('click', removeCircleMenu);
map.on('moveend', writeCookie);
map.on('zoomend', writeCookie);