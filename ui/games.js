var dorefresh = true;
var x = false;
var connectionProblem = false;
var gameListTemplate = '';
function refresh() {
	var c = color2rgb($('#proi').css('background-color'));
	c.g = {0xff:0xaa, 0xaa:0x66, 0x66:0x99, 0x99:0xdd, 0xdd:0xff}[c.g];
	$('#proi').css('background-color', rgb2color(c));

	$.getJSON('game.php', {a: 'games'}, function gamesC(games) {
		if (games['mygame']) {
			// скрыть форму новой игры
			$('#newgame').html('');
			// показать инфо про созданную игру и кнопки закрытия игры
			$('#gamelist').html(games['mygame']['creator'] + games['mygame']['name'] + 
				games['mygame']['maxplayers'] + games['mygame']['players']);
		
		} else if (games['games'].length) {
			if (gameListTemplate == '') gameListTemplate = req('', 'ui/gamelist.html');
			o = '';
			for(var i in games['games']) {
				tmp = gameListTemplate;
				tmp = tmp.replace('{GAME}', games['games'][i][1]).replace('{CREATOR}', games['games'][i][0]);
				o += tmp;
			}
			$('#gamelist').html(o);
		} else {
			$('#gamelist').text('No Games');
		}
	});
    if (dorefresh)
        setTimeout('refresh();', 10000);
}

/* тут тестил вызов события старта аякса но оно чтото не пашет :(
$('#log').ajaxStart(function() {
	alert('!!');
});
*/

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
