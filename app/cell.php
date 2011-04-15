<?php

class Cell {
	public $game;
	public $idx;
	public $name;
	public $class;
	public $owner;
	public $morgaged;
	
	function __construct($game, $idx, $name, $class) {
		$this->game = $game;
		$this->idx = $idx;
		$this->name = $name;
		$this->class = $class;
		$this->owner = 0;
		$this->morgaged = false;
	}
	
	function chest($player) {
		$result = true;
		$chest = array_pop($this->game->chest);
		switch ($chest) {
		case 0:
			$player->cash += 10;
			break;
		case 1:
			$player->cash += 20;
			break;
		case 2:
			$player->cash += 25;
			break;
		case 3:
			$player->cash += 45;
			break;
		case 4:
		case 5:
		case 6:
			$player->cash += 100;
			break;
		case 7:
			$player->cash += 200;
			break;
		case 8:
			$result = $player->pay(50, 0);
			break;
		case 9:
			$result = $player->pay(100, 0);
			break;
		case 10:
			$result = $player->pay(150, 0);
			break;
		case 11:
			$player->advanceGo();
			break;
		case 12:
			$player->place = 10;
			$player->jail->imprison();
			break;
		case 13:
			$player->chest = 1;
			$chest = -1;
			break;
		case 14:
			$this->game->everyPayTo(50, $player);
			break;
		case 15:
			$result = $player->payForHouseHotel(40, 115);
			break;
		}
		if ($chest != -1) array_unshift($this->game->chest, $chest);
		return $result;
		
	}
	function chance($player) {
		return true;
	}
	
	function action($player) {
		switch($this->class) {
		case 'go':
			$player->cash += 200;
			break;
		case 'cc':
			return $this->chest($player);
		case 'chance':
			return $this->chance($player);
		case 'incometax':
			return $player->pay(200, 0);
		case 'gotojail':
			$player->place = 10;
			$player->jail->imprison();
			break;
		case 'luxurytax': 
			return $player->pay(100, 0);
		case 'prop':
		case 'util':
		case 'rail':
			if ($this->owner != 0 && $this->owner != $player && !$this->mortgaged) {
				$rent = $this->calcRent();
				return $player->pay($rent, $cell->owner);
			} else if ($cell->owner == 0) {
				$player->canBuyOrAuc = true;
			}
		return true;
	}

}

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
	function calcRent() {
		$owned = 0;
		foreach($this->game->properties as $property) {
			if ($property->class == 'util' && $property->owner == $this->owner)
				$owned++;
		}
		if ($owned == 1) return Dice::sum() * $this->rent1;
		else if ($owned == 2) return Dice::sum() * $this->rent2;
		echo "Utility::calcRent() failed\n";
		return 0;
	}
}

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
	function isMonopoly() {
		foreach($this->game->properties as $property) {
			if ($property->class == 'prop' && $property->group == $this->group && $property != $this && $property->owner != $this->owner)
				return false;
		}
		return true;
	}

}
