<?php
/**
* 
* Class PatternListBox
*
* Creates a drop down list box using a table name and
* two column names (one column is the display value, the other
* is the option value).  The option value column should always
* be an ID that is a primary key in a table.
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 16 January 2003
* @package lib.visualpatterns
*
*/

class PatternListBox {
		
	/** Database table to get information from */
	var $sTableName;
	/** Column in table to display */
	var $sDisplayColumn;
	/** Column in table to use as option value */
	var $sValueColumn;
	/** Select name */
	var $sSelectName;
	/** Where clause */
	var $sWhereClause;
	/** Order columns ascending*/
	var $bOrderAsc;
	
	/**
	* Constructor
	*
	* @param	Table in database that information will come from
	* @param	Column in table that will be display in list box
	* @param	Column in table that will be assigned to the 'option' attribute
	* @param	'name' attribute of 'select' tab
	* @param	Where clause	
	*/
	function PatternListBox($sNewTableName, $sNewDisplayColumn, $sNewValueColumn, $sNewSelectName, $sNewWhereClause = null, $bNewOrderAsc = true) {
		$this->sTableName = $sNewTableName;
		$this->sDisplayColumn = $sNewDisplayColumn;
		$this->sValueColumn = $sNewValueColumn;
		$this->sSelectName = $sNewSelectName;
		$this->sWhereClause = $sNewWhereClause;
		$this->bOrderAsc = $bNewOrderAsc;
	}
	
	/**
	* Create the HTML string that will be used to render the list box
	*
	* @return String html used to render the list box
	*
	*/
	function & render() {
		$sql = new Owl_DB();
		$sQuery = "SELECT DISTINCT $this->sDisplayColumn AS display, $this->sValueColumn AS value FROM $this->sTableName ";
		if (isset($this->sWhereClause)) {
			$sQuery .= "WHERE " . $this->sWhereClause . " ";
		}
		
		$sQuery .= "ORDER BY $this->sDisplayColumn " . ($this->bOrderAsc ? "ASC" : "DESC");
		$sql->query($sQuery);
		$sToRender = "<SELECT NAME = \"$this->sSelectName\">\n";
		while ($sql->next_record()) {
			$sToRender .= "<OPTION value=\"" . $sql->f("value") . "\">" . $sql->f("display") . "</OPTION>\n";
		}
		$sToRender .= "</SELECT><br><br>";
		return $sToRender;
	}
}
?>
