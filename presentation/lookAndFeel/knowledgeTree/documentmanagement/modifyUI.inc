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
	$sQuery = "SELECT DF.name AS name, DFL.value as value, DFL.id AS id " .
			"FROM $default->owl_documents_table AS D INNER JOIN $default->owl_document_fields_table AS DFL ON D.id = DFL.document_id " .
			"INNER JOIN $default->owl_fields_table AS DF ON DF.id = DFL.document_field_id " .
			"WHERE document_id = " . $oDocument->getID() . " " .
			"AND DF.is_generic = 1";
	$aColumns = array("name", "value");
	$aColumnHeaders = array("Tag", "Value");
	$aColumnTypes = array(1,1);
	$aColumnEditable = array(0,1);
	$oPatternTableSqlQuery = & new PatternEditableTableSqlQuery($sQuery, $default->owl_document_fields_table, "generic_meta_data", $aColumns, $aColumnTypes, $aColumnEditable, $aColumnHeaders, "90%");
	$oPatternTableSqlQuery->setTableHeading("Generic Meta Data");
	return $oPatternTableSqlQuery->render();
	
}



?>
