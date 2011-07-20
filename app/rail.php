<?php

class Rail extends Cell {
	public $price;
	public $group;
	public $rent1;
	public $rent2;
	public $rent3;
	public $rent4;
	
	function __construct($game, $idx, $name, $class, $price, $group, $rent1, $rent2, $rent3, $rent4) {
		parent::__construct($game, $idx, $name, $class);
		$this->price = $price;
		$this->group = $group;
		$this->rent1 = $rent1;
		$this->rent2 = $rent2;
		$this->rent3 = $rent3;
		$this->rent4 = $rent4;
	}
	function getActions() {
		if ($this->mortgaged) return array('unmort');
		return array('mort');
	}
	
	function calcRent() {
		$owned = 0;
		foreach($this->game->properties as $property) {
			if ($property->class == 'rail' && $property->owner == $this->owner)
				$owned++;
		}
		if ($owned == 1) return $this->rent1;
		else if ($owned == 2) return $this->rent2;
		else if ($owned == 3) return $this->rent3;
		else if ($owned == 4) return $this->rent4;
		echo "Rail::calcRent() failed\n";
		return 0;
	}
}
