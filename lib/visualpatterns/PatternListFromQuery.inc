<?php
/**
* Class PatternListFromQuery
* Takes a SQL query, an array of column names and and an array of column types
* and displays the data in a two column list format
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 22 January 2003
* @package lib.visualpatterns
*
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
	
	function & render() {
		$sql = new Owl_DB();
		$sql->query($this->sQuery);		
		$sToRender = "";
		$sToRender .= "<table border = 0, cellpadding = 5 " . (isset($this->iTableWidth) ? ", width = $this->iTableWidth" : "") . " >\n";
		
		if (isset($this->sTableHeading)) {
					$sToRender .= "<caption align=\"top\"><b>$this->sTableHeading</b></caption>\n";
		}
		
		if ($sql->num_rows() == 0) {
			$sToRender .= "<tr>\n";					
			$sToRender .= "<td colspan=" . count($this->aColumns) . ">No " . (isset($this->sTableHeading) ? "$this->sTableHeading" : "") . " data</td>\n";
			$sToRender .= "</tr>\n";
		} else {
		
			while ($sql->next_record()) {
				$sToRender .= "<tr><td>\n";
				for ($i = 0; $i < count($this->aColumns); $i++) {		
					$sToRender .= "<tr>\n";				
					switch ($this->aColumnTypes[$i]) {						
						//plain text field
						case 1:						
							$sToRender .= "<td>" . $this->aColumnNames[$i] . "</td><td>" . $sql->f($this->aColumns[$i]) . "</td>\n";
							break;
						//text area
						case 2:
							$sToRender .= "<td>" . $this->aColumnNames[$i] . "</td><td><textarea cols=$this->iTextAreaColumns rows=$this->iTextAreaRows READONLY>" . $sql->f($this->aColumns[$i]) . "</textarea></td>\n";
							break;
						case 3:
							$sToRender .= "<td>" . $this->aColumnNames[$i] . "</b></td><td><a href=\"" . $this->aHyperLinkURL[$i] . "?" . $this->replaceValues($this->aQueryStringText[$i], $sql) . "\">" . $sql->f($this->aColumns[$i]) . "</a></td>\n";
							break;
						default:
							break;
					}				
					$sToRender .= "</tr>\n";
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
