<?php

class Player {
    public $id, $email, $pass, $name, $session;
    function __construct($player) {
        list($this->id, $this->email, $this->pass, $this->name, $this->session) = explode(':', $player);
    }
    function match($id = 0, $email = '', $name = '', $splayer = '') {
        if ($this->id == $id ||
            $this->email == $email ||
            $this->name == $name ||
            $this->session == $splayer) return true;
        return false;
    }
}

class Players {
    private $players;
    static $instance = null;
    function __construct() {
        self::$instance = $this;
        $this->players = DB::getInstance()->getRows('players');
        if ($this->players[0] == '') $this->players = array();
    }
    function getPlayer($id = 0, $email = '', $name = '', $splayer = '') {
        foreach($this->players as $player) {
            $playero = new Player($player);
            if ($playero->match($id, $email, $name, $splayer)) return $playero;
        }
        return false;
    }
    function login($email, $pass, $key) {
        $player = $this->getPlayer(0, $email);
        if (!$player) throw new Exception('Player not found');
        $playerpass = md5($player->pass . $key);
        if ($pass != $playerpass) throw new Exception('Wrong pass');
        return $player->id;
    }
    function newPlayer($email, $pass, $name) {
        if (!preg_match('/^[a-z0-9_\-\.]+@[a-z0-9\-\.]{5,63}$/', $email)) throw new Exception('Wrong email');
        if (!preg_match('/^[a-zA-Z0-9]{3,16}$/', $pass)) throw new Exception('Wrong pass');
        if (!preg_match('/^[a-zA-Z0-9]{3,16}$/', $name)) throw new Exception('Wrong name');
        if ($this->getPlayer(0, $email)) throw new Exception('Email used');
        if ($this->getPlayer(0, '', $name)) throw new Exception('Name used');
        $id = count($this->players) + 1;
        $this->players []= "$id:$email:$pass:$name";
        DB::getInstance()->putRows('players', $this->players);
        return $id;
    }
    function getInstance() {
        if (self::$instance == null) new Players();
        return self::$instance;
    }

}
