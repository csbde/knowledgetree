<?php

require_once("PatternListBox.inc");
/**
 * $Id$
 *
 * Renders document field appropriately (as a listbox if required).
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
class PatternMetaData {
	
	var $sMetaDataField;
	var $sFormName;
	var $sValue;
	/** 1 = drop down, 2 = text field */
	var $iType;
	
	
	function PatternMetaData($sNewMetaDataField, $sNewFormName, $sNewValue = null) {
		$this->sMetaDataField = $sNewMetaDataField;
		$this->sFormName = $sNewFormName;
		$this->sValue = $sNewValue;
	}
	
	
	function render() {
		global $default;
		$sQuery = "SELECT has_lookup FROM $default->document_fields_table WHERE name LIKE '" . addslashes($this->sMetaDataField) . "'";

		$sql = $default->db;
		$sql->query($sQuery);
		if ($sql->next_record()) {			
			if ($sql->f("has_lookup")) {
				//is a lookup, so display a drop down list
				$sWhereClause = "DF.name LIKE '" . $this->sMetaDataField . "'";
				$sFromClause = "INNER JOIN $default->document_fields_table AS DF ON ST.document_field_id = DF.id";
				$oPatternListBox = & new PatternListBox("$default->metadata_table", "name", "name", $this->sFormName);
				if ($this->sValue != null) {
					$oPatternListBox->setSelectedValue($this->sValue);
				}
				$oPatternListBox->setFromClause($sFromClause);
                $oPatternListBox->setWhereClause($sWhereClause);				
				return $oPatternListBox->render();
			} else {
                $sToRender = "<input type=\"text\" name=\"" . $this->sFormName . "\" ";                
				if ($this->sValue != null) {
					$sToRender .= "value=\"" . $this->sValue . "\" ";
				}
				$sToRender .= " />";
				return $sToRender;
			}
		} else {
			return "Error in PatternMetaData";
		}
	}
	
}

?>
