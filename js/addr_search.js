/*
Script provides a function to search a location by using Nominatim from OpenStreetMap
*/

function location_search(){
var input = document.getElementById("location");

/*
Getting a JSON object by Nominatim with 6 results that are put into a list 
which is displayed below the searchbar at the site of the big map
If there are no results an error message is being displayed to prompt the 
user to specify his request
*/

$.getJSON('http://nominatim.openstreetmap.org/search?format=json&limit=6&q=' + input.value, function(data){
var items = [];
$.each(data, function(key, val) {
  items.push(
    "<li><a href='#' onclick='choose_location(" + // by clicking on a result the map pans to the desired location
    val.lat + ", " + val.lon + ");setResultsHidden();return false;'>" + val.display_name +
    '</a></li>'
  );
});
$('#results').empty();
    /*
	Adding results to a list that suggests possible matches
	*/
	if (items.length != 0) {
      $('<p>', { html: "Suchergebnisse:" }).appendTo('#results');
      $('<ul/>', {
        'class': 'result-list',
        html: items.join('')
      }).appendTo('#results');
    } 
		else {
		$('<p>', { html: "Keine &Uuml;bereinstimmung: bitte pr&auml;zisieren Sie ihre Anfrage!" })
		.appendTo('#results'); //prompt to asks the user to specify his search
    }
    setResultsVisible();
  });
}

/*
Help function to pan to a location
*/
function choose_location(lat, lng){
var location = new L.LatLng(lat, lng);
map.panTo(location);
}

function setResultsVisible(){
	document.getElementById("results").style.visibility = "visible";
}

function setResultsHidden(){
	document.getElementById("results").style.visibility = "hidden";
}