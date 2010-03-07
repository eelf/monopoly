<?php
die("ezh.mine.nu");
session_start();
if (isset($_GET['last'])) {
    $sock = fsockopen('localhost', 8000);
    fwrite($sock, substr($_SERVER['REQUEST_URI'], 2) . '&sess=' . session_id() . '&ip=' . $_SERVER['REMOTE_ADDR']);
    echo stream_get_contents($sock);
    fclose($sock);
    die;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Untitled1</title>
<script type="text/javascript">
function b(id) { return document.getElementById(id); }
function a1() {
}
function log(str) {
    b('log').innerHTML = b('log').innerHTML + str + '<br/>';
}
function ping() {
    xmlhttp.open('GET', 'http://localhost/?last=' + event, false);
    xmlhttp.send(null);
    res = xmlhttp.responseText;
if (res == '') {alert('Empty res'); return; }
    if (res != 'NoNewEvents') {
        resa = res.split(':');
        event = resa[0];
        if (resa[1] == 'Name') {
            myid = resa[2];
            myname = resa[3];
        }
        if (resa[1] == 'Turn') turn = resa[2];
        if (turn == myid) {
            b('a1').disabled = '';
            b('a2').disabled = '';
        } else {
            b('a1').disabled = 'disabled';
            b('a2').disabled = 'disabled';
        }
        log(res);
    }
    setTimeout('ping()', 2000);
}
function init() {
    xmlhttp = new XMLHttpRequest();
    event = 0;
    myname = 'noname';
    myid = -1;
    turn = -1;
    ping();
}
</script>

</head>
<body onload="init();">
<button id="a1" disabled="disabled" onclick="a1();">Action1</button>
<button id="a2" disabled="disabled" onclick="a2();">Action2</button>
<div id="log"></div>
</body>
</html>