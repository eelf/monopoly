<?php

class Jail {
	/*
	doubles in a row count
	*/
	public $doubles;
	/*
	rounds spent in jail trying roll double
	*/
	public $rounds;
	private $player;
	function __construct($player) {
		$this->doubles = 0;
		$this->rounds = 0;
		$this->player = $player;
		
	}
	
	function isInside() {
		return $this->rounds > 0;
	}
	
	function checkDouble() {
		if (Dice::isDouble()) $this->doubles++;
		else $this->doubles = 0;
		
		if ($this->doubles == 3) {
			$this->imprison();
			return true;
		} else
			return false;
	}
	
	function imprison() {
		$this->rounds = 1;
	}
	
	function escape() {
		if (Dice::isDouble()) {
			$this->doubles = 0;
			$this->rounds = 0;
			return true;
		} else {
			$this->rounds++;
			return false;
		}
		
	}

}


