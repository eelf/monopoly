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
// чо за? пусь будет :)

class Game {
	function register() {
		try {
		$playerid = Players::getInstance()->newPlayer($_GET['email'], $_GET['password'], $_GET['name']);
		} catch (Exception $e) {
		die($e->getMessage());
		}
		$_SESSION['playerid'] = $playerid;
		echo "OK";
	}
	function login() {
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
	}
	function logout() {
		session_destroy();
		echo json_encode(array('auth'=>'OK'));
	}






	function games() {
		$game = Games::getInstance()->getGameByCreator($_SESSION['playerid']);
		$games = Games::getInstance()->getAllGames();
		return array('mygame'=>$game, 'games'=>$games);
	}
	/* О.о два одиноковых метода?? 
	function gamelist() {
		//отдаем список игр, создатель, игроки, кол-во мест
		// если игр нет - молчим
		// если список игр не изменился - молчим
		try {
			$games = Games::getInstance()->getAllGames();
		} catch (Exception $e) {
			$games['error'] = $e->getMessage();
		}
		echo json_encode($games);
	}
	*/
	function newgame() {
		try {
			Games::getInstance()->newGame($_SESSION['playerid'], $_GET['name'], $_GET['maxplayers']);
		} catch (Exception $e) {
			die($e->getMessage());
		}
		echo "OK";
	}
	function mygame() {
		$game = Games::getInstance()->getGameByCreator($_SESSION['playerid']);
		echo $game ? $game : 'No Game';
	}
	function joingame() {
	
	}










	function getchat() {
		$chat = '';
		try {
			$chat = Chat::getInstance()->getChatByLastId($_GET['chatid']);
			/*
			foreach ($getchat as $key => $value) {
				$chat['chat'][$key]['name'] = Players::getInstance()->getNameById($getchat[$key]['player']);
				$chat['chat'][$key]['msg'] = $getchat[$key]['msg'];
				$chat['id'] = $getchat[$key]['id'];
			}
			*/
		} catch (Exception $e) {
			$chat['error'] = $e->getMessage();
		}
		return $chat;
	}
	function sendmessage() {
		try {
			$chatid = Chat::getInstance()->addMessage($_SESSION['playerid'], $_GET['msg']);
			$chat['chatid'] = $chatid;
		} catch(Exception $e) {
			$chat['error'] = $e->getMessage();
		}
		echo json_encode($chat);
	}
}

/*
чуток подправил геймсервер, чтобы он вёл себя как мультизапросный геймсервер,
т.е. мог отвечать сразу на несколько запросов, см. коммон.жс:88  (88-это номер строки ;))
*/

$game = new Game();
$seq = array('a','b','c');
$ret = array();
foreach($seq as $el) {
	if (!isset($_GET[$el])) break;//die('Request empty');
	if (!preg_match('/^[a-z]{3,20}$/', $_GET[$el])) die('Request wrong');
	if (!method_exists($game, $_GET[$el])) die('Request handler inexistent');
	$ret = array_merge($ret, $game->$_GET[$el]());
}
echo json_encode($ret);
