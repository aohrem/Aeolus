var map = L.map('map_big').setView([51.962944, 7.628694,], 13);	

L.tileLayer('http://{s}.tile.cloudmade.com/cc2b230c7e24424eb2d4b2928fceba79/997/256/{z}/{x}/{y}.png', {
	attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a>, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Image data &copy; <a href="http://cloudmade.com">CloudMade</a>',
	maxZoom: 18
}).addTo(map);

function addEgg(lat, lon, feedID, type, color, value, sensor){
	var eggIcon = L.icon({
				iconUrl: 'img/map_eggs/' + type + '_' + sensor + '_' + color + '.png'
			});
	var eggMarker = L.marker([lat, lon], {icon: eggIcon}, {title: feedID}).addTo(map);
	eggMarker.on('click', function(e){
			alert("feedID: " + feedID + " | Wert: " + value);
		});
}

function addPopup(lat, lon, type){
	var popup = L.popup()
		.setLatLng([lat, lon])
		//.setContent(type)
		.openOn(map);
}

//addPopup(51.962944, 7.628694, "Hallo");
