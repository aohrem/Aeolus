function loading() {
    document.getElementById('loading').style.display = 'block';
}

function cancelLoading() {
    document.getElementById('loading').style.display = 'none';
    stop();
}