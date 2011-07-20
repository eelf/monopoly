<?php

class Utility extends Cell {
	public $price;
	public $group;
	public $rent1;
	public $rent2;
	
	function __construct($game, $idx, $name, $class, $price, $group, $rent1, $rent2) {
		parent::__construct($game, $idx, $name, $class);
		$this->price = $price;
		$this->group = $group;
		$this->rent1 = $rent1;
		$this->rent2 = $rent2;
	}
	function getActions() {
		if ($this->mortgaged) return array('unmort');
		return array('mort');	
	}
	
	function calcRent() {
		$owned = 0;
		foreach($this->game->properties as $property) {
			if ($property->class == 'util' && $property->owner != 0 && $property->owner == $this->owner)
				$owned++;
		}
		if ($owned == 1) return Dice::sum() * $this->rent1;
		else if ($owned == 2) return Dice::sum() * $this->rent2;
		echo "Utility::calcRent() failed\n";
		return 0;
	}
}
