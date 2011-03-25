<?php

//mb_internal_encoding("UTF-8");
//date_default_timezone_set('Europe/Moscow');

// $old_error_handler = set_error_handler("myErrorHandler");


class Game extends WSUserManager {

	private $properties;
	private $chance;
	private $chest;
	private $turn;
	private $isStarted;

	function __construct() {
		parent::__construct();
		$this->isStarted = false;


	}
	function add($sock, $address, $port) {
		$this->chat("player connected from $address:$port");
		$this->users []= new Player($this, $sock, $address, $port);
	}
	function message($user, $msg) {
		$msg = json_decode($msg, true);
		if ($msg == null || !isset($msg['a'])) return;
		if ($msg['a'] == 'roll') {
			$sum = Dice::roll();
			$str = Dice::toString();
			$text = "{$user->name} rolled $str";
			$this->chat($text);
		}
		if ($msg['a'] == 'rename' && isset($msg['name'])) {
			$name = $msg['name'];
			foreach($this->users as $usereach)
				if ($usereach->name == $name) {
					$data = array('a'=>'chat', 'text'=>'name already in use');
					$this->reply($user, json_encode($data));
					return;
				}
			$user->name = $name;
		}
		if ($msg['a'] == 'chat' && isset($msg['text'])) {
			$this->chat($user->name . ': ' . $msg['text']);
		}
	}
	
	function chat($text) {
		foreach($this->users as $user) {
			$data = array('a'=>'chat', 'text'=>$text);
			$this->reply($user, json_encode($data));
		}
	}


	function unser() {
		$rows = $this->db->getRows("SELECT * FROM em_state WHERE state_game = {$this->game_id}");
		$this->players = array();
//		$this->turn = array('idx'=>0, 'seq'=>array());
		$this->chance = $this->chest = range(0,15);
		shuffle($this->chance);
		shuffle($this->chest);

		foreach($rows as $row) {
			if ($row['state_field'] == 'player') {
				$player = new Player();
				$player->unser($row['state_value']);
				$this->players[$player->id] = $player;
			}
			if ($row['state_field'] == 'turn') {
				$this->turn = new Turn();
				$this->turn->unser($row['state_value']);
			}
			if ($row['state_field'] == 'chance') $this->chance = json_decode($row['state_value'], true);
			if ($row['state_field'] == 'chest') $this->chest = json_decode($row['state_value'], true);
			
		}
		$this->properties = array(
			1=>array('price'=>'60,2,10,30,90,160,250','group'=>array(3)),
			3=>array('price'=>'60,4,20,60,180,320,450','group'=>array(1)),
			5=>array('price'=>'200','group'=>array(15,25,35), 'notprop'=>1),
			6=>array('price'=>'100,6,30,90,270,400,550','group'=>array(8,9)),
			8=>array('price'=>'100,6,30,90,270,400,550','group'=>array(6,9)),
			9=>array('price'=>'120,8,40,100,300,450,600','group'=>array(8,6)),

			11=>array('price'=>'140,10,50,150,450,625,750','group'=>array(13,14)),
			12=>array('price'=>'150','group'=>array(28), 'notprop'=>2),
			13=>array('price'=>'140,10,50,150,450,625,750','group'=>array(11,14)),
			14=>array('price'=>'160,12,60,180,500,700,900','group'=>array(11,13)),
			15=>array('price'=>'200','group'=>array(5,25,35), 'notprop'=>1),
			16=>array('price'=>'180,14,70,200,550,700,900','group'=>array(18,19)),
			18=>array('price'=>'180,14,70,200,550,700,950','group'=>array(16,19)),
			19=>array('price'=>'200,16,80,220,600,800,1000','group'=>array(16,18)),

			21=>array('price'=>'220,18,90,250,700,875,1050','group'=>array(23,24)),
			23=>array('price'=>'220,18,90,250,700,875,1050','group'=>array(21,24)),
			24=>array('price'=>'240,20,100,300,750,925,1100','group'=>array(21,23)),
			25=>array('price'=>'200','group'=>array(15,5,35), 'notprop'=>1),
			26=>array('price'=>'260,22,110,330,800,975,1150','group'=>array(27,29)),
			27=>array('price'=>'260,22,110,330,800,975,1150','group'=>array(26,29)),
			28=>array('price'=>'150','group'=>array(12), 'notprop'=>2),
			29=>array('price'=>'280,24,120,360,850,1025,1200','group'=>array(26,27)),

			31=>array('price'=>'300,26,130,390,900,1100,1275','group'=>array(32,34)),
			32=>array('price'=>'300,26,130,390,900,1100,1275','group'=>array(31,34)),
			34=>array('price'=>'320,28,150,450,1000,1200,1400','group'=>array(31,32)),
			35=>array('price'=>'200','group'=>array(15,25,5), 'notprop'=>1),
			37=>array('price'=>'350,35,175,500,1100,1300,1500','group'=>array(39)),
			39=>array('price'=>'400,50,200,600,1400,1700,2000','group'=>array(37))
		);
		
		foreach($this->properties as $id=>$property) {
			$this->properties[$id]['owner'] = 0;
			$this->properties[$id]['houses'] = 0;
			$this->properties[$id]['mortgaged'] = false;
			$this->properties[$id]['price'] = explode(',', $this->properties[$id]['price']);
		}
		
		foreach($this->players as $player) {
			foreach($player->getProperties() as $idx=>$property) {
				foreach($property as $param=>$value)
					$this->properties[$idx][$param] = $value;

				$player->setProperty($idx, $this->properties[$idx]);
			}

		}
	}

	
	function ser() {
		if (!isset($_GET['action'])) return;
		if ($_GET['action'] == 'roll') {
			if ($this->turn->seq[$this->turn->idx] != $this->player_id) return;
			$player = $this->players[$this->player_id];
			$actions = $player->getActions();
			if (!in_array('roll', $actions)) return;
			$dice = Dice::roll();
			if ($player->jail->rounds) {
				if (Dice::isDouble()) {
					$player->jail->doubles = 0;
					$player->jail->rounds = 0;
					$player->place += $dice;
					$this->addEvent($this->player_id, 'Rolled double and got out of jail');
				} else {
					$player->jail->rounds++;
					$this->addEvent($this->player_id, 'Did not rolled double');
				}

			} else {
				if (Dice::isDouble()) $player->jail->doubles++;
				else $player->jail->doubles = 0;
				if ($player->jail->doubles == 3) {
					$player->jail->rounds = 1;
					
					$player->place = 10;
					$this->addEvent($this->player_id, 'Rolled double three times and going to jail');
				} else {
					$player->place += $dice;
					$this->addEvent($this->player_id, 'Rolled ' . Dice::toString());
				}
			}
			if (array_key_exists($player->place, $this->properties) && 
				$this->properties[$player->place]['owner'] != 0 && 
				$this->properties[$player->place]['owner'] != $this->player_id &&
				!$this->properties[$player->place]['mortgaged']) {
				$rent = $this->players[$this->properties[$player->place]['owner']]->calcRent($player->place);
				$assets = $player->calcAssets();
				if ($assets < $rent) {
					//bankruptcy
					$player->sellAll();
				}
			}
			$this->turn->idx = ($this->turn->idx + 1) % count($this->turn->seq);
			
			
		}
	}








}




/*
GO,200
Mediterranean Avenue,60
CC
Baltic Avenue,60
IncomeTax,10% or -200
Reading Railroad,200
Oriental Avenue,100
Chance
Vermont Avenue,100
Connecticut Avenue,120

Jail
St. Charles Place,140
Electric Company,150
States Avenue,140
Virginia Avenue,160
Pennsylvania Railroad,200
St. James Place,180
CC
Tennessee Avenue,180
New York Avenue,200

Free
Kentucky Avenue,220
Chance
Indiana Avenue,220
Illinois Avenue,240
B&O Railroad,200
Atlantic Avenue,260
Ventnor Avenue,260
Water Works,150
Marvin Gardens,280

Go to jail
Pacific Avenue,300
North Carolina Avenue,300
CC
Pennsylvania Avenue,320
Short Line,200
Chance
Park Place,350
Luxury Tax,-75
Boardwalk,400


CC:
// 0 [go-0]
// 1 [4-jail]
// 2 [+10 +20 +25 +45 +100 +100 +100 +200 -50 -100 -150]
// 3
// 4 40/115
// 5 50
You have won second prize in a beauty contest– collect $10
Tax refund – collect $20
Receive for services $25
From sale of stock you get $45
Life Insurance Matures – collect $100
You inherit $100 
Xmas fund matures - collect $100
Bank error in your favor – collect $200

Doctor's fee – Pay $50
Pay hospital $100
Pay School tax of $150

Advance to Go (Collect $200) 
Go to jail – go directly to jail – Do not pass Go, do not collect $200

Get out of jail free – this card may be kept until needed, or sold 

Grand opera Night – collect $50 from every player for opening night seats

You are assessed for street repairs – $40 per house, $115 per hotel



Chance:
//	types:
// 0-directmove target:[go-0, illinois-24,stchar-11,read-5,board-39]
// 1-relativemove target:[0-utility,1-railroad,3-back3space,4-jail]
// 2-cash [+50, +100, +150, -15]
// 3 - gojf
// 4 - house/hotel fee 25/100
// 5 - each player fee 50

// srand, shuffle, index, GOJF skip

Advance to Go (Collect $200)
Advance to Illinois Ave.
Advance to St. Charles Place – if you pass Go, collect $200
Take a ride on the Reading Railroad – if you pass Go collect $200
Take a walk on the Boardwalk – advance token to Boardwalk

Advance token to nearest Utility. If unowned, you may buy it from the Bank. If owned, throw dice and pay owner a total ten times the amount thrown.
Advance token to the nearest Railroad and pay owner twice the rental to which he/she is otherwise entitled. If Railroad is unowned, you may buy it from the Bank.
Go back 3 spaces 
Go directly to Jail – do not pass Go, do not collect $200

Get out of Jail free – this card may be kept until needed, or sold

Bank pays you dividend of $50
You have won a crossword competition - collect $100
Your building and loan matures – collect $150
Pay poor tax of $15

Make general repairs on all your property – for each house pay $25 – for each hotel $100
You have been elected chairman of the board – pay each player $50
*/
