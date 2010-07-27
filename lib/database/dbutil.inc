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
    
    private static $db;	// Master DB handle. Must always be present.
    private static $dbSlaves = array();
    private static $numSlaves; // count of how many slaves we have

	/**
	* Establish database connections
	* TODO: Add other database handlers here instead of defaulting to mysql
	* @params string $dsn : Any dsn descriptor (remote or local)
	*/
	private static function connect($dsn) {
		$oPear = new PEAR();
    
		$options = array(
			'debug'       => 2,
			'portability' => DB_PORTABILITY_ERRORS,
		);

		$db = DB::connect($dsn, $options);

		// Catch PEAR error (if any)
		if ($oPear->isError($db)) {
			// return PEAR error
			return $oPear->raiseError($db->getMessage());
		}

		$db->setFetchMode(DB_FETCHMODE_ASSOC);
		return $db;
	}

	/**
	 * @Description 
	 * Function to establish database connections
	 * using PEAR::DB
	 * @param mixed $db This could be a database specification or connection
	 * outside of KT
	 * @return mixed $db active DB connection
	 */
     public static function getDB($db = null) {
         global $default;
         $oPear = new PEAR();

         if (is_null($db)) {
             self::$db = self::connect($default->_db);
             // Catch PEAR error (if any)
             if ($oPear->isError($db)) {
                 // return PEAR error
                 return $db;
             
             }
         } else {
             self::$db = $db;
         }

         if(!isset(self::$db->_kt_initialized) || !self::$db->_kt_initialized) {
             self::$db->_kt_initialized = true;
         }

         return self::$db;
     }

    /**
     * Function to establish slave database connections
     * using PEAR::DB
     * @param string $db This could be a database specification or connection
     * outside of KT
     * @return mixed $db active slave DB connection
     * @author Prince Mbekwa
     */
    public static function initSlaves() {
        global $default;
		$oPear = new PEAR();
		foreach($default->_slave as $slave_info) {
             // initialize slave connection with randomized order
             $slave = self::connect($slave_info);
             if ($oPear->isError($slave)) {
                  // return PEAR error
                  continue;
          
              }else{
                self::$dbSlaves[] = $slave;
              }
         }
         if(count(self::$dbSlaves)<=0){
             //No live slaves found, then return master connection
            self::$dbSlaves[] = self::getDB();
         }else{
             //count the slave coonections
             self::$numSlaves = count(self::$dbSlaves);
         }
    }

   public static function getSlave() {

       if(empty(self::$dbSlaves)) {
           self::initSlaves();
       }

        return self::$dbSlaves[rand(0, self::$numSlaves - 1)];
    }
    /**
     *  Runs Database queries 
     * @param string $query Any database queries done here
     * @param mixed $db  Any specified active db
     * @return mixed $result executed query results
     * @author Prince Mbekwa
     */

    public static function runQuery($query, $db = null) {
        global $default;
        $oPear = new PEAR();
        $aParams = null;
        /**
         * We are doing a Binary safe case-insensitive string comparison of the first 6 characters
         * searching the incoming query statement matching "SELECT". We then ltrim it
         * to remove whitespaces. If the string contains SELECT AND Replication is set to TRUE
         * will connect to slave and execute query. This is really not expensive as regular expression check
         * 
         */
       
        if (is_array($query)) {
            $sQuery = $query[0];
            $aParams = (count($query) > 1)?$query[1]:array();
        }else {
            $sQuery = $query;
        }
         if(isset($default->_slave) && (strncasecmp(ltrim($sQuery), "select", 6) === 0 )){
            $db = self::getSlave();
            if($oPear->isError($db))
            {
                return $db;
            }
        }else{
            $db = self::getDB($db);
            if($oPear->isError($db))
            {
                return $db;
            }
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
    /**
     *  @Description 
     * Returns a row of data from a result set then moves the result pointer to the next row. 
     * The data can be formatted as an array or an object.
     * @param string $query
     * @param  mixed $db
     * @return mixed $aRow Recordset row
     * @author Prince Mbekwa
     **/

    public static function getOneResult($query, $db = null) {
        $result = self::runQuery($query, $db);
        if (PEAR::isError($result)) {
            // logging by runQuery
            return $result;
        }
        $aRow = $result->fetchRow();
        $result->free();
        return $aRow;
    }
    /**
     * Runs the query provided and returns the data from the first column of the first row then frees the result set. 
     * @param string $query
     * @param string $key
     * @param mixed $db
     * @return void
     * @author Prince Mbekwa
     **/

    static function getOneResultKey($query, $key, $db = null) {
        $aRow = self::getOneResult($query, $db);
        if (PEAR::isError($aRow)) {
            // logging by runQuery
            return $aRow;
        }
        return $aRow[$key];
    }
    
    /**
     * Returns a row of data from a result set then moves the result pointer to the next row. 
     * The data is formatted as an array
     * @param string $query
     * @return array $aRow
     * @author Prince Mbekwa
     **/

    public static function getResultArray($query, $db = null) {
        $result = self::runQuery($query, $db);
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
    /**
    * Returns a row from a result set from a gievn recordset key. 
    * @param string $query
    * @param sring $key
    * @return array $aRow
    * @author Prince Mbekwa
    **/

    public static function getResultArrayKey($query, $key, $db = null) {
        $result = self::runQuery($query, $db);
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
    
    /**
     * Sets up the database logger for any errors cought in queries
     *
     * @return void
     * @author Prince Mbekwa
     **/

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
    /**
     * Runs multiple Database queries 
     * @param array $aQueries
     * @return boolean on result failure or success
     * @author Prince Mbekwa
     **/

    public static function runQueries($aQueries, $db = null) {
        foreach ($aQueries as $sQuery) {
            $res = self::runQuery($sQuery, $db);
            if (PEAR::isError($res)) {
                return $res;
            }
        }
        return true;
    }
    /**
     *  Automatically prepares and executes INSERT  queries.
     *  This method builds a SQL statement using autoPrepare() and then executes the statement using execute() with it.
     * @param string $sTable
     * @param array $aFieldValues
     * @return mixed last inserted id
     * @author Prince Mbekwa
     **/
    public static function autoInsert($sTable, $aFieldValues, $aOptions = null) {
        if (is_null($aOptions)) {
            $aOptions = array();
        }

        $bNoId = KTUtil::arrayGet($aOptions, 'noid', false);
        global $default;
        // $default->log->debug('AutoInsert called for table ' . $sTable);
        self::getDB();
        $res = self::$db->autoExecute($sTable, $aFieldValues);
        if ($default->queryLog->isDebugEnabled())
        {
            $default->queryLog->debug('Query: ' . self::lastQuery(self::$db));
        }
        if ($res === DB_OK) {
            if ($bNoId)
                return;
            else
            	$ret = self::$db->getLastId();
                return $ret;
        }
        if (PEAR::isError($res)) {
        	self::logQueryError(self::lastQuery(self::$db), $res);
            return $res;
        }
        return PEAR::raiseError(_kt('Unknown return value for autoInsert'));
    }
    /**
       *  Automatically prepares and executes UPDATE queries.
       *  This method builds a SQL statement using autoPrepare() and then executes the statement using execute() with it.
       * @param string $sTable
       * @param array $aFieldValues
       * @return mixed last inserted id
       * @author Prince Mbekwa
       **/

    public static function autoUpdate($sTable, $aFieldValues, $iId, $db = null) {
        global $default;
        // $default->log->debug('AutoUpdate called for table ' . $sTable . ' with id ' . $iId);
        $database = self::getDB($db);
        $res = $database->autoExecute($sTable, $aFieldValues, DB_AUTOQUERY_UPDATE, 'id = ' . $iId);
        $default->queryLog->debug('Query: ' . self::lastQuery($database));
        if ($res === DB_OK) {
            return $res;
        }
        if (PEAR::isError($res)) {
        	self::logQueryError(self::lastQuery($database), $res);
        	return $res;
        }
        return PEAR::raiseError(_kt('Unknown return value for autoUpdate'));
    }
    /**
     * Automatically builds an INSERT or UPDATE SQL statement 
     * based on a gen where clause, later be used by execute() or  executeMultiple(). 
     *
     * @return mixed string
     * @author Prince Mbekwa
     **/

    public static function whereUpdate($sTable, $aFieldValues, $aWhereFieldValues, $db = null) {
        global $default;
        //$default->log->debug('WhereUpdate called for table ' . $sTable);
        $database = self::getDB($db);
        $aWhereFields = array();
        foreach (array_keys($aWhereFieldValues) as $k) {
            $aWhereFields[] = $k . ' = ?';
        }
        $sWhere = join(' AND ', $aWhereFields);
        $aValues = kt_array_merge(array_values($aFieldValues), array_values($aWhereFieldValues));

        $sth = $database->autoPrepare($sTable, array_keys($aFieldValues), DB_AUTOQUERY_UPDATE, $sWhere);
        $res = $database->execute($sth, array_values($aValues));
        $database->freePrepared($sth);

        $default->queryLog->debug('Query: ' . self::lastQuery($database));
        if ($res === DB_OK) {
            return $res;
        }
        if (PEAR::isError($res)) {
            return $res;
        }
        return PEAR::raiseError(_kt('Unknown return value for whereUpdate'));
    }
    /**
     * Gets last executed query
     *
     * @return mixed
     * @author Prince Mbekwa
     **/

    public static function lastQuery($db = null) {
        $database = self::getDB($db);
        return $database->last_query;
    }
    /**
     * Number of rows affected by a data manipulation query (for example INSERT, UPDATE or DELETE). 
     * Returns 0 for SELECT queries. 
     *
     * @return mixed
     * @author Prince Mbekwa
     **/

    public static function affectedRows($db = null) {
        $database = self::getDB($db);
        return $database->affectedRows();
    }
    /**
     * Perform DELETE statements
     * @param string $sTable The affected table 
     * @param string $iId
     * @return mixed
     * @author Prince Mbekwa
     **/

    public static function autoDelete($sTable, $iId, $db = null) {
        $database = self::getDB($db);
        $sQuery = "DELETE FROM " . $sTable . " WHERE id = ?";
        $aParams = array($iId);
        return self::runQuery(array($sQuery, $aParams), $database);
    }
    /**
     * Update record and sets "disabled" field to TRUE on a given id
     *
     * @return boolean
     * @author Prince Mbekwa
     **/

    public static function deReference($sTable, $iId, $db = null) {
        $database = self::getDB($db);
        $sQuery = "UPDATE " . $sTable . " SET disabled = true WHERE id = ?";
        $aParams = array($iId);
        return self::runQuery(array($sQuery, $aParams), $database);
    }
    /**
     * Perform DELETE statements with a given WHERE clause
     * @param string $sTable The affected table 
     * @param array $aWhereFieldValues
     * @return mixed
     * @author Prince Mbekwa
     **/

    public static function whereDelete($sTable, $aWhereFieldValues, $db = null) {
        global $default;
        $database = self::getDB($db);
        $aWhereFields = array();
        foreach (array_keys($aWhereFieldValues) as $k) {
            $aWhereFields[] = $k . ' = ?';
        }
        $sWhere = join(' AND ', $aWhereFields);
        $aValues = array_values($aWhereFieldValues);

        $sQuery = "DELETE FROM " . $sTable . " WHERE $sWhere";
        return self::runQuery(array($sQuery, $aValues), $database);
    }
    /**
     * Performs Autoprepare of given array
     * @param array $aArray
     * @return mixed
     * @author Prince Mbekwa
     **/

    public static function paramArray($aArray) {
        $iNumIds = count($aArray);
        if (empty($iNumIds)) {
            return "";
        }
        return join(",", array_fill(0, $iNumIds, '?'));
    }
    /**
     * Escape a string according to the current DBMS's standards. 
     * @param string $sString 
     * @return mixed
     * @author Prince Mbekwa
     **/

    public static function escapeSimple($sString, $db = null) {
        $database = self::getDB($db);
        return $database->escapeSimple($sString);
    }
    /**
     * Performs a cleaning of an array and removes line endings (\n)
     * @param string $sQuery
     * @return mixed
     * @author Prince Mbekwa
     **/

    public static function compactQuery($sQuery) {
        return str_replace("\n", " ", $sQuery);
    }
    /**
     * Initiates the begining of a Transaction
     * @return void
     * @author Prince Mbekwa
     **/

    public static function startTransaction() {
        self::runQuery("START TRANSACTION");
        $oCache = KTCache::getSingleton();
        $oCache->startTransaction();
    }
    /**
     * Performs a rollback
     *
     * @return void
     * @author Prince Mbekwa
     **/
    public static function rollback() {
        self::runQuery("ROLLBACK");
        $oCache = KTCache::getSingleton();
        $oCache->rollback();
    }
    /**
     * Performs a commit
     *
     * @return void
     * @author Prince Mbekwa
     **/
    public static function commit() {
        self::runQuery("COMMIT");
        $oCache = KTCache::getSingleton();
        $oCache->commit();
    }
    /**
     * Setup admin db connection
     *
     * @return void
     * @author Prince Mbekwa
     **/

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

        $default->_admindb = DB::connect($dsn, $options);
        if (PEAR::isError($default->_admindb)) {
            die($default->_admindb->toString());
        }
        $default->_admindb->setFetchMode(DB_FETCHMODE_ASSOC);
        return $default->_admindb;
    }
    
}
?>