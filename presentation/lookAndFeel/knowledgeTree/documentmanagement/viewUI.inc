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

function renderHeading() {
    global $default;
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender  = "<table border=\"0\" width=\"600\">\n";
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">Document Detail</font></th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

function renderDocumentPath($oDocument) {
    global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");	
    $sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($oDocument->getFolderID()), Folder::getFolderPathNamesAsArray($oDocument->getFolderID()), "$default->rootUrl/control.php?action=browse") . " > ";
    $sDocumentPath .= "<a href=\"$default->rootUrl/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php?fDocumentID=" . $oDocument->getID() . "&fForInlineView=1\">" . $oDocument->getName() . "</a>";    
    return "<table border=0 cellpadding=\"5\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sDocumentPath</td></tr></table>\n";
}

function renderDocumentData($oDocument) {
    global $default;
    $sQuery = "SELECT D.name, D.modified, DTT.datetime AS created, U.name AS initiator, CONCAT(CONCAT(D.major_version, '.'), D.minor_version) AS version, WDSL.name AS status, DTL.name AS document_type, D.is_checked_out,  COALESCE(U2.name, '') AS c_user " .
              "FROM $default->owl_documents_table AS D INNER JOIN $default->owl_web_documents_table AS WD ON WD.document_id = D.ID " .
              "INNER JOIN $default->owl_web_documents_status_table AS WDSL ON WD.status_id = WDSL.id " .
              "INNER JOIN $default->owl_users_table AS U ON U.id = D.creator_id " .
              "INNER JOIN $default->owl_document_transactions_table AS DTT ON DTT.document_id = D.id " .
              "INNER JOIN $default->owl_transaction_types_table AS TT ON DTT.transaction_id = TT.id " .
			  "INNER JOIN $default->owl_document_types_table AS DTL ON DTL.id = D.document_type_id " .
              "LEFT OUTER JOIN $default->owl_users_table AS U2 ON U2.id = D.checked_out_user_id " .
              "WHERE D.id = " . $oDocument->getID() . " " .
              "AND TT.name LIKE 'Create'";

    $aColumns = array("name", "modified", "created", "initiator", "document_type", "version", "status", "c_user");
    $aColumnNames = array("Document title", "Last updated", "Created", "Document initiator", "Document Type", "Version", "Status", "Checked out by");
    $aColumnTypes = array(1,1,1,1,1,1,1,1);
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
              "LEFT OUTER JOIN folders_users_roles_link AS FURL ON FURL.group_folder_approval_id = GFAL.id AND FURL.document_id = D.id " .
              "LEFT OUTER JOIN users AS U ON FURL.user_id = U.id " .
              "WHERE D.id = " . $oDocument->getID() . " " .
              "ORDER BY GFAL.precedence, role_name ASC";
    $aColumns = array("role_name", "name", "precedence", "active", "done", "edit");
    $aColumnHeaders = array("Role", "User", "Seq", "Active", "Done", "");
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
    $sQuery = "SELECT D.id as document_id, GFAL.id as id, R.name AS role_name, COALESCE(U.Name, 'Not assigned') AS name, GFAL.precedence AS precedence, COALESCE(FURL.active,0) AS active, COALESCE(FURL.done, 0) AS done " .
              "FROM documents AS D INNER JOIN $default->owl_groups_folders_approval_table AS GFAL ON D.folder_id = GFAL.folder_id " .
              "INNER JOIN roles AS R ON GFAL.role_id = R.id " .
              "LEFT OUTER JOIN folders_users_roles_link AS FURL ON FURL.group_folder_approval_id = GFAL.id AND FURL.document_id = D.id " .
              "LEFT OUTER JOIN users AS U ON FURL.user_id = U.id " .
              "WHERE D.id = " . $oDocument->getID() . " " .
              "ORDER BY GFAL.precedence, role_name ASC";

    $aColumns = array("role_name", "name", "precedence", "active", "done");
    $aColumnHeaders = array("Role", "User", "Seq", "Active", "Done");
    $aColumnTypes = array(1,1,1,1,1);
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "10%", "$default->rootUrl/control.php?action=modifyDocumentRouting",$aDBColumnArray,$aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading("Document Routing");
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);
    return $oPatternTableSqlQuery->render();

}

function displayActionButtons($oDocument, $bEdit) {
    global $default;
    if ($bEdit) {
         // display the check in button if the document is checked out and you checked the document out
        if ($oDocument->getIsCheckedOut() && ($oDocument->getCheckedOutUserID() == $_SESSION["userID"])) {
            $sToRender = "<td><a href=\"" . generateControllerLink("checkInDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/checkin.gif\" border=\"0\" /></a></td>\n";
        // otherwise display the check out button
        } else {
            $sToRender = "<td><a href=\"" . generateControllerLink("checkOutDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/checkout.gif\" border=\"0\" /></a></td>\n";
        }
    }
    
    $sToRender .= "<td><a href=\"" . generateControllerLink("emailDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/email.gif\" border=\"0\" /></a></td>\n";    
    if ($bEdit) {
        $sToRender .= "<td><a href=\"" . generateControllerLink("deleteDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/delete.gif\" border=\"0\" /></a></td>\n";
    }
    $sToRender .= "<td><a href=\"" . generateControllerLink("viewHistory", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/history.gif\" border=\"0\" /></a></td>\n";
    if ($bEdit) {
        $sToRender .= "<td><a href=\"" . generateControllerLink("moveDocument", "fFolderID=" . $oDocument->getFolderID() . "&fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/move.gif\" border=\"0\" /></a></td>\n";
    }
    
    // display the unsubscribe button if the user is subscribed to the document
    if (Subscription::exists($_SESSION["userID"], $oDocument->getID(), SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
        $sToRender .= "<td><a href=\"" . generateControllerLink("removeSubscription", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/unsubscribe.gif\" border=\"0\" /></a></td>\n";
    // otherwise display the subscribe button
    } else {
        $sToRender .= "<td><a href=\"" . generateControllerLink("addSubscription", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/subscribe.gif\" border=\"0\" /></a></td>\n";
    }
    $sToRender .= "<td><a onClick=\"alert('This will download a copy of the document and is not the same as Checking Out a document- changes to this downloaded file will not be managed in the DMS.'); return true;\" href=\"" . generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID() . "&fForDownload=1") . "\"><img src=\"$default->graphicsUrl/widgets/download.gif\" border=\"0\" /></a></td>\n";    
    $sToRender .= "<td><a href=\"" . generateControllerLink("viewDiscussion", "fDocumentID=" . $oDocument->getID() . "&fForDiscussion=1") . "\"><img src=\"$default->graphicsUrl/widgets/discussion.gif\" border=\"0\" /></a></td>\n";
    			
    return $sToRender;
}

function getEditPage($oDocument) {
    global $default;
	$iDocumentID = $oDocument->getID();
    $sToRender = renderHeading() . renderDocumentPath($oDocument) . "\n<br>\n";
    
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= displayActionButtons($oDocument, true);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    
    $sToRender .= "<table border = 0>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>\n";
    
    $sToRender .= "\t<table border = 0>\n";
    $sToRender .= "\t<tr width>\n";
    $sToRender .= "\t\t<td>" . renderDocumentData($oDocument) . "</td>\n";    
    $sToRender .= "\t</tr>\n";
	$sToRender .= "\t<tr>\n";
    $sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=modifyDocument&fDocumentID=$iDocumentID\"><image src=\"$default->graphicsUrl/widgets/edit.gif\" border=\"0\"></a></td>\n";
	$sToRender .= "\t</tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<td>" . renderGenericMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
	$sToRender .= "\t<tr>\n";
    $sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=modifyDocumentGenericMetaData&fDocumentID=$iDocumentID\"><image src=\"$default->graphicsUrl/widgets/edit.gif\" border=\"0\"></a></td>\n";    
	$sToRender .= "\t</tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . renderTypeSpecificMetaData($oDocument) . "</td>\n";
    
    $sToRender .= "\t</tr>\n";
	$sToRender .= "\t<tr>\n";
    $sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=modifyDocumentTypeMetaData&fDocumentID=$iDocumentID\"><image src=\"$default->graphicsUrl/widgets/edit.gif\" border=\"0\"></a></td>\n";
	$sToRender .= "\t</tr>\n";
    $sToRender .= "\t</table>\n";
    $sToRender .= "</td>\n";
    $sToRender .= "<td valign=top>\n";
    
    $sToRender .= "\t<table border = 0>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . renderEditableDocumentRouting($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t<tr>\n";
	if ($oDocument->hasCollaboration() && (!DocumentCollaboration::documentCollaborationStarted($oDocument->getID())) && ($_SESSION["userID"] == $oDocument->getCreatorID())) {
		//if not all collaboration steps have been set, then you cannot start the collaboration process
		//only the creator of the document can start the collaboration process		
		$sToRender .= "\t\t<td><a href=" . $_SERVER['PHP_SELF'] . "?fDocumentID=" . $oDocument->getID() . "&fBeginCollaboration=1><img src=\"$default->graphicsUrl/widgets/begin.gif\" border=\"0\"/></a>";
		$sToRender .= "</td>\n";
	} else if (DocumentCollaboration::userIsPerformingCurrentCollaborationStep($oDocument->getID())) {
		//if the current user is responsible for an active step in the collaboration process		
		$sToRender .= "\t\t<td><table border=\"0\"><tr>\n";
		$sToRender .= "\t\t<td><b>You currently have an active role<br> in the collaboration process</b></td>\n";				
		$sToRender .= "\t\t<td align=\"center\"><a href=" . $_SERVER['PHP_SELF'] . "?fDocumentID=" . $oDocument->getID() . "&fCollaborationStepComplete=1><img src=\"$default->graphicsUrl/widgets/approve.gif\" border=\"0\"/></a></td>\n";
		$sToRender .= "\t\t<td align=\"center\"><a href=\"$default->rootUrl/control.php?action=collaborationStepReject&fDocumentID=$iDocumentID\"><img src=\"$default->graphicsUrl/widgets/reject.gif\" border=\"0\"/></a></td>\n";
		$sToRender .= "\t\t</tr></table></td>\n";
	}
    
    if (!DocumentCollaboration::documentIsPendingWebPublishing($iDocumentID)) {
        // only display for unpublished documents        
        // if there is collaboration
        if ($oDocument->hasCollaboration()) {
            // only display publish button if collaboration is complete and you're the last user in the collaboration process
            if (DocumentCollaboration::documentCollaborationDone($oDocument->getID()) && ($_SESSION["userID"] == DocumentCollaboration::getLastCollaboratorID($iDocumentID))) {
                $sToRender .= "<td><a href=" . $_SERVER['PHP_SELF'] . "?fDocumentID=" . $oDocument->getID() . "&fForPublish=1><img src=\"$default->graphicsUrl/widgets/publish.gif\" border=\"0\"/></a></td>\n";
            }
        } else if ($_SESSION["userID"] == $oDocument->getCreatorID()) {
            // no collaboration for this folder, so only the originator can request publication
            // ??: or an administrator?
            $sToRender .= "<td><a href=" . $_SERVER['PHP_SELF'] . "?fDocumentID=" . $oDocument->getID() . "&fForPublish=1><img src=\"$default->graphicsUrl/widgets/publish.gif\" border=\"0\"/></a></td>\n";
        }
    }
    $sToRender .= "\t</tr>";
    $sToRender .= "\t</table>";
    $sToRender .= "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= displayActionButtons($oDocument, true);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

function getViewPage($oDocument) {
    global $default;
	$iDocumentID = $oDocument->getID();
    $sToRender = renderHeading() . renderDocumentPath($oDocument) . "\n<br>\n";
    
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= displayActionButtons($oDocument, false);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    
    $sToRender .= "<table border = 0>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>\n";
    $sToRender .= "\t<table border = 0 width = 280>\n";
    $sToRender .= "\t<tr width>\n";
    $sToRender .= "\t\t<td>" . renderDocumentData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<td>" . renderGenericMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . renderTypeSpecificMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t</table>\n";
    $sToRender .= "</td>\n";
    $sToRender .= "<td valign=top>\n";
    $sToRender .= "\t<table border = 0, width = 230>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . renderNonEditableDocumentRouting($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>";
	if (DocumentCollaboration::userIsPerformingCurrentCollaborationStep($oDocument->getID())) {
		//if the current user is responsible for an active step in the collaboration process		
		$sToRender .= "\t\t<tr><td><table border=\"0\"><tr>\n";
		$sToRender .= "\t\t<td><b>You currently have an active role<br> in the collaboration process</b></td>\n";				
		$sToRender .= "\t\t<td align=\"center\"><a href=" . $_SERVER['PHP_SELF'] . "?fDocumentID=" . $oDocument->getID() . "&fCollaborationStepComplete=1><img src=\"$default->graphicsUrl/widgets/approve.gif\" border=\"0\"/></a></td>\n";
		$sToRender .= "\t\t<td align=\"center\"><a href=\"$default->rootUrl/control.php?action=collaborationStepReject&fDocumentID=$iDocumentID\"><img src=\"$default->graphicsUrl/widgets/reject.gif\" border=\"0\"/></a></td>\n";
		$sToRender .= "\t\t</tr></table></td>\n";
	}
    $sToRender .= "\t</table>";
    $sToRender .= "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>";
    
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= displayActionButtons($oDocument, false);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

/**
 * Displays the document view page with additional input controls
 * for checking out the document
 *
 * @param object the document to view and check out
 */
function getCheckOutEditPage($oDocument, $bCheckedOut = false) {
    global $default;
    $sToRender = renderHeading() . renderDocumentPath($oDocument) . "\n<br>\n";
    $sToRender .= "<table border = 0>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>\n";
    $sToRender .= "\t<table border = 0 width = 280>\n";
    $sToRender .= "\t<tr width>\n";
    $sToRender .= "\t\t<td>" . renderDocumentData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<td>" . renderGenericMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . renderTypeSpecificMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t</table>\n";
    $sToRender .= "</td>\n";
    $sToRender .= "<td valign=top>\n";
    $sToRender .= "\t<table border = 0, width = 230>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . renderNonEditableDocumentRouting($oDocument) . "</td>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<tr>\n";
    if ($bCheckedOut) {
        $sToRender .= "\t\t<td>You have now checked out this document. No one else can make updates to the document while you have it checked out. Click the Download button to get the latest version of the file. Please check it back in, as soon as you finish working on it.</td>\n";
        $sToRender .= "\t</tr>";
        $sToRender .= "\t<tr>\n";
        $sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "&fForDownload=1\"><img src=\"$default->graphicsUrl/widgets/download.gif\" border=\"0\" /></a>\n";    
        $sToRender .= "<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a></td>\n";        
    } else {
        $sToRender .= "\t\t<td>" . renderCheckOutPage($oDocument) . "</td>\n";
    }
    $sToRender .= "\t</tr>";
    $sToRender .= "\t</table>";
    $sToRender .= "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>";

    return $sToRender;
}

/**
 * Displays the document view page with additional input controls
 * for checking in the document
 *
 * @param object the document to view and check out
 */
function getCheckInEditPage($oDocument) {
    global $default;
    $sToRender = renderHeading() . renderDocumentPath($oDocument) . "\n<br>\n";
    $sToRender .= "<table border = 0>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>\n";
    $sToRender .= "\t<table border = 0 width = 280>\n";
    $sToRender .= "\t<tr width>\n";
    $sToRender .= "\t\t<td>" . renderDocumentData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<td>" . renderGenericMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . renderTypeSpecificMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t</table>\n";
    $sToRender .= "</td>\n";
    $sToRender .= "<td valign=top>\n";
    $sToRender .= "\t<table border = 0, width = 230>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . renderNonEditableDocumentRouting($oDocument) . "</td>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . renderCheckInPage($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t</table>";
    $sToRender .= "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>";

    return $sToRender;
}

function getWebPublishPage($oDocument) {
    global $default;
    
    $oPatternListBox = & new PatternListBox($default->owl_web_sites_table, "web_site_name", "id", "fWebSiteID");
    
    $sToRender = renderHeading() . renderDocumentPath($oDocument) . "\n<br>\n";
    $sToRender .= "<table border = 0>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>\n";
    $sToRender .= "\t<table border = 0 width = 280>\n";
    $sToRender .= "\t<tr width>\n";
    $sToRender .= "\t\t<td>" . renderDocumentData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<td>" . renderGenericMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . renderTypeSpecificMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t</table>\n";
    $sToRender .= "</td>\n";
    $sToRender .= "<td valign=top>\n";
    $sToRender .= "\t<table border=\"0\" width=\"230\">\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . renderNonEditableDocumentRouting($oDocument) . "</td>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<th align=\"left\">Choose the website to publish to:</th>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternListBox->render() . "</td>\n";
    $sToRender .= "\t\t<input type=\"hidden\" name=\"fForPublish\" value=\"1\" />\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<th nowrap align=\"left\">Enter a comment for the web master:</th>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td><input type=\"text\" name=\"fComment\" size=\"30\" /></td>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td><input type=\"image\" src=\"$default->graphicsUrl/widgets/publish.gif\" border=\"0\"/></a>";
    $sToRender .= "\t\t<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a></td>\n";
    $sToRender .= "\t</tr>";    
    $sToRender .= "\t</table>";
    $sToRender .= "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>";

	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(document.MainForm.fWebSiteID,'Website'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
    $sToRender .= "\tif (!(validRequired(document.MainForm.fComment,'Publish comment'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";    
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";

    return $sToRender;
}

function wrapInTable($sHtml) {
    return "\n\t\t\t<table border = 1, width = 100%><tr><td>$sHtml</td></tr></table>\n";
}

?>
