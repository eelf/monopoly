<?php
$namesf = array('John', 'Paul', 'Tom', 'Adrian', 'Joey');
$namesl = array('Smith', 'Carmack', 'Doe', 'Romero', 'Hall');


class DDice {
    private $dice1, $dice2;
    function roll() {
        $dice1 = rand(1, 6);
        $dice2 = rand(1, 6);
    }
    function isDouble() {
        return $dice1 == $dice2;
    }
}
class Player {
    private $cash, $loc, $name, $activity, $sess, $ip;
    function __construct($cash, $loc) {
        $this->cash = $cash;
        $this->loc = $loc;
        global $namesf, $namesl;
        $this->name = $namesf[array_rand($namesf)] . ' ' . $namesl[array_rand($namesl)];
    }
}

class Field {
    private $property, $visit, $owner, $price, $mono;
}
class World {
    private $players, $fields;
    function __construct($playercount) {
        $this->players = array();
        $this->fields = array();
        $this->fields[0] = new Field();
        for($i = 0; $i < $playercount; $i++)
            $this->players[$i] = new Player(1500000, $this->fields[0]);
    }
}
$events = array();

$serversocket = stream_socket_server("tcp://0.0.0.0:8000");
$world = new World(2);
while (1) {
    $client = @stream_socket_accept($serversocket, 10, $peer);
    if (!$client) continue;
    echo "$peer\n";
    $reqraw = fread($client, 1024);
    foreach(explode('&', $reqraw) as $pair) {
        list($key, $value) = explode('=', $pair);
        $req[$key] = $value;
    }
    fwrite($client, "NoNewEvents");
    fclose($client);
}