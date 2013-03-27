var map = L.map('map_big').setView([51.962944, 7.628694,], 13);	

L.tileLayer('http://{s}.tile.cloudmade.com/cc2b230c7e24424eb2d4b2928fceba79/997/256/{z}/{x}/{y}.png', {
	attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a>, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Image data &copy; <a href="http://cloudmade.com">CloudMade</a>',
	maxZoom: 18
}).addTo(map);

function addEgg(lat, lon, feedID, type, color, value, sensor){
	var eggIcon = L.icon({
				iconUrl: 	'img/map_eggs/' + type + sensor + '_' + color + '.png',
				iconSize:	[33, 35],
				iconAnchor:	[16.5, 17.5]
			});
	var eggMarker = L.marker([lat, lon], {icon: eggIcon}, {title: feedID}).addTo(map);
	eggMarker.on('click', function(e){
			openCircleMenu(lat, lon, feedID);
			alert("feedID: " + feedID + " | Wert: " + value);
		});
}

function openCircleMenu(lat, lon, feedID){
	var checkIcon = L.icon({
			iconUrl:	'img/kreismenu_check.png',
			iconAnchor:	[75, 75],
			iconSize:	[150, 52]
		});
		var tableIcon = L.icon({
			iconUrl:	'img/kreismenu_tabelle.png',
			iconAnchor:	[0, 21],
			iconSize:	[75, 96]
		});
		var diagIcon = L.icon({
			iconUrl:	'img/kreismenu_diagramm.png',
			iconAnchor:	[75, 21],
			iconSize:	[75, 96]
		});
		var closeIcon = L.icon({
			iconUrl:	'img/close.png',
			iconSize:	[10, 10],
			iconAnchor:	[5, 5]
		});
		var checkMarker = new L.Marker(
				[lat, lon], 
				{icon: checkIcon}
			).addTo(map);
		var tableMarker = new L.Marker(
				[lat, lon],
				{icon: tableIcon}
			).addTo(map);
		var diagMarker = new L.Marker(
				[lat, lon],
				{icon: diagIcon}
			).addTo(map);
		var closeMarker = new L.Marker(
				[lat, lon],
				{icon: closeIcon}
			).addTo(map);
		
		checkMarker.on('click', function(e){ alert("check" + feedID) });
		tableMarker.on('click', function(e){ alert("table" + feedID) });
		diagMarker.on('click', function(e){ alert("diag" + feedID) });
		closeMarker.on('click', function(e){
				map.removeLayer(checkMarker);
				map.removeLayer(diagMarker);
				map.removeLayer(tableMarker);
				map.removeLayer(closeMarker);
			});
}

function addPopup(lat, lon, type){
	var popup = L.popup()
		.setLatLng([lat, lon])
		//.setContent(type)
		.openOn(map);
}

//addPopup(51.962944, 7.628694, "Hallo");
