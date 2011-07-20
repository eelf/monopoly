<?php

require 'app/boot.php';
	
$um = new Game();

function obj2json() {
	$num_args = func_num_args();
	$o = new stdClass;
	for($i = 0; $i < $num_args; $i += 2) {
		$name = func_get_arg($i);
		$value = func_get_arg($i + 1);
		$o->$name = $value;
	}
	return json_encode($o);
}

echo obj2json();

