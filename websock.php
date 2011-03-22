<?php

class WSUser {
	public $sock;
	public $ishandshake;
	public $address;
	public $port;
	function __construct($sock, $address, $port) {
		$this->ishandshake = false;
		$this->sock = $sock;
		$this->address = $address;
		$this->port = $port;
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
	function __construct() {
		$this->users = array();
	}
	function add($sock, $address, $port) {
		$this->users []= new WSUser($sock, $address, $port);
	}
	function fillSelect(&$select) {
		foreach($this->users as $user)
			$select []= $user->sock;
	}
	function close($user) {
		foreach($this->users as $idx => $usersea)
			if ($user == $usersea) {
				unset($user);
				unset($this->users[$idx]);
				break;
			}
	}
	function getBySock($sock) {
		foreach($this->users as $idx => $user)
			if ($user->sock == $sock)
				return $user;
	}
	function message($user, $data) {
		return $data;
	}

}

class WebSockServer {

	function __construct($um) {
		$server = socket_create_listen(8001);
		if ($server === false)
			die('socket_create_listen failed');
		echo "listening\n";
			
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
					echo "client connected\n";

				} else {
					$user = $um->getBySock($sock);
					$data = socket_read($sock, 1024);
					if (!$data) {
						socket_close($sock);
						$um->close($user);
						echo "client disconnected\n";
					} else if ($user->ishandshake) {
						$data = substr($data, 1, -1);
						$reply = $um->message($user, $data);
						socket_write($user->sock,  chr(0) . $reply . chr(255));
					} else {
						socket_write($user->sock, $user->handshake($data));
					}
					
				}
			}
		}	
	}
}
