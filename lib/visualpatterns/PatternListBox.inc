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
	/**from clause, use to add extra inner joins.  The default selected table gets
	a name of ST and the two selected columns are 'value' and 'display' */
	var $sFromClause;
	/** Order columns ascending*/
	var $bOrderAsc;
	/** set this to true to cause the list box to post back on a change */
	var $bPostBackOnChange = false;
	/** action to perform on a postback */
	var $sOnChangeAction = "document.MainForm.submit();";
	/** currently selected value */
	var $selectedValue;
	/** error message for an empty list box */
	var $sEmptyErrorMessage = "No values";
	/** whether to include 'None' as an option*/
	var $bIncludeDefaultValue = true;
	
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
	
	function setPostBackOnChange($bNewValue) {
		$this->bPostBackOnChange = $bNewValue;
	}
	
	function setOnChangeAction($sNewValue) {
		$this->sOnChangeAction = $sNewValue;
	}
	
	function setSelectedValue($NewValue) {
		$this->selectedValue = $NewValue;
	}
	
	function setWhereClause($sNewValue) {
		$this->sWhereClause = $sNewValue;
	}
	
	function setFromClause($sNewValue) {
		$this->sFromClause = $sNewValue;
	}
	
	function setEmptyErrorMessage($sNewValue) {
		$this->sEmptyErrorMessage = $sNewValue;
	}
	
	function setIncludeDefaultValue($bNewValue) {
		$this->bIncludeDefaultValue = $bNewValue;
	}
	
	/**
	* Create the HTML string that will be used to render the list box
	*
	* @return String html used to render the list box
	*
	*/
	function & render() {
        global $default;
        
		$sql = $default->db;
		$sQuery = "SELECT DISTINCT $this->sDisplayColumn AS display, $this->sValueColumn AS value FROM $this->sTableName ";
		if (isset($this->sWhereClause)) {
			$sQuery .= "WHERE " . $this->sWhereClause . " ";
		}
		
		$sQuery .= "ORDER BY $this->sDisplayColumn " . ($this->bOrderAsc ? "ASC" : "DESC");		
		$sql->query($sQuery);
		if ($sql->num_rows() > 0) {			
			$sToRender = "<SELECT NAME = \"$this->sSelectName\" ". ($this->bPostBackOnChange ? "onChange=\"$this->sOnChangeAction\" " : " ") . ">\n";
			if ($this->bIncludeDefaultValue) {
				$sToRender .= "<OPTION value=\"-1\">None</OPTION>\n";
			}
			while ($sql->next_record()) {
				if ($this->selectedValue == $sql->f("value")) {
					$sToRender .= "<OPTION value=\"" . $sql->f("value") . "\" SELECTED>" . $sql->f("display") . "</OPTION>\n";
				} else {
					$sToRender .= "<OPTION value=\"" . $sql->f("value") . "\">" . $sql->f("display") . "</OPTION>\n";
				}
			}
			$sToRender .= "</SELECT>\n";
		} else {
			$sToRender .= "<label class=\"errorText\">$this->sEmptyErrorMessage</label>\n";
		}		
		return $sToRender;
	}
}
?>
