function shownewplayer() {
    b('x').innerHTML = req('', ui + 'loginnew.html');
}
function login() {
    var email = b('email').value;
    var password = b('password').value;
    var key = getCookie('key');
log('key'+key);
    password = MD5(password + key);
    var t = req('a=login&email='+email+'&password='+password);
    if (t != 'OK') alert(t);
    else {
        b('x').innerHTML = req('', ui + 'games.html');
        refresh();
    }
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
    if (playerid == 0) b('x').innerHTML = req('', ui + 'login.html');
    else {
        b('x').innerHTML = req('', ui + 'games.html');
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
