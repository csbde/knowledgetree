<?php
/**
 * $Id$
 *
 * The default database wrapper class.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Mukhtar Dharsey, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.database 
 */
class Database extends DB_Sql {
    
    /** Class name */
    var $classname = "Database";
  
    /** Host name.  Retrieved from config/environment.php */
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
    function Database() {
      global $default;
      $this->Host = $default->dbHost;
      $this->Database = $default->dbName;
      $this->User = $default->dbUser;
      $this->Password = $default->dbPass;
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