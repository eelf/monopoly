<?php

class Games {
    /* Singleton pattern 
    http://ru.wikipedia.org/wiki/Одиночка_(шаблон_проектирования)
    */
    static $instance = null;
    static function getInstance() {
    	if (self::$instance == null) self::$instance = new self();
        return self::$instance;
    }

    function __construct() {
    }
    /*
    depricated, использовать метод с постраничной разбивкой
    */
    function getAllGames() {
	$games = DB::getInstance()->getRows("SELECT * FROM games");
	// массив игр получен, далее его надо както передать клиенту...
	// сериализуем объект: делаем из массива массивов строку: разделители "\н" и ":"
	$sendToClient = '';
	foreach($games as $game) $sendToClient .= "{$game['creator']}:{$game['name']}\n";
        return $sendToClient;
    }

    /*
    returns array_assoc (creator, name, maxplayers, players)
    see sql/monopoly.txt
    */
    function getGameByCreator($creator) {
        $game = DB::getInstance()->getRow("SELECT * FROM games WHERE creator = $creator");
        return $game;
    }
    function getGameByName($name) {
        $game = DB::getInstance()->getRow("SELECT * FROM games WHERE name = '$name'");
        return $game;
    }
    function newGame($player, $name, $maxplayers) {
        if (!preg_match('/^[A-Za-z0-9_]{4,15}$/', $name)) throw new Exception('Wrong game name');
        if ($this->getGameByName($name)) throw new Exception('Game already exists');
        if ($this->getGameByCreator($player)) throw new Exception('Already created game');
        $maxplayers = (int)$maxplayers;
        if ($maxplayers < 2 || $maxplayers > 4) throw new Exception('Wrong max players count');
        DB::getInstance()->query("INSERT INTO games (creator, name, maxplayers, players) VALUES ($player, '$name', $maxplayer, '')");
    }
    function joinGame() {
    }
}
