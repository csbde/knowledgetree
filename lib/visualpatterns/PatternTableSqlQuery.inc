<?php

/**
* Class PatternTableSqlQuer
*
* Renders query results in a table.
*
* The first column in the table can be rendered as a link 
* to the document/folder using the $iLinkType variable to specify the link type,
* the $sLinkPageURL to specify the page URL to link to and $sLinkImageURL to specify
* the image to display in the case of either a $iLinkType of 2 (image only) or 3 (image + text)
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 7 January 2003
*
* @todo $iLinkImageURL is hard coded - change
* @todo $sLinkPageURL is hard coded - change
* @package lib.visualpatterns
*/

class PatternTableSqlQuery {	
	
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
	
	
	function PatternTableSqlQuery($sTmpQuery, $aTmpColumns, $aTmpColumnTypes, $aTmpColumnHeaderNames, $sTmpWidth, $bTmpDisplayColumnHeadings = false, $sTmpWhereClause = null, $sTmpOrderByClause = null) {
		$this->sQuery = $sTmpQuery;
		$this->aColumns = & $aTmpColumns;
		$this->aColumnTypes = $aTmpColumnTypes;
		$this->aColumnHeaderNames = $aTmpColumnHeaderNames;
		$this->sWidth = $sTmpWidth;
		$this->sWhereClause = & $sTmpWhereClause;
		$this->sOrderByClause = $sTmpOrderByClause;
		$this->bDisplayColumnHeadings = $bTmpDisplayColumnHeadings;
	}
	
	function setTableHeading($sNewValue) {
		$this->sTableHeading = $sNewValue;
	}
	
	/**
	* Build the HTML string used to render the object
	*
	* @return String of HTML used to render object
	*
	* @todo possibly add in image size restraints for link types 2 and 3
	*/	
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
								$sToRender .= "<td>" . $sql->f($this->aColumns[$i]) . "</td>";
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