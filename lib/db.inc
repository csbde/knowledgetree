<?php


/**
 * $Id$
 * 
 * Performs db tasks
 *
 * @version $Revision$ 
 * @author Mukhtar Dharsey
 * @package dmslib
 *//
/*-----------------------------------------------------------------*/

/**
 * class Owl_DB extends DB_Sql 
 *
 * This class is used for DB connections
 *
 * @version v 1.1.1.1 2002/12/04
 * @author michael
 * @package Owl
 */

class Owl_DB extends DB_Sql {
    
    /** Class name */
    var $classname = "Owl_DB";

    // BEGIN wes changes -- moved these settings to config/owl.php
    // Server where the database resides
    
    /** Host name.  Retrieved from config/owl.php */
    var $Host = ""; 
    /** Database name */
    var $Database = "";
    /** Database user */
    var $User = "";
    /** Database user password */
    var $Password = "";     
    /** Query to execute */
    var $sQuery;
    /** Name of table last query was executed on*/
    var $sLastTableName;
    /** Where clause last used in query execution */
    var $sLastWhereClause;
    /** Order by clause last used in query execution */
    var $sLastOrderByClause;        
    
    /** Default Constructor */
    function Owl_DB() {
      global $default;
      $this->Host = $default->owl_db_host;
      $this->Database = $default->owl_db_name;
      $this->User = $default->owl_db_user;
      $this->Password = $default->owl_db_pass;
    }
    // END wes changes
    
    /**
    *   Create a query from the provided paramaters.  The ID column
    *   is seleted by default
    *   
    *   @param $sTableName      Table to query
    *   @param $aColumns        Columns in table
    *   @param $sWhereClause    Where clause (optional)
    *   @param $sOrderByClause  Order by clause (optional)
    */
    function createSQLQuery($sTableName, $aColumns, $sWhereClause = null, $sOrderByClause = null) {
        $this->sLastTableName = $sTableName;
        $this->sLastWhereCluase = $sWhereClause;
        $this->sLastOrderByClause = $sOrderByClause;
        
        $this->sQuery = "SELECT ID, ";
        
        for( $i = 0; $i < count($aColumns) - 1; $i++ ) {            
            $this->sQuery = $this->sQuery . $aColumns[$i] . ",";
        }
        
        $this->sQuery .= $aColumns[count($aColumns) - 1] . " ";
        $this->sQuery .= "FROM " . $sTableName . " ";
        
        if (isset($sWhereClause)) {
            $this->sQuery .= "WHERE " . $sWhereClause . " ";
        }   
        
        if (isset($sOrderByClause)) {
            $this->sQuery .= "ORDER BY " . $sOrderByClause . " ";
        }
		
		$this->query($this->sQuery);
        
    }
    
    /**
        Create a query from the provided paramaters, specifying a limit and an offset.
        The ID column is selected by default
        
        @param $sTableName      Table to query
        @param $aColumns        Columns in table
        @param $iOffset         Offset
        @param $iLimit          Limit
        @param $sWhereClause    Where clause (optional)
        @param $sOrderByClause  Order by clause (optional)
    */
    
    function createSQLQueryWithOffset($sTableName, $aColumns, $iOffset, $iLimit, $sWhereClause = null, $sOrderByClause = null) {
        $this->sLastTableName = $sTableName;
        $this->sLastWhereCluase = $sWhereClause;
        $this->sLastOrderByClause = $sOrderByClause;
        
        $this->sQuery = "SELECT ID, ";
        
        for( $i = 0; $i < count($aColumns) - 1; $i++ ) {            
            $this->sQuery = $this->sQuery . $aColumns[$i] . ",";            
        }
        
        $this->sQuery .= $aColumns[count($aColumns) - 1] . " ";
        $this->sQuery .= "FROM " . $sTableName . " ";
        
        
        
        if (isset($sWhereClause)) {
            $this->sQuery .= "WHERE " . $sWhereClause . " ";
        }   
        
        if (isset($sOrderByClause)) {
            $this->sQuery .= "ORDER BY " . $sOrderByClause . " ";
        }
        
        $this->sQuery .= "LIMIT " . $iOffset . ", " . $iLimit;
		$this->query($this->sQuery);
    }
    
    /**
    *   Get the result count for the previously executed query.  Meant
    *   to be used in conjuction with createSSQLQueryWithOffset so that
    *   the total number of results can be calculated
    *   
    *   @return int row count
    */
    function & getLastQueryResultCount() {
        if (isset($this->sLastTableName)) {
            $sCountResultQuery = "SELECT COUNT(*) AS ResultCount FROM " . $this->sLastTableName;
            
            if (isset($this->sLastWhereClause)) {
                sCountResultQuery . " WHERE " . $this->sLastWhereClause;
            }
            $this->query($sCountResultQuery);
            $this->next_record();
            return $this->f("ResultCount");
        } else {
            return 0;
        }
    }
    
    /**
    *   Execute the query and return the results
    *   
    *   @returns    Results of query
    */
    function & getQueryResults() {
        $result = null;
        if (isset($this->sQuery)) {
            $result = $this->query($this->sQuery);          
        }
        return $result;
    }
    
   /**
   * Display any database errors encountered
   */
   function haltmsg($msg) {
       printf("</td></table><b>Database error:</b> %s<br>\n", $msg);
       printf("<b>SQL Error</b>: %s (%s)<br>\n",$this->Errno, $this->Error);
   }
}

?>