<?php

/**
 * $Id$
 *
 * Database access utility class
 *
 * Copyright (c) 2004 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
 * @version $Revision$
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.database
 */

require_once ('PEAR.php');

class DBUtil {
    function &getDB($db = null) {
        global $default;
        if (is_null($db)) {
            return $default->_db;
        }
        return $db;
    }

    function runQuery($query, $db = null) {
        global $default;
        $aParams = null;

        $db =& DBUtil::getDB($db);

        if (is_array($query)) {
            $sQuery = $query[0];
            $aParams = $query[1];
        } else {
            $sQuery = $query;
        }
        $res = $db->query($sQuery, $aParams);
        $default->log->info('Query: ' . DBUtil::lastQuery($db));
        return $res;
    }

    function getOneResult($query, $db = null) {
        $result = DBUtil::runQuery($query, $db);
        if (PEAR::isError($result)) {
            DBUtil::logQueryError($query, $result);
            return $result;
        }
        $aRow = $result->fetchRow();
        $result->free();
        return $aRow;
    }

    function getOneResultKey($query, $key, $db = null) {
        $aRow = DBUtil::getOneResult($query, $db);
        if (PEAR::isError($aRow)) {
            return $aRow;
        }
        return $aRow[$key];
    }

    function getResultArray($query, $db = null) {
        $result = DBUtil::runQuery($query, $db);
        if (PEAR::isError($result)) {
            DBUtil::logQueryError($query, $result);
            return $result;
        }

        $aReturn = array();
        while ($aRow = $result->fetchRow()) {
            $aReturn[] = $aRow;
        }
        $result->free();
        return $aReturn;
    }

    function getResultArrayKey($query, $key, $db = null) {
        $result = DBUtil::runQuery($query, $db);
        if (PEAR::isError($result)) {
            DBUtil::logQueryError($query, $result);
            return $result;
        }

        $aReturn = array();
        while ($aRow = $result->fetchRow()) {
            $aReturn[] = $aRow[$key];
        }
        $result->free();
        return $aReturn;
    }

    function logQueryError($query, $result) {
        global $default;
        $default->log->error($result->toString());
    }

    function nextId($seqname, $ondemand = false, $db = null) {
        $db =& DBUtil::getDB($db);

        return $db->nextId($seqname, $ondemand);
    }

    function runQueries($aQueries, $db = null) {
        foreach ($aQueries as $sQuery) {
            $res = DBUtil::runQuery($sQuery, $db);
            if (PEAR::isError($res)) {
                return $res;
            }
        }
        return true;
    }

    function &autoInsert($sTable, $aFieldValues, $db = null) {
        global $default;
        $default->log->info('AutoInsert called for table ' . $sTable);
        $db =& DBUtil::getDB();
        if (!array_key_exists('id', $aFieldValues)) {
            $aFieldValues['id'] = DBUtil::nextId($sTable, null, $db);
        }
        $res = $db->autoExecute($sTable, $aFieldValues);
        $default->log->info('Query: ' . DBUtil::lastQuery($db));
        if ($res === DB_OK) {
            return $aFieldValues['id'];
        }
        if (PEAR::isError($res)) {
            return $res;
        }
        return PEAR::raiseError('Unknown return value for autoInsert');
    }

    function &autoUpdate($sTable, $aFieldValues, $iId, $db = null) {
        global $default;
        $default->log->info('AutoUpdate called for table ' . $sTable . ' with id ' . $iId);
        $db =& DBUtil::getDB();
        $res = $db->autoExecute($sTable, $aFieldValues, DB_AUTOQUERY_UPDATE, 'id = ' . $iId);
        $default->log->info('Query: ' . DBUtil::lastQuery($db));
        if ($res === DB_OK) {
            return $res;
        }
        if (PEAR::isError($res)) {
            return $res;
        }
        return PEAR::raiseError('Unknown return value for autoUpdate');
    }

    function &whereUpdate($sTable, $aFieldValues, $aWhereFieldValues, $db = null) {
        global $default;
        $default->log->info('WhereUpdate called for table ' . $sTable);
        $db =& DBUtil::getDB();
        $aWhereFields = array();
        foreach (array_keys($aWhereFieldValues) as $k) {
            $aWhereFields[] = $k . ' = ?';
        }
        $sWhere = join(' AND ', $aWhereFields);
        $aValues = array_merge(array_values($aFieldValues), array_values($aWhereFieldValues));

        $sth = $db->autoPrepare($sTable, array_keys($aFieldValues), DB_AUTOQUERY_UPDATE, $sWhere);
        $ret =& $db->execute($sth, array_values($aValues));
        $db->freePrepared($sth);

        $default->log->info('Query: ' . DBUtil::lastQuery($db));
        if ($res === DB_OK) {
            return $res;
        }
        if (PEAR::isError($res)) {
            return $res;
        }
        return PEAR::raiseError('Unknown return value for whereUpdate');
    }

    function &lastQuery($db) {
        $db =& DBUtil::getDB();
        return $db->last_query;
    }

    function &autoDelete($sTable, $iId, $db = null) {
        global $default;
        $default->log->info('AutoDelete called for table ' . $sTable . ' with id ' . $iId);
        $db =& DBUtil::getDB();
        $sQuery = "DELETE FROM " . $sTable . " WHERE id = ?";
        $aParams = array($iId);
        return DBUtil::runQuery(array($sQuery, $aParams), $db);
    }

}

?>
