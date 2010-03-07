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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Game</title>
<link rel="stylesheet" href="ui/style.css" type="text/css"/>
<script type="text/javascript" src="ui/algo.js"></script>
<script type="text/javascript" src="ui/common.js"></script>
<script type="text/javascript" src="ui/login.js"></script>
<script type="text/javascript" src="ui/games.js"></script>
<script type="text/javascript" src="js/jquery-1.3.2.min.js.js"></script>
<script type="text/javascript" src="js/jquery.json-2.2.min.js"></script>
</head>
<body>
    <div id="pro"></div>
    <div id="x"></div>
    <div id="log"></div>
</body>
</html>
