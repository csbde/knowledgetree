<?php
/**
 * $Id$
 *
 * Renders query results in a table.
 *
 * The first column in the table can be rendered as a link 
 * to the document/folder using the $iLinkType variable to specify the link type,
 * the $sLinkPageURL to specify the page URL to link to and $sLinkImageURL to specify
 * the image to display in the case of either a $iLinkType of 2 (image only) or 3 (image + text)
 *
 * If you wish to include images, there are two ways to do this
 *		o set the image url - this means that all rows will use the same image
 *		o set $bUseImageURLFromQuery to true - this will look for a column entitled image_url in
 *		  the sql result set, allowing you to specify different images for each entry
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
class PatternTableSqlQuery {	
	
	/** query to execute*/
	var $sQuery;
	/* Columns in table to query (ID is included by default) */
	var $aColumns;
	/* Column types.  Possibles are 1 = text, 2 = boolean, 3 = hyperlink url */
	var $aColumnTypes;
	/** header names to display */	
	var $aColumnHeaderNames;
	/** table width, either in pixels or as a percentage e.g. 600 or 100%*/
	var $sWidth;	
	/** link url used if a column type of 3 is specified */
	var $aLinkURLs;
	/** database column values to append to link url if a column type of 3 is specified */
	var $aDBQueryStringColumns;
	/** variables names to give $aDBQueryStringColumns on the query string */
	var $aQueryStringVariableNames;
	/** display the column headings or not */
	var $bDisplayColumnHeadings;
	/** table heading */
	var $sTableHeading;
	/** specify an image at the start of each row in the table */
	var $sImageURL;
	/** specify whether to use $sImageURL or to get the image url from the sql result set */
	var $bUseImageURLFromQuery = false;
	/** message that will be displayed if the table is empty*/
	var $sEmptyTableMessage;
	/** Picture paths */
	var $sChkPicPath = "widgets/checked.gif";
	var $sUnChkPicPath = "widgets/unchecked.gif";
	/** whether to force entries to wrap */
	var $bWordWrap = false;
				
	function PatternTableSqlQuery($sTmpQuery, $aTmpColumns, $aTmpColumnTypes, $aTmpColumnHeaderNames, $sTmpWidth, $aTmpLinkURLs = null, $aTmpDBQueryStringColumns = null, $aNewQueryStringVariableNames = null) {
		$this->sQuery = $sTmpQuery;
		$this->aColumns = & $aTmpColumns;
		$this->aColumnTypes = $aTmpColumnTypes;
		$this->aColumnHeaderNames = $aTmpColumnHeaderNames;
		$this->sWidth = $sTmpWidth;
		$this->bDisplayColumnHeadings = $bTmpDisplayColumnHeadings;
		$this->aLinkURLs = $aTmpLinkURLs;		
		$this->aDBQueryStringColumns = $aTmpDBQueryStringColumns;
		$this->aQueryStringVariableNames = $aNewQueryStringVariableNames;
	}
	
	function setEmptyTableMessage($sNewValue) {
		$this->sEmptyTableMessage = $sNewValue;
	}
	
	function setTableHeading($sNewValue) {
		$this->sTableHeading = $sNewValue;
	}
	
	function setImageURL($sNewValue) {
		$this->sImageURL = $sNewValue;
	}
	
	function setUseImageURLFromQuery($bNewValue) {
		$this->bUseImageURLFromQuery = $bNewValue;
	}
	
	function setDisplayColumnHeadings($bNewValue) {
		$this->bDisplayColumnHeadings = $bNewValue;
	}
	
	function setIncludeBorder($bNewValue) {
		$this->bIncludeBorder = $bNewValue;
	}
	
	function setChkPicPath($sNewChkPicPath) {
		$this->sChkPicPath = $sNewChkPicPath;	
	}
	
	function getChkPicPath() {
		return $this->sChkPicPath ;	
	}
	
	function setWordWrap($bNewValue) {
		$this->bWordWrap = $bNewValue;	
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
        
		$sToRender = "<table cellpadding=\"5\" border=\"" . ($this->bIncludeBorder ? "1" : "0") . "\" width=\"" . $this->sWidth . "\">\n";
		if (isset($this->sTableHeading)) {
			$sToRender .= "<caption align=\"top\" colspan=\"" . count($this->aColumns) . "\" align=\"left\"><b>$this->sTableHeading</b></caption>\n";
		}
		if ($this->bDisplayColumnHeadings) {
			for ($i = 0; $i < count($this->aColumnHeaderNames); $i++) {
				$sToRender .= "<th align=left>" . $this->aColumnHeaderNames[$i] . "</th>\n";
			}
		}
		$sql = $default->db;
		$sql->query($this->sQuery);
		if ($sql->num_rows() == 0) {
			$sToRender .= "<tr>\n";	
			if (isset($this->sEmptyTableMessage)) {
				$sToRender .= "<td colspan=" . count($this->aColumns) . ">$this->sEmptyTableMessage</td>\n";
			} else {
				$sToRender .= "<td colspan=" . count($this->aColumns) . ">No " . (isset($this->sTableHeading) ? $this->sTableHeading : "") . " data</td>\n";
			}
			$sToRender .= "</tr>\n";		
		} else {
			$iColour = 0;
			while ($sql->next_record()) {			
				$sToRender .= "<tr bgcolor=\"" . getColour($iColour) . "\">\n";
				$iColour++;
				for ($i = 0; $i < count($this->aColumns); $i++) {
					switch ($this->aColumnTypes[$i]) {
						case 1:
								//text
								$sToRender .= "<td>";
								if (isset($this->sImageURL)) {
									$sToRender .= $this->generateImageURL($this->sImageURL);
								} else if ($this->bUseImageURLFromQuery) {
									$sToRender .= $this->generateImageURL($sql->f("image_url"));
								}
								if ($sql->f($this->aColumns[$i]) != null) {
									if ($this->bWordWrap) {
										$sToRender .= wordwrap($sql->f($this->aColumns[$i]), 25, " ", 1) . "</td>";
									} else {
										$sToRender .= $sql->f($this->aColumns[$i]) . "</td>";
									}
								} else {
									$sToRender .= "&nbsp;</td>";
								}
								break;
						case 2:
								//boolean
								$sToRender .= "<td>";
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
						case 3:
								//hyperlink								
								$sToRender .= "<td><a href=\"" . $this->aLinkURLs[$i];
								for ($j = 0; $j < count($this->aDBQueryStringColumns); $j++) {
									if (strpos($sToRender, "?") === false) {
										$sToRender .= "?" . $this->aQueryStringVariableNames[$j] . "=" . $sql->f($this->aDBQueryStringColumns[$j]);										
									} else { 
										$sToRender .= "&" . $this->aQueryStringVariableNames[$j] . "=" . $sql->f($this->aDBQueryStringColumns[$j]);
									}									
								}
								$sToRender .= "\">";
								
								if (isset($this->sImageURL)) {
									$sToRender .= $this->generateImageURL($this->sImageURL);
								} else if ($this->bUseImageURLFromQuery) {
									$sToRender .= $this->generateImageURL($sql->f("image_url"));
								}
								$sToRender .= $sql->f($this->aColumns[$i]) . "</a></td>\n";
								break;
						default:
								break;
					}
				}
				$sToRender .= "</tr>\n";			
			}
		}
		$sToRender .= "</table>";		
		return $sToRender;
	}
	
	
	function generateImageURL($sURL) {
		return "<img src=\"" . $sURL . "\" border=\"0\"/>";
	}
}
?>