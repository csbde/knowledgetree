<?php

/**
* Presentation information used by modifySpecificMetaDataBL.php
* 
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 28 February 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*/

function getEditableTypeSpecificMetaData($iDocumentID, $iDocumentTypeID) {
    global $default;    
	$sQuery = "SELECT DISTINCT $iDocumentID AS document_id, COALESCE(DFL.id, -1) AS id, DF.id AS document_field_id, DFL.value AS value, DF.name AS field_name " .
				"FROM document_type_fields_link AS DTFL INNER JOIN document_fields AS DF ON DTFL.field_id = DF.id " .
				"LEFT OUTER JOIN document_fields_link AS DFL ON DFL.document_field_id = DTFL.field_id AND DFL.document_id = $iDocumentID " .
				"WHERE DF.is_generic = 0 " .
				"AND DTFL.document_type_id = $iDocumentTypeID";

	$aStoreColumnNames = array("document_id", "document_field_id", "value");
    $aDisplayColumnNames = array("document_id", "field_name", "value");
    $aColumnsEditable = array(0,0,1);
    $aColumnsVisible = array(0,1,1);
    $aColumnDisplayTypes = array(1,1,4);
    $aColumnDatabaseTypes = array(0,0,1);
	$aMetaDataColumnNames = array(2=>"field_name");

    $oPatternTableSqlQuery = & new PatternEditableTableSqlQuery($sQuery, "document_fields_link", $aStoreColumnNames, $aDisplayColumnNames, $aColumnsEditable, $aColumnsVisible, $aColumnDisplayTypes, $aColumnDatabaseTypes);
    $oPatternTableSqlQuery->setTableCaption("Type specific meta data");
    $oPatternTableSqlQuery->setUniqueName("tsmd");
	$oPatternTableSqlQuery->setMetaDataFields($aMetaDataColumnNames);	
    return $oPatternTableSqlQuery->render();
}

function getPage($iDocumentID, $iDocumentTypeID) {
	global $default;
	$sToRender = "<table>\n";
	$sToRender .= "<tr>\n";		
	$sToRender .= "<td>\n";
	$sToRender .= getEditableTypeSpecificMetaData($iDocumentID, $iDocumentTypeID);
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";		
	$sToRender .= "<td>\n";
	
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .= "<input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" />\n";
	$sToRender .= "</td>\n";
	$sToRender .= "<td>\n";
	$sToRender .= "<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=$iDocumentID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a>\n";
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	$sToRender .= "</td>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

?>


