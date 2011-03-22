<?php


class Games {
	const PLAYER_NOWHERE = 0;
	const PLAYER_SPECTATE = 1;
	const PLAYER_NOTREADY = 2;
	const PLAYER_READY = 3;

	const GAME_NOTFULL = 0;
	const GAME_NOTREADY = 1;
	const GAME_RUNNING = 2;

	private $player_id;
	private $player_state;
	private $game_id;
	private $game_state;

	private $players;
	const ROOT = '/evermonopoly/';
	
	function __construct($db, $sess) {
		$this->db = $db;
		$this->player_id = $sess->getPlayerId();

		// 
		$this->checkState();
		
		$this->gameListAction();
		
		if ($this->game_state == self::GAME_RUNNING) {
			$game = new Game($this->db, $sess, $this->player_id, $this->game_id);
		} else if ($this->player_state != self::PLAYER_NOWHERE) {
			$this->gameLobby();
		} else {
			$this->gameList();
		}
	}


	function checkState() {
		// get player info, entered game and other players in game
		$rows = $this->db->getRows("SELECT b.*,c.* FROM em_list a JOIN em_list b ON a.list_game = b.list_game JOIN em_player c ON b.list_player = c.player_id WHERE a.list_player = {$this->player_id}");
		// player not in game
		if (!$rows) {
			$this->playerstate = self::PLAYER_NOWHERE;
			return;
		}
		// get game id
		$this->game_id = $rows[0]['list_game'];
		// get all players in game
		$this->players = array();
		foreach($rows as $row) {
			$this->players[$row['list_player']] = new Player($row['list_player'], $row['player_name'], $row['list_started'], $row['player_token']);
		}
		// get player state: ready notready spect
		$this->player_state = $this->players[$this->player_id]->getState();
		// get game state: notfull notready running
		$this->game_state = $this->getGameState();

		if ($this->game_state == self::GAME_RUNNING) {
			$exists = $this->db->isExists("SELECT * FROM em_state WHERE state_game = {$this->game_id}");
			// if game is running and no game state info - init
			if (!$exists) {
				foreach($this->getPlayablePlayers() as $player) {
					$this->db->query("INSERT INTO em_state (state_game, state_field, state_value) VALUES
						({$this->game_id}, 'player', '{$player->ser()}')");
				}

				$list = array_keys($this->getPlayablePlayers());
				shuffle($list);
				$turn = new Turn($list);
				$this->db->query("INSERT INTO em_state (state_game, state_field, state_value) VALUES
					({$this->game_id}, 'turn', '{$turn->ser()}')");
				
			}


		}
	}

	function getPlayablePlayers() {
		$result = array();
		foreach($this->players as $id=>$player)
			if ($player->getState() != Games::PLAYER_SPECTATE)
				$result[$id] = $player;
		return $result;
	}

	function getGameState() {
		$ready = 0;
		foreach($this->players as $player)
			if ($player->getState() == self::PLAYER_NOTREADY) return self::GAME_NOTREADY;
			else if ($player->getState() == self::PLAYER_READY) $ready++;
		if ($ready < 2) return self::GAME_NOTFULL;
		return self::GAME_RUNNING;
	}



	function gameListAction() {
		if (!isset($_GET['action'])) return;
		if ($this->game_state == self::GAME_RUNNING && $this->player_state == self::PLAYER_READY) return;
		if ($_GET['action'] == 'create' && $this->player_state == self::PLAYER_NOWHERE) {
			$this->db->query("INSERT INTO em_list (list_player, list_started) VALUES ({$this->player_id}, 0)");
			if ($this->db->getAffectedRows() != 1) ; // log error
			header("Location: " . self::ROOT);
			die;

		} else if ($_GET['action'] == 'spectate' && isset($_GET['game']) && $this->player_state == self::PLAYER_NOWHERE) {
			$game = (int) $_GET['game'];
			$this->db->query("INSERT INTO em_list (list_game, list_player, list_started) VALUES ($game, {$this->player_id}, -1)");
			header("Location: " . self::ROOT);
			die;

		} else if ($_GET['action'] == 'join' && isset($_GET['game']) && $this->player_state == self::PLAYER_NOWHERE) {
			$game = (int) $_GET['game'];
			$this->db->query("INSERT INTO em_list (list_game, list_player, list_started) VALUES ($game, {$this->player_id}, UNIX_TIMESTAMP())");			
			header("Location: " . self::ROOT);
			die;
		} else if ($_GET['action'] == 'part' && $this->player_state != self::PLAYER_NOWHERE) {
			$this->db->query("DELETE FROM em_list WHERE list_player = {$this->player_id}");
			header("Location: " . self::ROOT);
			die;
		} else if ($_GET['action'] == 'ready' && $this->player_state == self::PLAYER_NOTREADY) {
			$this->db->query("UPDATE em_list SET list_started = UNIX_TIMESTAMP() WHERE list_player = {$this->player_id}");
			header("Location: " . self::ROOT);
			die;
		} else if ($_GET['action'] == 'notready' && $this->player_state == self::PLAYER_READY) {
			$this->db->query("UPDATE em_list SET list_started = 0 WHERE list_player = {$this->player_id}");
			header("Location: " . self::ROOT);
			die;
		}
	}

	function gameList() {
		$tpl = new Template('gamelist.tpl');
		$rows = $this->db->getRows("SELECT list_game game,COUNT(*) players,if (COUNT(*)>1, MIN(list_started),0) _started FROM em_list WHERE list_started != -1 GROUP BY list_game");
		echo $tpl->build(array('+GAMES'=>$rows));
	}

	function gameLobby() {
		$tpl = new Template('gamelobby.tpl');
	
		$data = array();
		foreach($this->players as $id=>$player)
			$data []= array('PLAYER'=>$player->getName(),
			'_PLAYERSTATE'=> $player->getStateTpl());

		echo $tpl->build(array('GAME'=>$this->game_id, '+PLAYERS'=>$data, '_IMREADY'=>$this->player_state == self::PLAYER_READY,
			'_IMNOTREADY'=>$this->player_state == self::PLAYER_NOTREADY));
	}
	
}

