<?php

class Player extends WSUser {

	private $cash;
	private $place;
	private $properties;
	private $jail;

	function __construct($sock, $address, $port) {
		parent::__construct($sock, $address, $port);
		$this->cash = 1500;
		$this->place = 0;
		$this->properties = array();
		$this->jail = new Jail();
	}


	function propertyAction($idx, $property) {
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
					
		if ($this->jail->rounds > 0 && $this->jail->rounds < 4) $actions []= 'roll';
		if ($this->jail->rounds > 0 && $this->jail->chance) $actions []= 'chance';
		if ($this->jail->rounds > 0 && $this->jail->chest) $actions []= 'chest';
		if ($this->jail->rounds > 0) $actions []= 'jail';
		return $actions;
	}



	function getProperties() {
		return $this->properties;
	}
	function setProperty($idx, &$property) {
		$this->properties[$idx] = $property;
	}
	function render() {
		$r_mort = $r_hotel = $r_house4 = $r_house3 = $r_house2 = $r_house1 = $r_prop = array();
		foreach($this->properties as $idx=>$property) {
			$r_prop []= $idx;
			if ($property['houses'] == 1) $r_house1 []= $idx;
			if ($property['houses'] == 2) $r_house2 []= $idx;
			if ($property['houses'] == 3) $r_house3 []= $idx;
			if ($property['houses'] == 4) $r_house4 []= $idx;
			if ($property['houses'] == 5) $r_hotel []= $idx;
			if ($property['mortgaged']) $r_mort []= $idx;
		}
		return array('id'=>$this->id,
			'name'=>$this->name,
			'token'=>$this->token,
			'cash'=>$this->cash,
			'place'=>$this->place,
			'props'=>$r_prop,
			'houses1'=>$r_house1,
			'houses2'=>$r_house2,
			'houses3'=>$r_house3,
			'houses4'=>$r_house4,
			'hotels'=>$r_hotel,
			'mortgaged'=>$r_mort
			
			);
	}
	function getState() {
		if ($this->ready == 0) return Games::PLAYER_NOTREADY;
		if ($this->ready == -1) return Games::PLAYER_SPECTATE;
		return Games::PLAYER_READY;
		 
	}
	function getStateTpl() {
		if ($this->ready == 0) return 'NOTREADY';
		if ($this->ready == -1) return 'SPECT';
		return 'READY';
		 
	}

	function isMonopoly($idx) {
		foreach($this->property[$idx]['group'] as $property)
			if (!array_key_exists($property, $this->property)) return false;
		return true;
	}

	
	function calcRent($idx) {
		if(isset($this->properties[$idx]['notprop']) && $this->properties[$idx]['notprop'] == 1) {
			$rent = 25;
			foreach($this->properties[$idx]['group'] as $gidx)
				if (array_key_exists($gidx, $this->properties)) $rent *= 2;
		} else if(isset($this->properties[$idx]['notprop']) && $this->properties[$idx]['notprop'] == 2) {
			$rent = 4;
			if (array_key_exists($this->properties[$idx]['group'][0], $this->properties)) $rent = 10;
			$rent *= Dice::sum();
		} else {
			if ($this->properties[$idx]['houses']) $rent = $this->properties[$id]['price'][1 + $this->properties[$idx]['houses']];
			else if ($this->isMonopoly($idx)) $rent = $this->properties[$id]['price'][1] * 2;
			else $this->properties[$id]['price'][1];
		}
		return $rent;
	}

	
	function calcAssets() {
		$assets = $this->cash;
		foreach($this->properties as $idx => $property) {
			if(!isset($property['notprop']) && $property['houses'] > 0) {
				$assets += $this->houseCost($idx) / 2 * $property['houses'];
			}
			$assets += $property['price'] / 2;
		}
		return $assets;
	}
	function sellAll() {
		foreach($this->properties as $idx => $property) {
			if(!isset($property['notprop']) && $property['houses'] > 0) {
				$this->cash += $this->houseCost($idx) / 2 * $property['houses'];
				$property['houses'] = 0;
			}
		}
	}
	function houseCost($idx) {
		if ($idx < 10) return 50;
		if ($idx < 20) return 100;
		if ($idx < 30) return 150;
		return 200;
	}
}


