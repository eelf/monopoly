<?php

define('SYS', 'sys/');

include SYS . 'db.php';
include SYS . 'games.php';
include SYS . 'players.php';
session_start();
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
    } catch (Exception $e) {
    die($e->getMessage());
    }
    $_SESSION['playerid'] = $playerid;
    echo "OK";
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
    $game = Games::getInstance()->getPlayerGame($_SESSION['playerid']);
    echo $game ? $game : 'No Game';
    break;
case 'joingame':
    
    break;
}
