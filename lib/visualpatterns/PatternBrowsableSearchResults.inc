<?php
/**
 * $Id$
 *
 * Renders paginated query results in a table.
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
 */
class PatternBrowseableSearchResults {
	
	/** query to be executed */
	var $sQuery;
	/** columns in query to be displayed */
	var $aColumns;
	/** column types */
	var $aColumnTypes;
	/** column headings to display */
	var $aColumnHeadings;
	/** link href for column type 3 */
	var $aLinkURLs;
	/** database column values to append to link url if a column type of 3 is specified */
	var $aDBQueryStringColumns;
	/** variables names to give $aDBQueryStringColumns on the query string */
	var $aQueryStringVariableNames;
	/** number of results to display per page */
	var $iResultsToDisplay;
	/* Result  number to start on  */
	var $iStartIndex;
	/** Column to order by */
	var $sOrderByColumn;
	/** direction of ordering, ascending or descending */
	var $sOrderDirection = "ASC";
	/** New QueryString when submitting to self */
	var $sQueryString;
	/** Search criteria **/
	var $sSearchText;
	
	function PatternBrowseableSearchResults($sTmpQuery, $iTmpResultsToDisplay, $aTmpColumns, $aTmpColumnTypes, $aTmpColumnHeaders, $aTmpLinkURLs = null, $aTmpDBQueryStringColumns = null, $aTmpQueryStringVariableNames = null) {
		$this->sQuery = $sTmpQuery;
		$this->iResultsToDisplay = $iTmpResultsToDisplay;
		$this->aColumns = $aTmpColumns;
		$this->aColumnHeadings = $aTmpColumnHeaders;
		$this->aColumnTypes = $aTmpColumnTypes;
		$this->aLinkURLs = $aTmpLinkURLs;
		$this->aDBQueryStringColumns = $aTmpDBQueryStringColumns;		
		$this->aQueryStringVariableNames = $aTmpQueryStringVariableNames;
		$this->sQueryString = "";		
	}
	
	function setQueryString($sNewQueryString) {
		$this->sQueryString = $sNewQueryString;
	}
	
	function getQueryString() {
		return $this->sOrderByColumn;
	}
	
	function setStartIndex($iNewValue) {
		$this->iStartIndex = $iNewValue;
	}
	
	function setOrderByColumn($sNewValue) {
		$this->sOrderByColumn = $sNewValue;
	}
	
	function setOrderDirection($sNewValue) {
		$this->sOrderDirection = $sNewValue;
	}
	function setSearchText($sNewValue) {
		$this->sSearchText = $sNewValue;
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
		
		$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
		$sTHBGColour = $default->siteMap->getSectionColour($sSectionName, "th");
		
		// run the query first and get the number of rows
		$iTotalResults = $this->getResultCount();
				
		// now add the limit and offset stuff for cutting down result set
		// decrement startIndex because LIMIT starts at zero and startIndex starts at 1 (for display purposes)
		$sLimitQuery = $this->sQuery . " LIMIT " . ($this->iStartIndex-1) . ", " . $this->iResultsToDisplay;

		$sql = & $default->db;
		$sql->query($sLimitQuery);

		if ($sql->num_rows() == 0) {
			//no results
			$sToRender .= "<table width=\"100%\" height=\"100%\">\n";		
			$sToRender .= "<tr>\n";		
			$sToRender .= "<td><p class=\"errorText\">No results matched your criteria</p></td>\n";
			$sToRender .= "</tr>\n";
			$sToRender .= "</table>\n";
		} else {
        
			$sToRender .= "<table width=\"100%\" height=\"100%\">\n";
			
			// display the number of results
			$iEndIndex = $this->iStartIndex+$this->iResultsToDisplay-1 < $iTotalResults ? $this->iStartIndex+$this->iResultsToDisplay-1 : $iTotalResults;
			
			$sToRender .= "<tr><td colspan=\"3\">Searched the KnowledgeTree for '$this->sSearchText'.</td></tr>";
			$sToRender .= "<tr><td colspan=\"3\" align=\"right\">Displaying results $this->iStartIndex - $iEndIndex of $iTotalResults</td></tr>\n";
					
			$sToRender .= "<tr>\n";		
			for ($i = 0; $i < count($this->aColumnHeadings); $i++) {
				if (! (strcmp($this->sOrderByColumn, $this->aColumns[$i]) === false) && (strcmp($this->sOrderByColumn, $this->aColumns[$i]) == 0)) {
					if (!(strcmp($this->sOrderDirection,"ASC") === false) && (strcmp($this->sOrderDirection,"ASC") == 0)) {					
						$sToRender .= "<th align=\"left\" bgcolor=\"" . $sTHBGColour . "\">" . $this->aColumnHeadings[$i]. "</th>\n";
					} else {
						$sToRender .= "<th align=\"left\" bgcolor=\"" . $sTHBGColour . "\">" . $this->aColumnHeadings[$i]. "</th>\n";
					}
				} else {
					$sToRender .= "<th align=\"left\" bgcolor=\"" . $sTHBGColour . "\">" . $this->aColumnHeadings[$i]. "</th>\n";
				}
			}
			$sToRender .= "</tr>\n";		
			$iColour = 0;
			$iDisplayed = 0;
		
			//limit the result set displayed			
			while($sql->next_record()) {
				$sToRender .= "<tr bgcolor=\"" . getColour($iColour) . "\">";
				$iColour++; $iDisplayed++;
				
				for ($i = 0; $i < count($this->aColumns); $i++) {
					switch ($this->aColumnTypes[$i]) {
						case 1:							
							//display text
							$sToRender .= "<td>" . $sql->f($this->aColumns[$i]) . "</td>\n";					
						break;
						case 2:
							//display a checkbox
							$sToRender .= "<td>" . ($sql->f($this->aColumns[$i]) ? "Yes" : "No") . "</td>\n";						
							break;
						case 3:					
							//display a url
							$sToRender .= "<td><a href=\"" ;
							$sURLplusQuery = $this->aLinkURLs[$i];
							for ($j = 0; $j < count($this->aDBQueryStringColumns); $j++) {
			  				 	if (strpos($sURLplusQuery, "?") === false) {
									$sURLplusQuery .= "?" . $this->aQueryStringVariableNames[$j] . "=" . $sql->f($this->aDBQueryStringColumns[$j]);										
								} else { 
									$sURLplusQuery .= "&" . $this->aQueryStringVariableNames[$j] . "=" . $sql->f($this->aDBQueryStringColumns[$j]);
								}									
							}
							$sToRender .= $sURLplusQuery;
							$sToRender .= "\">" . $sql->f($this->aColumns[$i]) . "</a></td>\n";
							
							break;
						case 4:
							//diplay an image URL
							$sToRender .= "<td><a href=\"" . $this->aLinkURLs[$i];
							for ($j = 0; $j < count($this->aDBQueryStringColumns); $j++) {
								if (strpos($sToRender, "?") === false) {
									$sToRender .= "?" . $this->aQueryStringVariableNames[$j] . "=" . $sql->f($this->aDBQueryStringColumns[$j]);										
								} else { 
									$sToRender .= "&" . $this->aQueryStringVariableNames[$j] . "=" . $sql->f($this->aDBQueryStringColumns[$j]);
								}									
							}						
							$sToRender .= "\"><img src=\"" . $sql->f($this->aColumns[$i]) . "\" border=\"0\" /></a></td>\n";						
							break;
						default:
							break;
					}
				}
				$sToRender .= "</tr>\n";
			} 
			
			//if we displayed less results than the number to display
			//simply pad the table
			while ($iDisplayed < $this->iResultsToDisplay) {
				$sToRender .= "<tr><td>&nbsp;</td></tr>\n";
				$iDisplayed++;
			}
			$sToRender .= "</table>";

			$sToRender .= "<table>";
			$sToRender .= "<tr>\n";		
			$sToRender .= "<input type=\"hidden\" name=\"fStartIndex\" value=\"$iEndIndex\" />\n";
			// Display only the next button
			if (($this->iStartIndex + $this->iResultsToDisplay) < $iTotalResults && $this->iStartIndex == 1) {
				$sToRender .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
				$sToRender .= "<td align=\"left\">";
				$sToRender .= "<input type=\"image\" src=\"$default->graphicsUrl/widgets/next.gif\" onClick=\"setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fStartIndex=" . ($this->iStartIndex +  $this->iResultsToDisplay) . $this->sQueryString . "')\" />";
				$sToRender .= "</td>\n";
			}
			// Display both the next and the previous buttons		
			else if (($this->iStartIndex + $this->iResultsToDisplay) < $iTotalResults && $this->iStartIndex > 1) {
				$sToRender .= "<td>";
				$sToRender .= "<input type=\"image\" src=\"$default->graphicsUrl/widgets/previous.gif\" onClick=\"setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fStartIndex=" . ($this->iStartIndex -  $this->iResultsToDisplay) . $this->sQueryString . "')\" />";
				$sToRender .= "</td>";
				$sToRender .= "<td>";
				$sToRender .= "<input type=\"image\" src=\"$default->graphicsUrl/widgets/next.gif\" onClick=\"setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fStartIndex=" . ($this->iStartIndex +  $this->iResultsToDisplay)  . $this->sQueryString . "')\" />";				
				$sToRender .= "</td>\n";
				
			}	
			// Display only the previous button
			else if ($this->iStartIndex > 1) {
				$sToRender .= "<td align=\"left\">\n";
				$sToRender .= "<input type=\"image\" src=\"$default->graphicsUrl/widgets/previous.gif\" onClick=\"setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fStartIndex=" . ($this->iStartIndex -  $this->iResultsToDisplay) . $this->sQueryString . "')\" />";
				$sToRender .= "</td>";
			}
			
			$sToRender .= "</tr>\n";
			$sToRender .= "</table>\n";
		}
		return $sToRender;
	}
	
	function getResultCount() {
		global $default;
		$sql = & $default->db;
		if ($sql->query($this->sQuery)) {
			return $sql->num_rows();
		} else {
			return 0;
		}
	}
}
?>