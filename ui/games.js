var dorefresh = true;
var x = false;
var connectionProblem = false;
var gameListTemplate = '';
function refresh() {
    c = color2rgb(b('pro').style.color ? b('pro').style.color : '#00ff00');
    switch (c.g) { case 0xff: c.g = 0x88; break; case 0x88: c.g = 0x44; break; case 0x44: c.g = 0xff; }
    b('pro').style.color = rgb2color(c);
    if (!getMyGame()) listGames();
    if (dorefresh)
        setTimeout('refresh();', 10000);
}
function listGames() {
    t = req('a=listgames');
    if (t == 'No Games') {
        b('gamelist').innerHTML = 'No Games';
    } else {
        if (gameListTemplate == '') gameListTemplate = req('', 'ui/gamelist.html');
        tt = t.split("\n");
        o = '';
        for(var i in tt) {
            game = tt[i].split(':');
            tmp = gameListTemplate;
            tmp = tmp.replace('{GAME}', game[1]).replace('{CREATOR}', game[0]);
            o += tmp;
        }
        b('gamelist').innerHTML = o;
    }
}
function getMyGame() {
    t = req('a=mygame');
    if (t == 'No Game') return false;
    b('newgame').innerHTML = '';
    tt = t.split(':');
    o = 'mygame:';
    for(var i in tt) o += ' ' + tt[i];
    b('gamelist').innerHTML = o;
    return true;
}
function newgame() {
    var name = b('gamename').value;
    var maxplayers = b('maxplayers').value;
    var r = req('a=newgame&name='+name+'&maxplayers='+maxplayers);
    if (r != 'OK') alert(r);
}
function joinGame(creator) {
    var t = req('?a=joingame&creator='+creator);
    if (t != 'OK') alert(t);
}
