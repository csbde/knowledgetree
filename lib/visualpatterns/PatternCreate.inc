<?php
/**
 * $Id$
 *
 * This pattern facilities the creation of new entries in the database using
 * the objects associated with those entries.
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
    var $aDefaultValues;
	
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
    
    function setDefaultValues($aNewValue) {
        $this->aDefaultValues = $aNewValue;
    }
	
	function render() {
        $this->sUniqueName .= KTUtil::randomString();
        $_SESSION["pelfq_" . $this->sUniqueName . "_object"] = $this->sObject;
        $_SESSION["pelfq_" . $this->sUniqueName . "_fn"] = $this->sObjectPath;
        $_SESSION["pageAccess"][$default->rootUrl . '/presentation/lookAndFeel/knowledgeTree/create.php'] = true;

		$sToRender = "<table border=\"0\">\n";
		//unique_start marks the start of information to be parsed from the HTML page by create.php
		$sToRender .= "<input type=\"hidden\" name=\"unique_start_" . $this->sUniqueName . "\" />\n";
		//write the name of the object to be created
		// $sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_object\" value=\"" . $this->sObject . "\" />\n";
		//name of folder in lib directory in which object .inc file is located
		// $sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_fn\" value=\"" . $this->sObjectPath . "\" />\n";
		for ($i = 0; $i < count($this->aDisplayRowNames); $i++) {
			$sToRender .= "<tr>\n";	
			//write the parameter number of this value in the object's constructor
			$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $i . "_parnum\" value=\"" . $this->aParameterNumbers[$i]  . "\" />\n";
			switch ($this->aDisplayColumnTypes[$i]) {
				case 1:
					//write the type (in this case text)
					$sToRender .= "<input type=\"hidden\" name=\"" . $this->sUniqueName . "_" . $i . "_type\" value=\"1\" />\n";
					//write the value
					$sToRender .= "<td>" . $this->aDisplayRowNames[$i] . "</td><td><input size = \"30\" type=\"text\" name=\"" . $this->sUniqueName . "_" . $i . "_value\" value=\"" . (isset($this->aDefaultValues[$i]) ? $this->aDefaultValues[$i] : "") . "\" /></td>\n";
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
					if (isset($this->aDropDownListValueColumns[$i])) {
						$sValueColumn = $this->aDropDownListValueColumns[$i];
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
		$sToRender .= "function validateForm(theForm) {\n";			
		for ($i = 0; $i < count($this->aDisplayRowNames); $i++) {
			if ($this->aRequired[$i]) {
				$sToRender .= "\tif (!(validRequired(document.MainForm." . $this->sUniqueName . "_" . $i . "_value, '" . $this->aDisplayRowNames[$i] . "'))) {\n";
				$sToRender .= "\t\treturn false;\n\t}\n";
			}
		}
		$sToRender .= "return true;\n}\n";
		$sToRender .= "//-->\n</script>\n\n";
		
		return $sToRender;
	}
}
?>
