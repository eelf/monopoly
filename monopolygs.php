<?php

require 'app/boot.php';
	
$um = new Game();
new WebSockServer($um);


