<?php
/**
* Presentation information for listing Document Types
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getDocumentTypes() {
	global $default;
	
	$sQuery = 	"SELECT id as DocTypeID, name as DocTypeName, " . 
				"'Edit', 'Delete', 'Edit Fields' " .
				"FROM " . $default->owl_document_types_table . " " .
				"ORDER BY name";
	
    $aColumns = array("DocTypeName", "Edit", "Delete", "Edit Fields");
    $aColumnNames = array("Name", "Edit", "Delete", "Edit Fields");
    $aColumnTypes = array(1,3,3,3);
    $aDBColumnArray = array("DocTypeID");
    $aQueryStringVariableNames = array("fDocTypeID");
    	    
    $aHyperLinkURL = array(	1=> "$default->rootUrl/control.php?action=editDocType&fDocTypeSelected=1",                       			
                     			2=> "$default->rootUrl/control.php?action=removeDocType",
                     			3=> "$default->rootUrl/control.php?action=editDocTypeFields&fDocTypeSelected=1");
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);	    
	$oSearchResults->setDisplayColumnHeadings(true);
    return $oSearchResults->render() ;	
}

function getPage() {
	global $default;
	$sToRender .= renderHeading("Document Type Management");

	// add user link
	$sToRender .= getAddLink("addDocType", "Add A Document Type");
	$sToRender .= getDocumentTypes();
	return $sToRender;
}
?>