function shownewplayer() {
    b('x').innerHTML = req('', ui + 'loginnew.html');
}
function login() {
    var email = $('#email').val();
    var password = $('#password').val();
    var key = getCookie('key');
    var kpassword = MD5(password + key);
	// function loginCallback(loginObject)
	$.getJSON('game.php', {a: 'login', email: email, password: kpassword}, function loginC(loginO) {
		if (loginO['auth'] != 'OK') alert(loginO['auth']);
		else {
			$.get(ui + 'games.html', function(html) { $('#x').html(html); });
			refresh();
		}
	});
}
function logout() {
	var t = req('a=logout');
    if (t != 'OK') alert(t);
    else {
        b('x').innerHTML = req('', ui + 'login.html');
        refresh();
    }	
}
function checklogin() {
    playerid = getCookie('playerid');
    if (playerid == 0) $.get(ui + 'login.html', function(html) { $('#x').html(html); });
    else {
		$.get(ui + 'games.html', function(html) { $('#x').html(html); });
        refresh();
    }
}
function newplayer() {
    email = b('email').value;
    password = b('password').value;
    name = b('name').value;
    t = req('a=register&email='+email+'&password='+password+'&name='+name);
    if (t != 'OK') alert(t);
    else b('x').innerHTML = req('', ui + 'games.html');
}
/*
//ajax func
function login_j(a_email, a_password) {
    var key = getCookie('key');
//log('key'+key);
    password = MD5(a_password + key);
	$tmp = {arg: 'login', email: a_email, password: password};
//log('arg:'+$tmp.arg+' email:'+$tmp.email+' pass:'+$tmp.password);
		$.ajax({
			type: 'POST', //по умолчанию GET
    		url: 'game.php', // указываем URL и
    		dataType: 'json', // тип загружаемых данных
    		data: "data="+$.toJSON($tmp),//отправляемая строка на сервер
    		success: function (data, textStatus) { // вешаем свой обработчик на функцию success
//log(data.text+'  '+data.playerid);
   		 	},
   		 	error: function (XMLHttpRequest, errcode) { // вешаем свой обработчик на функцию error
					$("body").before('<b> Error: '+errcode+' in '+XMLHttpRequest.responseText+'</b>');
   		 	}
		});
}
*/
