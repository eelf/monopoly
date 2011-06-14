<?php

class Player extends WSUser {

	public $cash;
	public $place;
	public $properties;
	private $jail;
	public $name;
	public $debt;
	private $debtplayer;
	public $canBuyOrAuc;
	/*
	chance/chest cards to get out jail for free
	*/
	public $chest;
	public $chance;

	function __construct($sock, $address, $port) {
		parent::__construct($sock, $address, $port);
		$this->cash = 1500;
		$this->place = 0;
		$this->debt = 0;
		$this->debtplayer = null;
		$this->properties = array();
		$this->jail = new Jail($this);
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
				Game::$i->chat("{$this->name} Rolled double and got out of jail");
			}
		} else {
			if ($this->jail->checkDouble()) {
				$this->place = 10;
				Game::$i->chat("{$this->name} Rolled double three times and going to jail");
				return true;
			} else {
				$this->place += Dice::sum();
			}
		}
		
		$newplace = $this->place % 40;
		if ($newplace < $this->place) {
			$this->actCell(0);
			$this->place = $newplace;
		}
		
		$actCell = $this->actCell($this->place);
		
		return $actCell && !Dice::isDouble();
	}
	
	function actCell($cell = -1) {
		if ($cell != -1)
			$this->place = $cell;
		$cell = Game::$i->getCell($this->place);
		return $cell->action($this);
	}
	
	function advanceGo() {
		$this->place = 0;
		$cell = Game::$i->getCell($this->place);
		$cell->action($this);
	}

	function getActions() {
		$actions = array();
		
		foreach($this->properties as $property)
			$actions = array_merge($actions, $property->getActions());
		
		if (!$this->jail->isInside() && !$this->canBuyOrAuc && $this->debt == 0) $actions []= 'roll';
		if ($this->jail->isInside() && $this->chance) $actions []= 'chance';
		if ($this->jail->isInside() && $this->chest) $actions []= 'chest';
		if ($this->jail->isInside()) $actions []= 'jail';
		if ($this->canBuyOrAuc) {
			$actions []= 'buyprop';
			$actions []= 'auc';
		}
		return $actions;
	}


}


