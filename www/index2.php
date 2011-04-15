<?php

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Game</title>
<link rel="stylesheet" href="style.css" type="text/css"/>
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="js/jquery-jtemplates_uncompressed.js"></script>
<script type="text/javascript" src="main.js"></script>
<!--script type="text/javascript" src="ui/algo.js"></script-->
<!--script type="text/javascript" src="ui/common.js"></script>
<script type="text/javascript" src="ui/chat.js"></script>
<script type="text/javascript" src="ui/login.js"></script>
<script type="text/javascript" src="ui/games.js"></script-->
<!--script type="text/javascript">

	chat = {};
	chat.a = document.getElementById('chat');
	chat.add = function(str) {
		self.a.innerHTML = str + '<br>' + self.a.innerHTML;
	};

	if ("WebSocket" in window) {
		var ws = new WebSocket("ws://127.0.0.1:8001/");
		ws.onopen = function() {
			chat.add("game opened");
			ws.send("a test message");
		}
		ws.onmessage = function(e) {
			chat.add("&gt; " + e.data);
		}
		ws.onclose = function() {
			chat.add("game closed");
		}
	} else {
		alert("No WebSockets support");
	}
</script-->
</head>
<body>
<div id="abs-cont"></div>
<div id="chat">123</div>
<div id="controls">66</div>

</body>
</html>
