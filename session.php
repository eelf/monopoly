<?php

class Session {
	private $db;
	private $logged = false;
	private $err = '';
	
	function __construct($db) {
		$this->db = $db;
		$this->checkSession();
	}
	
	function checkSession() {
		session_start();
		if (!isset($_SESSION['remote_addr'])) {
			if (!$this->login());
				return false;
		}
		$this->logged = true;
		$this->player_id = $_SESSION['player_id'];
		$this->db->query("UPDATE em_player SET player_lasttime = UNIX_TIMESTAMP() WHERE player_id = {$this->player_id}");
	}
	
	function login() {
		if (!isset($_POST['email']) || !isset($_POST['password'])) return false;
		if (!preg_match('/^[A-Za-z\.\-0-9_]+@[a-z\-0-9\.]+$/', $_POST['email'])) {
			$this->err = 'wrong email';
			return false;
		}
		$email = $this->db->escapeString($_POST['email']);
		$password = md5($_POST['password']);
		$row = $this->db->getRow("SELECT * FROM em_player WHERE player_email = '$email' AND player_pass = '$password'");
		if (!$row) {
			$this->err = 'wrong email or password';
			return false;
		}
		$_SESSION['remote_addr'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['player_id'] = $row['player_id'];
		return true;
	}

	function isLogged() {
		return $this->logged;
	}

	function getPlayerId() {
		return $this->player_id;
	}
	function getTpl() {
		return array('LOGIN'=>'email', 
			'PASSWORD'=>'password', 
			'_ERR'=>!empty($this->err), 
			'ERR'=>$this->err);
	}
}

