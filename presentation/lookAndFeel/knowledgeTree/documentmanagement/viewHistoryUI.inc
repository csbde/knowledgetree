<?php
/**
* viewHistoryBL.php
* Contains HTML information required to build the document history view page.
* Will be used by viewHistoryBL.php
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 12 February 2003
* @package presentation.lookAndFeel.knowledgeTree.documentManager
*/

function getDocumentPath($iFolderID, $sDocumentName) {
    global $default;
    $sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->owl_root_url/control.php?action=browse") . " > " . $sDocumentName;
    return "<table border=1 width = 100%><tr><td>$sDocumentPath</td></tr></table>\n";
}

function getDocumentHistory($iDocumentID) {
    global $default;
    $sQuery = "SELECT DTT.name AS transaction_name, U.name AS user_name, DT.version AS version, DT.comment AS comment, DT.datetime AS datetime " .
			"FROM $default->owl_document_transactions_table AS DT INNER JOIN $default->owl_users_table AS U ON DT.user_id = U.id " .
			"INNER JOIN $default->owl_transaction_types_table AS DTT ON DTT.id = DT.transaction_id " . 
			"WHERE DT.document_id = $iDocumentID";
	
    $aColumns = array("transaction_name", "user_name", "version", "comment", "datetime");
    $aColumnHeaders = array("Type","Users","Version","Comment","Datetime");
    $aColumnTypes = array(1,1,1,1,1);
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "90%");
    $oPatternTableSqlQuery->setTableHeading("Transaction History");
	$oPatternTableSqlQuery->setIncludeBorder(true);
	$oPatternTableSqlQuery->setDisplayColumnHeadings(true);
    return $oPatternTableSqlQuery->render();
}

function getPage($iDocumentID, $iFolderID, $sDocumentName) {
	$sToRender = "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getDocumentPath($iFolderID, $sDocumentName) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getDocumentHistory($iDocumentID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}

?>
