<?php
/**
 * $Id$
 *
 * Takes a SQL query, an array of column names and and an array of column types
 * and displays the data in a two column list format.
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
 * @todo implement HyperLinkURL option 
 */
class PatternListFromQuery {
	
	/** SQL query to execute */
	var $sQuery;
	/** array of columns to display */
	var $aColumns;
	/** array of column types (1 = text, 2 = boolean, 3 = hyperlink) */	
	var $aColumnTypes;
	/** names of columns to display */
	var $aColumnNames;
	/** array of hyperlink URLS */
	var $aHyperLinkURL;
	/** array of text to be used on querystring with a hyperlink */	
	var $aQueryStringText;
	/** number of rows for text area if text area option is being used */
	var $iTextAreaRows = 6;
	/** number of colums for text area if text area option is being used */
	var $iTextAreaColums = 20;
	/** heading for table */
	var $sTableHeading;	
	/** set the table width */
	var $iTableWidth = null;
	/** Picture paths */
	var $sChkPicPath = "widgets/checked.gif";
	var $sUnChkPicPath = "widgets/unchecked.gif";
	/** message that will be displayed if the table is empty*/
	var $sEmptyTableMessage;	
	
	/**
	* Default constructor
	*
	* @param 	String		Query to execute
	* @param 	
	*/
	function PatternListFromQuery($sNewQuery, $aNewColumns, $aNewColumnNames, $aNewColumnTypes, $aNewHyperLinkURL = null, $aNewQueryStringText = null) {
		$this->sQuery = $sNewQuery;
		$this->aColumns = $aNewColumns;
		$this->aColumnNames = $aNewColumnNames;
		$this->aColumnTypes = $aNewColumnTypes;
		$this->aHyperLinkURL = $aNewHyperLinkURL;
		$this->aQueryStringText = $aNewQueryStringText;		
	}
	
	function setTableWidth($iNewValue) {
		$this->iTableWidth = $iNewValue;
	}
	
	function setTextAreaRows($iNewValue) {
		$this->iTextAreaRows = $iNewValue;
	}
	
	function setTextAreaColumns($iNewValue) {
		$this->iTextAreaColumns = $iNewValue;
	}
	
	function setTableHeading($sNewValue) {
		$this->sTableHeading = $sNewValue;
	}
	
	function setRenderIndividualTableForEachResult($bNewValue) {
		$this->bIndividualTableForEachResult = $bNewValue;
	}
	
	function setEmptyTableMessage($sNewValue) {
		$this->sEmptyTableMessage = $sNewValue;
	}
		
	function & render() {
        global $default;
		$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
		$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
        
		$sql = $default->db;
		$sql->query($this->sQuery);		
		$sToRender = "";
		$sToRender .= "<table border=\"0\" cellpadding=\"5\" " . (isset($this->iTableWidth) ? " width=\"$this->iTableWidth\"" : "") . " >\n";
		
		if (isset($this->sTableHeading)) {
			$sToRender .= "<caption align=\"top\" colspan=\"" . count($this->aColumns) . "\"><b>$this->sTableHeading</b></caption>\n";
		}
		
		if ($sql->num_rows() == 0) {
			$sToRender .= "<tr>\n";
			if (isset($this->sEmptyTableMessage)) {
				$sToRender .= "<td colspan=" . count($this->aColumns) . ">$this->sEmptyTableMessage</td>\n";
			} else {
				$sToRender .= "<td colspan=" . count($this->aColumns) . ">No " . (isset($this->sTableHeading) ? "$this->sTableHeading" : "") . " data</td>\n";
			}
			$sToRender .= "</tr>\n";
		} else {
			$iColour = 0;
			while ($sql->next_record()) {
				$sToRender .= "<tr><td>\n";
				for ($i = 0; $i < count($this->aColumns); $i++) {		
					$sToRender .= "<tr>\n";				
					switch ($this->aColumnTypes[$i]) {						
						//plain text field
						case 1:
												
							$sToRender .= "<td nowrap bgcolor=\"$sTDBGColour\">" . $this->aColumnNames[$i] . "</td><td width=\"100%\" bgcolor=\"" . getColour($iColour) ."\">";
							if ($sql->f($this->aColumns[$i]) != null) {	 							
								$sToRender .= $sql->f($this->aColumns[$i]) . "</td>\n";
							} else {
								$sToRender .= "&nbsp;</td>";
							}
								
							break;
						//text area
						case 2:
							//boolean
							$sToRender .= "<td nowrap bgcolor=\"$sTDBGColour\">" . $this->aColumnNames[$i] . "</td><td width=\"100%\" bgcolor=\"" . getColour($iColour) ."\">";
							if ($sql->f($this->aColumns[$i]) != null) {									
								$value = $sql->f($this->aColumns[$i]);
								if ($value) { 
									$sToRender .= "<img src=\"$default->graphicsUrl/" . $this->sChkPicPath . "\">";
								} else {
									$sToRender .= "<img src=\"$default->graphicsUrl/" . $this->sUnChkPicPath . "\">";
								}
								$sToRender .= "&nbsp;</td>";	
							} else {
								$sToRender .= "&nbsp;</td>";
							}							

							break;						
							//$sToRender .= "<td bgcolor=\"$sTDBGColour\">" . $this->aColumnNames[$i] . "</td><td bgcolor=\"" . getColour($iColour) ."\"><textarea cols=$this->iTextAreaColumns rows=$this->iTextAreaRows READONLY>" . $sql->f($this->aColumns[$i]) . "</textarea></td>\n";
							//break;
						case 3:
							$sToRender .= "<td bgcolor=\"$sTDBGColour\">" . $this->aColumnNames[$i] . "</b></td><td bgcolor=\"" . getColour($iColour) ."\"><a href=\"" . $this->aHyperLinkURL[$i] . "?" . $this->replaceValues($this->aQueryStringText[$i], $sql) . "\">" . $sql->f($this->aColumns[$i]) . "</a></td>\n";
							break;
						default:
							break;
					}				
					$sToRender .= "</tr>\n";
					$iColour++;
				}
			}
		}
		$sToRender .= "</table>\n";		
		return $sToRender;
	}
	
	function replaceValues($sQueryStringText, $sql) {
		return $sQueryStringText;
	}
}
?>
