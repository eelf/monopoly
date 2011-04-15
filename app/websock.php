<?php

class WSUser {
	public $sock;
	public $ishandshake;
	public $address;
	public $port;
	
	public $flood;
	
	function __construct($sock, $address, $port) {
		$this->ishandshake = false;
		$this->sock = $sock;
		$this->address = $address;
		$this->port = $port;
		$this->flood = 0;
	}

	function keydivnum($key) {
		$numkey = '';
		$spaces = 0;
		for($i = 0; $i < strlen($key); $i++)
			if ($key[$i] >= '0' && ord($key[$i]) <= 0x39) $numkey .= $key[$i];
			else if ($key[$i] == ' ') $spaces++;
		//var_dump($numkey, $spaces);
		return $numkey / $spaces;
	}
	function handshake($data) {
		$this->ishandshake = true;
		$r = $h = $o = $key1 = $key2 = $body = null;
		if(preg_match("/GET (.*) HTTP/"   ,$data,$match)) $r = $match[1];
		if(preg_match("/Host: (.*)\r\n/"  ,$data,$match)) $h = $match[1];
		if(preg_match("/Origin: (.*)\r\n/",$data,$match)) $o = $match[1];
		if(preg_match("/Sec-WebSocket-Key2: (.*)\r\n/",$data,$match))
			$key2 = $this->keydivnum($match[1]);
		if(preg_match("/Sec-WebSocket-Key1: (.*)\r\n/",$data,$match))
			$key1 = $this->keydivnum($match[1]);
		if(preg_match("/\r\n(.*?)\$/",$data,$match)){ $body=$match[1]; }
		
		$ctx = hash_init('md5');
		hash_update($ctx, pack("N", $key1));
		hash_update($ctx, pack("N", $key2));
		hash_update($ctx, $body);
		$hash_data = hash_final($ctx, true);

		return "HTTP/1.1 101 WebSocket Protocol Handshake\r\n" .
  		  "Upgrade: WebSocket\r\n" .
		  "Connection: Upgrade\r\n" .
		  "Sec-WebSocket-Origin: " . $o . "\r\n" .
		  "Sec-WebSocket-Location: ws://" . $h . $r . "\r\n" .
		  "\r\n" .
		  $hash_data;
	}
	
}

class WSUserManager {
	public $users;
	protected $server;
	function __construct() {
		$this->users = array();
	}
	function registerServer($server) {
		$this->server = $server;
	}
	function add($sock, $address, $port) {
		$this->users []= new WSUser($sock, $address, $port);
	}
	function fillSelect(&$select) {
		//var_dump($this->users);
		foreach($this->users as $user)
			$select []= $user->sock;
	}
	function close($sock) {
		$idx = $this->getIdxBySock($sock);
		unset($this->users[$idx]);
	}
	function getIdxBySock($sock) {
		foreach($this->users as $idx => $user)
			if ($user->sock == $sock)
				return $idx;
	}	
	function getBySock($sock) {
		foreach($this->users as $idx => &$user)
			if ($user->sock == $sock)
				return $user;
	}
	function message($user, $data) {
		$user->flood++;
		return $data;
	}
	function reply($user, $data) {
		$this->server->send($user, $data);
	}
	function hsComplete($user) {
	}
	
	function tick() {
		$user->flood -= 4;
		if ($user->flood < 0) $user->flood = 0;
	}

}

class WebSockServer {

	function __construct($um) {
		$um->registerServer($this);
		
		//$server = socket_create_listen(8001);
		$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($server === false)
			die('socket_create_listen failed');
		socket_bind($server, '192.168.88.33', 8001);
		socket_listen($server);
		echo "listening\n";
			
		$tick = time();
		while (true) {
			$socks = array($server);
			$um->fillSelect($socks);
			
			$socksempty = null;
			$select = socket_select($socks, $socksempty, $socksempty, 5);
			if ($select === false)
				die("select fail\n");
				

			foreach($socks as $sock) {
				if ($sock == $server) {
					$client = socket_accept($server);
					$address = $port = '';
					socket_getpeername($client, $address, $port);
					$um->add($client, $address, $port);
					echo "client connected $address:$port\n";

				} else {
					$user = $um->getBySock($sock);
					$data = socket_read($sock, 1024);
					if (!$data) {
						echo "client disconnected {$user->address}:{$user->port}\n";
						$um->close($sock);
						socket_close($sock);
					} else if ($user->ishandshake) {
						$data = substr($data, 1, -1);
						$reply = $um->message($user, $data);
						if ($reply)
							socket_write($user->sock,  chr(0) . $reply . chr(255));
					} else {
						socket_write($user->sock, $user->handshake($data));
						$um->hsComplete($user);
					}
					
				}
			}
			
			if (time() - $tick > 4) {
				$um->tick();
				$tick = time();
			}
			
			
		}	
	}
	function send($user, $data) {
		socket_write($user->sock,  chr(0) . $data . chr(255));
	}
}
