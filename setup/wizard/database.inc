<?php
	class DBUtil {
		public $dbhost = '';
		public $dbname = '';
		public $dbuname = '';
		public $dbpassword = '';
		public $dbconnection = '';
		
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
      		else 
      			return false;
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
			else 
				return false;
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
				return false;
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
	      mysql_close();
	    }

	}
?>