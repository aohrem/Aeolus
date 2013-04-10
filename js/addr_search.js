/*
Script provides a function to search a location by using Nominatim from OpenStreetMap
*/

function location_search() {
	var input = document.getElementById("location");

/*
Getting a JSON object by Nominatim with 6 results that are put into a list 
which is displayed below the searchbar at the site of the big map
If there are no results an error message is being displayed to prompt the 
user to specify his request
*/

	$.getJSON('http://nominatim.openstreetmap.org/search?format=json&limit=6&q=' + input.value, function(data) {
		var items = [];
		$.each(data, function(key, val) {
			items.push(
				"<li><a href='#' onclick='choose_location(" + // by clicking on a result the map pans to the desired location with an adjusted zoom level
				val.boundingbox[0] + ", " + val.boundingbox[1] + " , " + val.boundingbox[2] + " , " + val.boundingbox[3] + ");setResultsHidden();return false;'>" + val.display_name +
				'</a></li>'
			);
		});
		$('#results').empty();
		/*
		Adding results to a list that suggests possible matches
		*/
		if (items.length != 0) {
			//$('<p>', { html: "Suchergebnisse:" }).appendTo('#results');
			$('<ul/>', {
				'class': 'result-list',
				html: items.join('')
			}).appendTo('#results');
		} 
		else {
			$('<p>', { html: "[[no_result]]" })
			.appendTo('#results'); //prompt to asks the user to specify his search
		}
		setResultsVisible();
	});
}

/*
function to pan to the boundingbox that fits to the chosen result and adjusting the best fitting zoomlevel
*/
function choose_location(swLat, neLat, swLon, neLon) {
	/*
	creating a new boundingbox for leaflet from json output
	*/
	var southWest = new L.latLng(swLat, swLon);
	var northEast = new L.LatLng(neLat, neLon);
	var bounds 	  = new L.LatLngBounds(southWest, northEast);
	map.fitBounds(bounds); //Sets a map view that contains the given geographical bounds with the maximum zoom level possible.
	//map.setZoom(map.getBoundsZoom(bounds));
	// var i = (map.getBoundsZoom(bounds));
	// alert(i); 
}

function setResultsVisible() {
	document.getElementById("results_frame").style.visibility = "visible";
}

function setResultsHidden() {
	document.getElementById("results_frame").style.visibility = "hidden";
}