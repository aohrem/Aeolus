// scale factor
var f = 1.08;

// get diagram canvas
var diagram = document.getElementById("diagram");
var ctx = diagram.getContext("2d");

// color and style settings
ctx.strokeStyle = 'black';
ctx.lineCap = 'round';
ctx.fillStyle = 'black';

// y-axis arrow
ctx.beginPath();
ctx.moveTo(20 * f,10 * f);
ctx.lineTo(15 * f,25 * f);
ctx.lineTo(25 * f,25 * f);
ctx.lineTo(20 * f,10 * f);
ctx.closePath();
ctx.stroke();
ctx.fill();

// x and y-axis
ctx.moveTo(20 * f,10 * f);
ctx.lineTo(20 * f,325 * f);
ctx.moveTo(10 * f,315 * f);
ctx.lineTo(660 * f,315 * f);
ctx.stroke();

// x-axis arrow
ctx.beginPath();
ctx.moveTo(660 * f,315 * f);
ctx.lineTo(645 * f,310 * f);
ctx.lineTo(645 * f,320 * f);
ctx.lineTo(660 * f,315 * f);
ctx.closePath();
ctx.stroke();
ctx.fill();

// interval lines on y-axis
for ( var i = 275 * f; i >= (30 * f); i -= (40 * f) ) {
	ctx.moveTo(20 * f,i);
	ctx.lineTo(23 * f,i);
}
ctx.stroke();

// interval lines on x-axis
for ( var j = 60 * f; j <= (630 * f); j += (40 * f) ) {
	ctx.moveTo(j,315 * f);
	ctx.lineTo(j,312 * f);
}
ctx.stroke();

// draw graph
ctx.moveTo(20* f,240 * f);
ctx.lineTo(60 * f,200 * f);
ctx.lineTo(100 * f,250 * f);
ctx.lineTo(140 * f,290 * f);
ctx.lineTo(180 * f,210 * f);
ctx.stroke();

// axis labeling
ctx.font = f * 9 + 'pt Verdana';

// x-axis
var x_text = 'Zeit';
ctx.fillText(x_text, (640 - x_text.length * 6) * f, 330 * f);

// y-axis
var y_text = 'NO2 Konzentration';
ctx.rotate((Math.PI / 180) * 270);
ctx.fillText(y_text, (-30 - y_text.length * 7) * f, 16 * f);