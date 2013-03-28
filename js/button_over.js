function buttonOver(button, src) {
    var button = document.getElementById(button);
    button.src = 'img/' + src + '.png';
}

function downloadOver(id) {
    var button = document.getElementById(id + '_button');
    button.src = 'img/download_hover.png';
    var text = document.getElementById(id + '_text');
    text.setAttribute("class", "text texthover");
}

function downloadOut(id) {
    var button = document.getElementById(id + '_button');
    button.src = 'img/download.png';
    var text = document.getElementById(id + '_text');
    text.setAttribute("class", "text");
}