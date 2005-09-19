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

require_once(KT_LIB_DIR . '/templating/templating.inc.php');

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
        
        $aRows = DBUtil::getResultArray($this->sQuery);
		$sToRender = "";
		$sToRender .= "<table border=\"0\" cellpadding=\"5\" " . (isset($this->iTableWidth) ? " width=\"$this->iTableWidth\"" : "") . " >\n";
		
		if (isset($this->sTableHeading)) {
			$sToRender .= "<caption align=\"top\" colspan=\"" . count($this->aColumns) . "\"><b>$this->sTableHeading</b></caption>\n";
		}
		
		if (count($aRows) == 0) {
			$sToRender .= "<tr>\n";
			if (isset($this->sEmptyTableMessage)) {
				$sToRender .= "<td colspan=" . count($this->aColumns) . ">$this->sEmptyTableMessage</td>\n";
			} else {
				$sToRender .= "<td colspan=" . count($this->aColumns) . ">" .
                    sprintf(_("No %s data"), (isset($this->sTableHeading) ? "$this->sTableHeading" : "")) .
                    "</td>\n";
			}
			$sToRender .= "</tr>\n";
            $sToRender .= "</table>\n";		
            return $sToRender;
		}

        $map = array();
        foreach ($this->aColumns as $sColumn) {
            $map[$sColumn] = array();
        }

        $iColour = 0;
        foreach ($aRows as $aRow) {
            $i = 0;
            foreach ($this->aColumns as $sColumn) {
                switch ($this->aColumnTypes[$i]) {						
                    //plain text field
                    case 1:
                        $value = $aRow[$this->aColumns[$i]];
                        break;
                    case 2:
                        $value = $aRow[$this->aColumns[$i]];
                        if (!empty($value)) {
                            if ($value) { 
                                $value = "<img src=\"$default->graphicsUrl/" . $this->sChkPicPath . "\">";
                            } else {
                                $value = "<img src=\"$default->graphicsUrl/" . $this->sUnChkPicPath . "\">";
                            }
                            $value .= "&nbsp;";	
                        }
                        break;						
                    case 3:
                        $value = $aRow[$this->aColumns[$i]];
                        $value = sprintf('<a href="%s?%s">%s</a>',
                            $this->aHyperLinkURL[$i],
                            $this->replaceValues($this->aQueryStringText[$i], ""),
                            $value);
                        break;
                    default:
                        break;
                }				

                $map[$sColumn][] = $value;
                $i++;
            }
        }
        $oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('ktcore/document_data');
        $aColumnNames = array();
        $iLen = count($this->aColumnNames);
        for ($c = 0; $c < $iLen; $c++) {
            $aColumnNames[$this->aColumns[$c]] = $this->aColumnNames[$c];
        }
        $sToRender = $oTemplate->render(array(
            'map' => $map,
            'cnames' => $aColumnNames,
        ));
		return $sToRender;
	}
	
	function replaceValues($sQueryStringText, $sql) {
		return $sQueryStringText;
	}
}
?>
