<?php

class Jail {
	private $data = array();
	function __construct() {
		$this->doubles = 0;
		$this->rounds = 0;
		$this->chance = 0;
		$this->chest = 0;
		
	}
	function __get($name) {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;        		
	}
	function __set($name, $value) {
		$this->data[$name] = $value;
	}
	function ser() {
		return array('doubles'=>$this->doubles,
			'rounds'=>$this->rounds,
			'chance'=>$this->chance,
			'chest'=>$this->chest);
	}
	function unser($a) {
//		$a = json_decode($json, true);
		$this->doubles = $a['doubles'];
		$this->rounds = $a['rounds'];
		$this->chance = $a['chance'];
		$this->chest = $a['chest'];
		
	}
}


