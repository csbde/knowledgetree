<?php
/**
* Class PatternCreate
*
* This pattern facilities the creation of new entries in the database using
* the objects associated with those entries
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 4 February 2003
* @package presentation.lib.visualpatterns
*/

class PatternCreate {
	
	var $sObject;	
	var $aDisplayRowNames;
	var $aParamaterNumbers;
	var $aDisplayColumnTypes;	
	var $aDropDownListTableNames;
	var $aDropDownListValueColumns;	
	var $aDropDownListDisplayColumns;
	var $sUniqueName;
	
	
	/**
	* Default constructor
	*
	* @param 	String		Name of object
	* @param 	Array		Name of each item that will be displayed
	* @param 	Array		Paramater number for each column e.g. is it constructor parameter 1 or 2 etc.
	* @param 	Array		Types of columns to be displayed (1 = text, 2 = boolean, 3 = drop down list)	
	* @param 	Array		Names of lookup tables for display column type 3
	* @param 	Array		Names of column in lookup table to use as value in select (if not is specified, id is assumed)
	* @param 	Array		Names of column in lookup table to display (if not is specified, name is assumed)
	*/
	function PatternCreate($sNewObject, $aNewDisplayRowNames, $aNewParameterNumbers, $aNewDisplayColumnTypes, $aNewDropDownListTableNames = null, $aNewDropDownListValueColumns = null, $aNewDropDownListDisplayColumns = null) {
		$this->sObject = $sNewObject;
		$this->sNewQuery = $sNewQuery;
		$this->aDisplayRowNames = $aNewDisplayRowNames;
		$this->aParamaterNumbers = $aNewParameterNumbers;
		$this->aDisplayColumnTypes = $aNewDisplayColumnTypes;		
		$this->aDropDownListTableNames = $aNewDropDownListTableNames;
		$this->aDropDownListValueColumns = $aNewDropDownListValueColumns;
		$this->aDropDownListDisplayColumns = $aNewDropDownListDisplayColumns;
		
	}
	
	function setUniqueName($sNewValue) {
		$this->sUniqueName = $sNewValue;
	}
	
	function render() {
		$sToRender = "<table border=\"0\">\n";
		$sToRender .= "<input type=\"hidden\" name=\"unique_start_" . $this->sUniqueName . "\" />\n";
		$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "\_object value=\"" . $this->sObject . "\" />\n";
		for ($i = 0; $i < count($this->aDisplayRowNames); $i++) {
			$sToRender .= "<tr>\n";	
			$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $i . "_parnum value=\"" . $this->aParameterNumbers[$i]  . "\" />\n";
			switch ($this->aDisplayColumnTypes[$i]) {
				case 1:
					$sToRender .= "<td>" . $this->aDisplayRowNames[$i] . "</td><td><input type=\"text\" name=\"" . $this->sUniqueName . "_" . $i . "_value value=\"\" /></td>\n";
					break;
				case 2:
					$sToRender .= "<td>" . $this->aDisplayRowNames[$i] . "</td><td><input type=\"checkbox\" name=\"" . $this->sUniqueName . "_" . $i . "_value value=\"checked\" /></td>\n";
					break;
				case 3:
					$sDisplayColumn;
					$sValueColumn;
					if (isset($this->sDropDownListValueColumns[$i])) {
						$sValueColumn = $this->sDropDownListValueColumns[$i];
					} else {
						$sValueColumn = "id";
					}
					
					if (isset($this->aDropDownListDisplayColumns[$i])) {
						$sDisplayColumn = $this->aDropDownListDisplayColumns[$i];
					} else {
						$sDisplayColumn = "name";
					}					
					$oPatternListBox = & new PatternListBox($this->aDropDownListTableNames[$i], $sDisplayColumn, $sValueColumn, $this->sUniqueName . "_" . $i . "_value");					
					$sToRender .= "\t<td>" . $this->aDisplayRowNames[$i] . "</td><td>" . $oPatternListBox->render() . "</td>\t\n";					
					break;
				default;
					break;
			}
			
			$sToRender .= "</tr>\n";			
		}		
		$sToRender .= "<input type=\"hidden\" name=\"unique_end_" . $this->sUniqueName . "\" />\n";
		$sToRender .= "</table>\n";
		return $sToRender;
		
	}
}

?>
