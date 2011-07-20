<?php


class Property extends Cell {
	public $price;
	public $group;
	public $rent;
	public $rentmonopoly;
	public $rent1;
	public $rent2;
	public $rent3;
	public $rent4;
	public $renthotel;
	public $housecost;
	public $houses;

	function __construct($game, $idx, $name, $class, $price, $group, $rent, $rentmonopoly, $rent1, $rent2, $rent3, $rent4, $renthotel, $housecost) {
		parent::__construct($game, $idx, $name, $class);
		$this->price = $price;
		$this->group = $group;
		$this->rent = $rent;
		$this->rentmonopoly = $rentmonopoly;
		$this->rent1 = $rent1;
		$this->rent2 = $rent2;
		$this->rent3 = $rent3;
		$this->rent4 = $rent4;
		$this->renthotel = $renthotel;
		$this->housecost = $housecost;
		$this->houses = 0;
	}
	function calcRent() {
		switch($this->houses) {
		case 1: return $this->rent1;
		case 2: return $this->rent2;
		case 3: return $this->rent3;
		case 4: return $this->rent4;
		case 5: return $this->renthotel;
		default: if ($this->isMonopoly()) return $this->rentmonopoly;
			else return $this->rent;
		}
	}
	function getActions() {
		if ($this->mortgaged) return array('unmort');
		$actions = array();

		if ($this->houses == 0)	$actions []= 'mort';
		$mono = $this->isMonopoly();
		$max = $this->maxHouses();
		if ($mono && $this->houses <= $max && $this->houses < 5) $actions []= 'buy';
		else if ($mono && $this->houses > 0 && $this->houses == $max) $actions [] = 'sell';
		return $actions;
	}
	function isMonopoly() {
		foreach($this->game->properties as $property) {
			if ($property->class == 'prop' && $property->group == $this->group && $property != $this && $property->owner !== $this->owner)
				return false;
		}
		return true;
	}
	function maxHouses() {
		$max = 0;
		foreach($this->game->properties as $property) {
			if ($property->class == 'prop' && $property->group == $this->group && $property != $this && 
				$property->owner !== $this->owner && $property->houses > $max)
				$max = $property->houses;
		}
		return $max;
	}

}
