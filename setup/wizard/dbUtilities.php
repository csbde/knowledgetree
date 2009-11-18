<?php
/**
* Installer Database Control.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
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
*
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Installer
* @version Version 0.1
*/
class dbUtilities {
	/**
	* Host
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string 
	*/
	protected $dbhost = '';
	
	/**
	* Host
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string 
	*/
	protected $dbname = '';
	
	/**
	* Host
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string 
	*/
	protected $dbuname = '';

	/**
	* Host
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string 
	*/
	protected $dbpassword = '';
	
	/**
	* Host
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var object mysql connection
	*/
	protected $dbconnection = '';
	
	/**
	* Any errors encountered
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array 
	*/
	protected $error = array();
	
	/**
	* Constructs database connection object
	*
	* @author KnowledgeTree Team
	* @access public
 	*/
	public function __construct() {
		
	}
	
	public function load($dhost = 'localhost', $dport = 'default', $duname, $dpassword, $dbname) {
		if(!$this->isConnected($dhost, $duname, $dpassword, $dbname)) {
			if($dport == 'default' || $dport == '')
				$dport = '3306';
			$this->dbhost = $dhost.":".$dport;
			$this->dbuname = $duname;
			$this->dbpassword = $dpassword;
			$this->dbconnection = @mysql_connect($dhost, $duname, $dpassword);
			$this->dbname = $dbname;
		}
	}

	public function isConnected($dhost = 'localhost', $duname, $dpassword, $dbname) {
		$current = array($this->dbhost, $this->dbuname, $this->dbpassword, $this->dbname);
		$new = array($dhost, $duname, $dpassword, $dbname);
		$diff = array_diff($new, $current);
		if(count($diff) == 0) {
			if($this->getDatabaseLink()) // Make sure theres a link
				return true; // Already connected
		}
		return false; // Reconnect
	}
	
	public function getDatabaseLink() {
		return $this->dbconnection;
	}
	/**
	* Choose a database to use
	*
	* @param string $dbname name of the database
	* @access public
	* @return boolean
	*/
	public function useDb() {
		if(@mysql_select_db($this->dbname, $this->dbconnection))
			return true;
		else {
			$this->error[] = @mysql_error($this->dbconnection);
			return false;
		}
	}
	
	public function setDb($dbname) {
		$this->dbname = $dbname;
	}
	
   /**
    * Query the database.
    * 
    * @param $query the sql query.
    * @access public
    * @return object The result of the query.
    */
    public function query($query) {
		$this->useDb();
      	$result = mysql_query($query, $this->dbconnection);
		if($result) {
			return $result;
		} else {
			$this->error[] = @mysql_error($this->dbconnection);
			return false;
		}
    }

   /**
    * Do the same as query.
    * 
    * @param $query the sql query.
    * @access public
    * @return boolean
    */
    public function execute($query) {
    	$this->useDb();
      	$result = @mysql_query($query, $this->dbconnection);
		if(!$result) {
			$this->error[] = @mysql_error($this->dbconnection);
		}
		
		return $result;
    }
    
	/** 
	* Convenience method for mysql_fetch_object().
	* 
	* @param $result The resource returned by query().
	* @access public
	* @return object An object representing a data row.
	*/
    public function fetchNextObject($result = NULL) {
	      if ($result == NULL || @mysql_num_rows($result) < 1)
	        return NULL;
	      else
	        return @mysql_fetch_object($result);
    }

	/** 
	* Convenience method for mysql_fetch_assoc().
	* 
	* @param $result The resource returned by query().
	* @access public
	* @return array Returns an associative array of strings.
	*/
    public function fetchAssoc($result = NULL) {
    	$r = array();
	      if ($result == NULL || @mysql_num_rows($result) < 1)
	        return NULL;
	      else {
	      	while(($r[] = mysql_fetch_assoc($result)) || array_pop($r)); 
   			return $r;
	      }
    }
    
   	/**
   	 * Close the connection with the database server.
   	 * 
     * @param none.
     * @access public
     * @return void.
     */
    public function close() {
      @mysql_close($this->dbconnection);
    }

   	/**
   	 * Get database errors.
   	 * 
     * @param none.
     * @access public
     * @return array.
     */
    public function getErrors() {
		if(!$this->dbconnection) {
			$this->error[] = @mysql_error();
		}
    	return $this->error;
    }
    
    public function clearErrors() {
    	return $this->error = array();
    }
    
    /**
     * Fetches the last generated error
     
     * @return string 
     */
    function getLastError() {
        return end($this->error);
    }
    
    /**
     * Start a database transaction 
     */
    public function startTransaction() {
        $this->query("START TRANSACTION");
    }
    
    /**
     * Roll back a database transaction 
     */
    public function rollback() {
        $this->query("ROLLBACK");
    }
    
	public function runQueries($aQueries) {
        foreach ($aQueries as $sQuery) {
            $res = $this->query($sQuery);
            if (!$res) {
                return $res;
            }
        }
        return true;
	}
}
?>