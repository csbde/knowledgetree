<?php

function getEditableGenericMetaData($iDocumentID, $iDocumentTypeID) {
    global $default;
    $sQuery = "SELECT DISTINCT $iDocumentID AS document_id, COALESCE(DFL.id, -1) AS id, DF.id AS document_field_id, DFL.value AS value, DF.name AS field_name " .
				"FROM document_fields AS DF LEFT OUTER JOIN document_fields_link AS DFL ON DF.id = DFL.document_field_id AND DFL.document_id = $iDocumentID " .				
				"WHERE DF.is_generic = 1 ";

    $aStoreColumnNames = array("document_id", "document_field_id","value");
    $aDisplayColumnNames = array("document_id", "field_name", "value");
    $aMetaDataColumnNames = array(2=>"field_name");
    $aColumnsEditable = array(0,0,1);
    $aColumnsVisible = array(0,1,1);
    $aColumnDisplayTypes = array(1,1,4);
    $aColumnDatabaseTypes = array(0,0,1);
    
    $oPatternTableSqlQuery = & new PatternEditableTableSqlQuery($sQuery, "document_fields_link", $aStoreColumnNames, $aDisplayColumnNames, $aColumnsEditable, $aColumnsVisible, $aColumnDisplayTypes, $aColumnDatabaseTypes);
    $oPatternTableSqlQuery->setTableCaption("Generic Meta Data");
    $oPatternTableSqlQuery->setUniqueName("gmd");
	$oPatternTableSqlQuery->setRequiredColumnNames(array("value"));
	$oPatternTableSqlQuery->setMetaDataFields($aMetaDataColumnNames);
    return $oPatternTableSqlQuery->render();

}

function getPage($iDocumentID, $iDocumentTypeID, $fFirstEdit) {
	global $default;
	$sToRender = "<table>\n";
	$sToRender .= "<tr>\n";		
	$sToRender .= "<td>\n";
	$sToRender .= getEditableGenericMetaData($iDocumentID, $iDocumentTypeID);
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";		
	$sToRender .= "<td>\n";
	
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .= "<input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" />\n";
	$sToRender .= "</td>\n";
	if (!isset($fFirstEdit)) {
		//can't cancel if you're uploading for the first time, must fill out the generic meta data
		$sToRender .= "<td>\n";
		$sToRender .= "<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=$iDocumentID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a>\n";
		$sToRender .= "</td>\n";
		$sToRender .= "</tr>\n";
	}
	$sToRender .= "</table>\n";
	
	$sToRender .= "</td>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

?>
