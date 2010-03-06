<?php

if (!isset($_SERVER['argv'][1]) || !isset($_SERVER['argv'][2])) die($_SERVER['argv'][0] . " <input> <output>\n");


$atoms = file_get_contents($_SERVER['argv'][1]);
/*<<<EOT
qwe
asd
123
ogh
bocux
cux
mle
if
xor
hiki
nemo
456
zxc
789
mikha
098
qwd
wer
sdf
EOT;
*/

function splitr($str) {
    if (strlen($str) == 1) return array(array($str));
    $r = array();
    for($i = 1; $i <= strlen($str); $i++) {
        $kus = substr($str, 0, $i);
        $ost = substr($str, $i);
        if (!$ost) $r []= array($kus);
        else {
            $splits = splitr($ost);
            foreach($splits as $split) {
                array_unshift($split, $kus);
                $r []= $split;
            }
        }
    }
    return $r;
}

function splitmix($a, $b) {
    $r = '';
//echo '    a=', a2s($a), ' b=', a2s($b), "\n";
    for($i = 0; $i < count($a) + count($b) + 1; $i++) {
        $tmp = ($i % 2) ? each($b) : each($a);
        $r .= $tmp[1];
    }
//echo "    r=$r\n";
    return $r;
}

function a2s($a) {
    return implode(',', $a);
}

function mix($a, $b) {
//echo "mix($a, $b)\n";
    $aaa = splitr($a);
    $bbb = splitr($b);
    $r = array();
    foreach($aaa as $aa)
        foreach($bbb as $bb) {
            $r []= splitmix($aa, $bb);
            $r []= splitmix($bb, $aa);
        }
    return $r;
}

$atoms1 = explode("\r\n", $atoms);
$atoms2 = $atoms1;
$dic = array();
foreach($atoms1 as $k=>$a1) {
    if (!$a1) continue;
    foreach($atoms2 as $a2) {
        if (!$a2) continue;
        $a1rev = strrev($a1);
        $a2rev = strrev($a2);
        $m1 = mix($a1, $a2);
        $m2 = ($a1 != $a1rev) ? mix($a1rev, $a2) : array();
        $m3 = ($a2 != $a2rev) ? mix($a1, $a2rev) : array();
        $m4 = ($a1 != $a1rev && $a2 != $a2rev) ? mix($a1rev, $a2rev) : array();
        $dic = array_merge($dic, $m1, $m2, $m3, $m4);
    }
    echo $k+1, '/', count($atoms1), "\n";
}
$udic = array_unique($dic);
sort($udic);
file_put_contents($_SERVER['argv'][2], implode("\n", $udic));
