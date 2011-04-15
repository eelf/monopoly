<?php

require 'app/websock.php';
require 'app/game.php';
require 'app/player.php';
require 'app/dice.php';
require 'app/jail.php';
require 'app/cell.php';


	
$um = new Game();
new WebSockServer($um);


