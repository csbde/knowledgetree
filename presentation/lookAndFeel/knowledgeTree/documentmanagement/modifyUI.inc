<?php

/**
* Presentation data used to modify documents (will be used by modifyBL.inc)
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 24 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*
*/

function renderDocumentPath($oDocument) {
    $sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($oDocument->getFolderID()), Folder::getFolderPathNamesAsArray($oDocument->getFolderID())) . " > " . $oDocument->getName();
    return "<table border=1 width = 100%><tr><td>$sDocumentPath</td></tr></table>\n";
}

function renderEditableDocumentData($oDocument) {
    global $default;
    $sQuery = "SELECT D.id AS id, D.name AS name " .
              "FROM $default->owl_documents_table AS D " .
              "WHERE D.id = " . $oDocument->getID();

    $aDisplayColumns = array("name");
    $aStoreColumns = array("name");
    $aColumnNames = array("Document Title");
    $aDisplayColumnTypes = array(1);
    $aDatabaseColumnTypes = array(1);

    $oPatternEditableListFromQuery = & new PatternEditableListFromQuery($sQuery, $default->owl_documents_table, $aDisplayColumns, $aStoreColumns, $aColumnNames, $aDisplayColumnTypes, $aDatabaseColumnTypes);
    $oPatternEditableListFromQuery->setUniqueName("dd");
    $oPatternEditableListFromQuery->setTableHeading("Document Data");
	$oPatternEditableListFromQuery->setColumnsRequired(array(1));
    return $oPatternEditableListFromQuery->render();
}

function renderEditableGenericMetaData($oDocument) {
    global $default;
    $sQuery = "SELECT " . $oDocument->getID() . " AS document_id, COALESCE(DFL.id, -1) AS id, DF.name AS name, DF.id as document_field_id, COALESCE(DFL.value, null) AS value " .
              "FROM $default->owl_fields_table AS DF LEFT OUTER JOIN $default->owl_document_fields_table AS DFL ON DFL.document_field_id = DF.id " .
              "RIGHT OUTER JOIN documents AS D ON D.id = DFL.document_id " .
              "WHERE DF.is_generic = 1 " .
              "AND D.id = " . $oDocument->getID();

    $aStoreColumnNames = array("document_id", "document_field_id","value");
    $aDisplayColumnNames = array("document_id", "name", "value");
    $aColumnsEditable = array(0,0,1);
    $aColumnsVisible = array(0,1,1);
    $aColumnDisplayTypes = array(1,1,1);
    $aColumnDatabaseTypes = array(0,0,1);


    $oPatternTableSqlQuery = & new PatternEditableTableSqlQuery($sQuery, "document_fields_link", $aStoreColumnNames, $aDisplayColumnNames, $aColumnsEditable, $aColumnsVisible, $aColumnDisplayTypes, $aColumnDatabaseTypes);
    $oPatternTableSqlQuery->setTableCaption("Generic Meta Data");
    $oPatternTableSqlQuery->setUniqueName("gmd");
    return $oPatternTableSqlQuery->render();

}

function renderEditableTypeSpecificMetaData($oDocument) {
    global $default;
    $sQuery = "SELECT COALESCE(DFL.id, -1) AS id, " . $oDocument->getID() . " AS document_id, DTFL.document_type_id AS document_type_id, DTL.name AS document_type_name, DTFL.field_id AS document_field_id, DF.name AS field_name, DFL.value AS value, DTFL.is_mandatory AS is_mandatory " .
              "FROM $default->owl_document_type_fields_table AS DTFL INNER JOIN $default->owl_folders_table AS F ON F.document_type_id = DTFL.document_type_id " .
              "INNER JOIN $default->owl_documents_table AS D ON D.folder_id = F.id " .
              "INNER JOIN $default->owl_document_types_table AS DTL ON DTL.id = DTFL.document_type_id " .
              "INNER JOIN $default->owl_fields_table AS DF ON DTFL.field_id = DF.id " .
              "LEFT OUTER JOIN $default->owl_document_fields_table AS DFL ON DFL.document_field_id = DF.id " .
              "WHERE D.id =  " . $oDocument->getID() . " " .
              "AND DF.is_generic = 0 ";

    $aStoreColumnNames = array("document_id", "document_field_id", "value");
    $aDisplayColumnNames = array("document_id", "field_name", "value");
    $aColumnsEditable = array(0,0,1);
    $aColumnsVisible = array(0,1,1);
    $aColumnDisplayTypes = array(1,1,1);
    $aColumnDatabaseTypes = array(0,0,1);

    $oPatternTableSqlQuery = & new PatternEditableTableSqlQuery($sQuery, "document_fields_link", $aStoreColumnNames, $aDisplayColumnNames, $aColumnsEditable, $aColumnsVisible, $aColumnDisplayTypes, $aColumnDatabaseTypes);
    $oPatternTableSqlQuery->setTableCaption("Type specific meta data");
    $oPatternTableSqlQuery->setUniqueName("tsmd");
    return $oPatternTableSqlQuery->render();
}

function renderPage($oDocument) {
    global $default;
    $sToRender = "<table border = 0>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>\n";
    $sToRender .= wrapInTable(renderEditableDocumentData($oDocument)) . "\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>\n";
    $sToRender .= wrapInTable(renderEditableGenericMetaData($oDocument)) . "\n";
    $sToRender .= "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>\n";
    $sToRender .= wrapInTable(renderEditableTypeSpecificMetaData($oDocument)) . "\n";
    $sToRender .= "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" /><td>\n";
    $sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a><td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

function wrapInTable($sHtml) {
    return "\n\t\t\t<table border = 1, width = 100%><tr><td>$sHtml</td></tr></table>\n";
}



?>
