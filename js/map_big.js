var map = L.map('map_big').setView([51.962944, 7.628694,], 13);	

L.tileLayer('http://{s}.tile.cloudmade.com/cc2b230c7e24424eb2d4b2928fceba79/997/256/{z}/{x}/{y}.png', {
	attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a>, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Image data &copy; <a href="http://cloudmade.com">CloudMade</a>',
	maxZoom: 18
}).addTo(map);

function addEgg(lat, lon, feedID, type, color, sensor){
	var eggIcon = L.icon({
				iconUrl: 	'img/map_eggs/' + type + sensor + '_' + color + '.png',
				iconSize:	[33, 35],
				iconAnchor:	[16.5, 17.5]
			});
	var eggMarker = L.marker([lat, lon], {icon: eggIcon}, {title: feedID}).addTo(map);
	eggMarker.on('click', function(e){
			openCircleMenu(lat, lon, feedID);
		});
}

function openCircleMenu(lat, lon, feedID){
		var bgIcon = L.icon({
			iconUrl:	'img/kreismenu_hintergrund.png',
			iconAnchor:	[75, 75],
			iconSize:	[150, 150]
		});
		var checkIcon = L.icon({
			iconUrl:	'img/kreismenu_check.png',
			iconAnchor:	[35, 75],
			iconSize:	[70, 55]
		});
		var tableIcon = L.icon({
			iconUrl:	'img/kreismenu_tabelle.png',
			iconAnchor:	[-3, 15],
			iconSize:	[72, 90]
		});
		var diagIcon = L.icon({
			iconUrl:	'img/kreismenu_diagramm.png',
			iconAnchor:	[75, 15],
			iconSize:	[72, 90]
		});
		var closeIcon = L.icon({
			iconUrl:	'img/close.png',
			iconSize:	[10, 10],
			iconAnchor:	[5, 5]
		});
		var bgMarker = new L.Marker([lat, lon], {
				icon: bgIcon,
				zIndexOffset: 200
			}).addTo(map);
		var checkMarker = new L.Marker([lat, lon], {
				icon: checkIcon,
				zIndexOffset: 200
			}).addTo(map);
		var tableMarker = new L.Marker([lat, lon], {
				icon: tableIcon,
				zIndexOffset: 200
			}).addTo(map);
		var diagMarker = new L.Marker([lat, lon], {
				icon: diagIcon,
				zIndexOffset: 200
			}).addTo(map);
		var closeMarker = new L.Marker([lat, lon], {
				icon: closeIcon,
				zIndexOffset: 200
			}).addTo(map);
		
		checkMarker.on('click', function(e){ alert("check" + feedID) });
		diagMarker.on('click', function(e){ location.href='index.php?s=diagram&fid=' + feedID + '&lang=de';   });
		tableMarker.on('click', function(e){ location.href='index.php?s=table&fid=' + feedID + '&timeframe=6h&interpolateoutliers=false&sensitivity=2&lang=de'; });
		closeMarker.on('click', function(e){
				map.removeLayer(bgMarker);
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
