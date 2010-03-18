var ui = 'ui/html/';
var dev = true;

/* работа с цветом */
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

/* шестнадцатиричные строки */
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

/* кукисы
todo: removeCookie
*/
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


// Convert all applicable characters to HTML entities
function htmlentities(s) {
    var div = document.createElement('div');
    var text = document.createTextNode(s);
    div.appendChild(text);
    return div.innerHTML;
}

/* в див с айдишником лог добавляет строку или объект со всеми его свойствами 
   делит содержимое дива на строки \н и оставляет 30 с конца
*/
function log(str) {
    if (typeof(str) == 'object') {
        var o = '';
        for(var i in str) o += i + ':' + str[i]+',';
        str = o;
    }
	$('#log').append('<div>' + str + '</div>');
	$('#log').html($('#log > div').slice(-25));
}

var updateListeners = [];
/*
это конечно легко и просто можно понавешать тыщу функций которые будут вызывать себя каждую
секунду каждая из них, но бля сервак просто ахуеет от одного такого пользователя который
с каждой из своих 10 ебучих функций будет посылать один отдельный запрос хДДД

вощем есть единая куита для апдейта. каждая куйня которая хочет периодически получать
данные от сервера по своему вопросу регистрируется вот тут
сообщая желаемый интервал обновления (не сделано - нада сделать, а можно и не делать)
а также указывает функцию которую нада вызвать чтобы добавить параметров в запрос
и функцию которую вызвать когда придёт ответ. ответ будет общий, так что каждая
куита должна из общего ответа выбрать чтото своё
*/
function registerUpdateListener(interval, request, callback) {
	updateListeners.push({interval:interval, request:request, callback:callback});
}
/* собственно единая обновлялка
чат хочет сделать запрос а=гетчат и свой параметр чатайди=число
геймлист запрос а=геймс без параметров
таким образом это всё можно объеденить в а=геймс&б=гетчат&чатайди=123
*/
function update() {
	if (updateListeners.length) {
	var seq = 'abcdef';
	var request = {};
	for(var i in updateListeners) {
		var req = updateListeners[i].request();
		req[seq.charAt(i)] = req['a'];
		request = jQuery.extend(req, request);
	}
	//log(request);
	$.getJSON("game.php", request, function fn(data) {
	//log(data);
		for(var i in updateListeners) {
			updateListeners[i].callback(data);
		}
	});
	}
	setTimeout('update()', 5000);
}


/*
	функция всех функций в общем файле жс
*/
$(function() {
	$('body').setTemplateURL(ui + 'templates.tpl');
	$('body').processTemplate({});
    //chat_update();
    checklogin();
	update();
	$('#log').ajaxSuccess(function(e,d) {$(this).append(d.responseText + '<br/>');});
	$('#log').ajaxError(function(e,d) {$(this).append(d.responseText);});
});
