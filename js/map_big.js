var lat, lon, zoom;
var map = L.map('map_big');	
centermap();
var bgMarker, downloadMarker, diagMarker, tableMarker, menuDisplayed = false;

L.tileLayer('http://{s}.tile.cloudmade.com/cc2b230c7e24424eb2d4b2928fceba79/997/256/{z}/{x}/{y}.png', {
	attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a>, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Image data &copy; <a href="http://cloudmade.com">CloudMade</a>',
	maxZoom: 18
}).addTo(map);

function addEgg(lat, lon, feedID, type, color, sensor, coValue, no2Value, tempValue, humValue, title){
	var eggIcon = L.icon({
				iconUrl: 	'img/map_eggs/' + type + sensor + '_' + color + '.png',
				iconSize:	[33, 35],
				iconAnchor:	[16.5, 17.5]
			});
	var eggMarker = L.marker([lat, lon], {icon: eggIcon}, {title: feedID}).addTo(map);
	eggMarker.on('click', function(e){
			writeCookie();
			openCircleMenu(lat, lon, feedID);
			document.getElementById("eggValueFeedId").innerHTML = title;
			document.getElementById("eggValueCo").innerHTML = coValue + " ppm";
			document.getElementById("eggValueNo2").innerHTML = no2Value + " ppm";
			document.getElementById("eggValueTemp").innerHTML = tempValue + " &deg;C";
			document.getElementById("eggValueHum").innerHTML = humValue + " %";
			document.getElementById("eggValue").style.visibility = "visible";
		});
}

function openCircleMenu(lat, lon, feedID){
		if (menuDisplayed == true){
			removeCircleMenu();
			menuDisplayed = false;
		}
		var bgIcon = L.icon({
			iconUrl:	'img/kreismenu.png',
			iconAnchor:	[75, 73],
			iconSize:	[150, 150]
		});
		var downloadIcon = L.icon({
			iconUrl:	'img/kreismenu_download.png',
			iconAnchor:	[30, -17],
			iconSize:	[60, 60]
		});
		var tableIcon = L.icon({
			iconUrl:	'img/kreismenu_tabelle.png',
			iconAnchor:	[-25, 73],
			iconSize:	[50, 90]
		});
		var diagIcon = L.icon({
			iconUrl:	'img/kreismenu_diagramm.png',
			iconAnchor:	[75, 73],
			iconSize:	[50, 90]
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
		
		downloadMarker.on('click', function(e){ location.href='index.php?s=download&fid=' + feedID + '&timeframe=6h&interpolateoutliers=false&sensitivity=2&lang=de';   });
		diagMarker.on('click', function(e){ location.href='index.php?s=diagram&fid=' + feedID + '&lang=de';   });
		tableMarker.on('click', function(e){ location.href='index.php?s=table&fid=' + feedID + '&timeframe=6h&interpolateoutliers=false&sensitivity=2&lang=de'; });
}

function removeCircleMenu(){
	map.removeLayer(bgMarker);
	map.removeLayer(downloadMarker);
	map.removeLayer(diagMarker);
	map.removeLayer(tableMarker);
	document.getElementById("eggValue").style.visibility = "hidden";
}

function centermap(){
	//read from cookie:
	lat = readCookie("x");
	if (lat == null){
		lat = 51.962944;
	}
	lon = readCookie("y");
	if (lon == null){
		lon = 7.628694;
	}
	zoom = readCookie("zoom");
	if (zoom == null){
		zoom = 5;
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

map.on('click', removeCircleMenu);