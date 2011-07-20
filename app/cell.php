<?php


class Cell {
	public $game;
	public $idx;
	public $name;
	public $class;
	public $owner;
	public $mortgaged;
	
	function __construct($game, $idx, $name, $class) {
		$this->game = $game;
		$this->idx = $idx;
		$this->name = $name;
		$this->class = $class;
		$this->owner = 0;
		$this->mortgaged = false;
	}
	
	function chest($player) {
		$result = true;
		$chest = array_pop($this->game->chest);
		switch ($chest) {
		case 0:
			$player->cash += 10;
			$this->game->chat("{$player->name} cash +10");
			break;
		case 1:
			$player->cash += 20;
			$this->game->chat("{$player->name} cash +20");
			break;
		case 2:
			$player->cash += 25;
			$this->game->chat("{$player->name} cash +25");
			break;
		case 3:
			$player->cash += 45;
			$this->game->chat("{$player->name} cash +45");
			break;
		case 4:
		case 5:
		case 6:
			$player->cash += 100;
			$this->game->chat("{$player->name} cash +100");
			break;
		case 7:
			$player->cash += 200;
			$this->game->chat("{$player->name} cash +200");
			break;
		case 8:
			$result = $player->pay(50, 0);
			$this->game->chat("{$player->name} cash -50");
			break;
		case 9:
			$result = $player->pay(100, 0);
			$this->game->chat("{$player->name} cash -100");
			break;
		case 10:
			$result = $player->pay(150, 0);
			$this->game->chat("{$player->name} cash -150");
			break;
		case 11:
			$player->advanceGo();
			break;
		case 12:
			$player->place = 10;
			$player->jail->imprison();
			$this->game->chat("{$player->name} imprisoned");
			break;
		case 13:
			$player->chest = 1;
			$chest = -1;
			$this->game->chat("{$player->name} GetOutFreeJail chest");
			break;
		case 14:
			$this->game->everyPayTo(50, $player);
			$this->game->chat("{$player->name} collects 50 from every pay");
			break;
		case 15:
			$result = $player->payForHouseHotel(40, 115);
			$this->game->chat("{$player->name} pays for house hotels");
			break;
		}
		if ($chest != -1) array_unshift($this->game->chest, $chest);
		return $result;
		
	}
	function chance($player) {
		$result = true;
		$chance = array_pop($this->game->chance);
		switch($chance) {
		case 0:
			$player->advanceGo();
			break;
		case 1:
			$player->place = 24;
			$this->game->chat("{$player->name} moved to 24");
			break;
		case 2:
			if ($player->place > 11)
				$player->advanceGo();
			$player->place = 11;
			$this->game->chat("{$player->name} moved to 11");
			break;
		case 3:
			if ($player->place > 5)
				$player->advanceGo();
			$player->place = 5;
			$this->game->chat("{$player->name} moved to 5");
			break;
		case 4:
			$player->place = 39;
			$this->game->chat("{$player->name} moved to 39");
			break;
		case 5:
			$this->game->chat("{$player->name} moved to 12/28");
			if ($player->place >= 1 && $player->place <= 20) {
				$cell = $this->game->getCell(12);
			} else {
				$cell = $this->game->getCell(28);
			}
			$player->place = $cell->idx;
			var_dump($cell->owner);
			if (!$cell->mortgaged && $cell->owner != 0 && $cell->owner != $player) {
				$pay = Dice::roll() * 10;
				return $player->pay($pay, $cell->owner);
			} else if ($cell->owner == 0) {
				return $cell->action($player);
			}
			break;
		case 6:
			$this->game->chat("{$player->name} moved to 5/15/25/35");
			if ($player->place >= 1 && $player->place <= 10) {
				$cell = $this->game->getCell(5);
			} else if ($player->place >= 11 && $player->place <= 20) {
				$cell = $this->game->getCell(15);
			} else if ($player->place >= 21 && $player->place <= 30) {
				$cell = $this->game->getCell(25);
			} else {
				$cell = $this->game->getCell(35);
			}
			$player->place = $cell->idx;
			var_dump($cell->owner);
			if (!$cell->mortgaged && $cell->owner != 0 && $cell->owner != $player) {
				$pay = $cell->calcRent() * 2;
				return $player->pay($pay, $cell->owner);
			} else if ($cell->owner == 0) {
				return $cell->action($player);
			}
			break;
		case 7:
			$player->place -= 3;
			if ($player->place < 0) $player->place += 40;
			$player->actCell($player->place);
			$this->game->chat("{$player->name} moved -3");
			break;
		case 8:
			$player->place = 10;
			$player->jail->imprison();
			$this->game->chat("{$player->name} imprisoned");
			break;
		case 9:
			$player->chance = 1;
			$chance = -1;
			$this->game->chat("{$player->name} GetOutFreeJail chance");
			break;
		case 10:
			$player->cash += 50;
			$this->game->chat("{$player->name} cash +50");
			break;
		case 11:
			$player->cash += 100;
			$this->game->chat("{$player->name} cash +100");
			break;
		case 12:
			$player->cash += 150;
			$this->game->chat("{$player->name} cash +150");
			break;
		case 13:
			$result = $player->pay(15, 0);
			$this->game->chat("{$player->name} cash -15");
			break;
		case 14:
			$result = $player->payForHouseHotel(25, 100);
			$this->game->chat("{$player->name} pays for house hotels");
			break;
		case 15:
			$result = $this->game->payEvery(50, $player);
			$this->game->chat("{$player->name} pays every other player 50");
			break;
		}
		return true;
	}
	
	function action($player) {
		switch($this->class) {
		case 'go':
			$player->cash += 200;
			$this->game->chat("{$player->name} landed/advanced go, collected 200");
			break;
		case 'cc':
			$this->game->chat("{$player->name} enters community chest");
			return $this->chest($player);
		case 'chance':
			$this->game->chat("{$player->name} enters chance");
			return $this->chance($player);
		case 'incometax':
			$this->game->chat("{$player->name} must pay income tax");
			return $player->pay(200, 0);
		case 'gotojail':
			$this->game->chat("{$player->name} goes to jail");
			$player->place = 10;
			$player->jail->imprison();
			break;
		case 'luxurytax': 
			$this->game->chat("{$player->name} must pay luxury tax");
			return $player->pay(100, 0);
		case 'prop':
		case 'util':
		case 'rail':
			
			if ($this->owner !== 0 && $this->owner != $player && !$this->mortgaged) {
				$rent = $this->calcRent();
				$this->game->chat("{$player->name} must pay rent $rent to {$this->owner->name}");
				return $player->pay($rent, $this->owner);
			} else if ($this->owner == 0) {
				$this->game->chat("{$player->name} landed on free prop {$this->name}, can buy or auc");
				$player->canBuyOrAuc = true;
				return false;
			}
		}
		return true;
	}

}
