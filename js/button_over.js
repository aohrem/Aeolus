// replaces the image of a button on mouse over
function buttonOver(button, src) {
    var button = document.getElementById(button);
    button.src = 'img/' + src + '.png';
}

// replaces the image of a download button and changes the text style on mouse over
function downloadOver(id) {
    var button = document.getElementById(id + '_button');
    button.src = 'img/download_hover.png';
    var text = document.getElementById(id + '_text');
    text.setAttribute("class", "text texthover");
}

// replaces the image of a download button and changes the text style on mouse out
function downloadOut(id) {
    var button = document.getElementById(id + '_button');
    button.src = 'img/download.png';
    var text = document.getElementById(id + '_text');
    text.setAttribute("class", "text");
}