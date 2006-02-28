<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

class DBCompat {
    function & query ($sQuery) {
        global $default;
        $aParams = array();
        if (is_array($sQuery)) {
            $aParams = $sQuery[1];
            $sQuery = $sQuery[0];
        }
        $this->lastResult =& DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($this->lastResult)) {
            $default->log->error("Error running query: " .  DBUtil::lastQuery());
            $default->log->error($this->lastResult->toString());
        } else {
            if (is_object($this->lastResult)) {
                $this->lastResult->autofree = true;
            }
        }
        return $this->lastResult;
    }

    function num_rows () {
        global $default;
        if (PEAR::isError($this->lastResult)) {
            // var_dump($this->lastResult);
            $default->log->error("DBCompat::numrows: lastResult was an error");
            return $this->lastResult;
        }
        $default->log->debug("Number of rows was: " . $this->lastResult->numRows());
        return $this->lastResult->numRows();
    }

    function next_record () {
        if (is_int($this->lastResult)) {
            return;
        }
        if (PEAR::isError($this->lastResult)) {
            return false;
        }
        $this->lastRow = $this->lastResult->fetchRow(DB_FETCHMODE_ASSOC);
        return $this->lastRow;
    }

    function f ($field) {
        global $default;
        // $default->log->debug("Requesting field $field");
        $value = $this->lastRow[$field];
        // $default->log->debug("Yielding value $value");
        return $value;
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
