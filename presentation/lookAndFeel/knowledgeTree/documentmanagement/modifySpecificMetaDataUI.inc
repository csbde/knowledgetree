<?php
/**
 * $Id$
 *
 * Presentation information used by modifySpecificMetaDataBL.php
 * 
 * @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */

function getEditableTypeSpecificMetaData($iDocumentID, $iDocumentTypeID) {
    global $default;    
	$sQuery = "SELECT DISTINCT $iDocumentID AS document_id, COALESCE(DFL.id, -1) AS id, DF.id AS document_field_id, DFL.value AS value, DF.name AS field_name, DTFL.is_mandatory AS is_mandatory " .
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
    $oPatternTableSqlQuery->setEmptyTableMessage("No Type Specific Meta Data");
    $oPatternTableSqlQuery->setUniqueName("tsmd");
	$oPatternTableSqlQuery->setMetaDataFields($aMetaDataColumnNames);	
    return $oPatternTableSqlQuery->render();
}

function getPage($iDocumentID, $iDocumentTypeID, $bFirstEdit) {
	global $default;
	$sToRender .= renderHeading("Edit Type Specific Meta Data");
	$sToRender .= displayDocumentPath($iDocumentID);	
	$sToRender .= "<table>\n";
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
	$sToRender .= "<input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" border=\"0\"/>\n";
	$sToRender .= "</td>\n";
    if (!isset($bFirstEdit)) {    
        $sToRender .= "<td>\n";
        $sToRender .= generateControllerLink("viewDocument, "fDocumentID=$iDocumentID&fShowSection=typeSpecificMetaData", "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/>");
        $sToRender .= "</td>\n";
    }
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	$sToRender .= "</td>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}
?>