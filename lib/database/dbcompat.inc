<?php

class DBCompat {
    function & query () {
        global $default;
        $args = func_get_args();
        $default->log->debug("Query was: " . $args[0]);
        $this->lastResult = &call_user_method_array('query', $default->_db, $args);
        $this->lastInsertID = mysql_insert_id($default->_db->connection);
        $this->lastResult->autofree = true;
        return $this->lastResult;
    }

    function num_rows () {
        global $default;
        $default->log->debug("Number of rows was: " . $this->lastResult->numRows());
        return $this->lastResult->numRows();
    }

    function next_record () {
        if (is_int($this->lastResult)) {
            return;
        }
        $this->lastRow = $this->lastResult->fetchRow(DB_FETCHMODE_ASSOC);
        return $this->lastRow;
    }

    function f ($field) {
        global $default;
        $default->log->info("Requesting field $field");
        $value = $this->lastRow[$field];
        $default->log->info("Yielding value $value");
        return $value;
    }

    function insert_id () {
        return $this->lastInsertID;
    }
}

function quote($value) {
    if (is_numeric($value)) {
        return $value;
    } elseif (is_bool($value)) {
        return $value ? 1 : 0;
    } elseif (is_null($value)) {
        return 'NULL';
    }

    if (function_exists('mysql_real_escape_string')) {
        // TODO: include the relevant connection id to take charset into account
        $value = "'" . mysql_real_escape_string($value) . "'";
    } elseif (function_exists('mysql_escape_string')) {
        $value = "'" . mysql_escape_string($value) . "'";
    } else {
        $value = "'" . addslashes($value) . "'";
    }
    return $value;
}

?>
