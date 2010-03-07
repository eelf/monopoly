<?php
/**
  * Class DB
  *
  * @author ezh
  */

class DB {

	private $debug;
	private $freeresult;
	private $db_link;
	private $last_result;


	public static $instance = null;

	public static function getInstance() {
	    if (self::$instance == null) self::$instance = new self(Config::$config);
	    return self::$instance;
	}

    function __construct($config, $debug = false, $freeresult = false, $persistent = false) {
    	$this->debug = $debug;
    	$this->freeresult = $freeresult;

    	$this->db_link = $persistent ? mysql_pconnect($config['dbhost'], $config['dbuser'], $config['dbpassword']) :
    	    mysql_connect($config['dbhost'], $config['dbuser'], $config['dbpassword']);
        if ($this->db_link === false)
            throw new Exception('E_SQL_CONNECT');

        $db_selected = @mysql_select_db($config['dbname'], $this->db_link);

        if ($db_selected === false)
        	throw new Exception('E_SQL_SELECTDB');
        $this->query('SET NAMES UTF8');

    }

    function query($sql) {
    	if ($this->debug)
    		echo "<pre>{$sql}</pre>";

    	$this->last_result = mysql_query($sql, $this->db_link);

    	if ($this->last_result === false)
    		throw new Exception('E_SQL_SQLFAILED: ' . mysql_error($this->db_link));
    }

    function getNumRows() {
    	return mysql_num_rows($this->last_result);
    }

    function fetchRow($assoc = true) {
    	return $assoc ? mysql_fetch_assoc($this->last_result) : mysql_fetch_row($this->last_result);
    }

    function endQuery() {
    	if ($this->freeresult)
    		mysql_free_result($this->last_result);
    }

    function close() {
    	mysql_close($this->db_link);
    }

    function getAffectedRows() {
    	return mysql_affected_rows($this->db_link);
    }

    function getLastId() {    	return mysql_insert_id($this->db_link);
    }

    function escapeString($str) {    	return mysql_real_escape_string($str, $this->db_link);
    }




    function isExists($sql) {
    	$this->query($sql);
    	return $this->getNumRows();
    }

    function getValue($sql) {    	$this->query($sql);
    	$row = $this->fetchRow(false);
    	$this->endQuery();
    	if (!$row) return false;
    	return $row[0];
    }
    function getRow($sql) {
    	$this->query($sql);
    	$row = $this->fetchRow();
    	$this->endQuery();
    	if (!$row) return false;
    	return $row;
    }
    function getRows($sql, $field = false) {
    	$this->query($sql);
    	$rows = array();
    	while ($row = $this->fetchRow())
    		$rows[] = $field ? $row[$field] : $row;
    	$this->endQuery();
    	return $rows;
    }

}
