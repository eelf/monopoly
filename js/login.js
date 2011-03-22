function shownewplayer() {
	$('body').processTemplate({page:'loginnew'});
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
			$('body').processTemplate({page:'games'});
			registerUpdateListener(10000, gameListRequest, gameListListener);
			registerUpdateListener(10000, chatUpdateRequest, chatUpdateListener);
			//refresh();
		}
	});
}
function logout() {
	$.getJSON('game.php', {a: 'logout'}, function logoutC(logoutO) {
		if (logoutO['auth'] != 'OK') alert(logoutO['auth']);
		else window.location = "index.php"; // перезагружаем страницу, сессия то йок
	});
}
function checklogin() {
    playerid = getCookie('playerid');
    if (playerid == 0) //$.get(ui + 'login.html', function(html) { $('#x').html(html); });
		$('body').processTemplate({page:'login'});
    else {
		$('body').processTemplate({page:'games'});
		//$.get(ui + 'games.html', function(html) { $('#x').html(html); });
		registerUpdateListener(10000, gameListRequest, gameListListener);
		registerUpdateListener(10000, chatUpdateRequest, chatUpdateListener);
		//refresh();
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
