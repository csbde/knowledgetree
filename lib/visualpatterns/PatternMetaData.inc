<?php

require_once("PatternListBox.inc");

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
		$sQuery = "SELECT has_lookup FROM $default->owl_fields_table WHERE name LIKE '" . $this->sMetaDataField . "'";
		$sql = $default->db;
		$sql->query($sQuery);
		if ($sql->next_record()) {			
			if ($sql->f("has_lookup")) {
				//is a lookup, so display a drop down list
				$sWhereClause = "DF.name LIKE '" . $this->sMetaDataField . "'";
				$sFromClause = "INNER JOIN $default->owl_fields_table AS DF ON ST.document_field_id = DF.id";
				$oPatternListBox = & new PatternListBox("$default->owl_document_fields_lookup_tables", "id", "name",  $this->sFormName, $sWhereClause);
				if ($this->sValue != null) {
					$oPatternListBox->setSelectedValue($this->sValue);
				}
				$oPatternListBox->setFromClause($sFromClause);				
				return $oPatternListBox->render();
			} else {
				$sToRender = "<input type=\"text\" name=\"" . $this->sMetaDataField . "\" ";
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
