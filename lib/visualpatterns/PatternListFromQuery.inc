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
	/** array of column types (textfield = 1, textarea = 2, hyperlink = 3) */	
	var $aColumnTypes;
	/** names of columns to display */
	var $aColumnNames;
	/** array of hyperlink URLS */
	var $aHyperLinkURL;
	/** array of text to be used on querystring with a hyperlink 
	Each entry must take the format of:
			<queryStringName>=<[columnName]>&<queryStringName2>=<columnName2> e.g. fId = [DOC.id] (the querystring will be
			build by replacing <columnName> with the actual value from the column
			retrieved from the query)*/
	var $aQueryStringText;
	/** number of rows for text area if text area option is being used */
	var $iTextAreaRows = 6;
	/** number of colums for text area if text area option is being used */
	var $iTextAreaColums = 20;
	/** heading for table */
	var $sTableHeading;
	/** whether to create an individual table for each row in the result set or not */
	var $bIndividualTableForEachResult = false;
	
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
		$sToRender .= "<table border = 0, cellpadding = 5>\n";
		while ($sql->next_record()) {
			if (isset($this->sTableHeading)) {
				$sToRender .= "<caption align = left><b>$this->sTableHeading</b></caption>\n";
			}
			
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
		$sToRender .= "</table>\n";		
		return $sToRender;
	}
	
	function replaceValues($sQueryStringText, $sql) {
		return $sQueryStringText;
	}
	
}

?>
