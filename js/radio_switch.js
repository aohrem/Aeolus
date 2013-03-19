function radioSwitch(radio) {
    var radioButton = document.getElementById(radio);

    if (radioButton.checked) {
        radioButton.checked = false;
    }
    else {
        radioButton.checked = true;
    }
}