<?php
/**
 * $Id$
 *
 * Database access utility class
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
 */

require_once ('PEAR.php');
require_once('DB.php');

class DBUtil {
    
    public static function getDB($db = null) {
        global $default;
		$oPear = new PEAR();
		/**
		 * Establish database connections
		 * TODO: Add other database handlers here instead of defaulting to mysql
		 *@params @db : Any db connection (remote or local)
		 */
        if (is_null($db)) {
			$options = array(
                'debug'       => 2,
                'portability' => DB_PORTABILITY_ERRORS,
                'seqname_format' => 'zseq_%s',
                );
			$master_conn = DB::connect($default->_db, $options);
			
			//Catch PEAR error (if any)
			if ($oPear->isError($master_conn)) {
	            // return PEAR error
	            return PEAR::raiseError($master_conn->getMessage());
	        } 
	        $master_conn->setFetchMode(DB_FETCHMODE_ASSOC);
	        return $master_conn;
        }

        if(!isset($db->_kt_initialized) || !$db->_kt_initialized) {
            $db->query("SET NAMES 'utf8'");
            $db->_kt_initialized = true;
        }

        return $db;
    }
    
    public static function getSlave($db = null) {
        global $default;
		$oPear = new PEAR();
		
		if(is_null($db)) {
		    $options = array(
                'debug'       => 2,
                'portability' => DB_PORTABILITY_ERRORS,
                'seqname_format' => 'zseq_%s',
                );
            $slave_dns = $default->_slave;
             
			$slave_conn = DB::connect($default->_slave, $options);
			//Catch PEAR error (if any)
			if ($oPear->isError($slave_conn)) {
	            // return PEAR error
	            return  PEAR::raiseError($slave_conn->getMessage());
	        } 
	        $slave_conn->setFetchMode(DB_FETCHMODE_ASSOC);
	        $db =  $slave_conn;
	        return $db;
		}
		if(!isset($db->_kt_initialized) || !$db->_kt_initialized) {
            $db->query("SET NAMES 'utf8'");
            $db->_kt_initialized = true;
        }
      
        return $db;
    }

    public static function &runQuery($query, $db = null,$select = false) {
        global $default;
        $oPear = new PEAR();
        $aParams = null;
        if(isset($default->_slave) && $select == true){
            $db = self::getSlave($db);
        }else{
            $db = self::getDB($db);
        }  
        if (is_array($query)) {
            $sQuery = $query[0];
            $aParams = (count($query) > 1)?$query[1]:array();
        } else {
            $sQuery = $query;
        }
        $res = $db->query($sQuery, $aParams);
        if (isset($default->queryLog) && $default->queryLog->isDebugEnabled())
        {
            $default->queryLog->debug(self::lastQuery($db));
        }
        if ($oPear->isError($res))
        {
        	self::logQueryError(self::lastQuery($db), $res);
        }
        return $res;
    }

    public static function getOneResult($query, $db = null) {
        $result = self::runQuery($query, $db, true);
        if (PEAR::isError($result)) {
            // logging by runQuery
            return $result;
        }
        $aRow = $result->fetchRow();
        $result->free();
        return $aRow;
    }

    static function getOneResultKey($query, $key, $db = null) {
        $aRow = self::getOneResult($query, $db);
        if (PEAR::isError($aRow)) {
            // logging by runQuery
            return $aRow;
        }
        return $aRow[$key];
    }

    public static function getResultArray($query, $db = null) {
        $result = self::runQuery($query, $db, true);
        $oPear = new PEAR();
        if ($oPear->isError($result)) {
            // logging by runQuery
            return $result;
        }

        $aReturn = array();
        while ($aRow = $result->fetchRow()) {
            $aReturn[] = $aRow;
        }
        $result->free();
        return $aReturn;
    }

    public static function getResultArrayKey($query, $key, $db = null) {
        $result = self::runQuery($query, $db, true);
        if (PEAR::isError($result)) {
            // logging by runQuery
            return $result;
        }

        $aReturn = array();
        while ($aRow = $result->fetchRow()) {
            $aReturn[] = $aRow[$key];
        }
        $result->free();
        return $aReturn;
    }

    public static function logQueryError($query, $result) {
        global $default;
        if (isset($default->queryLog) && !$default->queryLog->isDebugEnabled())
        {
            // if debug is enabled, the query is already logged.
            $default->queryLog->error($query);
        }
        if(isset($default->log)){
            $default->log->error('Query error: ' . $result->getMessage());
        }
    }

    public static function runQueries($aQueries, $db = null) {
        foreach ($aQueries as $sQuery) {
            $res = self::runQuery($sQuery, $db, true);
            if (PEAR::isError($res)) {
                return $res;
            }
        }
        return true;
    }

    public static function autoInsert($sTable, $aFieldValues, $aOptions = null) {
        if (is_null($aOptions)) {
            $aOptions = array();
        }

        $bNoId = KTUtil::arrayGet($aOptions, 'noid', false);
        global $default;
        // $default->log->debug('AutoInsert called for table ' . $sTable);
        $db =& self::getDB();
        $res = $db->autoExecute($sTable, $aFieldValues);
        if ($default->queryLog->isDebugEnabled())
        {
            $default->queryLog->debug('Query: ' . self::lastQuery($db));
        }
        if ($res === DB_OK) {
            if ($bNoId)
                return;
            else
            	$ret=$db->getLastId();
                return $ret;
        }
        if (PEAR::isError($res)) {
        	self::logQueryError(self::lastQuery($db), $res);
            return $res;
        }
        return PEAR::raiseError(_kt('Unknown return value for autoInsert'));
    }

    public static function autoUpdate($sTable, $aFieldValues, $iId, $db = null) {
        global $default;
        // $default->log->debug('AutoUpdate called for table ' . $sTable . ' with id ' . $iId);
        $db = self::getDB();
        $res = $db->autoExecute($sTable, $aFieldValues, DB_AUTOQUERY_UPDATE, 'id = ' . $iId);
        $default->queryLog->debug('Query: ' . self::lastQuery($db));
        if ($res === DB_OK) {
            return $res;
        }
        if (PEAR::isError($res)) {
        	self::logQueryError(self::lastQuery($db), $res);
        	return $res;
        }
        return PEAR::raiseError(_kt('Unknown return value for autoUpdate'));
    }

    public static function whereUpdate($sTable, $aFieldValues, $aWhereFieldValues, $db = null) {
        global $default;
        //$default->log->debug('WhereUpdate called for table ' . $sTable);
        $db = self::getDB();
        $aWhereFields = array();
        foreach (array_keys($aWhereFieldValues) as $k) {
            $aWhereFields[] = $k . ' = ?';
        }
        $sWhere = join(' AND ', $aWhereFields);
        $aValues = kt_array_merge(array_values($aFieldValues), array_values($aWhereFieldValues));

        $sth = $db->autoPrepare($sTable, array_keys($aFieldValues), DB_AUTOQUERY_UPDATE, $sWhere);
        $res = $db->execute($sth, array_values($aValues));
        $db->freePrepared($sth);

        $default->queryLog->debug('Query: ' . self::lastQuery($db));
        if ($res === DB_OK) {
            return $res;
        }
        if (PEAR::isError($res)) {
            return $res;
        }
        return PEAR::raiseError(_kt('Unknown return value for whereUpdate'));
    }

    public static function lastQuery($db = null) {
        $db = self::getDB();
        return $db->last_query;
    }

    public static function affectedRows($db = null) {
        $db = self::getDB();
        return $db->affectedRows();
    }

    public static function autoDelete($sTable, $iId, $db = null) {
        $db = self::getDB();
        $sQuery = "DELETE FROM " . $sTable . " WHERE id = ?";
        $aParams = array($iId);
        return self::runQuery(array($sQuery, $aParams), $db);
    }

    public static function deReference($sTable, $iId, $db = null) {
        $db = self::getDB();
        $sQuery = "UPDATE " . $sTable . " SET disabled = true WHERE id = ?";
        $aParams = array($iId);
        return self::runQuery(array($sQuery, $aParams), $db);
    }

    public static function whereDelete($sTable, $aWhereFieldValues, $db = null) {
        global $default;
        $db = self::getDB();
        $aWhereFields = array();
        foreach (array_keys($aWhereFieldValues) as $k) {
            $aWhereFields[] = $k . ' = ?';
        }
        $sWhere = join(' AND ', $aWhereFields);
        $aValues = array_values($aWhereFieldValues);

        $sQuery = "DELETE FROM " . $sTable . " WHERE $sWhere";
        return self::runQuery(array($sQuery, $aValues), $db);
    }

    public static function paramArray($aArray) {
        $iNumIds = count($aArray);
        if (empty($iNumIds)) {
            return "";
        }
        return join(",", array_fill(0, $iNumIds, '?'));
    }

    public static function &escapeSimple($sString, $db = null) {
        $db = self::getDB();
        return $db->escapeSimple($sString);
    }

    public static function compactQuery($sQuery) {
        return str_replace("\n", " ", $sQuery);
    }

    public static function startTransaction() {
        self::runQuery("START TRANSACTION");
        $oCache =& KTCache::getSingleton();
        $oCache->startTransaction();
    }
    
    public static function rollback() {
        self::runQuery("ROLLBACK");
        $oCache =& KTCache::getSingleton();
        $oCache->rollback();
    }
    
    public static function commit() {
        self::runQuery("COMMIT");
        $oCache =& KTCache::getSingleton();
        $oCache->commit();
    }

    public static function setupAdminDatabase() {
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