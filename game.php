<?php

define('SYS', 'sys/');

include SYS . 'functions.php';
include SYS . 'config.php';
include SYS . 'db.php';
include SYS . 'games.php';
include SYS . 'players.php';
include SYS . 'chat.php';
session_start();
//mb_internal_encoding("UTF-8");
//date_default_timezone_set('Europe/Moscow');

$old_error_handler = set_error_handler("myErrorHandler");

//$player = session_id() . str_pad(dechex(ip2long($_SERVER['REMOTE_ADDR'])), 8, '0', false);

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
    try {
    $playerid = Players::getInstance()->login($_GET['email'], $_GET['password'], $_SESSION['key']);
    $loginame = Players::getInstance()->getPlayerByEmail($_GET['email']);
	$login['playerid'] = $playerid;
	$login['loginame'] = $loginame['name'];
    $_SESSION['playerid'] = $playerid;
    $_SESSION['loginame'] = $loginame['name'];
  $login['auth'] = 'OK';
    } catch (Exception $e) {
	$login['auth'] = $e->getMessage();
    }
	echo json_encode($login);
    break;
case 'logout':
    session_destroy();
    echo json_encode(array('auth'=>'OK'));
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
    
case 'getchat':
	try {
		$getchat = Chat::getInstance()->getChatById($_GET['id']);
		foreach ($getchat as $key => $value) {
			$chat['chat'][$key]['name'] = Players::getInstance()->getNameById($getchat[$key]['player']);
			$chat['chat'][$key]['msg'] = $getchat[$key]['msg'];
			$chat['id'] = $getchat[$key]['id'];
		}
	} catch (Exception $e) {
    die($e->getMessage());
	}
	if(isset($chat)) echo json_encode($chat);
	break;
case 'sendmessage':
	$chatid = Chat::getInstance()->addMessage($_SESSION['playerid'], $_GET['msg']);
	$_SESSION['chatid'] = $chatid;
	$result['chatid'] = $chatid;
	break;
}//end switch



