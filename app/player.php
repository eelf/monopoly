<?php

class Player extends WSUser {

	public $cash;
	public $place;
	public $properties;
	public $jail;
	public $game;
	public $name;
	public $debt;
	public $debtplayer;
	public $canBuyOrAuc;
	public $chest;
	public $chance;

	function __construct($game, $sock, $address, $port) {
		parent::__construct($sock, $address, $port);
		$this->game = $game;
		$this->cash = 1500;
		$this->place = 0;
		$this->debt = 0;
		$this->debtplayer = null;
		$this->properties = array();
		$this->jail = new Jail();
		$this->name = $address . ':' . $port;
		$this->canBuyOrAuc = false;
	}

	function pay($amount, $whom) {
		if ($this->cash < $amount) {
			$this->debt = $amount;
			$this->debtplayer = $whom;
			return false;
		} else {
			$this->cash -= $amount;
			if ($whom instanceof Player)
				$whom->cash += $amount;
		}
		return true;
	}
	
	function payForHouseHotel($house, $hotel) {
		$pay = 0;
		foreach($this->properties as $property) {
			if ($property->class == 'prop')
				if ($property->houses > 0 && $property->houses < 5)
					$pay += $property->houses * $house;
				else if ($property->houses == 5)
					$pay += $hotel;
		}
		return $this->pay($pay, 0);
	}
	
	function roll() {
		
		if ($this->jail->isInside()) {
			if ($this->jail->escape()) {
				$this->place += Dice::sum();
				$this->game->chat("{$this->name} Rolled double and got out of jail");
			}
		} else {
			if ($this->jail->checkDouble()) {
				$this->place = 10;
				$this->game->chat("{$this->name} Rolled double three times and going to jail");
				return;
			} else {
				$this->place += Dice::sum();
			}
		}
		
		$newplace = $this->place % 40;
		if ($newplace < $this->place) {
			$this->advanceGo();
			$this->place = $newplace;
		}
		
		$cell = $this->game->getCell($this->place);
		$cell->action($this);

		
		return true;
	}
	
	function advanceGo() {
		$this->place = 0;
		$cell = $this->game->getCell($this->place);
		$cell->action($this);
	}

	function propertyActions($idx, $property) {
		$actions = array();

		if (!isset($property['notprop'])) {
			$max = $min = $this->properties[$idx]['houses'];
			$owner = $this->properties[$idx]['owner'];
			foreach($this->properties[$idx]['group'] as $gidx) {
				if ($this->properties[$gidx]['owner'] != $owner) $owner = 0;
				if ($this->properties[$gidx]['houses'] > $max) $max = $this->properties[$gidx]['houses'];
				else if ($this->properties[$gidx]['houses'] < $min) $min = $this->properties[$gidx]['houses'];
			}
		}
		if ($this->properties[$idx]['owner'] == 0) $actions []= 'buy';
		if (!isset($property['notprop']) && $property['houses'] == $max) $actions []= 'sell';
		if (!isset($property['notprop']) && $owner == $this->id && $property['houses'] == $min) $actions []= 'house';
		if (!$property['mortgaged'] && (isset($property['notprop']) || $max == 0)) $actions []= 'mort';
		if ($property['mortgaged']) $actions []= 'unmort';
		return $actions;
	}

	function getActions() {
		$actions = array();
		
		foreach($this->properties as $idx => $property)
			$actions = array_merge($actions, $this->properyActions($idx, $property, $actions));
		
		if ($this->jail->rounds < 4) $actions []= 'roll';
		if ($this->jail->rounds > 0 && $this->jail->chance) $actions []= 'chance';
		if ($this->jail->rounds > 0 && $this->jail->chest) $actions []= 'chest';
		if ($this->jail->rounds > 0) $actions []= 'jail';
		return $actions;
	}


}


