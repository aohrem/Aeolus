function mouseOver(element) {
	document.getElementById(element).src='img/' + element + '_over.png';
	document.getElementById(element + '_over').style.visibility = 'visible';
}

function mouseOut(element) {
	document.getElementById(element).src='img/' + element + '.png';
	document.getElementById(element + '_over').style.visibility = 'hidden';
}

function registerClick() {
	document.getElementById('register_over').style.visibility = 'hidden';
	document.getElementById('register_click').style.display = 'block';
}

function registerClose() {
	document.getElementById('register_click').style.display = 'none';
}