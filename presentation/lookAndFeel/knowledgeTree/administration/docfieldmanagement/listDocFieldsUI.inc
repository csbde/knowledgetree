<?php
/**
* Presentation information for listing Documemnt Fields
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getDocumentFields() {
	global $default;
	
	$sQuery = 	"SELECT id as DocFieldID, name as DocFieldName, data_type, is_generic, has_lookup, " . 
				"'Edit', 'Delete', 'Edit Lookups' " .
				"FROM " . $default->owl_fields_table . " " .
				"ORDER BY name";
	
    $aColumns = array("DocFieldName", "data_type", "is_generic", "has_lookup","Edit", "Delete", "Edit Lookups" );
    $aColumnNames = array("Name", "Data type", "Generic?", "Lookup?", "Edit", "Delete", "Edit Lookups");
    $aColumnTypes = array(1,1,2,2,3,3,3);
    $aDBColumnArray = array("DocFieldID");
    $aQueryStringVariableNames = array("fDocFieldID");
    	    
    $aHyperLinkURL = array(	4=> "$default->rootUrl/control.php?action=editDocField",                       			
                     			5=> "$default->rootUrl/control.php?action=removeDocField",
                     			6=> "$default->rootUrl/control.php?action=editDocFieldLookups");                       			
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);	    
	$oSearchResults->setDisplayColumnHeadings(true);	
    return $oSearchResults->render() ;	
}

function getPage() {
	global $default;
	$sToRender .= renderHeading("Document Field Management");

	// add user link
	$sToRender .= getAddLink("addDocField", "Add A Document Field");
	$sToRender .= getDocumentFields();
	return $sToRender;
}
?>