<?php


include 'gamelist.php';
include 'player.php';
include 'game.php';
include 'session.php';
include 'db.php';
include 'config.php';
include 'template.php';
include 'turn.php';
include 'jail.php';
include 'dice.php';

function predump($var) {
	echo '<pre>'; var_dump($var); echo '</pre>';
}

$config = Config::$config;
try {
$db = new Db($config);

$sess = new Session($db);

if (!$sess->isLogged()) {
	$tpl = new Template('login.tpl');
	echo $tpl->build($sess->getTpl());
} else {
	$games = new Games($db, $sess);		

}
} catch (Exception $e) {
	echo $e->getTraceAsString();
	predump($e->getMessage());
}



session_start();
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
<script type="text/javascript" src="js/jquery-jtemplates_uncompressed.js"></script>
<script type="text/javascript" src="ui/algo.js"></script>
<script type="text/javascript" src="ui/common.js"></script>
<script type="text/javascript" src="ui/chat.js"></script>
<script type="text/javascript" src="ui/login.js"></script>
<script type="text/javascript" src="ui/games.js"></script>
</head>
<body>

</body>
</html>
