// replaces the image of a navigation button and shows the describtion popup on mouse over
function mouseOver(element) {
    document.getElementById(element).src='img/' + element + '_over.png';
    document.getElementById(element + '_over').style.visibility = 'visible';
}

// replaces the image of a navigation button and hides the describtion popup on mouse out
function mouseOut(element) {
    document.getElementById(element).src='img/' + element + '.png';
    document.getElementById(element + '_over').style.visibility = 'hidden';
}

// shows the register popup
function registerClick() {
    document.getElementById('register_over').style.visibility = 'hidden';
    document.getElementById('register_click').style.display = 'block';
}

// hides the register popup
function registerClose() {
    document.getElementById('register_click').style.display = 'none';
}