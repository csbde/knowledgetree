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
	$sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($oDocument->getFolderID())) . " > " . $oDocument->getName();
	return "<table border=1 width = 100%><tr><td>$sDocumentPath</td></tr></table>\n";	
}

/*function renderEditableDocumentData($oDocument, $aDocumentDataArray) {
	global $default;
	$sQuery = "SELECT D.name, D.modified, DTT.datetime AS created, U.name AS initiator, CONCAT(CONCAT(D.major_version, '.'), D.minor_version) AS version, WDSL.name AS status " .
			"FROM $default->owl_documents_table AS D INNER JOIN $default->owl_web_documents_table AS WD ON WD.document_id = D.ID " .
			"INNER JOIN $default->owl_web_documents_status_table AS WDSL ON WD.status_id = WDSL.id " .
			"INNER JOIN $default->owl_users_table AS U ON U.id = D.creator_id " .
			"INNER JOIN $default->owl_document_transactions_table AS DTT ON DTT.document_id = D.id " .
			"INNER JOIN $default->owl_transaction_types_table AS TT ON DTT.transaction_id = TT.id " .
			"WHERE D.id = " . $oDocument->getID() . " " .
			"AND TT.name LIKE 'Create'";
				
		$sql = new Owl_DB();
		$sql->query($sQuery);
		$sql->next_record();
		
		$sToRender = "<html><head></head><body><form action=\"handler\" onsubmit=\"return validateString(documents&name&1,'Please enter user name',3,100)\">\n";
		$sToRender .= "<table width = 100%>";
		$sToRender .= "<caption align = left><b>Generic Meta Data</caption>";
		
		$aDocumentDataArray[count($aDocumentDataArray)] = "document.name.". $oDocument->getID();
		$sToRender .= "<tr>\n";
		$sToRender .= "\t<td>Document title</td><td>" . textInput(("documents&name&". $oDocument->getID()), $sql->f("name")) . "</td>\n";
		//$sToRender .= "\t<td>Document title</td><td>" . textInput("docname", $sql->f("name")) . "</td>\n";
		$sToRender .= "</tr>\n";
		
		$sToRender .= "<tr>\n";
		$sToRender .= "\t<td>Date created</td><td>" . $sql->f("created") . "</td>\n";
		$sToRender .= "</tr>\n";
				
		$sToRender .= "<tr>\n";
		$sToRender .= "\t<td>Last updated</td><td>" . $sql->f("last_modified") . "</td>\n";
		$sToRender .= "</tr>\n";

		$sToRender .= "<tr>\n";
		$sToRender .= "\t<td>Document initiator</td><td>" . $sql->f("initiator") . "</td>\n";
		$sToRender .= "</tr>\n";
		
		$sToRender .= "</table>\n";

		$sToRender .= "<input type=submit value=\"Submit\" /></form></body></html>";
		return $sToRender;
}*/

/*function renderGenericMetaData() {
	"SELECT DF.name, 
}*/

function renderEditableGenericMetaData($oDocument) {
	global $default;	
	$sQuery =	"SELECT " . $oDocument->getID() . " AS document_id, COALESCE(DFL.id, -1) AS id, DF.name AS name, DF.id as document_field_id, COALESCE(DFL.value, null) AS value " .
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
	
	
	$oPatternTableSqlQuery = & new PatternEditableTableSqlQuery($sQuery, "document_fields_links", $aStoreColumnNames, $aDisplayColumnNames, $aColumnsEditable, $aColumnsVisible, $aColumnDisplayTypes, $aColumnDatabaseTypes);
	$oPatternTableSqlQuery->setTableCaption("Generic Meta Data");
	$oPatternTableSqlQuery->setUniqueName("gmd");
	return $oPatternTableSqlQuery->render();
	
}

function renderEditableTypeSpecificMetaData($oDocument) {
	global $default;
	$sQuery = "SELECT COALESCE(DFL.id, -1) AS id, " . $oDocument->getID() . " AS document_id, DTFL.document_type_id AS document_type_id, DTL.name AS document_type_name, DTFL.field_id, DF.name AS field_name, DFL.value AS field_value, DTFL.is_mandatory AS is_mandatory " .
			"FROM $default->owl_document_type_fields_table AS DTFL INNER JOIN $default->owl_folders_table AS F ON F.document_type_id = DTFL.document_type_id " .
			"INNER JOIN $default->owl_documents_table AS D ON D.folder_id = F.id " .
			"INNER JOIN $default->owl_document_types_table AS DTL ON DTL.id = DTFL.document_type_id " .
			"INNER JOIN $default->owl_fields_table AS DF ON DTFL.field_id = DF.id " .
			"LEFT OUTER JOIN $default->owl_document_fields_table AS DFL ON DFL.document_field_id = DF.id " .
			"WHERE D.id =  " . $oDocument->getID() . " " .
			"AND DF.is_generic = 0 ";
			
	$aStoreColumnNames = array("document_id", "field_id", "field_value");
	$aDisplayColumnNames = array("document_id", "field_name", "field_value");
	$aColumnsEditable = array(0,0,1);
	$aColumnsVisible = array(0,1,1);
	$aColumnDisplayTypes = array(1,1,1);
	$aColumnDatabaseTypes = array(0,0,1);
	
	$oPatternTableSqlQuery = & new PatternEditableTableSqlQuery($sQuery, "document_fields_links", $aStoreColumnNames, $aDisplayColumnNames, $aColumnsEditable, $aColumnsVisible, $aColumnDisplayTypes, $aColumnDatabaseTypes);																
	$oPatternTableSqlQuery->setTableCaption("Type specific meta data");
	$oPatternTableSqlQuery->setUniqueName("tsmd");
	return $oPatternTableSqlQuery->render();
}

function renderPage($oDocument) {
	$sToRender = "<table border = 0>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .= wrapInTable(renderEditableGenericMetaData($oDocument)) . "\n";
	$sToRender .= "</td>\n";
	$sToRender .= "<td>\n";
	$sToRender .= wrapInTable(renderEditableTypeSpecificMetaData($oDocument)) . "\n";
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "</table>\n";
	$sToRender .= "<input type=\"submit\" value=\"Submit\" />\n";
	return $sToRender;
}

function wrapInTable($sHtml) {
	return "\n\t\t\t<table border = 1, width = 100%><tr><td>$sHtml</td></tr></table>\n";	
}



?>
