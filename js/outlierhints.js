// shows/hides an outlier note in the table view on mouse over
function outlierNote(id) {
    var hintbox = document.getElementById(id);
    if (hintbox.style.visibility == 'visible') {
        hintbox.style.visibility = 'hidden';
    }
    else {
        hintbox.style.visibility = 'visible';
    }
}