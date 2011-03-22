<?php

$f = fsockopen('127.0.0.1', 8001);
fwrite($f, "\r\n" . date('r'));
fread($f, 1);
fclose($f);

