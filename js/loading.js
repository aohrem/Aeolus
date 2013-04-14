// shows the loading screen on top of the rest of the page
function loading() {
    document.getElementById('loading').style.display = 'block';
}

// hides the loading screen and stops loading a new page
function cancelLoading() {
    document.getElementById('loading').style.display = 'none';
    stop();
}