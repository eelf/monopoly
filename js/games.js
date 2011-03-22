var dorefresh = true;
var x = false;
var connectionProblem = false;
var gameListTemplate = '';

/* О.о два одинаковых метода?
function update_gamelist() {
	$.getJSON('game.php', {a: 'gamelist'}, 
		function(data) {
			if(data != null) {
				for (var v in data) {
					$("#gamelist").append(data.responseText);
				}//for	
			}//if
		});
}
*/
function gameListRequest() {
	return {a:'games'};
}
function gameListListener(games) {
	if (games['mygame']) {
		// скрыть форму новой игры
		//$('#newgame').html('');
		// показать инфо про созданную игру и кнопки закрытия игры
		//$('#gamelist').html(games['mygame']['creator'] + games['mygame']['name'] + 
		//	games['mygame']['maxplayers'] + games['mygame']['players']);

	} else if (games['games'].length) {
		if (gameListTemplate == '') gameListTemplate = req('', 'ui/html/gamelist.html');
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
