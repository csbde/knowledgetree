<?php
/**
 * $Id$
 *
 * Will take a query that generates a table like
 * result and create and editable HTML table from it that can
 * be used as part of a form.  
 *
 * 1 columns is REQUIRED in the Sql query:
 *		o column giving primary key of entry in table (primary key column name specified in $aPrimaryKey)
 *
 * 3 hidden fields are generated for each editable entry:
 *	o <uniquename>_id:		holds the primary key of the entry in the table (-1 = no entry in table i.e. create not update)
 *	o <uniquename>_tn: 		holds the tablename for which the primary key is valid
 *	o <uniquename>_type:	holds the type of entry (text/boolean/list) - for parsing purposes
 *
 * The actual value is held in a form field name <uniquename>
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
 * @todo - add client side validation stuff
 * @todo - add column type 3 -> select field stuff 
 */
class PatternEditableTableSqlQuery {
	
	/** query to execute*/
	var $sQuery;
	/** table to perform insert/update on */
	var $sTableName;
	/** columns names in $sQuery to display */
	var $aDisplayColumnNames;
	/** column names in $sQuery that will be stored/updated */
	var $aStoreColumnNames;
	/** Array of boolean values dictating column editability */
	var $aColumnsEditable;
	/** columns visible or not (store columns) */
	var $aColumnsVisible;
	/** column types: 1 = text, 2 = boolean, 3 = drop down list, 4 = meta data lookup */
	var $aColumnDisplayTypes;
	/** 0 = id, 1 = text, 2 = boolean*/
	var $aColumnDatabaseTypes;
	/** Unique name */
	var $sUniqueName;
	/** message that will be displayed if the table is empty*/
	var $sEmptyTableMessage;
	/** whether or not to include javascript validation rendering automatically */
	var $bRenderJavascriptValidation = true;
	/** temporary array holding the required fields */
	var $aRequiredVariables;
	
	var $aRequiredColumnNames;
	var $aRequiredFieldNames;
	var $aMetaDataFields;	
	
	function PatternEditableTableSqlQuery($sTmpQuery, $sTmpTableName, $aTmpStoreColumnNames, $aTmpDisplayColumnNames, $aTmpColumnsEditable, $aTmpColumnsVisible, $aTmpColumnDisplayTypes, $aTmpColumnDatabaseTypes) {
		$this->sQuery = $sTmpQuery;
		$this->sTableName = $sTmpTableName;
		$this->aStoreColumnNames = $aTmpStoreColumnNames;
		$this->aDisplayColumnNames = $aTmpDisplayColumnNames;		
		$this->aColumnsEditable = $aTmpColumnsEditable;
		$this->sUniqueName = $sTmpUniqueName;
		$this->aColumnsVisible = $aTmpColumnsVisible;
		$this->aColumnDisplayTypes = $aTmpColumnDisplayTypes;		
		$this->aColumnDatabaseTypes = $aTmpColumnDatabaseTypes;
	}
	
	function setUniqueName($sNewValue) {
		$this->sUniqueName = $sNewValue;
	}
	
	function setMetaDataFields($aNewValue) {		
		$this->aMetaDataFields = $aNewValue;
	}
	
	function setDisplayColumnHeadings($bNewValue) {
		$this->bDisplayColumnHeadings = $bNewValue;	
	}
	
	function setTableCaption($sNewValue) {
		$this->sTableCaption = $sNewValue;
	}
	
	function setColumnHeaderNames($aNewValue) {
		$this->aColumnHeaderNames = $aNewValue;
	}
	
	/*function getRequiredVariableNames() {
		return $this->aRequiredVariableNames;
	}*/
	
	function setRequiredColumnNames($aNewValue) {
		$this->aRequiredColumnNames = $aNewValue;
	}
	
	function setEmptyTableMessage($sNewValue) {
		$this->sEmptyTableMessage = $sNewValue;
	}
	
	function setRenderJavascriptValidation($bNewValue) {
		$this->bRenderJavascriptValidation = $bNewValue;
	}
	
	function & render() {
        global $default;
        
		$sToRender = "<table cellpadding=\"5\" border=\"0\">\n";
		if (isset($this->sTableCaption)) {
			$sToRender .= "<caption align=\"top\" colspan=\"" . count($this->aColumnsSql) . "\" align=\"left\"><b>$this->sTableCaption</b></caption>\n";
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
				$sToRender .= "<td colspan=" . count($this->aStoreColumnNames) . ">$this->sEmptyTableMessage</td>\n";
			} else {
				$sToRender .= "<td colspan=" . count($this->aStoreColumnNames) . ">No " . (isset($this->sTableCaption) ? $this->sTableCaption : "") . " data</td>\n";
			}
			$sToRender .= "</tr>\n";		
		} else {
			$this->aRequiredVariables = array();
			$iRowCount = 0;
			while ($sql->next_record()) {				
				$sToRender .= "<tr>\n";
				$sToRender .= "<input type=\"hidden\" name=\"unique_start_" . $this->sUniqueName . $iRowCount . "\" value=\"\" />\n";
				$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "\" value=\"" . $sql->f("id") . "\" />\n";
				$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "_tn\" value=\"" . $this->sTableName . "\" />\n";
                
                // check for is_mandatory field validation
                if ($sql->f("is_mandatory") == "1") {
                    $this->aRequiredVariables[]["formName"] = $this->sUniqueName . "_" . $iRowCount . (count($this->aStoreColumnNames)-1) . "_value";
                }                
				for ($i = 0; $i < count($this->aStoreColumnNames); $i++) {					
					if (isset($this->aRequiredColumnNames)) {
						for ($k = 0; $k < count($this->aRequiredColumnNames); $k++) {
							if (strcmp($this->aStoreColumnNames[$i], $this->aRequiredColumnNames[$k]) == 0) {								
								$this->aRequiredVariables[count($this->aRequiredVariables)]["formName"] = $this->sUniqueName . "_" . $iRowCount . $i . "_value";
							}
						}
					}
					
					//generate the right kind of editable field
					$sToRender .= "\t<td>\n";
					switch ($this->aColumnDisplayTypes[$i]) {
						case 1:
							//output the table column name
							$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . $i . "_tc\" value=\"" . $this->aStoreColumnNames[$i] . "\" />\n";
							//output the column type
							$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . $i . "_type\" value=\"" . $this->aColumnDatabaseTypes[$i] . "\" />\n";
							//output the value
							if ($this->aColumnsVisible[$i]) {
								if ($this->aColumnsEditable[$i]) {
									$sToRender .= "\t<input type=\"text\" name=\"" . $this->sUniqueName . "_" . $iRowCount . $i . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />\n";
								} else {
									$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . $i . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />" . $sql->f($this->aDisplayColumnNames[$i])."\n";
								}
							} else {
								$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . $i . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />\n";
							}
							break;
						case 2:
							//output the table column name
							$sToRender .= "\t<input type=\"hidden\" name=\"tc_" . $this->sUniqueName . "_" . $iRowCount . $i . "_tc\" value=\"" . $this->aStoreColumnNames[$i] . "\" />\n";
							//output the column type
							$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . $i . "_type\" value=\"" . $this->aColumnDatabaseTypes[$i] . "\" />\n";
							//output the value
							if ($this->aColumnsVisible[$i]) {
								if ($this->aColumnsEditable[$i]) {
									$sToRender .= "\t<input type=\"checkbox\" name=\"" . $this->sUniqueName . "_" . $iRowCount . $i . "_value\" value=\"" . ($sql->f($this->aStoreColumnNames[$i] ? "1\" checked" : "0\"")) . " />\n";
								} else {
									$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . $i . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />".$sql->f($this->aDisplayColumnNames[$i])."\n";
								}
							} else {
								$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . $i . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />\n";
							}							
							break;
						case 3:
							$sToRender .= "<b>The column type for drop downs is not implemented yet</b>\n";
							break;
						case 4:
							//meta data
							//could either be a drop down or a text field, depending
							
							//output the table column name
                            $sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . $i . "_tc\" value=\"" . $this->aStoreColumnNames[$i] . "\" />\n";
							//output the column type
							$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . $i . "_type\" value=\"" . $this->aColumnDatabaseTypes[$i] . "\" />\n";
							if ($this->aColumnsVisible[$i]) {
								if ($this->aColumnsEditable[$i]) {
									$oPattern = & new PatternMetaData($sql->f($this->aMetaDataFields[$i]), $this->sUniqueName . "_" . $iRowCount . $i . "_value", $sql->f($this->aStoreColumnNames[$i]));
									$sToRender .= $oPattern->render();
								} else {
									$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . $i . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />".$sql->f($this->aDisplayColumnNames[$i])."\n";
								}
							} else {
								$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . $i . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />\n";
							}							
							break;
							
							break;
						default:							
							$sToRender .= "<b>You are attempting to render an unknown column type " . ($this->aColumnTypes[$i]) . " in PatternEditableTableSqlQuery</b>\n";
							break;
					}

					$sToRender .= "\t</td>\n";
				}
				$sToRender .= "<input type=\"hidden\" name=\"unique_end_" . $this->sUniqueName . $iRowCount . "\" value=\"\" />\n";
				$sToRender .= "</tr>\n";
				$iRowCount++;
			}
		}
		$sToRender .= "</table>";
		if (isset($this->aRequiredVariables)) {
			if ($this->bRenderJavascriptValidation) {
				$sToRender .= $this->generateRequiredFieldValidation($this->aRequiredVariables);
			}
		}
		return $sToRender;
	}
	
	function generateRequiredFieldValidation($aValidationColumnNames) {
		$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
		$sToRender .= "function validateForm(theForm) {\n";
		for ($i = 0; $i < count($aValidationColumnNames); $i++) {
            $sToRender .= "\tif (!(validRequired(document.MainForm." . $aValidationColumnNames[$i]["formName"] . ", 'selected'))) {\n";
            $sToRender .= "\t\treturn false;\n\t}\n";
		}
		$sToRender .= "return true;\n}\n";
		$sToRender .= "//-->\n</script>\n\n";
		return $sToRender;
	}
	
	function getValidationJavascript() {
		for ($i = 0; $i < count($this->aRequiredVariables); $i++) {
            $sToRender .= "\tif (!(validRequired(document.MainForm." . $this->aRequiredVariables[$i]["formName"] . ", 'selected'))) {\n";
            $sToRender .= "\t\treturn false;\n\t}\n";
		}
		return $sToRender;
	}
}
?>