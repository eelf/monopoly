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
    function getChatByLastId($id) {
		//мы знаем id последнего известного нам сообщения, получаем всё что позже
		/*
		возвращает: (id, player, msg) // player = id пользователя
		*/
		$id = (int) $id;
        $msg = DB::getInstance()->getRows("SELECT * FROM chat c JOIN players p ON c.player = p.id WHERE c.id > $id");
        return $msg;
    }
    function addMessage($player, $msg) {
		//добавляем сообщение, возвращаем его id
		/*
		$id - id последнего нам известного сообщения
		$player - собственно ник
		$msg - собсвтенно сообщение
		возвращает: id последнего сообщения
		*/  
		//FIXIT проверка на вшивость сообщения, и на кол-во символов, типа 0 низя
		$msg = DB::getInstance()->escapeString(trim($msg));
		if (strlen($msg) < 3 || strlen($msg) > 99) throw new Exception('Wrong msg length');
        //if (!preg_match('/^[\w\s]+$/', $msg)) throw new Exception('Wrong msg chars');
		DB::getInstance()->query("INSERT INTO chat (player, msg) VALUES ($player, '$msg')");
		$resultid = DB::getInstance()->getLastId();
		return $resultid;
    }
    
}
