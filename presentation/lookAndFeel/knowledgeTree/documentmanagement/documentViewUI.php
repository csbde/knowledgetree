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
	echo "<table border=1><tr><td>$sDocumentPath</td></tr></table>\n";
}

function renderDocumentGenericMetaData($oDocument) {
	echo "<table border=1>\n";
}

function renderDocumentMetaData($oDocument) {
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
	echo $oPatternListFromQuery->render();
	
}
?>
