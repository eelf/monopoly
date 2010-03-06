<?php

class Games {
    private $games;
    static $instance = null;
    function __construct() {
        self::$instance = $this;
        $this->games = DB::getInstance()->getRows('games');
        if ($this->games[0] == '') $this->games = array();
    }
    function getAllGames() {
/*
        $r = '';
        foreach($this->games as $game) {
            list($gamecreator, $gamename, $gamemaxplayers, $gameplayers) = explode(':', $game);
            $r .= "\n" . $g
        }
*/
        return implode("\n", $this->games);
    }
    function getGames($creator = '', $name = '', $maxplayers = 0, $players = array()) {
        $result = array();
        foreach($this->games as $game) {
            list($gamecreator, $gamename, $gamemaxplayers, $gameplayers) = explode(':', $game);
            $found = false;
            foreach(explode(";", $gameplayers) as $gameplayer)
                if (in_array($gameplayer, $players)) {
                    $found = true;
                    break;
                }
            if ($creator == $gamecreator ||
                $name == $gamename ||
                $maxplayers == $gamemaxplayers ||
                $found
                ) $result []= $game;
        }
        return $result;
    }
    function getGame($creator = '', $name = '', $maxplayers = 0, $players = array()) {
        $games = $this->getGames($creator, $name, $maxplayers, $players);
        if (count($games)) return $games[0];
        return '';
    }
    function newGame($player, $name, $maxplayers) {
        if (!preg_match('/^[A-Za-z0-9_]{4,15}$/', $name)) throw new Exception('Wrong game name');
        if ($this->getGame('', $name)) throw new Exception('Game already exists');
        if ($this->getGame($player)) throw new Exception('Already created game');
        $maxplayers = (int)$maxplayers;
        if ($maxplayers < 2 || $maxplayers > 4) throw new Exception('Wrong max players count');
        $this->games []= "$player:$name:$maxplayers";
        DB::getInstance()->putRows('games', $this->games);
    }
    function getPlayerGame($player) {
        $game = Games::getInstance()->getGame($player);
        return $game;
    }
    function getInstance() {
        return (self::$instance == null) ? new Games() : self::$instance;
    }
    function joinGame() {
    }
}
