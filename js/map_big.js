var map = L.map('map_big').setView([51.962944, 7.628694,], 13);	
var bgMarker, downloadMarker, diagMarker, tableMarker, menuDisplayed = false;

L.tileLayer('http://{s}.tile.cloudmade.com/cc2b230c7e24424eb2d4b2928fceba79/997/256/{z}/{x}/{y}.png', {
	attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a>, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Image data &copy; <a href="http://cloudmade.com">CloudMade</a>',
	maxZoom: 18
}).addTo(map);

function addEgg(lat, lon, feedID, type, color, sensor, coValue, no2Value, tempValue, humValue, title){
	var coValue = 123;
	var no2Value = 1234;
	var tempValue = 22;
	var humValue = 54;
	var eggIcon = L.icon({
				iconUrl: 	'img/map_eggs/' + type + sensor + '_' + color + '.png',
				iconSize:	[33, 35],
				iconAnchor:	[16.5, 17.5]
			});
	var eggMarker = L.marker([lat, lon], {icon: eggIcon}, {title: feedID}).addTo(map);
	eggMarker.on('click', function(e){
			openCircleMenu(lat, lon, feedID);
			document.getElementById("eggValueFeedId").innerHTML = "Air Quality Egg #" + feedID;
			document.getElementById("eggValueCo").innerHTML = "CO: " + coValue + " ppm";
			document.getElementById("eggValueNo2").innerHTML = "NO<sub>2</sub>: " + no2Value + " ppm";
			document.getElementById("eggValueTemp").innerHTML = "Temp: " + tempValue + "&deg;C";
			document.getElementById("eggValueHum").innerHTML = "Hum:" + humValue + "%";
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
			iconAnchor:	[75, 75],
			iconSize:	[150, 150]
		});
		var downloadIcon = L.icon({
			iconUrl:	'img/kreismenu_download.png',
			iconAnchor:	[30, -15],
			iconSize:	[60, 60]
		});
		var tableIcon = L.icon({
			iconUrl:	'img/kreismenu_tabelle.png',
			iconAnchor:	[0, 75],
			iconSize:	[75, 90]
		});
		var diagIcon = L.icon({
			iconUrl:	'img/kreismenu_diagramm.png',
			iconAnchor:	[75, 75],
			iconSize:	[75, 90]
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

function centermap(lat, lon, zoom){
	map.setView([lat, lon], zoom)
}

map.on('click', removeCircleMenu);