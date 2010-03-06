<?php
session_start();
if (isset($_REQUEST['p']))
	$_SESSION['p'] = $_REQUEST['p'];

?>

<html><head><script type="text/javascript">
function getCookie(n) {
	var c = " " + document.cookie;
	var s = " " + n + "=";
	var r = null;
	var o = 0;
	var e = 0;
	if (c.length > 0) {
		o = c.indexOf(s);
		if (o != -1) {
			o += s.length;
			e = c.indexOf(";", o)
			if (e == -1)
				e = c.length;
			r = unescape(c.substring(o, e));
		}
	}
	return(r);
}
function g() {
	document.getElementById('txt').value = getCookie('PHPSESSID');
}
function s(i) {
	if (event.keyCode == 13) {
		document.cookie = 'PHPSESSID=' + i.value;
	}
}
</script>
</head>
<body>
<form method="post">Store some data in session:<input type="text" name="p" onkeypress="if (event.keyCode == 13) this.submit();" size="40" value="<?=$_SESSION['p']?>"/>
</form>
<button onclick="g();">Get php session id</button><input type="text" id="txt" onkeypress="s(this);" size="40"/><br/>
<button onclick="window.location.pathname = 'co.php';">Reload</button><br/>
</body>
</html>