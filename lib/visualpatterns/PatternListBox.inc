<?php
/**
 * $Id$
 *
 * Creates a drop down list box using a table name and
 * two column names (one column is the display value, the other
 * is the option value).  The option value column should always
 * be an ID that is a primary key in a table
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
 * @todo - if the list is set to submit on change, it submits the form.  Investigate ways
 * to get a list action to occur, instead of the default form action 
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
    /** additional list box items */
    var $aAdditionalEntries;
    /** override display name */
    var $sCompositeDisplayColumnName;
    /** override query */
    var $sQuery;
    
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
	
	function setQuery($sQuery) {
		$this->sQuery = $sQuery;
	}		
	function setSelectName($sNewSelectName) {		
		$this->sSelectName = $sNewSelectName;
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
    
    function setAdditionalEntries($aNewValue) {
        $this->aAdditionalEntries = $aNewValue;
    }
    
    function setCompositeDisplayName($sNewValue) {
        $this->sCompositeDisplayColumnName = $sNewValue;
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
		if (isset($this->sQuery)) {
			$sQuery = $this->sQuery;
		} else {
			$sQuery = "SELECT ";/*wc*/
	        if (isset($this->sCompositeDisplayColumnName)) {
	            $sQuery .= "$this->sCompositeDisplayColumnName";
	        } else {
	            $sQuery .= "DISTINCT ST." . $this->sDisplayColumn;
	        }
	        $sQuery .=  " AS display, ST." .  $this->sValueColumn . " AS value FROM $this->sTableName AS ST ";
			if (isset($this->sFromClause)) {
				$sQuery .= $this->sFromClause . " ";
			}
			if (isset($this->sWhereClause)) {
				$sQuery .= "WHERE " . $this->sWhereClause . " ";
			}
			$sQuery .= "ORDER BY ST.$this->sDisplayColumn " . ($this->bOrderAsc ? "ASC" : "DESC");
		}
		$sql->query($sQuery);
		if ($sql->num_rows() > 0 || $this->bIncludeDefaultValue && (strlen($this->sEmptyErrorMessage) == 0)) {			
			if (isset($this->sOnChangeAction)) {
				$sToRender = "<SELECT NAME=\"$this->sSelectName\" ". ($this->bPostBackOnChange ? "onChange=\"$this->sOnChangeAction\" " : " ") . ">\n";
			} else {
				$sToRender = "<SELECT NAME=\"$this->sSelectName\" ". ($this->bPostBackOnChange ? "onChange=\"document.MainForm.submit();\" " : " ") . ">\n";
			}
			if ($this->bIncludeDefaultValue) {
                $sToRender .= "<OPTION value=\"\">None</OPTION>\n";
			}
			while ($sql->next_record()) {
				if ($this->selectedValue == $sql->f("value")) {
					$sToRender .= "<OPTION value=\"" . $sql->f("value") . "\" SELECTED>" . $sql->f("display") . "</OPTION>\n";
				} else {
					$sToRender .= "<OPTION value=\"" . $sql->f("value") . "\">" . $sql->f("display") . "</OPTION>\n";
				}
			}
            if (isset($this->aAdditionalEntries)) {
                for ($i=0; $i<count($this->aAdditionalEntries); $i++) {
                    $sToRender .= "<OPTION value=\"" . $this->aAdditionalEntries[$i]["value"] . "\">" . $this->aAdditionalEntries[$i]["display"] . "</OPTION>\n";
                }
            }
			$sToRender .= "</SELECT>\n";
		} else {
			$sToRender .= "<label class=\"errorText\">$this->sEmptyErrorMessage</label>\n";
		}		
		return $sToRender;
	}
    
    function getEntries() {
        global $default;
        
		$sql = $default->db;
		$sQuery = "SELECT DISTINCT ST." . $this->sDisplayColumn . " AS display, ST." .  $this->sValueColumn . " AS value FROM $this->sTableName AS ST ";/*wc*/
		if (isset($this->sFromClause)) {
			$sQuery .= $this->sFromClause . " ";
		}
		if (isset($this->sWhereClause)) {
			$sQuery .= "WHERE " . $this->sWhereClause . " ";
			
		}
		
		$sQuery .= "ORDER BY ST.$this->sDisplayColumn " . ($this->bOrderAsc ? "ASC" : "DESC");		

		$sql->query($sQuery);
        $aValues = array();
        while ($sql->next_record()) {
            $aValues[] = array("value" => $sql->f("value"),
                               "display" => $sql->f("display"));
        }
        return $aValues;
    }
}
?>
