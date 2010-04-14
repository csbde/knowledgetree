<?php

require_once (HOME . '/common/ConfigManager.inc.php');

// TODO error handling of responses

class DbObject {

    public function __construct()
    {
        // connect
        ConfigManager::load(HOME . '/config/database.ini');
        if (ConfigManager::error()) {
            // log error and die...
            die (ConfigManager::getErrorMessage());
        }

        // load amazon authentication information
        $dbParams = ConfigManager::getSection('Connection');

        mysql_pconnect($dbParams['host'], $dbParams['user'], $dbParams['pass']);
        mysql_select_db($dbParams['database']);
    }

    public function insert($id, $object)
    {
        $result = mysql_query("INSERT INTO queue_store VALUES ('$id', '" . mysql_real_escape_string($object) . "', NOW()) "
        . "ON DUPLICATE KEY UPDATE object = '" . mysql_real_escape_string($object) . "'");
    }

    public function update($id)
    {
        $result = mysql_query("UPDATE queue_store SET object = '" . mysql_real_escape_string($object) . "' WHERE id = '$id'");
    }

    public function select($id)
    {
        $result = mysql_query("SELECT object FROM queue_store WHERE id = '$id'");
        $object = mysql_fetch_row($result);

        return $object[0];
    }

    public function delete($id)
    {
        $result = mysql_query("DELETE FROM queue_store WHERE id = '$id'");
    }

    public function lock()
    {
        mysql_query('LOCK TABLE queue_store');
    }

    public function unlock()
    {
        mysql_query('UNLOCK TABLES');
    }

    /**
     * Utility function called by the cleanSQS script
     * DO NOT run this function unless you are sure that it is what you want to do
     */
    public function clear()
    {
        mysql_query('TRUNCATE queue_store');
    }

}

?>