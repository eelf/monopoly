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
	private $autoStart;
	private $turnPlayers;

	function __construct() {
		parent::__construct();
		$this->isStarted = false;
		$this->autoStart = 0;
		$this->turnPlayers = array();

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
		foreach($this->properties as $k => $v) 
			$this->properties[$k]['owner'] = 0;


	}
	function add($sock, $address, $port) {
		$player = new Player($this, $sock, $address, $port);
		$player->name = "Player" . (count($this->users) + 1);
		$this->users []= $player;
	}
	function hsComplete($user) {
		$this->chat("player connected from {$user->address}:{$user->port}");
	}
	
	
	function tick() {
		//echo "tick {$this->autoStart} " . time() . "\n";
		if (!$this->isStarted && $this->autoStart != 0 && $this->autoStart < time()) {
			$this->isStarted = true;
			$turn = current($this->turnPlayers);
			$this->chat("game started, it is {$turn->name} turn");
		}
		
	}
	
	
	function message($user, $msg) {
		$msg = json_decode($msg, true);
		if ($msg == null || !isset($msg['a'])) return;
		
		
		$actions = array('rename', 'chat');
		if (!$this->isStarted) $actions []= 'ready';
		else {
			$turn = current($this->turnPlayers);
			if ($user == $turn)
				foreach($user->getActions() as $action)
					$actions []= $action;
		}
		
		
		if (!in_array($msg['a'], $actions)) {
			$this->chat("action not available", $user);
			return;
		}
		
		if ($msg['a'] == 'ready') {
			if (!in_array($user, $this->turnPlayers)) {
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
			
			if ($user->jail->rounds) {
				if (Dice::isDouble()) {
					$user->jail->doubles = 0;
					$user->jail->rounds = 0;
					$user->place += $sum;
					$this->chat("{$user->name} Rolled double and got out of jail");
				} else {
					$user->jail->rounds++;
				}

			} else {
				if (Dice::isDouble()) $user->jail->doubles++;
				else $user->jail->doubles = 0;
				if ($user->jail->doubles == 3) {
					$user->jail->rounds = 1;
					
					$user->place = 10;
					$this->chat("{$user->name} Rolled double three times and going to jail");
				} else {
					$user->place += $sum;
				}
			}
			if (array_key_exists($user->place, $this->properties) && 
				$this->properties[$user->place]['owner'] != 0 && 
				$this->properties[$user->place]['owner'] != $user &&
				!$this->properties[$user->place]['mortgaged']) {
				//$rent = $this->players[$this->properties[$player->place]['owner']]->calcRent($player->place);
				//$assets = $player->calcAssets();
				//if ($assets < $rent) {
					//bankruptcy
					//$player->sellAll();
				//}
			}
			$player = next($this->turnPlayers);
			if ($player === false) {
				$player = reset($this->turnPlayers);
			}
			$this->chat("{$player->name} turn");
			
			
		}
		if ($msg['a'] == 'rename' && isset($msg['name'])) {
			$this->rename($user, $msg);
		}
		if ($msg['a'] == 'chat' && isset($msg['text']) && strlen($msg['text']) > 1) {
			$this->chat($user->name . ': ' . htmlspecialchars($msg['text']));
		}
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
		if ($user == null)
			foreach($this->users as $user) {
				$this->reply($user, json_encode($data));
			}
		else 
			$this->reply($user, json_encode($data));
	}



}
