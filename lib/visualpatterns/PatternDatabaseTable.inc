<?php
/**
 * $Id$
 *
 * Builds a query using the table name and the array of specified columns
 * and displays the results in an HTML table.  The ID column of the table is
 * included by default (basically renders a database table in html)
 *
 * The first column in the table can be rendered as a link 
 * to the document/folder using the $iLinkType variable to specify the link type,
 * the $sLinkPageURL to specify the page URL to link to and $sLinkImageURL to specify
 * the image to display in the case of either a $iLinkType of 2 (image only) or 3 (image + text)
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.visualpatterns
 * @todo $iLinkImageURL is hard coded - change
 * @todo $sLinkPageURL is hard coded - change
 */
class PatternDatabaseTable {
	
	/* Name of table to query */
	var $sTableName;
	/* Columns in table to query (ID is included by default) */
	var $aColumns;
	/* Column types.  Possibles are 1 = text, 2 = boolean*/
	var $aColumnTypes;
	/* Where clause */
	var $sWhereClause;
	/* Order by clause */
	var $sOrderByClause;
	/* Link type.  Possibles are: 0 = none; 1 = text; 2 = image, 3 = text/image */
	var $iLinkType;
	/* Image to display if $iLinkType = 1 */
	var $sLinkImageURL;
	/* URL of page to redirect to */
	var $sLinkPageURL = "Navigate.inc";
	/* Number of result to display in the table.  The default value is 15 */
	var $iResultsToDisplay;
	/* Result  number to start on (internal variable - not set by developer) */
	var $iStartIndex;
	
	function PatternTableSqlQuery($sTmpTableName, $aTmpColumns, $aTmpColumnTypes, $sTmpWhereClause = null, $iTmpLinkType = 0, $sTmpLinkImageURL = null, $iTmpResultsToDisplay = 15) {
		$this->sTableName = & $sTmpTableName;
		$this->aColumns = & $aTmpColumns;
		$this->aColumnTypes = $aTmpColumnTypes;
		$this->sWhereClause = & $sTmpWhereClause;
		$this->iLinkType = & $iTmpLinkType;
		$this->iResultsToDisplay = $iTmpResultsToDisplay;
		$this->sLinkImageURL = "C:\temp\test\up.jpg";		
	}
	
	/** Set the variable $this->sTableName */
	function setTableName($sNewVal) {
		$this->sTableName = & $sNew7Val;
	}
	
	/** Set the variable $this->aColumns */
	function setColumns($aNewVal) {
		$this->aColumns = & $aNewVal;
	}
	
	/** Set the variable $this->aColumnTypes */
	function setColumnTypes($aNewVal) {
		$this->aColumnTypes = $aTmpColumnTypes;
	}
	
	/** Set the variable $this->sWhereClause */
	function setWhereClause($sNewVal) {
		$this->sWhereClause = & $sNewVal;		
	}
	
	/** Set the variable $this->iLinkType */
	function setLinkType($iNewVal) {
		$this->iLinkType = & $iNewVal;
	}
	
	/** Set the variable $this->sLinkImageURL */
	function setLinkImageURL($sNewVal) {
		$this->sLinkImageURL = & $sNewVal;
	}
	
	/** Set the variable $this->iStartIndex */
	function setStartIndex($iNewVal) {
		$this->iStartIndex = & $iNewVal;
	}
	
	/** Set the variable $this->iResultsToDisplay */
	function setResultsToDisplay($iNewVal) {
		$this->iResultsToDisplay = & $iNewVal;
	}
	
	
	/**
	* Build the HTML string used to render the object
	*
	* @return String of HTML used to render object
	*
	* @todo possibly add in image size restraints for link types 2 and 3
	*/	
	function & render() {
        global $default;
        
		$sToRender = "<table width=\"100%\" height=\"100%\">\n";
		$sToRender .= "<tr>\n";
		//$i starts at 1 because the $aColumns[0] is the ID column, which
		//is used in links, but never actually displayed
		for ($i = 0; $i < count($this->aColumns); $i++) {			
			$sToRender .= "<th align=\"left\">".$this->aColumns[$i]."</th>\n";
		}
		$sToRender .= "</tr>\n";
		
		$sql = & $default->db;
		$sql->createSQLQueryWithOffset($this->sTableName, $this->aColumns, $this->iStartIndex, $this->iResultsToDisplay);
		
				
		$iDisplayed = 0;
		//limit the result set displayed		
		while(($aRow = $sql->next_record()) && ($iDisplayed < $this->iResultsToDisplay)) {
			$sToRender .= "<tr>";			
			
			//get the value for each column in the row
			//and put it in a table column.
			//$i starts at 1 because $aRow[0] is the ID column for the specified table, which
			//is used in links, but never actually displayed
			//$i < count(this->aColumns) + 1 because $this->aColumns should not contain the ID column
			for ($i = 1; $i < count($this->aColumns) + 1; $i++) {				
				//if the first column is a link column
				if (($this->iLinkType == 1 || $this->iLinkType == 2 || $this->iLinkType == 3) && ($i == 1)) {
					switch ($this->iLinkType) {
						case 1:							
							//display text only
							$sToRender .= "<td><a href=\"" . $this->iLinkPageURL . "?fID=" . $aRow[0] . "&fTableName=" . $this->sTableName . "\">" . $aRow[$i] . "</td>\n";					
						
						$sToRender .= "</a></td>\n";
						break;
						case 2:
							//display an image only
							$sToRender .= "<td><a href=\"" . $this->iLinkPageURL . "?fID=" . $aRow[0] . "&fTableName=" . $this->sTableName . "\"><img src=\"" . $this->sLinkImageURL . "\"/></a></td>\n";
							break;
						case 3:
							//display both an image and text
							$sToRender .= "<td><a href=\"" . $this->iLinkPageURL . "?fID=" . $aRow[0] . "&fTableName=" . $this->sTableName . "\"><img src=\"" . $this->sLinkImageURL . "\"/>" . $aRow[0] . "</a></td>\n";
							break;
						default:
							break;
					}
				} else {
						$sToRender .= "<td>".$aRow[$i]."</td>\n";
				}
			}
			$sToRender .= "</tr>\n";
			$iDisplayed++;
		}
		
		//if we displayed less results than the number to display
		//simply pad the table
		while ($iDisplayed < $this->iResultsToDisplay) {
			$sToRender .= "<tr><td>&nbsp</td></tr>\n";
			$iDisplayed++;
		}
		
		$sToRender .= "<tr>\n";		
		
		/* Display only the next button */
		if (($this->iStartIndex + $this->iResultsToDisplay) < $sql->getLastQueryResultCount() && $this->iStartIndex == 0) {
			$sToRender .= "<td>";
			$sToRender .= ("<a href=\"test.php?fStartIndex=" . ($this->iStartIndex +  $this->iResultsToDisplay) . "\">Next</a>");
			$sToRender .= "</td>\n";
		}
		/* Display both the next and the previous buttons */		
		else if (($this->iStartIndex + $this->iResultsToDisplay) < $sql->getLastQueryResultCount() && $this->iStartIndex > 0) {
			$sToRender .= "<td>";
			$sToRender .= ("<a href=\"test.php?fStartIndex=" . ($this->iStartIndex +  $this->iResultsToDisplay) . "\">Next</a>");
			$sToRender .= "</td>";
			$sToRender .= "<td>";
			$sToRender .= ("<a href=\"test.php?fStartIndex=" . ($this->iStartIndex - $this->iResultsToDisplay) . "\">Previous</a>");
			$sToRender .= "</td>\n";
			
		}	
		/* Display only the previous button */
		else if ($this->iStartIndex > 0) {
			$sToRender .= "<td>\n";
			$sToRender .= ("&nbsp");
			$sToRender .= "</td>";
			$sToRender .= "<td>\n";
			$sToRender .= ("<a href=\"test.php?fStartIndex=" . ($this->iStartIndex - $this->iResultsToDisplay) . "\">Previous</a>");
			$sToRender .= "</td>";
		}
		
		
		//$sToRender .= "</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "</table>\n";		
		return $sToRender;
	}
	
}

?>
