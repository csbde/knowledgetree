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
	
	function PatternEditableTableSqlQuery($sTmpQuery, $sTmpTableName, $aTmpDisplayColumnNames, $aTmpStoreColumnNames, $aTmpColumnsEditable, $aTmpColumnsVisible, $sTmpUniqueName) {
		$this->sQuery = $sTmpQuery;
		$this->sTableName = $sTmpTableName;
		$this->aDisplayColumnNames = $aTmpDisplayColumnNames;
		$this->aStoreColumnNames = $aTmpStoreColumnNames;
		$this->aColumnsEditable = $aTmpColumnsEditable;
		$this->sUniqueName = $sTmpUniqueName;
		$this->aColumnsVisible = $aTmpColumnsVisible;
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
					if ($this->aColumnsVisible[$i]) {
						if ($this->aColumnsEditable[$i]) {
							$sToRender .= "\t<input type=\"text\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />\n";
						} else {
							$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />".$sql->f($this->aDisplayColumnNames[$i])."\n";
						}
					} else {
						$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "_value\" value=\"" . $sql->f($this->aStoreColumnNames[$i]) . "\" />";
					}
					$sToRender .= "\t<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $iRowCount . "_tc\" value=\"" . $this->aStoreColumnNames[$i] . "\" />\n";					

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

/*
case 1:									
								$sToRender .= "\t<td>\n";
								if ($this->aColumnsVisible[$i]) {																		
									$sToRender .= "\t\t<input type=\"text\" name=\"unique_" .$this->sUniqueName . "_" . $this->iVariableCount . "\" value=\"" . $sql->f($this->aDisplayColumns[$i]) . "\" />\n";
									$sToRender .= "\t\t<input type=\"hidden\" name=\"unique_" . $this->sUniqueName . "_" . $this->iVariableCount . "_row\" value=\"" . $iRowCount . "\" />\n";
									$sToRender .= "\t\t<input type=\"hidden\" name=\"unique_" . $this->sUniqueName . "_" . $this->iVariableCount . "_id\" value=\"" . $sql->f($this->aPrimaryKey[$i]) . "\" />\n";
									$sToRender .= "\t\t<input type=\"hidden\" name=\"unique_" . $this->sUniqueName . "_" . $this->iVariableCount . "_tn\" value=\"" . $this->sTableName . "\" />\n";
									$sToRender .= "\t\t<input type=\"hidden\" name=\"unique_" . $this->sUniqueName . "_" . $this->iVariableCount . "_tc\" value=\"" . $this->aTableColumns[$i] . "\" />\n";
									$sToRender .= "\t\t<input type=\"hidden\" name=\"unique_" . $this->sUniqueName . "_" . $this->iVariableCount . "_type\" value=\"text\" />\n";
									if ($this->aColumnsRequired[$i]) {
										$this->aRequiredFormVariableNames[count($this->aRequiredFormVariableNames)] = "unique_" .$this->sUniqueName . "_" . $this->iVariableCount;
									}									
									$this->iVariableCount++;
									
								} else {
									$sToRender .= "\t\t" . $sql->f($this->aColumnsSql[$i]) . "\n";
								}
								$sToRender .= "\t</td>\n";
								break;
						case 2:
							//todo - implement checkbox functionality
							break;
						case 3:
							//todo - implement drop down list functionality
							break;
						default:								
								break;
*/
?>
