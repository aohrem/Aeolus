// script provides a function to search a location using the OpenStreetMap Nominatim API
function location_search() {
    var input = document.getElementById("location");

    // get a JSON object by Nominatim with 6 results that are put into a list which is displayed below the searchbar
    // if there are no results, show an error message to prompt the user to specify his request
    $.getJSON('http://nominatim.openstreetmap.org/search?format=json&limit=6&q=' + input.value, function(data) {
        var items = [];
        $.each(data, function (key, val) {
            // clicking a result the map pans to the desired location with an adjusted zoom level
            items.push('<li><a href="#" onclick="choose_location(' + val.boundingbox[0] + ', ' + val.boundingbox[1] + ', ' + val.boundingbox[2] + ', ' + val.boundingbox[3] + '); setResultsHidden(); return false;">' + val.display_name + '</a></li>');
        });
        $('#results').empty();

        // add results to a list that suggests possible matches
        if (items.length != 0) {
            $('<ul/>', {
                'class': 'result-list',
                html: items.join('')
            }).appendTo('#results');
        }
        // prompt to ask the user to specify his search
        else {
            $('<p>', { html: "[[no_result]]" }).appendTo('#results');
        }
        setResultsVisible();
    });
}

// function to pan to the bounding box that fits to the chosen result and adjusting the best fitting zoomlevel
function choose_location(swLat, neLat, swLon, neLon) {
    // create a new boundingbox for leaflet from json output
    var southWest = new L.latLng(swLat, swLon);
    var northEast = new L.LatLng(neLat, neLon);
    var bounds = new L.LatLngBounds(southWest, northEast);

    // set a map view that contains the given geographical bounds with the maximum zoom level possible
    map.fitBounds(bounds);
}

function setResultsVisible() {
    document.getElementById("results_frame").style.visibility = "visible";
}

function setResultsHidden() {
    document.getElementById("results_frame").style.visibility = "hidden";
}