<?php session_start();
$ip = isset($_SESSION['ip']) ? $_SESSION['ip'] : $_SERVER['REMOTE_ADDR'];
$_SESSION['ip'] = $ip;
if ($_SERVER['REMOTE_ADDR'] != $ip) {
    session_destroy();
    die('Session deny');
}

$key = isset($_SESSION['key']) ? $_SESSION['key'] : md5(uniqid(rand()));
$_SESSION['key'] = $key;
setcookie('key', $key);

$playerid = isset($_SESSION['playerid']) ? $_SESSION['playerid'] : 0;
setcookie('playerid', $playerid);

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Game</title>
<link rel="stylesheet" href="ui/style.css" type="text/css"/>
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="ui/algo.js"></script>
<script type="text/javascript" src="ui/common.js"></script>
<script type="text/javascript" src="ui/login.js"></script>
<script type="text/javascript" src="ui/games.js"></script>
</head>
<body>
    <div id="pro"><div id="proi">&nbsp;</div></div>
    <div id="x"></div>

	<div id="container">
		<div id="gamelist">
			game list:
		</div>
		<div id="chat_container">
			<div id="login_bl">
				login informaton:
			</div>
			<div id="info_bl">
				<div id="chat">
					<textarea id="screen" cols="50" rows="20"  readonly="readonly"></textarea><br />
					<input id="message" size="40"><input type="button" value="send" onclick="send_message();">
				</div>	
			</div>
		</div>
	</div>
    
    
<div id="log"></div>
</body>
</html>
