<?php
/**
 * $Id$
 *
 * Takes a SQL query, an array of column names and and an array of column types
 * and displays the data in an editable two column list format.
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
	/** set the tables to check for display type 3 */
	var $aDropDownListTableNames;
	/** set whether a column is required or not */
	var $aColumnsRequired;
	/** used when type = 4 to tell which meta data field to pick */
	var $aMetaDataFields;
	
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
	
	function setDropDownListTableNames($aNewValue) {
		$this->aDropDownListTableNames = $aNewValue;
	}
	
	function setColumnsRequired($aNewValue) {
		$this->aColumnsRequired = $aNewValue;
	}
	
	function setMetaDataFields($aNewValue) {
		$this->aMetaDataFields = $aNewValue;
	}
	
	function & render() {
        global $default;
		$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
		$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
		//records the columns to generate validation javascript for
		$aValidationColumnNames = array();
        
		$sql = $default->db;		
		$sql->query($this->sQuery);
		if ($sql->next_record()) {;
			$sToRender = "";
			$sToRender .= "<table border = 0, cellpadding = 5 " . (isset($this->iTableWidth) ? ", width = $this->iTableWidth" : "") . " >\n";
			$sToRender .= "<input type=\"hidden\" name=\"unique_start_" . $this->sUniqueName . "\" value=\"\" />\n";
			$sToRender .= "<input type=\"hidden\" name=\"$this->sUniqueName\" value=\"" . $sql->f("id") . "\" />\n";
			$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_tn\" value=\"$this->sTableName\" />\n";
			
			if (isset($this->sTableHeading)) {
						$sToRender .= "<caption align=\"top\"><b>$this->sTableHeading</b></caption>\n";
			}		
			
			for ($i = 0; $i < count($this->aDisplayColumns); $i++) {		
					$sToRender .= "<tr>\n";
					$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $i . "_tc\" value=\"" . $this->aStoreColumns[$i] . "\" />\n";
					$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $i . "_type\" value=\"" . $this->aDatabaseColumnTypes[$i] . "\" />\n";					
					$sToRender .= "\t<td bgcolor=\"$sTDBGColour\">" . $this->aColumnNames[$i] . "</td>\n";
					switch ($this->aDisplayColumnTypes[$i]) {						
						case 1:	
							//plain text field
							$sToRender .= "\t<td bgcolor=\"" . getColour($i) . "\"><input type=\"text\" size = \"30\" name=\"" . $this->sUniqueName . "_" . $i . "_value\" value=\"" . stripslashes($sql->f($this->aDisplayColumns[$i])) . "\"</td>\n";
							break;						
						case 2:
							//boolean value
							$sToRender .= "\t<td bgcolor=\"" . getColour($i) . "\"><input type=\"checkbox\" name=\"" . $this->sUniqueName . "_" . $i . "_value\" value=\"1\" " . ($sql->f($this->aDisplayColumns[$i]) ? " CHECKED " : " ") . "/></td>\n";
							break;
						case 3:	
							$oPatternListBox = & new PatternListBox($this->aDropDownListTableNames[$i], "name", "id", $this->sUniqueName . "_" . $i . "_value");
							$oPatternListBox->setSelectedValue($sql->f($this->aStoreColumns[$i]));
							$sToRender .= "\t<td bgcolor=\"" . getColour($i) . "\">" . $oPatternListBox->render() . "</td>\t\n";
							break;
						case 4:
							//meta data type
							$oPattern = & new PatternMetaData($this->aMetaDataFields[$i], $this->sUniqueName . "_" . $i . "_value", $sql->f($this->aStoreColumns[$i]));
							$sToRender .= "\t<td bgcolor=\"" . getColour($i) . "\">" . $oPattern->render() . "</td>\t\n";
						default:
							break;
					}				
					$sToRender .= "</tr>\n";
					
					//check for required columns
					if (isset($this->aColumnsRequired) &&($this->aColumnsRequired[$i])) {
						$iArrayEntry = count($aValidationColumnNames);
						$aValidationColumnNames[$iArrayEntry]["formName"] = $this->sUniqueName . "_" . $i . "_value";
						$aValidationColumnNames[$iArrayEntry]["displayName"] =  $this->aColumnNames[$i];
					}
			}
			
			$sToRender .= "<input type=\"hidden\" name=\"unique_end_" . $this->sUniqueName . "\" value=\"\" />\n";
			$sToRender .= "</table>\n";
		} else {			
			$sToRender .= "<tr>\n";					
			$sToRender .= "<td colspan=" . count($this->aDisplayColumns) . ">No " . (isset($this->sTableHeading) ? "$this->sTableHeading" : "") . " data</td>\n";
			$sToRender .= "</tr>\n";			
		}
		
		$sToRender .= $this->generateRequiredFieldValidation($aValidationColumnNames);
		
		return $sToRender;
	}
	
	function generateRequiredFieldValidation($aValidationColumnNames) {
		$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
		$sToRender .= "function validateForm(theForm) {\n";			
		for ($i = 0; $i < count($aValidationColumnNames); $i++) {			
			$sToRender .= "\tif (!(validRequired(document.MainForm." . $aValidationColumnNames[$i]["formName"] . ", '" . $aValidationColumnNames[$i]["displayName"] . "'))) {\n";
			$sToRender .= "\t\treturn false;\n\t}\n";			
		}
		$sToRender .= "return true;\n}\n";
		
		$sToRender .= "//-->\n</script>\n\n";
		
		return $sToRender;
                
	}
}
?>
