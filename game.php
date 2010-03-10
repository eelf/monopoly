<?php

define('SYS', 'sys/');

include SYS . 'functions.php';
include SYS . 'config.php';
include SYS . 'db.php';
include SYS . 'games.php';
include SYS . 'players.php';
session_start();
//mb_internal_encoding("UTF-8");
//date_default_timezone_set('Europe/Moscow');

$old_error_handler = set_error_handler("myErrorHandler");

//$player = session_id() . str_pad(dechex(ip2long($_SERVER['REMOTE_ADDR'])), 8, '0', false);


/* !!!весь game.php и предназначен для обсулживания ajax запросов!!!
	и толку проверять аякс это или нет нету т.к. кому нада подделает заголовок
	а нам легче отлаживать делает запрос из браузера */
/***работаем с ajax***/
//проверяем, что нам поступил именно ajax запрос
/*
if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
//получаем параметры и раскодируем их
	$params = stripslashes($_POST['data']); // обязательно избегаем слешей...
	$p = json_decode($params); // use $p->needparam
//формируем ответ
	switch($p->arg) {
		case 'login':
    	try {
    		$playerid = Players::getInstance()->login($p->email, $p->password, $_SESSION['key']);
    	} catch (Exception $e) {
    		die($e->getMessage());
    	}
    	$_SESSION['playerid'] = $playerid;
    	$result['playerid'] = $playerid; //теперь можно передать информацию в страничку
    	$result['text'] = 'success'; // собственно ради этого и делалось
    	// удобство в том, что эти данные можно пихать в любые места, т.к. со стороны js у нас появилась свобода
			break;
	}
//кодируем в json и отправляем назад
	echo json_encode($result);
}}else{
*/

//иначе упс... работаем по старой схеме
/* собственно разницы никакой нет: 1) взять из массива гет нужную переменную и смотреть 
её что за функцию исполнить
2) взять из массива пост нужную переменную, раскодировать её жсоном, в получившемся объекте 
опять взять нужную переменную и посмотреть какую функцию исполнить

кодирование жс объекта и передача его через гет запрос происходит автоматически
а вот пхп объект нужно закодировать синтаксисом жс
*/
if (!isset($_GET['a'])) die('Nothing');

switch($_GET['a']) {
case 'register':
    try {
    $playerid = Players::getInstance()->newPlayer($_GET['email'], $_GET['password'], $_GET['name']);
    } catch (Exception $e) {
    die($e->getMessage());
    }
    $_SESSION['playerid'] = $playerid;
    echo "OK";
    break;
case 'login':
	$login = array('auth'=>'OK');
    try {
    $playerid = Players::getInstance()->login($_GET['email'], $_GET['password'], $_SESSION['key']);
	$login['playerid'] = $playerid;
    $_SESSION['playerid'] = $playerid;
    } catch (Exception $e) {
	$login['auth'] = $e->getMessage();
    }
	echo json_encode($login);
    break;
case 'logout':
    //unset($_SESSION['playerid']);
    session_destroy();
    echo "OK";
    break;
	
case 'games':
    $game = Games::getInstance()->getGameByCreator($_SESSION['playerid']);
	$games = Games::getInstance()->getAllGames();
	echo json_encode(array('mygame'=>$game, 'games'=>$games));
	break;
case 'listgames':
    $games = Games::getInstance()->getAllGames();
    echo $games ? $games : 'No Games';
    break;
case 'newgame':
    try {
    Games::getInstance()->newGame($_SESSION['playerid'], $_GET['name'], $_GET['maxplayers']);
    } catch (Exception $e) {
    die($e->getMessage());
    }
    echo "OK";
    break;
case 'mygame':
    $game = Games::getInstance()->getGameByCreator($_SESSION['playerid']);
    echo $game ? $game : 'No Game';
    break;
case 'joingame':
    
    break;
}//end switch
//}//end else



