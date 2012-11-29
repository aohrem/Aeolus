function mouseOver(element) {
	document.getElementById(element).src='img/' + element + '_over.png';
	document.getElementById(element + '_over').style.visibility = 'visible';
}

function mouseOut(element) {
	document.getElementById(element).src='img/' + element + '.png';
	document.getElementById(element + '_over').style.visibility = 'hidden';
}