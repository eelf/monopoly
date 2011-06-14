<?php
	function myErrorHandler($errno, $errstr, $errfile, $errline) {
		echo '<pre>';
		switch ($errno) {
			case 2: echo 'WARNING'; break;
			case 4: echo 'PARSE'; break;
			case 8: echo 'NOTICE'; break;
			case 2048: echo 'STRICT'; break;
			default: echo $errno;
		}
		echo ": $errstr\n";
		foreach(debug_backtrace() as $level)
			if (isset($level['file'])) echo $level['file'], ':', $level['line'], "\n";
		echo '</pre>';
		return true;
	}