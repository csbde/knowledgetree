<?php

/**
 * $Id$
 *
 * Database access utility class
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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
            $db =& $default->_db;
        }

	if(!isset($db->_kt_initialized) || !$db->_kt_initialized) {
	    $db->query("SET NAMES 'utf8'");
	    $db->_kt_initialized = true;
	}

        return $db;
    }

    function &runQuery($query, $db = null) {
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
        if ($default->queryLog) {
            $default->queryLog->debug('Query: ' . DBUtil::lastQuery($db));
        }
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
        $default->queryLog->error($result->toString());
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

    function &autoInsert($sTable, $aFieldValues, $aOptions = null) {
        if (is_null($aOptions)) {
            $aOptions = array();
        }

        $bNoId = KTUtil::arrayGet($aOptions, 'noid', false);
        global $default;
        // $default->log->debug('AutoInsert called for table ' . $sTable);
        $db =& DBUtil::getDB();
        if (!array_key_exists('id', $aFieldValues) && ($bNoId === false)) {
            $res = DBUtil::nextId($sTable, null, $db);
            if (PEAR::isError($res)) {
                return $res;
            }
            $aFieldValues['id'] = $res;
        }
        $res = $db->autoExecute($sTable, $aFieldValues);
        if ($default->queryLog) {
            $default->queryLog->debug('Query: ' . DBUtil::lastQuery($db));
        }
        if ($res === DB_OK) {
            return $aFieldValues['id'];
        }
        if (PEAR::isError($res)) {
            return $res;
        }
        return PEAR::raiseError(_kt('Unknown return value for autoInsert'));
    }

    function &autoUpdate($sTable, $aFieldValues, $iId, $db = null) {
        global $default;
        // $default->log->debug('AutoUpdate called for table ' . $sTable . ' with id ' . $iId);
        $db =& DBUtil::getDB();
        $res = $db->autoExecute($sTable, $aFieldValues, DB_AUTOQUERY_UPDATE, 'id = ' . $iId);
        $default->queryLog->debug('Query: ' . DBUtil::lastQuery($db));
        if ($res === DB_OK) {
            return $res;
        }
        if (PEAR::isError($res)) {
            return $res;
        }
        return PEAR::raiseError(_kt('Unknown return value for autoUpdate'));
    }

    function &whereUpdate($sTable, $aFieldValues, $aWhereFieldValues, $db = null) {
        global $default;
        //$default->log->debug('WhereUpdate called for table ' . $sTable);
        $db =& DBUtil::getDB();
        $aWhereFields = array();
        foreach (array_keys($aWhereFieldValues) as $k) {
            $aWhereFields[] = $k . ' = ?';
        }
        $sWhere = join(' AND ', $aWhereFields);
        $aValues = kt_array_merge(array_values($aFieldValues), array_values($aWhereFieldValues));

        $sth = $db->autoPrepare($sTable, array_keys($aFieldValues), DB_AUTOQUERY_UPDATE, $sWhere);
        $res =& $db->execute($sth, array_values($aValues));
        $db->freePrepared($sth);

        $default->queryLog->debug('Query: ' . DBUtil::lastQuery($db));
        if ($res === DB_OK) {
            return $res;
        }
        if (PEAR::isError($res)) {
            return $res;
        }
        return PEAR::raiseError(_kt('Unknown return value for whereUpdate'));
    }

    function &lastQuery($db = null) {
        $db =& DBUtil::getDB();
        return $db->last_query;
    }

    function autoDelete($sTable, $iId, $db = null) {
        global $default;
        // $default->log->debug('AutoDelete called for table ' . $sTable . ' with id ' . $iId);
        $db =& DBUtil::getDB();
        $sQuery = "DELETE FROM " . $sTable . " WHERE id = ?";
        $aParams = array($iId);
        return DBUtil::runQuery(array($sQuery, $aParams), $db);
    }

    function &whereDelete($sTable, $aWhereFieldValues, $db = null) {
        global $default;
        $db =& DBUtil::getDB();
        $aWhereFields = array();
        foreach (array_keys($aWhereFieldValues) as $k) {
            $aWhereFields[] = $k . ' = ?';
        }
        $sWhere = join(' AND ', $aWhereFields);
        $aValues = array_values($aWhereFieldValues);

        $sQuery = "DELETE FROM " . $sTable . " WHERE $sWhere";
        return DBUtil::runQuery(array($sQuery, $aValues), $db);
    }

    function paramArray($aArray) {
        $iNumIds = count($aArray);
        if (empty($iNumIds)) {
            return "";
        }
        return join(",", array_fill(0, $iNumIds, '?'));
    }

    function &escapeSimple($sString, $db = null) {
        $db =& DBUtil::getDB();
        return $db->escapeSimple($sString);
    }

    function compactQuery($sQuery) {
        return str_replace("\n", " ", $sQuery);
    }

    function startTransaction() {
        DBUtil::runQuery("START TRANSACTION");
        $oCache =& KTCache::getSingleton();
        $oCache->startTransaction();
    }
    function rollback() {
        DBUtil::runQuery("ROLLBACK");
        $oCache =& KTCache::getSingleton();
        $oCache->rollback();
    }
    function commit() {
        DBUtil::runQuery("COMMIT");
        $oCache =& KTCache::getSingleton();
        $oCache->commit();
    }

    function setupAdminDatabase() {
        global $default;
        $dsn = array(
            'phptype'  => $default->dbType,
            'username' => $default->dbAdminUser,
            'password' => $default->dbAdminPass,
            'hostspec' => $default->dbHost,
            'database' => $default->dbName,
        );

        $options = array(
            'debug'       => 2,
            'portability' => DB_PORTABILITY_ERRORS,
            'seqname_format' => 'zseq_%s',
        );

        $default->_admindb = &DB::connect($dsn, $options);
        if (PEAR::isError($default->_admindb)) {
            die($default->_admindb->toString());
        }
        $default->_admindb->setFetchMode(DB_FETCHMODE_ASSOC);
        return;
    }
}

?>
