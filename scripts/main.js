var data = [
'GO,,0',
'Mediterranean Avenue,60,1',
'Community Chest,,0',
'Baltic Avenue,60,1',
'IncomeTax,10% or -200,0',
'Reading Railroad,200,8',
'Oriental Avenue,100,2',
'Chance,,0',
'Vermont Avenue,100,2',
'Connecticut Avenue,120,2',

'Jail,,0',
'St. Charles Place,140,3',
'Electric Company,150,9',
'States Avenue,140,3',
'Virginia Avenue,160,3',
'Pennsylvania Railroad,200,8',
'St. James Place,180,4',
'Community Chest,,0',
'Tennessee Avenue,180,4',
'New York Avenue,200,4',

'Free,,0',
'Kentucky Avenue,220,5',
'Chance,,0',
'Indiana Avenue,220,5',
'Illinois Avenue,240,5',
'B&O Railroad,200,8',
'Atlantic Avenue,260,6',
'Ventnor Avenue,260,6',
'Water Works,150,9',
'Marvin Gardens,280,6',

'Go to jail,,0',
'Pacific Avenue,300,7',
'North Carolina Avenue,300,7',
'Community Chest,,0',
'Pennsylvania Avenue,320,7',
'Short Line,200,8',
'Chance,,0',
'Park Place,350,10',
'Luxury Tax,-75,0',
'Boardwalk,400,10'
];

Array.prototype.indexOf = function(v) {
	for (i = 0; i < this.length; i++) {
		if (this[i] == v) return i;
	}
	return false;
}


	/*[
{id:1, place: 7, token: 'an6.gif', props:[1,3,12,15,23], mortgaged:[23], houses1:[], houses2:[], houses3:[1], houses4:[3], hotels:[]},
{id:11, place: 34, token: 'a2.gif', props:[6,5,19,18,16], mortgaged:[5], houses1:[], houses2:[], houses3:[], houses4:[], hotels:[16,18,19]}
	];
	*/
	var token_left = [3, 20, 40, 60];
	var token_top = [35, 17, 5, 25];
	var group_color_map = ['transparent', '#62c', 'grey', '#f4b', '#fb4', '#f00', '#ff0', '#0f0', '#bbb', '#077','#00f'];
	var players_color = ['#77f', '#7f7', '#f77', '#ff7'];

$(function(){
	var top = 500;
	var left = 800;
	var width = 80;
	var height = 50;
	for(var i in data) {
		spl = data[i].split(',');
		var title = $('<div></div>').css('background-color', group_color_map[spl[2]]).html(spl[2]!=0 ? '&nbsp;' : '');
		var price = $('<div>').text(spl[1]);
		var el = $('<div class="ff">').append(title).append(spl[0]).append(price).css('top', top + 'px').css('left', left + 'px');
		$('#abs-cont').append(el);

		/*
		var token = ''
		for (var j in players) {
			if (players[j]['props'].indexOf(i) !== false) {
				el.css('background-color', players_color[j]);
			}
			if (players[j]['mortgaged'].indexOf(i) !== false) {
				title.append('MORTGAGED');
			}
			if (players[j]['houses1'].indexOf(i) !== false) {
				title.append('*');
			}
			if (players[j]['houses2'].indexOf(i) !== false) {
				title.append('**');
			}
			if (players[j]['houses3'].indexOf(i) !== false) {
				title.append('***');
			}
			if (players[j]['houses4'].indexOf(i) !== false) {
				title.append('****');
			}
			if (players[j]['hotels'].indexOf(i) !== false) {
				title.append('&copy;');
			}
			if (players[j]['place'] == i) {
				var fishka = $('<img height="24" style="position:absolute;" />').css('top', top + token_top[j] + 'px').css('left', left + token_left[j] + 'px').attr('src', 'tokens/' + players[j]['token']);
				$('#abs-cont').append(fishka);

			}
			
		}
		*/
		if (i < 10) left -= width;
		else if (i < 20) top -= height;
		else if (i < 30) left += width;
		else if (i < 40) top += height;
	}

	$('#controls').empty();
	if (turn.seq[turn.idx] == myid) {
		$('#controls').append('My turn');
		var btn_roll = $('<a href="?action=roll" class="act-btn">Roll dice</a>');
		$('#controls').append(btn_roll);
	} else for(var i in players) if (players[i].id == turn.seq[turn.idx]) $('#controls').text(players[i].name + ' turn');

});	

