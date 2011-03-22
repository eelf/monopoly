<?php

require 'websock.php';
require 'game.php';
require 'player.php';
require 'dice.php';
require 'jail.php';


	
$um = new Game();
new WebSockServer($um);


