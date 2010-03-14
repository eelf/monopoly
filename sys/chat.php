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
    /*
    возвращает: (id, player, msg) // player = id пользователя
    */
        $msg = DB::getInstance()->getRows("SELECT * FROM chat WHERE id > '$id'");
        return $msg;
    }
    function addMessage($player, $msg) {	//добавляем сообщение, возвращаем его id
    /*
		$id - id последнего нам известного сообщения
		$player - собственно ник
		$msg - собсвтенно сообщение
		возвращает: id последнего сообщения
		*/  
      //FIXIT проверка на вшивость сообщения, и на кол-во символов, типа 0 низя
			DB::getInstance()->query("INSERT INTO chat (player, msg) VALUES ($player, '$msg')");
      $resultid = DB::getInstance()->getLastId();
			return $resultid; // возвращаем id свежедобавленного сообщения
    }
    
}
