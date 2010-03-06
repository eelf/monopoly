var ui = 'ui/';

function dec2rgb(d) {
    var red = (d & 0xff0000) >> 16;
    var green = (d & 0x00ff00) >> 8;
    var blue = (d & 0x0000ff);
    return {r:red, g:green, b:blue};
}
function rgb2dec(rbg) {
    return rgb.b + (rgb.g << 8) + (rgb.r << 16);
}
function rgb2color(rgb) {
    var color = dec2hex(rgb.r << 16) + dec2hex(rgb.g << 8) + dec2hex(rgb.b);
    while (color.length < 6) color = '0' + color;
    return '#' + color;
}
function color2rgb(color) {
    return dec2rgb(hex2dec(color.substring(1)));
}

function dec2hex(d) {
    var r = '';
    while(d > 0) {
        r = '0123456789abcdef'.charAt(d & 0xf) + r;
        d = d >> 4;
    }
    return r;
}
function hex2dec(s) {
    var b = 1;
    var r = 0;
    for(var i = s.length - 1; i >= 0; i--) {
        r += '0123456789abcdef'.indexOf(s.charAt(i)) * b;
        b = b << 4;
    }
    return r;
}

function getCookie(n) {
	var c = " " + document.cookie;
	var s = " " + n + "=";
	var r = null;
	var o = 0;
	var e = 0;
	if (c.length > 0) {
		o = c.indexOf(s);
		if (o != -1) {
			o += s.length;
			e = c.indexOf(";", o)
			if (e == -1)
				e = c.length;
			r = unescape(c.substring(o, e));
		}
	}
	return(r);
}
function b(id) {
    return document.getElementById(id);
}
function log(str) {
    if (typeof(str) == 'object') {
        var o = '';
        for(var i in str) o += i + ':' + str[i];
        str = o;
    }
    b('log').innerHTML = b('log').innerHTML + str + '<br>';
}
function req(r, server) {
    if (server == undefined) server = 'game.php?';
    x.open('GET', server + r, false);
    x.send(null);
    if (x.status != 200) {
        connectionProblem = true;
        return '';
    }
    return x.responseText;
}
function pro() {
    var s = '-\\|/';
    b('pro').innerHTML = s.charAt((s.indexOf(b('pro').innerHTML) + 1) % s.length);
    setTimeout('pro();', 500);
}

window.onload = function() {
    x = new XMLHttpRequest();
    pro();
    checklogin();
}
