<?php
/**
* documentViewUI.php
* Contains HTML information required to build the document view page.
* Will be used by documentViewBL.php
*
* Variables expected:
*			o $fDocumentID		Primary key of document to view
*
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 21 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentManager
*/

function renderDocumentPath($oDocument) {
	$sDocumentPath = Folder::getFolderDisplayPath($oDocument->getFolderID()) . " > " . $oDocument->getName();
	return "<table border=1><tr><td>$sDocumentPath</td></tr></table>\n";
}

function renderDocumentGenericMetaData($oDocument) {
	echo "<table border=1>\n";
}

function renderGenericDocumentMetaData($oDocument) {
	$sQuery = "SELECT D.name AS name, DT.datetime AS created_date, D.modified as last_modified, U.name as initiator, DFL.value as authors, CONCAT(CONCAT(D.major_version, \".\"), D.minor_version) AS version, WDSL.name AS status, DFL2.value AS category " . 
				"FROM documents AS D INNER JOIN document_fields_link AS DFL on D.id = DFL.document_id  " .
				"INNER JOIN users AS U on D.creator_id = U.id " .
				"INNER JOIN document_transactions AS DT on DT.document_id = D.id " .
				"INNER JOIN document_fields AS DF ON DF.id = DFL.document_field_id " .
				"INNER JOIN document_transaction_types_lookup AS DTTL ON DTTL.id = DT.transaction_id " .
				"INNER JOIN document_fields_link AS DFL2 ON DFL2.document_id = D.id " .
				"INNER JOIN document_fields AS DF2 ON DF2.id = DFL2.document_field_id " .
				"INNER JOIN web_documents AS WD ON WD.document_id = D.ID " .
				"INNER JOIN web_documents_status_lookup AS WDSL ON WD.status_id = WDSL.id " .
				"WHERE D.id = " . $oDocument->getID() . " " .
				"AND DF.name LIKE 'Author' " . 
				"AND DF2.name LIKE 'Category' " .
				"AND DTTL.name LIKE 'Create'";
				
	$aColumns = array("name", "created_date", "last_modified", "initiator", "authors", "version", "status", "category");
	$aColumnNames = array("Document title", "Date created", "Last updated", "Document initiator", "Author(s)", "Version", "Status", "Category");
	$aColumnTypes = array(1,1,1,1,1,1,1,1);
	
	$oPatternListFromQuery = & new PatternListFromQuery($sQuery, $aColumns, $aColumnNames, $aColumnTypes);
	$oPatternListFromQuery->setTableHeading("Generic Meta Data");
	//$oPatternListFromQuery->setRenderIndividualTableForEachResult(true);
	return $oPatternListFromQuery->render();
	
}

function renderTypeSpecificMetaData($oDocument) {
	$sQuery = "SELECT DF.name AS name, DFL.value AS value " .
				"FROM documents AS D INNER JOIN document_fields_link AS DFL ON D.id = DFL.document_id " .
				"INNER JOIN document_fields AS DF ON DF.ID = DFL.document_field_id " .
				"WHERE D.id = " . $oDocument->getID() . " " .
				"AND DF.name NOT LIKE 'Author' " .
				"AND DF.name NOT LIKE 'Category'";
	$aColumns = array("name", "value");
	$aColumnHeaders = array("Tag", "Value");
	$oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnHeaders, 300);
	$oPatternTableSqlQuery->setTableHeading("Type Specific Meta Data");
	return $oPatternTableSqlQuery->render();
}

function renderDocumentRouting($oDocument) {
	$sQuery = "SELECT R.name AS role_name, COALESCE(U.Name, 'Not assigned') AS name, GFAL.precedence AS precedence " .
				"FROM documents AS D INNER JOIN groups_folders_approval_link AS GFAL ON D.folder_id = GFAL.folder_id " .
				"INNER JOIN roles AS R ON GFAL.role_id = R.id " .
				"LEFT OUTER JOIN folders_users_roles_link AS FURL ON FURL.folder_id = D.folder_id " .
				"LEFT OUTER JOIN users AS U ON FURL.user_id = U.id " .
				"WHERE D.id = " . $oDocument->getID() . " " .
				"ORDER BY GFAL.precedence, role_name ASC";
		
	$aColumns = array("role_name", "name", "precedence");
	$aColumnHeaders = array("Role", "Player", "Sequence");
	$oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnHeaders, 300, true);
	$oPatternTableSqlQuery->setTableHeading("Document Routing");
	return $oPatternTableSqlQuery->render();
	
}

function renderPage($oDocument) {
	$sToRender = renderDocumentPath($oDocument) . "\n<br>\n"; 
	$sToRender .= "<table border = 0>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
		$sToRender .= "\t<table border = 0>\n";
		$sToRender .= "\t<tr>\n";
		$sToRender .= "\t\t<td>" . wrapInTable(renderGenericDocumentMetaData($oDocument)) . "</td>\n";
		$sToRender .= "\t</tr>\n";
		$sToRender .= "\t<tr>\n";
		$sToRender .= "\t\t<td>" . wrapInTable(renderTypeSpecificMetaData($oDocument)) . "</td>\n";
		$sToRender .= "\t</tr>\n";
		$sToRender .= "\t</table>\n";
	$sToRender .= "</td>\n";
	$sToRender .= "<td valign=top>\n";
		$sToRender .= "\t<table border = 0>\n";
		$sToRender .= "\t<tr>\n";
		$sToRender .= "\t\t<td>" . wrapInTable(renderDocumentRouting($oDocument)) . "</td>\n";
		$sToRender .= "\t</tr>";
		$sToRender .= "\t</table>";
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>";
		
	echo $sToRender;
	/*$sToRender = "<table border = 0 cellpadding = 4 width = 800>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td align = left colspan=2>" . wrapInTable(renderDocumentPath($oDocument)) . "</td>\n";
	$sToRender .= "</tr>\n";
	
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . wrapInTable(renderGenericDocumentMetaData($oDocument)) . "</td><td>" . wrapInTable(renderDocumentRouting($oDocument)) . "</td>\n";
	$sToRender .= "</tr>\n";
	
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . wrapInTable(renderTypeSpecificMetaData($oDocument)) . "</td><td>&nbsp</td>\n";	
	$sToRender .= "</tr>";
	
	$sToRender .= "</table>";
	
	
	echo $sToRender;*/
}

function wrapInTable($sHtml) {
	return "<table border = 1 width=400><tr><td>$sHtml</td></tr></table>";	
}

?>
