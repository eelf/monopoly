<?php

class Chat {
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
    function getChatById($id) { //мы знаем id последнего известного нам сообщения, получаем всё что позже
        $msg = DB::getInstance()->getRow("SELECT * FROM chat WHERE id = '$id'"); // гммм FIXIT
        return $msg;
    }
    function addMessage($player, $msg) {	//добавляем сообщение, возвращаем его id
    // $id - id последнего нам известного сообщения
    // $player - собственно ник
    // $msg - собсвтенно сообщение
        //$player = DB::getInstance()->getRow("SELECT * FROM players WHERE email = '$email'");
			DB::getInstance()->query("INSERT INTO chat (player, msg) VALUES ($player, $msg)"); //!!! ругается!!
      $resultid = DB::getInstance()->getLastId();
			return $resultid; // возвращаем id свежедобавленного сообщения
    }
    
}
