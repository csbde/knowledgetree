<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
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
