<?php
/**
* Class PatternListBox
*
* Creates a drop down list box using a table name and
* two column names (one column is the display value, the other
* is the option value).  The option value column should always
* be an ID that is a primary key in a table.
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 16 January 2003
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
	/** Order columns ascending*/
	var $bOrderAsc;
	
	/**
	* Constructor
	*
	* @param $sNewTableName			Table in database that information will come from
	* @param $sNewDisplayColumn		Column in table that will be display in list box
	* @param $sNewValueColumn		Column in table that will be assigned to the 'option' attribute
	* @param $sNewSelectName		'name' attribute of 'select' tab
	* @param $iNewWidth				Width of list box
	*/
	function PatternListBox($sNewTableName, $sNewDisplayColumn, $sNewValueColumn, $sNewSelectName, $bNewOrderAsc = true) {
		$this->sTableName = $sNewTableName;
		$this->sDisplayColumn = $sNewDisplayColumn;
		$this->sValueColumn = $sNewValueColumn;
		$this->sSelectName = $sNewSelectName;
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
		$sql->query("SELECT $this->sDisplayColumn AS display, $this->sValueColumn AS value FROM $this->sTableName ORDER BY $this->sDisplayColumn " . ($this->bOrderAsc ? "ASC" : "DESC"));
		$sToRender = "<SELECT NAME = \"$this->sName\"><br>";
		while ($sql->next_record()) {
			$sToRender .= "<OPTION value=\"" . $sql->f("value") . "\">" . $sql->f("display") . "</OPTION><br>";
		}
		$sToRender .= "</SELECT><br><br>";
		return $sToRender;
	}
}
?>
