<?php


class PatternEditableTableSqlQuery {
	
	/** query to execute*/
	var $sQuery;
	/* Columns in table to query (ID is included by default) */
	var $aColumns;
	/* Column types.  Possibles are 1 = text, 2 = boolean*/
	var $aColumnTypes;
	/** header names to display */	
	var $aColumnHeaderNames;
	/** table width, either in pixels or as a percentage e.g. 600 or 100%*/
	var $sWidth;
	/* Where clause */
	var $sWhereClause;
	/* Order by clause */
	var $sOrderByClause;	
	/** display the column headings or not */
	var $bDisplayColumnHeadings;
	/** table heading */
	var $sTableHeading;
	var $aColumnEditable;
	var $sTableName;
	var $iVariableCount = 0;
	var $sUniqueName;
	//var $aVariableArray;
	
	function PatternEditableTableSqlQuery($sTmpQuery, $sTmpTableName, $sTmpUniqueName, $aTmpColumns, $aTmpColumnTypes, $aTmpColumnEditable, $aTmpColumnHeaderNames, $sTmpWidth, $bTmpDisplayColumnHeadings = false, $sTmpWhereClause = null, $sTmpOrderByClause = null) {
		$this->sQuery = $sTmpQuery;
		$this->aColumns = & $aTmpColumns;
		$this->aColumnTypes = $aTmpColumnTypes;		
		$this->aColumnHeaderNames = $aTmpColumnHeaderNames;
		$this->sWidth = $sTmpWidth;
		$this->sWhereClause = & $sTmpWhereClause;
		$this->sOrderByClause = $sTmpOrderByClause;
		$this->bDisplayColumnHeadings = $bTmpDisplayColumnHeadings;
		$this->aColumnEditable = $aTmpColumnEditable;
		$this->sTableName = $sTmpTableName;
		$this->sUniqueName = $sTmpUniqueName;
	}
	
	function setTableHeading($sNewValue) {
		$this->sTableHeading = $sNewValue;
	}
	
	function & render() {
		//$sToRender = "<table border = 0, width=$this->sWidth>\n";
		$sToRender = "<table cellpadding = 5, border = 0>\n";
		if (isset($this->sTableHeading)) {
			$sToRender .= "<caption colspan = " . count($this->aColumns) . ", align=left><b>$this->sTableHeading</b></caption>\n";
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
			$sToRender .= "<td colspan=" . count($this->aColumns) . ">No " . (isset($this->sTableHeading) ? $this->sTableHeading : "") . " data</td>\n";			
			$sToRender .= "</tr>\n";		
		} else {
			while ($sql->next_record()) {				
				$sToRender .= "<tr>\n";
				for ($i = 0; $i < count($this->aColumns); $i++) {					
					switch ($this->aColumnTypes[$i]) {
						case 1:									
								$sToRender .= "\t<td>\n";
								if ($this->aColumnEditable[$i]) {
									//$this->aVariableArray[$this->iVariableCount] = $this->sUniqueName . "_" . $this->iVariableCount;									
									$sToRender .= "\t\t<input type=\"text\" name=\"unique_" .$this->sUniqueName . "_" . $this->iVariableCount . "\" value=\"" . $sql->f($this->aColumns[$i]) . "\" />\n";
									$sToRender .= "\t\t<input type=\"hidden\" name=\"unique_" . $this->sUniqueName . "_" . $this->iVariableCount . "_id\" value=\"" . $sql->f("id") . "\" />\n";
									$sToRender .= "\t\t<input type=\"hidden\" name=\"unique_" . $this->sUniqueName . "_" . $this->iVariableCount . "_tn\" value=\"" . $this->sTableName . "\" />\n";
									$this->iVariableCount++;
								} else {
									$sToRender .= "\t\t" . $sql->f($this->aColumns[$i]) . "\n";
								}
								$sToRender .= "\t</td>\n";
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
}

?>
