<?php
	class DBUtil {
		public $dbhost = '';
		public $dbname = '';
		public $dbuname = '';
		public $dbpassword = '';
		public $dbconnection = '';
		public $error = '';
		
		public function __construct() {
		}
		
		/** Connect to a MySQL database
		 * 
		 * @param string $dhost host
		 * @param string $duname database username
		 * @param string $dpassword database password
		 * @access public
		 * @return boolean
      	*/
		public function DBUtil($dhost = 'localhost', $duname, $dpassword, $dbname = '') {
			$this->dbhost = $dhost;
			$this->dbuname = $duname;
			$this->dbpassword = $dpassword;
			$this->dbconnection = @mysql_connect($dhost, $duname, $dpassword);
			if($dbname != '') {
				$this->setDb($dbname);
				$this->useBD($dbname);
			}
      		if($this->dbconnection)
      			return $this->dbconnection;
      		else {
      			$this->error = @mysql_error();
      			return false;
      		}
		}
		
		/**
		* Choose a database to use
		*
		* @param string $dbname name of the database
		* @access public
		* @return boolean
		*/
		public function useBD($dbname) {
			if($dbname != '') {
				$this->setDb($dbname);
			}
			
			if(@mysql_select_db($this->dbname))
				return true;
			else {
				$this->error = @mysql_error();
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
	    * @return The result of the query.
	    */
	    public function query($query)
	    {
	      $result = @mysql_query($query);
			if($result) {
				return $result;
			} else {
				$this->error = @mysql_error();
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
	      $result = @mysql_query($query);
			if($result) {
				return true;
			} else {
				$this->error = @mysql_error();
				return false;
			}
	    }
	    
		/** Convenience method for mysql_fetch_object().
		* @param $result The resource returned by query().
		* @access public
		* @return An object representing a data row.
		*/
	    public function fetchNextObject($result = NULL) {
		      if ($result == NULL || mysql_num_rows($result) < 1)
		        return NULL;
		      else
		        return mysql_fetch_object($result);
	    }
    
		/** Convenience method for mysql_fetch_assoc().
		* @param $result The resource returned by query().
		* @access public
		* @return array Returns an associative array of strings.
		*/
	    public function fetchAssoc($result = NULL) {
	    	$r = array();
		      if ($result == NULL || mysql_num_rows($result) < 1)
		        return NULL;
		      else {
	   			while ($row = mysql_fetch_assoc($result)) {
	   				$r[] = $row;
	   			}
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
	      @mysql_close();
	    }

	   	/**
	   	 * Get database errors.
	   	 * 
	     * @param none.
	     * @access public
	     * @return string.
	     */
	    public function getErrors() {
	    	return $this->error;
	    }
	}
?>