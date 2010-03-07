<?php

class Players {
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
    function getPlayerByName($id) {
        $player = DB::getInstance()->getRow("SELECT * FROM players WHERE name = '$name'");
        return $player;
    }
    function getPlayerByEmail($email) {
        $player = DB::getInstance()->getRow("SELECT * FROM players WHERE email = '$email'");
        return $player;
    }
    function login($email, $pass, $key) {
        if (!preg_match('/^[a-z0-9_\-\.]+@[a-z0-9\-\.]{5,63}$/', $email)) throw new Exception('Wrong email');
        $player = $this->getPlayerByEmail($email);
        if (!$player) throw new Exception('Player not found');
        $playerpass = md5($player['pass'] . $key);
        if ($pass != $playerpass) throw new Exception('Wrong pass');
        return $player['id'];
    }
    function newPlayer($email, $pass, $name) {
        if (!preg_match('/^[a-z0-9_\-\.]+@[a-z0-9\-\.]{5,63}$/', $email)) throw new Exception('Wrong email');
        if (!preg_match('/^[a-zA-Z0-9]{3,16}$/', $pass)) throw new Exception('Wrong pass');
        if (!preg_match('/^[a-zA-Z0-9]{3,16}$/', $name)) throw new Exception('Wrong name');
        if ($this->getPlayerByEmail($email)) throw new Exception('Email used');
        if ($this->getPlayerByName($name)) throw new Exception('Name used');
        DB::getInstance()->query("INSERT INTO players (name, email, pass) VALUES ($name, $email, $pass)");
        $id = DB::getInstance()->getLastId();
        return $id;
    }

}
