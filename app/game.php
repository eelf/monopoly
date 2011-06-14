<?php

//mb_internal_encoding("UTF-8");
//date_default_timezone_set('Europe/Moscow');

// $old_error_handler = set_error_handler("myErrorHandler");


class Game extends WSUserManager {

	public $properties;
	public $chance;
	public $chest;
	private $turn;
	private $isStarted;
	private $autoStart;
	private $turnPlayers;
	public static $i;

	function __construct() {
		Game::$i = $this;
		parent::__construct();
		$this->isStarted = false;
		$this->autoStart = 0;
		$this->turnPlayers = array();
		$this->chance = range(0,15);
		$this->chest = range(0,15);
		shuffle($this->chance);
		shuffle($this->chest);
		

		
//idx, name, class, price, group, rent, monopoly, h1, h2, h3, h4, hotel, housecost
		$celldata = <<<EOT
0,GO,go
1,Mediterranean Avenue,prop,60,1,2,4,10,30,90,160,250,50
2,Community Chest,cc
3,Baltic Avenue,prop,60,1,4,8,20,60,180,320,450,50
4,Income Tax,incometax
5,Reading Railroad,rail,200,99,50,100,150,200
6,Oriental Avenue,prop,100,2,6,12,30,90,270,400,550,50
7,Chance,chance
8,Vermont Avenue,prop,100,2,6,12,30,90,270,400,550,50
9,Connecticut Avenue,prop,120,2,8,16,40,100,300,450,600,50
10,Jail,jail
11,St. Charles Place,prop,140,11,10,20,50,150,450,625,750,100
12,Electric Company,util,150,88,4,10
13,States Avenue,prop,140,11,10,20,50,150,450,625,750,100
14,Virginia Avenue,prop,160,11,12,24,60,180,500,700,900,100
15,Pennsylvania Railroad,rail,200,99,50,100,150,200
16,St. James Place,prop,180,12,14,28,70,200,550,700,900,100
17,Community Chest,cc
18,Tennessee Avenue,prop,180,12,14,28,70,200,550,700,950,100
19,New York Avenue,prop,200,12,16,32,80,220,600,800,1000,100
20,Free Parking,parking
21,Kentucky Avenue,prop,220,21,18,36,90,250,700,875,1050,150
22,Chance,chance
23,Indiana Avenue,prop,220,21,18,36,90,250,700,875,1050,150
24,Illinois Avenue,prop,240,21,20,40,100,300,750,925,1100,150
25,B&O Railroad,rail,200,99,50,100,150,200
26,Atlantic Avenue,prop,260,22,22,44,110,330,800,975,1150,150
27,Ventnor Avenue,prop,260,22,22,44,110,330,800,975,1150,150
28,Water Works,util,150,88,4,10
29,Marvin Gardens,prop,280,22,24,48,120,360,850,1025,1200,150
30,Go to jail,gotojail
31,Pacific Avenue,prop,300,31,26,52,130,390,900,1100,1275,200
32,North Carolina Avenue,prop,300,31,26,52,130,390,900,1100,1275,200
33,Community Chest,cc
34,Pennsylvania Avenue,prop,320,31,28,56,150,450,1000,1200,1400,200
35,Short Line,rail,200,99,50,100,150,200
36,Chance,chance
37,Park Place,prop,350,32,35,70,175,500,1100,1300,1500,200
38,Luxury Tax,luxurytax
39,Boardwalk,prop,400,32,50,100,200,600,1400,1700,2000,200
EOT;
		foreach(explode("\n", $celldata) as $cellline) {
			$cell = explode(',', trim($cellline));
			if ($cell[2] == 'prop') {
				$ocell = new Property($this, $cell[0], $cell[1], $cell[2], $cell[3], $cell[4], $cell[5], $cell[6], $cell[7], $cell[8], $cell[9], $cell[10], $cell[11], $cell[12]);
			} else if ($cell[2] == 'util') {
				$ocell = new Utility($this, $cell[0], $cell[1], $cell[2], $cell[3], $cell[4], $cell[5], $cell[6]);
			} else if ($cell[2] == 'rail') {
				$ocell = new Rail($this, $cell[0], $cell[1], $cell[2], $cell[3], $cell[4], $cell[5], $cell[6], $cell[7], $cell[8]);
			} else {
				$ocell = new Cell($this, $cell[0], $cell[1], $cell[2]);
			}
			$this->properties [$cell[0]] = $ocell;
		}


	}
	function add($sock, $address, $port) {
		$this->isAwaitingPlayer($sock);
		$player = new Player($sock, $address, $port);
		$player->name = "Player" . (count($this->users) + 1);
		$this->users []= $player;
	}
	function close($sock) {
		$user = $this->getBySock($sock);
		$msg = "{$user->name} disconnected";
		$user->sock = null;
		//parent::close($sock);
		$this->chat($msg);
	}
	function hsComplete($user) {
		$this->chat("{$user->name} connected from {$user->address}:{$user->port}");
	}
	
	
	function tick() {
		parent::tick();
		//echo "tick {$this->autoStart} " . time() . "\n";
		if (!$this->isStarted && $this->autoStart != 0 && $this->autoStart < time()) {
			$this->isStarted = true;
			// shuffle turnPlayers
			$turn = current($this->turnPlayers);
			$this->chat("game started, {$turn->name} turn");
		}
		
	}
	
	
	function message($user, $msg) {
		parent::message($user, $msg);
		$msg = json_decode($msg, true);
		if ($msg == null || !isset($msg['a'])) return;

		if ($user->flood > 20) {
			$this->chat("{$user->name} kicked due to flood");
			$this->close($user->sock);
			return;
		}
		
		
		$actions = array('rename', 'chat');
		$awaiting = $this->isAwaitingPlayer();
		if (!$this->isStarted || ($awaiting && !in_array($user, $this->turnPlayers, true))) {
			$actions []= 'ready';
		} else {
			$turn = current($this->turnPlayers);
			if ($user === $turn)
				foreach($user->getActions() as $action)
					$actions []= $action;
		}
		
		
		if (!in_array($msg['a'], $actions)) {
			$this->chat("action not available", $user);
			$data = array('aa' => $actions);
			$this->reply($user, json_encode($data));
			return;
		}
		
		/*
		below are actions possible for this player
		*/
		
		if ($msg['a'] == 'ready') {
			if ($awaiting) {
				$this->isAwaitingPlayer($user->sock);
				return;
			}
			if (!in_array($user, $this->turnPlayers, true)) {
				$this->chat("{$user->name} is now ready");
				$this->turnPlayers []= $user;
			}
			if (count($this->turnPlayers) >= 2 && $this->autoStart == 0 ) {
				$this->autoStart = time() + 5;
				$this->chat("enough players reached, game starts in 5 seconds");
			}
			
		}
		if ($msg['a'] == 'roll') {
			$sum = Dice::roll();
			$str = Dice::toString();
			$this->chat("{$user->name} rolled $str");
			// turn is not advanced
			if (!$user->roll()) {
				return;
			}


			$this->nextTurn();
			
		}
		if ($msg['a'] == 'buyprop') {
			$prop = $this->getCell($user->place);
			if ($prop->price <= $user->cash) {
				$prop->owner = $user;
				$user->cash -= $prop->price;
				$user->properties []= $prop;
				$this->chat("{$user->name} bought {$prop->name} for {$prop->price} ({$user->cash} cash left)");
				$user->canBuyOrAuc = false;
				if (!Dice::isDouble()) $this->nextTurn();
			} else {
				$this->chat("not enough cash ({$prop->price} needed, {$user->cash} avail)", $user);
			}
		}
		if ($msg['a'] == 'rename' && isset($msg['name'])) {
			$this->rename($user, $msg);
		}
		if ($msg['a'] == 'chat' && isset($msg['text']) && strlen($msg['text']) > 1) {
			$this->chat($user->name . ': ' . htmlspecialchars($msg['text']));
		}
	}
	
	function nextTurn() {
		$next = next($this->turnPlayers);
		if ($next === false) {
			$next = reset($this->turnPlayers);
		}
		$this->chat("{$next->name} turn, he is at {$next->place} {$this->properties[$next->place]->name}");
	
	}
	
	function isAwaitingPlayer($sock = null) {
		for($i = 0; $i < count($this->turnPlayers); $i++) {
			//if (!is_resource($this->turnPlayers[$i]->sock)) {
			if ($this->turnPlayers[$i]->sock == null) {
				if ($sock != null) {
					$this->turnPlayers[$i]->sock = $sock;
					$this->chat("{$sock} replaces player $i");
				}
				return true;
			}
		}
		return false;
	}
	
	function rename($user, $msg) {
		$name = $msg['name'];
		foreach($this->users as $usereach)
			if ($usereach->name == $name) {
				$this->chat('name already in use', $user);
				return;
			}
		if (strlen($name) < 3 || strlen($name) > 16) {
			$this->chat('name too short or long', $user);
			return;
		}
		$this->chat("{$user->name} now known as $name");
		$user->name = $name;
	}
	
	function chat($text, $user = null) {
		$data = array('a'=>'chat', 'text'=>$text);
		if ($user == null) {
			foreach($this->users as $user) {
				if ($user->sock != null)
					$this->reply($user, json_encode($data));
			}
		} else if ($user->sock != null) {
			$this->reply($user, json_encode($data));
		}
	}
	
	function getCell($idx) {
		return $this->properties[$idx];
	}

	function everyPayTo($amount, $player) {
		foreach($this->turnPlayers as $payPlayer) {
			if ($payPlayer == $player) continue;
			$payPlayer->pay($amount, $player);
		}
	}
	function payEvery($amount, $player) {
		$result = true;
		foreach($this->turnPlayers as $payPlayer) {
			if ($payPlayer == $player) continue;
			if (!$player->pay($amount, $payPlayer))
				$result = false;
		}
		return true;
	}

}
