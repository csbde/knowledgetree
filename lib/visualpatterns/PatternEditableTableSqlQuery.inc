<?php

/**
* Class PatternEditableTableSqlQuery
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
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 25 January 2003
* @todo - add client side validation stuff
* @todo - add column type 3 -> select field stuff
* @package lib.visualpatterns
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
	/** column types: 1 = text, 2 = boolean, 3 = drop down list */
	var $aColumnTypes;
	/** Unique name */
	var $sUniqueName;
	
	function PatternEditableTableSqlQuery($sTmpQuery, $sTmpTableName, $aTmpStoreColumnNames, $aTmpDisplayColumnNames, $aTmpColumnsEditable, $aTmpColumnsVisible, $aTmpColumnTypes, $sTmpUniqueName) {
		$this->sQuery = $sTmpQuery;
		$this->sTableName = $sTmpTableName;
		$this->aStoreColumnNames = $aTmpStoreColumnNames;
		$this->aDisplayColumnNames = $aTmpDisplayColumnNames;		
		$this->aColumnsEditable = $aTmpColumnsEditable;
		$this->sUniqueName = $sTmpUniqueName;
		$this->aColumnsVisible = $aTmpColumnsVisible;
		$this->aColumnTypes = $aTmpColumnTypes;
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
	
	function getRequiredVariableNames() {
		return $this->aRequiredVariableNames;
	}
	
	function & render() {
		//$sToRender = "<table border = 0, width=$this->sWidth>\n";
		$sToRender = "<table cellpadding = 5, border = 0>\n";
		if (isset($this->sTableCaption)) {
			$sToRender .= "<caption colspan = " . count($this->aColumnsSql) . ", align=left><b>$this->sTableCaption</b></caption>\n";
		}
		if ($this->bDisplayColumnHeadings) {
			for ($i = 0; $i < count($this->aColumnHeaderNames); $i++) {
				$sToRender .= "<th align=left>" . $this->aColumnHeaderNames[$i] . "</th>\n";
			}
		}
		$sql = new Owl_DB();
		$sql->query($this->sQuery);
		if ($sql->num_rows() == 0) {
			$sToRender .= "<tr>\n";			
			$sToRender .= "<td colspan=" . count($this->aColumnsSql) . ">No " . (isset($this->sTableCaption) ? $this->sTableCaption : "") . " data</td>\n";			
			$sToRender .= "</tr>\n";		
		} else {
			$iRowCount = 0;
			while ($sql->next_record()) {				
				$sToRender .= "<tr>\n";
				$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "\" value=\"" . $sql->f("id") . "\" />\n";
				$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "_tc\" value=\"" . $this->sTableName . "\" />\n";
				for ($i = 0; $i < count($this->aStoreColumnNames); $i++) {
					//generate the right kind of editable field
					$sToRender .= "\t<td>\n";
					switch ($this->aColumnTypes[$i]) {
						case 1:
							if ($this->aColumnsVisible[$i]) {
								if ($this->aColumnsEditable[$i]) {
									$sToRender .= "\t<input type=\"text\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />\n";
								} else {
									$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />".$sql->f($this->aDisplayColumnNames[$i])."\n";
								}
							} else {
								$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />\n";
							}
							$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "_tc\" value=\"" . $this->aStoreColumnNames[$i] . "\" />\n";
							break;
						case 2:
							if ($this->aColumnsVisible[$i]) {
								if ($this->aColumnsEditable[$i]) {
									$sToRender .= "\t<input type=\"checkbox\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "_value\" value=\"" . ($sql->f($this->aStoreColumnNames[$i] ? "1\" checked" : "0\"")) . " />\n";
								} else {
									$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />".$sql->f($this->aDisplayColumnNames[$i])."\n";
								}
							} else {
								$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />\n";
							}
							$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "_tc\" value=\"" . $this->aStoreColumnNames[$i] . "\" />\n";
							break;
						case 3:
							break;
						default:
							$sToRender .= "<b>You are attempting to render an unknown column type " . ($this->aColumnTypes[$i]) . " in PatternEditableTableSqlQuery</b>\n";
							break;
					}

					$sToRender .= "\t</td>\n";
				}
				$sToRender .= "</tr>\n";
				$iRowCount++;
			}
		}
		$sToRender .= "</table>";		
		return $sToRender;
	}
}

?>
