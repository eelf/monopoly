<?php

class Turn {

	private $data = array();
	function __construct($seq = array()) {
		$this->idx = 0;
		$this->seq = $seq;
		
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
		return json_encode(array('idx'=>$this->idx, 'seq'=>$this->seq));
	}
	function unser($json) {
		$a = json_decode($json, true);
		$this->idx = $a['idx'];
		$this->seq = $a['seq'];
	}
}


