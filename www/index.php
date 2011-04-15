<?php
	$remote = $_SERVER['REMOTE_ADDR'];
	$server = ($remote == '192.168.88.33') ? '192.168.88.33' : 'ezh.mine.nu';
?><html>
<head>
<title>Game</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<script type="text/javascript" src="scripts/jquery-1.4.2.min.js"></script>
<!--script type="text/javascript" src="js/jquery-jtemplates_uncompressed.js"></script-->

<script type="text/javascript">
// implement JSON.stringify serialization
JSON = {};
JSON.stringify = JSON.stringify || function (obj) {
    var t = typeof (obj);
    if (t != "object" || obj === null) {
        // simple data type
        if (t == "string") obj = '"'+obj.replace(/\"/g, '\\"')+'"';
        return String(obj);
    }
    else {
        // recurse array or object
        var n, v, json = [], arr = (obj && obj.constructor == Array);
        for (n in obj) {
            v = obj[n]; t = typeof(v);
            //if (t == "string") v = '"'+v.replace("\"", "\\\"")+'"';
            //else if (t == "object" && v !== null) 
			v = String(JSON.stringify(v));
            json.push( (arr ? "" : '"' + n + '":') + v );
        }
        return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
    }
};

$(function(){
	chat = {};
	chat.add = function(str) {
		//$('#c').html(str + '<br>' + $('#c').html());
		$('#c').prepend('<div>' + str + '</div>');
		$('#c').html($('#c > div').slice(0, 15));		
	};
	chat.send = function(text) {
		var t = {a:'chat', 'text':text};
		ws.send(JSON.stringify(t));
	}
	$('#a').keypress(function(e){
		if (e.which == 13) {
			e.preventDefault();
			chat.send(this.value);
			$(this).val('');
		}
	});
	$('#ready').click(function(e){
		var t = {a:'ready'};
		ws.send(JSON.stringify(t));
	});
	$('#roll').click(function(e){
		var t = {a:'roll'};
		ws.send(JSON.stringify(t));
	});
	$('#buyprop').click(function(e){
		ws.send(JSON.stringify({a:'buyprop'}));
	});
	$('#auc').click(function(e){
		ws.send(JSON.stringify({a:'auc'}));
	});
	$('#rename').click(function(e){
		var t = {a:'rename', name:$('#a').val()};
		$('#a').val('');
		ws.send(JSON.stringify(t));
	});
	if ("WebSocket" in window) {
		var ws = new WebSocket("ws://<?= $server; ?>:8001/");
		ws.onopen = function() {
			chat.add("game opened");
			//var t = {a:'helo'};
			//ws.send(JSON.stringify(t));
		}
		ws.onmessage = function(e) {
			msg = $.parseJSON(e.data);
			if (msg.a == 'chat')
				chat.add("&gt; " + msg.text);
			if (msg.aa)
				$('#aa').text(JSON.stringify(msg.aa));
		}
		ws.onclose = function() {
			chat.add("game closed");
		}
	} else {
		alert("No WebSockets support");
	}

});
</script>
</head>
<body>
<!-- inputboxy for chat text, rename name, auction bid -->
<input type="text" id="a"/>
<!-- available actions -->
<div id="aa"></div>
<input type="button" id="ready" value="ready"/>
<input type="button" id="roll" value="roll"/>
<input type="button" id="rename" value="rename"/>
<input type="button" id="buyprop" value="buyprop"/>
<input type="button" id="auc" value="auc"/>
<div id="c"></div>
</body>
</html>
