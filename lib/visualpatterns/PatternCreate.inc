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
	var	$sObjectPath;
	var $aDisplayRowNames;
	var $aParameterNumbers;
	var $aDisplayColumnTypes;
	var	$aRequired;
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
							NB PARAMETER NUMBERS START AT ZERO
	* @param 	Array		Types of columns to be displayed (1 = text, 2 = boolean, 3 = drop down list)	
	* @param 	Array		Names of lookup tables for display column type 3
	* @param 	Array		Names of column in lookup table to use as value in select (if not is specified, id is assumed)
	* @param 	Array		Names of column in lookup table to display (if not is specified, name is assumed)
	*/
	function PatternCreate($sNewObject, $sNewObjectPath, $aNewDisplayRowNames, $aNewParameterNumbers, $aNewDisplayColumnTypes, $aNewRequired, $aNewDropDownListTableNames = null, $aNewDropDownListValueColumns = null, $aNewDropDownListDisplayColumns = null) {
		$this->sObject = $sNewObject;
		$this->sObjectPath = $sNewObjectPath;
		$this->sNewQuery = $sNewQuery;
		$this->aDisplayRowNames = $aNewDisplayRowNames;
		$this->aParameterNumbers = $aNewParameterNumbers;
		$this->aDisplayColumnTypes = $aNewDisplayColumnTypes;
		$this->aRequired = $aNewRequired;
		$this->aDropDownListTableNames = $aNewDropDownListTableNames;
		$this->aDropDownListValueColumns = $aNewDropDownListValueColumns;
		$this->aDropDownListDisplayColumns = $aNewDropDownListDisplayColumns;
		
	}
	
	function setUniqueName($sNewValue) {
		$this->sUniqueName = $sNewValue;
	}
	
	function render() {
		$sToRender = "<table border=\"0\">\n";
		//unique_start marks the start of information to be parsed from the HTML page by create.php
		$sToRender .= "<input type=\"hidden\" name=\"unique_start_" . $this->sUniqueName . "\" />\n";
		//write the name of the object to be created
		$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_object\" value=\"" . $this->sObject . "\" />\n";
		//name of folder in lib directory in which object .inc file is located
		$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_fn\" value=\"" . $this->sObjectPath . "\" />\n";
		for ($i = 0; $i < count($this->aDisplayRowNames); $i++) {
			$sToRender .= "<tr>\n";	
			//write the parameter number of this value in the object's constructor
			$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $i . "_parnum\" value=\"" . $this->aParameterNumbers[$i]  . "\" />\n";
			switch ($this->aDisplayColumnTypes[$i]) {
				case 1:
					//write the type (in this case text)
					$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $i . "_type\" value=\"1\" />\n";
					//write the value
					/*if ($this->aRequired[$i]) {						
						$sToRender .= "<td>" . $this->aDisplayRowNames[$i] . "</td><td><input type=\"text\" name=" . $this->sUniqueName . "_" . $i . "_value value=\"\" onfocus=\"validRequired(" . $this->sUniqueName . "_" . $i . "_value, '" . $this->aDisplayRowNames[$i] . "')\" /></td>\n";
					} else {
						$sToRender .= "<td>" . $this->aDisplayRowNames[$i] . "</td><td><input type=\"text\" name=\"" . $this->sUniqueName . "_" . $i . "_value value=\"\" /></td>\n";						
					}*/
					$sToRender .= "<td>" . $this->aDisplayRowNames[$i] . "</td><td><input type=\"text\" name=\"" . $this->sUniqueName . "_" . $i . "_value\" value=\"\" /></td>\n";
					break;
				case 2:
					//write the type (in this case checkbox)
					$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $i . "_type\" value=\"2\" />\n";
					//write the value
					$sToRender .= "<td>" . $this->aDisplayRowNames[$i] . "</td><td><input type=\"checkbox\" name=\"" . $this->sUniqueName . "_" . $i . "_value\" value=\"1\" /></td>\n";
					break;
				case 3:
					//write the type (in this case dropdown)
					$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $i . "_type\" value=\"3\" />\n";
					$sDisplayColumn;
					$sValueColumn;
					//write the value
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
		//unique_end marks the end of information to be parsed from the HTML page by create.php
		$sToRender .= "<input type=\"hidden\" name=\"unique_end_" . $this->sUniqueName . "\" />\n";		
		$sToRender .= "</table>\n";
		
		$sToRender .= $this->generateRequiredFieldValidation();
		
		return $sToRender;
		
	}
	
	function generateRequiredFieldValidation() {
		$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
		$sToRender .= "function validate666Form(theForm) {\n";			
		for ($i = 0; $i < count($this->aDisplayRowNames); $i++) {
			if ($this->aRequired[$i]) {
				$sToRender .= "\tif (!(validRequired(document.MainForm." . $this->sUniqueName . "_" . $i . "_value, '" . $this->aDisplayRowNames[$i] . "'))) {\n";
				$sToRender .= "\t\treturn false;\n\t}\n";
				//$sToRender .= "alert(\"You dumbass\");\n";
				//$sToRender .= "alert(theForm." . $this->sUniqueName . "_" . $i . "_value);\n";
				//$sToRender .= "validRequired(theForm." . $this->sUniqueName . "_" . $i . "_value, '" . $this->aDisplayRowNames[$i] . "');\n";
				
			}
		}
		$sToRender .= "return true;\n}\n";
		
		$sToRender .= "//-->\n</script>\n\n";
		
		return $sToRender;
                
	}
}

?>
