<?php


$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
$referal = 'http://mp.ezh.mine.nu';
$host = '192.168.88.33';
$port = 8001;


function gen_key() {
	$sec = rand(500000, 999999);
	$mod = rand(4, 6);
	$mix = (string) ($sec * $mod);
	$garb = '".+;)(%ABCDEFUVWXYZ-*';
	$i = 0;
	while($i < $mod) {
		$pos = rand(1, strlen($mix) - 2);
		if (($mix[$pos] != ' ') && 
			($mix[$pos - 1] != ' ') &&
			($mix[$pos + 1] != ' ')) {
			$mix = substr($mix, 0, $pos) . ' ' . substr($mix, $pos);
			$i++;
		} else if (strlen($mix) < 16) {
			$mix = substr($mix, 0, $pos) . $garb[rand(0, strlen($garb) - 1)] . substr($mix, $pos);
		}
	}
	return array($sec, $mix);
}

list($key1s, $key1) = gen_key();
list($key2s, $key2) = gen_key();
$body = pack("NN", 0xc0c1c2c3, 0xb0b1b2b3);

$ctx = hash_init('md5');
hash_update($ctx, pack("N", $key1s));
hash_update($ctx, pack("N", $key2s));
hash_update($ctx, $body);
$hash_data = hash_final($ctx, true);

$hello = "GET / HTTP/1.1\r\nUpgrade: WebSocket\r\nOrigin: $referal\r\nConnection: Upgrade\r\nHost: $host:$port\r\nSec-WebSocket-Key1: $key1\r\nSec-WebSocket-Key2: $key2\r\n\r\n$body";

socket_connect($sock, $host, $port);
socket_write($sock, $hello);
$data = socket_read($sock, 1024);
$response = "HTTP/1.1 101 WebSocket Protocol Handshake\r\nUpgrade: WebSocket\r\nConnection: Upgrade\r\nSec-WebSocket-Origin: $referal\r\nSec-WebSocket-Location: ws://$host:$port/\r\n\r\n$hash_data";
assert('$data == $response');
//	echo "assertion failed\n$response\n$data";
//else echo "OK";
socket_read($sock, 1024);
socket_shutdown($sock);
socket_close($sock);
