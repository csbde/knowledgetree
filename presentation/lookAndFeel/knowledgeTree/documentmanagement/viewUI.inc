<?php
/**
* documentViewUI.php
* Contains HTML information required to build the document view page.
* Will be used by documentViewBL.php
*
* Variables expected:
*   o $fDocumentID  Primary key of document to view
*
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 21 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentManager
*/

function renderDocumentPath($oDocument) {
    global $default;
    $sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($oDocument->getFolderID()), Folder::getFolderPathNamesAsArray($oDocument->getFolderID()), "$default->rootUrl/control.php?action=browse") . " > <a href=\"" . $_SERVER["PHP_SELF"] . "?fDocumentID=" . $oDocument->getID() . "&fForInlineView=1\">" . $oDocument->getName() . "</a>";
    return "<table border=1 width = 100%><tr><td>$sDocumentPath</td></tr></table>\n";
}

function renderDocumentData($oDocument) {
    global $default;
    $sQuery = "SELECT D.name, D.modified, DTT.datetime AS created, U.name AS initiator, CONCAT(CONCAT(D.major_version, '.'), D.minor_version) AS version, WDSL.name AS status " .
              "FROM $default->owl_documents_table AS D INNER JOIN $default->owl_web_documents_table AS WD ON WD.document_id = D.ID " .
              "INNER JOIN $default->owl_web_documents_status_table AS WDSL ON WD.status_id = WDSL.id " .
              "INNER JOIN $default->owl_users_table AS U ON U.id = D.creator_id " .
              "INNER JOIN $default->owl_document_transactions_table AS DTT ON DTT.document_id = D.id " .
              "INNER JOIN $default->owl_transaction_types_table AS TT ON DTT.transaction_id = TT.id " .
              "WHERE D.id = " . $oDocument->getID() . " " .
              "AND TT.name LIKE 'Create'";

    $aColumns = array("name", "modified", "created", "initiator", "version", "status");
    $aColumnNames = array("Document title", "Last updated", "Created", "Document initiator", "Version", "Status");
    $aColumnTypes = array(1,1,1,1,1);
    $oPatternListFromQuery = & new PatternListFromQuery($sQuery, $aColumns, $aColumnNames, $aColumnTypes);
    $oPatternListFromQuery->setTableHeading("Document Data");
    $oPatternListFromQuery->setTableWidth("90%");
    return $oPatternListFromQuery->render();
}

function renderGenericMetaData($oDocument) {
    global $default;
    $sQuery = "SELECT DF.name AS name, DFL.value as value " .
              "FROM $default->owl_documents_table AS D INNER JOIN $default->owl_document_fields_table AS DFL ON D.id = DFL.document_id " .
              "INNER JOIN $default->owl_fields_table AS DF ON DF.id = DFL.document_field_id " .
              "WHERE document_id = " . $oDocument->getID() . " " .
              "AND DF.is_generic = 1";
    $aColumns = array("name", "value");
    $aColumnHeaders = array("Tag", "Value");
    $aColumnTypes = array(1,1);
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "90%");
    $oPatternTableSqlQuery->setTableHeading("Generic Meta Data");
    return $oPatternTableSqlQuery->render();

}


function renderTypeSpecificMetaData($oDocument) {
    $sQuery = "SELECT DF.name AS name, DFL.value AS value " .
              "FROM documents AS D INNER JOIN document_fields_link AS DFL ON D.id = DFL.document_id " .
              "INNER JOIN document_fields AS DF ON DF.ID = DFL.document_field_id " .
              "WHERE D.id = " . $oDocument->getID() . " " .
              "AND DF.name NOT LIKE 'Author' " .
              "AND DF.name NOT LIKE 'Category' " .
              "AND DF.is_generic = 0";
    $aColumns = array("name", "value");
    $aColumnHeaders = array("Tag", "Value");
    $aColumnTypes = array(1,1);
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "90%");
    $oPatternTableSqlQuery->setTableHeading("Type Specific Meta Data");
	$oPatternTableSqlQuery->setEmptyTableMessage("No Type Specific Meta Data");
    return $oPatternTableSqlQuery->render();
}

function renderEditableDocumentRouting($oDocument) {
    global $default;
    $sQuery = "SELECT D.id as document_id, GFAL.id as id, R.name AS role_name, COALESCE(U.Name, 'Not assigned') AS name, GFAL.precedence AS precedence, COALESCE(FURL.active,0) AS active, COALESCE(FURL.done, 0) AS done, 'Edit' as edit " .
              "FROM documents AS D INNER JOIN $default->owl_groups_folders_approval_table AS GFAL ON D.folder_id = GFAL.folder_id " .
              "INNER JOIN roles AS R ON GFAL.role_id = R.id " .
              "LEFT OUTER JOIN folders_users_roles_link AS FURL ON FURL.group_folder_approval_id = GFAL.id " .
              "LEFT OUTER JOIN users AS U ON FURL.user_id = U.id " .
              "WHERE D.id = " . $oDocument->getID() . " " .
              "ORDER BY GFAL.precedence, role_name ASC";

    $aColumns = array("role_name", "name", "precedence", "active", "done", "edit");
    $aColumnHeaders = array("Role", "Player", "Seq", "Active", "Done", "");
    $aColumnTypes = array(1,1,1,1,1,3);
    $aDBColumnArray = array("id","document_id","active","done");
    $aQueryStringVariableNames = array("fFolderCollaborationID", "fDocumentID","fIsActive","fIsDone");
    $aLinkURLs = array(5=>"$default->rootUrl/control.php?action=modifyDocumentRouting");

    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "10%", $aLinkURLs,$aDBColumnArray,$aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading("Document Routing");
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);
    return $oPatternTableSqlQuery->render();

}

function renderNonEditableDocumentRouting($oDocument) {
    global $default;
    $sQuery = "SELECT D.id as document_id, GFAL.id as id, R.name AS role_name, COALESCE(U.Name, 'Not assigned') AS name, GFAL.precedence AS precedence " .
              "FROM documents AS D INNER JOIN $default->owl_groups_folders_approval_table AS GFAL ON D.folder_id = GFAL.folder_id " .
              "INNER JOIN roles AS R ON GFAL.role_id = R.id " .
              "LEFT OUTER JOIN folders_users_roles_link AS FURL ON FURL.group_folder_approval_id = GFAL.id " .
              "LEFT OUTER JOIN users AS U ON FURL.user_id = U.id " .
              "WHERE D.id = " . $oDocument->getID() . " " .
              "ORDER BY GFAL.precedence, role_name ASC";

    $aColumns = array("role_name", "name", "precedence");
    $aColumnHeaders = array("Role", "Player", "Seq");
    $aColumnTypes = array(1,1,1);
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "10%", "$default->rootUrl/control.php?action=modifyDocumentRouting",$aDBColumnArray,$aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading("Document Routing");
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);
    return $oPatternTableSqlQuery->render();

}

function getEditPage($oDocument) {
    global $default;
    $sToRender = renderDocumentPath($oDocument) . "\n<br>\n";
    $sToRender .= "<table border = 0>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>\n";
    //$sToRender .= "\t<table border = 0 width = 280>\n";
    $sToRender .= "\t<table border = 0>\n";
    $sToRender .= "\t<tr width>\n";
    $sToRender .= "\t\t<td>" . wrapInTable(renderDocumentData($oDocument)) . "</td>\n";
    //$sToRender .= "\t\t<td>" . renderGenericDocumentMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<td>" . wrapInTable(renderGenericMetaData($oDocument)) . "</td>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . wrapInTable(renderTypeSpecificMetaData($oDocument)) . "</td>\n";
    //$sToRender .= "\t\t<td>" . renderTypeSpecificMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t</table>\n";
    $sToRender .= "</td>\n";
    $sToRender .= "<td valign=top>\n";
    //$sToRender .= "\t<table border = 0, width = 230>\n";
    $sToRender .= "\t<table border = 0>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . wrapInTable(renderEditableDocumentRouting($oDocument)) . "</td>\n";
    //$sToRender .= "\t\t<td>" . renderDocumentRouting($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td><a href=" . $_SERVER['PHP_SELF'] . "?fDocumentID=" . $oDocument->getID() . "&fBeginCollaboration=1><img src=\"$default->graphicsUrl/widgets/begin.gif\" border=\"0\"/></a></td>\n";
    //$sToRender .= "\t\t<td>" . renderDocumentRouting($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t</table>";
    $sToRender .= "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    //$sToRender .= "<td><input type=\"image\" srcvalue=\"Edit\"></td>\n";
    $sToRender .= "<td><input type=\"image\" src=\"$default->graphicsUrl/widgets/edit.gif\" border=\"0\"></td>\n";
    $sToRender .= "<td><a href=\"" . $_SERVER["PHP_SELF"] . "?fDocumentID=" . $oDocument->getID() . "&fForDownload=1\"><img src=\"$default->graphicsUrl/widgets/download.gif\" border=\"0\" /></a></td>\n";
    $sToRender .= "<td><a href=\"" . generateControllerLink("emailDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/email.gif\" border=\"0\" /></a></td>\n";    
    $sToRender .= "<td><a href=\"" . generateControllerLink("deleteDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/delete.gif\" border=\"0\" /></a></td>\n";
    
    // display the check in button if the document is checked out
    if ($oDocument->getIsCheckedOut()) {
        $sToRender .= "<td><a href=\"" . generateControllerLink("checkInDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/checkin.gif\" border=\"0\" /></a></td>\n";
    // otherwise display the check out button
    } else {
        $sToRender .= "<td><a href=\"" . generateControllerLink("checkOutDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/checkout.gif\" border=\"0\" /></a></td>\n";
    }
    
    // display the unsubscribe button if the user is subscribed to the document
    if (Subscription::exists($_SESSION["userID"], $oDocument->getID(), SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
        $sToRender .= "<td><a href=\"" . generateControllerLink("removeSubscription", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/unsubscribe.gif\" border=\"0\" /></a></td>\n";
    // otherwise display the subscribe button
    } else {
        $sToRender .= "<td><a href=\"" . generateControllerLink("addSubscription", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/subscribe.gif\" border=\"0\" /></a></td>\n";
    }
    
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

function getViewPage($oDocument) {
    global $default;
    $sToRender = renderDocumentPath($oDocument) . "\n<br>\n";
    $sToRender .= "<table border = 0>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>\n";
    $sToRender .= "\t<table border = 0 width = 280>\n";
    $sToRender .= "\t<tr width>\n";
    $sToRender .= "\t\t<td>" . wrapInTable(renderDocumentData($oDocument)) . "</td>\n";
    //$sToRender .= "\t\t<td>" . renderGenericDocumentMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<td>" . wrapInTable(renderGenericMetaData($oDocument)) . "</td>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . wrapInTable(renderTypeSpecificMetaData($oDocument)) . "</td>\n";
    //$sToRender .= "\t\t<td>" . renderTypeSpecificMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t</table>\n";
    $sToRender .= "</td>\n";
    $sToRender .= "<td valign=top>\n";
    $sToRender .= "\t<table border = 0, width = 230>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . wrapInTable(renderNonEditableDocumentRouting($oDocument)) . "</td>\n";
    //$sToRender .= "\t\t<td>" . renderDocumentRouting($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t</table>";
    $sToRender .= "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><a href=\"" . $_SERVER["PHP_SELF"] . "?fDocumentID=" . $oDocument->getID() . "&fForDownload=1\"><img src=\"$default->graphicsUrl/widgets/download.gif\" border=\"0\" /></a></td>\n";
    $sToRender .= "<td><a href=\"" . generateControllerLink("emailDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/email.gif\" border=\"0\" /></a></td>\n";
    
    // display the unsubscribe button if the user is subscribed to the document
    if (Subscription::exists($_SESSION["userID"], $oDocument->getID(), SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
        $sToRender .= "<td><a href=\"" . generateControllerLink("removeSubscription", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/unsubscribe.gif\" border=\"0\" /></a></td>\n";
    // otherwise display the subscribe button
    } else {
        $sToRender .= "<td><a href=\"" . generateControllerLink("addSubscription", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/subscribe.gif\" border=\"0\" /></a></td>\n";
    }
    
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

function wrapInTable($sHtml) {
    return "\n\t\t\t<table border = 1, width = 100%><tr><td>$sHtml</td></tr></table>\n";
}

?>
