<?php

class DB {
    static $instance = null;
    function __construct() {
        $this->instance = $this;
    }
    function getRows($table) {
        $table = SYS . "$table.dat";
        if (!file_exists($table)) touch($table);
        $dat = file_get_contents($table);    
        return explode("\n", $dat);
    }
    function putRows($table, $rows) {
        $table = SYS . "$table.dat";
        file_put_contents($table, implode("\n", $rows));
    }
    function getInstance() {
        return (self::$instance == null) ? new DB() : self::$instance;
    }

}
