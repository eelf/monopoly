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
	/*
	chance/chest cards to get out jail for free
	*/
	public $chance;
	public $chest;
	function __construct() {
		$this->doubles = 0;
		$this->rounds = 0;
		$this->chance = 0;
		$this->chest = 0;
		
	}

}


