<?php
/**
* Class PatternEditableListFromQuery
* Takes a SQL query, an array of column names and and an array of column types
* and displays the data in an editable two column list format
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 27 January 2003
* @package lib.visualpatterns
*
* @todo implement HyperLinkURL option
*/

class PatternEditableListFromQuery {
	
	/** SQL query to execute */
	var $sQuery;
	/** table on which to perform store/create */
	var $sTableName;
	/** array of columns to display */
	var $aDisplayColumns;
	/** columns to store */
	var $aStoreColumns;
	/** names of columns to display */	
	var $aColumnNames;	
	/** array of column types (1 = text, 2 = boolean, 3 = dropdown list) */	
	var $aDisplayColumnTypes;
	/** database column types (0 = id, 1 = text, 2 = boolean) */
	var $aDatabaseColumnTypes;
	/** number of rows for text area if text area option is being used */	
	var $iTextAreaRows = 6;
	/** number of colums for text area if text area option is being used */
	var $iTextAreaColums = 20;
	/** heading for table */
	var $sTableHeading;	
	/** set the table width */
	var $iTableWidth = null;
	/** unique name to prepend to form variables */
	var $sUniqueName;
	
	/**
	* Default constructor
	*
	* @param 	String		Query to execute
	* @param 	
	*/
	function PatternEditableListFromQuery($sNewQuery, $sNewTableName, $aNewDisplayColumns, $aNewStoreColumns, $aNewColumnNames, $aNewDisplayColumnTypes, $aNewDatabaseColumnTypes) {
		$this->sQuery = $sNewQuery;
		$this->sTableName = $sNewTableName;
		$this->aDisplayColumns = $aNewDisplayColumns;		
		$this->aStoreColumns = $aNewStoreColumns;
		$this->aColumnNames = $aNewColumnNames;
		$this->aDisplayColumnTypes = $aNewDisplayColumnTypes;
		$this->aDatabaseColumnTypes = $aNewDatabaseColumnTypes;
	}
	
	function setUniqueName($sNewValue) {
		$this->sUniqueName = $sNewValue;
	}
	
	function setTableWidth($iNewValue) {
		$this->iTableWidth = $iNewValue;
	}
	
	function setTextAreaRows($iNewValue) {
		$this->iTextAreaRows = $iNewValue;
	}
	
	function setTextAreaDisplayColumns($iNewValue) {
		$this->iTextAreaDisplayColumns = $iNewValue;
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
		if ($sql->next_record()) {;
			$sToRender = "";
			$sToRender .= "<table border = 0, cellpadding = 5 " . (isset($this->iTableWidth) ? ", width = $this->iTableWidth" : "") . " >\n";
			$sToRender .= "<input type=\"hidden\" name=\"unique_start_" . $this->sUniqueName . "\" value=\"\" />\n";
			$sToRender .= "<input type=\"hidden\" name=\"$this->sUniqueName\" value=\"" . $sql->f("id") . "\" />\n";
			$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_tn\" value=\"$this->sTableName\" />\n";
			
			if (isset($this->sTableHeading)) {
						$sToRender .= "<caption align = left><b>$this->sTableHeading</b></caption>\n";
			}
			
			if ($sql->num_rows() == 0) {
				$sToRender .= "<tr>\n";					
				$sToRender .= "<td colspan=" . count($this->aDisplayColumns) . ">No " . (isset($this->sTableHeading) ? "$this->sTableHeading" : "") . " data</td>\n";
				$sToRender .= "</tr>\n";
			} else {
				for ($i = 0; $i < count($this->aDisplayColumns); $i++) {		
						$sToRender .= "<tr>\n";
						$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_tc\" value=\"" . $this->aStoreColumns[$i] . "\" />\n";
						$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_type\" value=\"" . $this->aDatabaseColumnTypes[$i] . "\" />\n";					
						$sToRender .= "\t<td>" . $this->aColumnNames[$i] . "</td>\n";
						switch ($this->aDisplayColumnTypes[$i]) {						
							case 1:	
								//plain text field							
								$sToRender .= "\t<td><input type=\"text\" name=\"" . $this->sUniqueName . "_" . $i . "_value\" value=\"" . $sql->f($this->aDisplayColumns[$i]) . "\"</td>\n";
								break;						
							case 2:
								//boolean value
								//$sToRender .= "\t<td><textarea cols=$this->iTextAreaDisplayColumns rows=$this->iTextAreaRows READONLY>" . $sql->f($this->aDisplayColumns[$i]) . "</textarea></td>\n";
								break;
							case 3:	
								break;
							default:
								break;
						}				
						$sToRender .= "</tr>\n";
				}
				
				/*while ($sql->next_record()) {
					//$sToRender .= "<tr><td>\n";
					for ($i = 0; $i < count($this->aDisplayColumns); $i++) {		
						$sToRender .= "<tr>\n";
						$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_tc\" value=\"" . $this->aStoreColumns[$i] . "\" />\n";
						$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_type\" value=\"" . $this->aDatabaseColumnTypes[$i] . "\" />\n";					
						$sToRender .= "\t<td>" . $this->aColumnNames[$i] . "</td>\n";
						switch ($this->aDisplayColumnTypes[$i]) {						
							case 1:	
								//plain text field							
								$sToRender .= "\t<td><input type=\"text\" name=\"" . $this->sUniqueName . "_" . $i . "_value\" value=\"" . $sql->f($this->aDisplayColumns[$i]) . "\"</td>\n";
								break;						
							case 2:
								//boolean value
								//$sToRender .= "\t<td><textarea cols=$this->iTextAreaDisplayColumns rows=$this->iTextAreaRows READONLY>" . $sql->f($this->aDisplayColumns[$i]) . "</textarea></td>\n";
								break;
							case 3:	
								break;
							default:
								break;
						}				
						$sToRender .= "</tr>\n";
					}
				}*/
			}
			$sToRender .= "<input type=\"hidden\" name=\"unique_end_" . $this->sUniqueName . "\" value=\"\" />\n";
			$sToRender .= "</table>\n";
		}
		return $sToRender;
	}
	
	function replaceValues($sQueryStringText, $sql) {
		return $sQueryStringText;
	}
	
}

?>
