<?php
/**
 * $Id$
 *
 * Contains HTML information required to build the document history view page.
 * Will be used by viewHistoryBL.php
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @date 12 February 2003
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */

function getDocumentPath($iFolderID, $sDocumentName) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");	    
    $sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse") . " > " . $sDocumentName;
	return "<table border=\"0\"cellpadding=\"5\" width=\"610\"><tr bgcolor=\"$sTDBGColour\"><td width=\"87%\">$sDocumentPath</td></tr></table>\n";
}

function getDocumentHistory($iDocumentID) {
    global $default;
    $sQuery = "SELECT DTT.name AS transaction_name, U.name AS user_name, DT.version AS version, DT.comment AS comment, DT.datetime AS datetime " .
			"FROM $default->owl_document_transactions_table AS DT INNER JOIN $default->owl_users_table AS U ON DT.user_id = U.id " .
			"INNER JOIN $default->owl_transaction_types_table AS DTT ON DTT.id = DT.transaction_id " . 
			"WHERE DT.document_id = $iDocumentID ORDER BY DT.datetime DESC";
	$sql = $default->db;
    $sToRender  = "<table cellpadding=\"5\" width=\"100%\">\n";
    $sToRender .= "<caption align=\"top\" colspan=\"5\" align=\"left\"><b>Transaction History</b></caption>\n";
    $sql->query($sQuery);
    if ($sql->num_rows() == 0) {
        $sToRender .= "<tr>\n";	
        $sToRender .= "\t<td colspan=\"5\">No Transaction History data</td>\n";
        $sToRender .= "</tr>\n";
    } else {
        $sToRender .= "<tr><th align=\"left\">Type</th>\n";
        $sToRender .= "<th align=\"left\">Users</th>\n";
        $sToRender .= "<th align=\"left\">Version</th>\n";
        $sToRender .= "<th align=\"left\">Comment</th>\n";
        $sToRender .= "<th align=\"left\">Datetime</th></tr>\n";
        $i = 0;
        while ($sql->next_record()) {
            if ($sql->f("transaction_name") == "Check Out") {
	        	$sVersion = generateControllerLink("downloadDocument", "fDocumentID=$iDocumentID&fVersion=" . $sql->f("version"), "<strong>" . $sql->f("version") . "</strong>");
            } else {
                $sVersion = $sql->f("version");
            }
            $sToRender .= "<tr bgcolor=\"" . getColour($i++) . "\">\n";
            $sToRender .= "<td>" . $sql->f("transaction_name") . "</td><td>" . $sql->f("user_name") . "</td><td>$sVersion</td>\n";
            $sToRender .= "<td>" . displaySpace($sql->f("comment")) . "</td><td>" . $sql->f("datetime") . "</td></tr>\n";
        }
        $sToRender .= "</table>\n";
    }
    return $sToRender;
}

function displaySpace($sHtml) {
	if ($sHtml == "") {
		return "&nbsp;";
	} else {
		return $sHtml;
	}
}

function getPage($iDocumentID, $iFolderID, $sDocumentName) {
    global $default;
    $sToRender  = renderHeading("Document History");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getDocumentPath($iFolderID, $sDocumentName) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getDocumentHistory($iDocumentID) . "</td>\n";
	$sToRender .= "</tr>\n";
    $sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=$iDocumentID\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a></td></tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}

?>
