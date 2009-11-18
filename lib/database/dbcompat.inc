<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
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
