<?php
require_once("$default->fileSystemRoot/phplib/db_" . $default->dbType .".inc");
require_once("$default->fileSystemRoot/lib/database/escape.inc");
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
    /** Database type */
    var $databaseType = "mysql";
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
      $this->databaseType = $default->dbType;
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
