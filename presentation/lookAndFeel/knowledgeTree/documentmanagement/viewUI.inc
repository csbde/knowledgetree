<?php
/**
 * $Id$
 *
 * Contains HTML information required to build the document view page.
 * Will be used by documentViewBL.php
 *
 * Variables expected:
 *   o $fDocumentID  Primary key of document to view
 *
 *
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @date 21 January 2003
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */

function renderDocumentPath($oDocument, $bDisplayActions = false) {
    global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	
	$sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($oDocument->getFolderID()), Folder::getFolderPathNamesAsArray($oDocument->getFolderID()), "$default->rootUrl/control.php?action=browse") . " > ";	
	if ($bDisplayActions) {	
	    $sDocumentPath .= "<a onClick=\"alert('This will view a copy of the current version of this document in the DMS.  Any changes to this file will not be managed in the DMS.'); return true;\" href=\"" . generateControllerUrl("downloadDocument", "fDocumentID=" . $oDocument->getID() . "&fForInlineView=1") . "\">" . $oDocument->getName() . "</a>";
	} else {
		$sDocumentPath .= $oDocument->getName();
	}    
	return "<table border=\"0\" cellpadding=\"5\" width=\"610\"><tr bgcolor=\"$sTDBGColour\"><td width=\"87%\">$sDocumentPath</td>" . ($bDisplayActions ? "<td width=\"15%\" align=\"center\" valign=\"middle\"><strong>Actions</strong></td>" : "") . "</tr></table>\n";
}

function renderDocumentData($oDocument, $bEditable, $sStatusMessage = "") {
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
    $oPatternListFromQuery->setTableWidth("400");   
      
    $sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";
	if ($sStatusMessage) {
		$sToRender .= "<tr><td>&nbsp;</td></tr><tr><td><font color=\"red\">$sStatusMessage</font></td></tr>";
	}    
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternListFromQuery->render() . "</td>\n";    
    $sToRender .= "\t</tr>\n";
    if ($bEditable) {    
		$sToRender .= "\t<tr>\n";
	    $sToRender .= "<td colspan=\"2\"><a href=\"$default->rootUrl/control.php?action=modifyDocument&fDocumentID=" . $oDocument->getID() . "\"><image src=\"$default->graphicsUrl/widgets/edit.gif\" border=\"0\"></a></td>\n";
		$sToRender .= "\t</tr>\n";
    }
	$sToRender .= "\t</table>\n";

    return $sToRender;
}


function renderGenericMetaData($oDocument, $bEditable) {
    global $default;
    $sQuery = "SELECT DF.name AS name, DFL.value as value " .
              "FROM $default->owl_documents_table AS D INNER JOIN $default->owl_document_fields_table AS DFL ON D.id = DFL.document_id " .
              "INNER JOIN $default->owl_fields_table AS DF ON DF.id = DFL.document_field_id " .
              "WHERE document_id = " . $oDocument->getID() . " " .
              "AND DF.is_generic = 1";
    $aColumns = array("name", "value");
    $aColumnHeaders = array("Tag", "Value");
    $aColumnTypes = array(1,1);
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500");
    $oPatternTableSqlQuery->setTableHeading("Generic Meta Data");
    $oPatternTableSqlQuery->setEmptyTableMessage("No Generic Meta Data");
    
    $sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";    	
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<td>" . $oPatternTableSqlQuery->render() . "</td>\n";
    $sToRender .= "\t</tr>\n";
    if ($bEditable) {
		$sToRender .= "\t<tr>\n";
	    $sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=modifyDocumentGenericMetaData&fDocumentID=" . $oDocument->getID() . "\"><image src=\"$default->graphicsUrl/widgets/edit.gif\" border=\"0\"></a></td>\n";    
		$sToRender .= "\t</tr>\n";
    }
	$sToRender .= "\t</table>\n";
    
    return $sToRender;
}

function renderTypeSpecificMetaData($oDocument, $bEditable) {
	global $default;
	
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
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500");
    $oPatternTableSqlQuery->setTableHeading("Type Specific Meta Data");
	$oPatternTableSqlQuery->setEmptyTableMessage("No Type Specific Meta Data");
    
    $sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";	
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternTableSqlQuery->render() . "</td>\n";    
    $sToRender .= "\t</tr>\n";
    if ($bEditable) {
		$sToRender .= "\t<tr>\n";
	    $sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=modifyDocumentTypeMetaData&fDocumentID=" . $oDocument->getID() . "\"><image src=\"$default->graphicsUrl/widgets/edit.gif\" border=\"0\"></a></td>\n";
		$sToRender .= "\t</tr>\n";
    }
    $sToRender .= "\t</table>\n";
    return $sToRender;    
}

function renderEditableDocumentArchiveSettings($oDocument) {
	global $default;
    $sQuery = "SELECT d.id, atl.name, 'Edit' AS edit FROM $default->owl_document_archiving_table AS da " .
    		  "INNER JOIN $default->owl_archiving_settings_table AS ast ON da.archiving_settings_id=ast.id " .
    		  "INNER JOIN $default->owl_archiving_type_lookup_table AS atl ON ast.archiving_type_id=atl.id " .
			  "INNER JOIN $default->owl_documents_table AS d ON da.document_id=d.id " .
			  "WHERE d.id = " . $oDocument->getID();
    
    $aColumns = array("name", "edit");
    $aColumnHeaders = array("Archiving Type");
    $aColumnTypes = array(1,3);
    $aDBColumnArray = array("id");
    $aQueryStringVariableNames = array("fDocumentID");
    $aLinkURLs = array(1=>"$default->rootUrl/control.php?action=modifyDocumentArchiveSettings");
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500", $aLinkURLs ,$aDBColumnArray,$aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading("Archiving Settings");
    $oPatternTableSqlQuery->setEmptyTableMessage("No archiving settings");
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);
    return $oPatternTableSqlQuery->render();	
}

function renderNonEditableDocumentArchiveSettings($oDocument) {
	global $default;
    $sQuery = "SELECT d.id, atl.name FROM $default->owl_document_archiving_table AS da " .
    		  "INNER JOIN $default->owl_archiving_settings_table AS ast ON da.archiving_settings_id=ast.id " .
    		  "INNER JOIN $default->owl_archiving_type_lookup_table AS atl ON ast.archiving_type_id=atl.id " .
			  "INNER JOIN $default->owl_documents_table AS d ON da.document_id=d.id " .
			  "WHERE d.id = " . $oDocument->getID();
    
    $aColumns = array("name");
    $aColumnHeaders = array("Archiving Type");
    $aColumnTypes = array(1);
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500", $aLinkURLs ,$aDBColumnArray,$aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading("Archiving Settings");
    $oPatternTableSqlQuery->setEmptyTableMessage("No archiving settings");
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);
    return $oPatternTableSqlQuery->render();
}

function renderDocumentArchiveSettings($oDocument, $bEditable) {
    global $default;   

    $sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . ($bEditable ? renderEditableDocumentArchiveSettings($oDocument) : renderNonEditableDocumentArchiveSettings($oDocument)) . "</td>\n";
    $sToRender .= "\t</tr>";
    if ($bEditable) {
	   	$sToRender .= "\t<tr>\n";
	    // if there are no archiving settings then allow their addition
	    $oDocumentArchiving = DocumentArchiving::getFromDocumentID($oDocument->getID());
	    $sToRender .= "\t\t<td>" . ($oDocumentArchiving ? "" : generateControllerLink("addDocumentArchiveSettings", "fDocumentID=" . $oDocument->getID(), "<img src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\"/>")) . "</td>\n";
		$sToRender .= "\t</tr>";
    }        	    
	$sToRender .= "\t</table>\n";

    return $sToRender;
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

    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500", $aLinkURLs,$aDBColumnArray,$aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading("Document Routing");
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);
    
    $sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternTableSqlQuery->render() . "</td>\n";
    $sToRender .= "\t</tr>";            
    $sToRender .= "\t<tr>\n";    
    // FIXME display messages instead of just not displaying button
    // collaboration cannot be started or approved/rejected unless the document is not checked out    
	if (!$oDocument->getIsCheckedOut() && $oDocument->hasCollaboration() && (!DocumentCollaboration::documentCollaborationStarted($oDocument->getID())) && ($_SESSION["userID"] == $oDocument->getCreatorID())) {
		//if not all collaboration steps have been set, then you cannot start the collaboration process
		//only the creator of the document can start the collaboration process		
		$sToRender .= "\t\t<td><a href=" . $_SERVER['PHP_SELF'] . "?fDocumentID=" . $oDocument->getID() . "&fBeginCollaboration=1><img src=\"$default->graphicsUrl/widgets/begin.gif\" border=\"0\"/></a>";
		$sToRender .= "</td>\n";
	} else if (DocumentCollaboration::userIsPerformingCurrentCollaborationStep($oDocument->getID())) {
		//if the current user is responsible for an active step in the collaboration process		
		$sToRender .= "\t\t<td><table border=\"0\"><tr>\n";
		$sToRender .= "\t\t<td><b>You currently have an active role<br> in the collaboration process</b></td>\n";
		// collaboration cannot be started or approved/rejected if the document is checked out
		if (!$oDocument->getIsCheckedOut()) {				
			$sToRender .= "\t\t<td align=\"center\"><a onClick=\"return confirm('Are you sure you want to approve?  You will not have access to this document until the collaboration process is complete (unless the document is rejected in a subsequent step)');\" href=" . $_SERVER['PHP_SELF'] . "?fDocumentID=" . $oDocument->getID() . "&fCollaborationStepComplete=1><img src=\"$default->graphicsUrl/widgets/approve.gif\" border=\"0\"/></a></td>\n";
			$sToRender .= "\t\t<td align=\"center\"><a href=\"$default->rootUrl/control.php?action=collaborationStepReject&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/reject.gif\" border=\"0\"/></a></td>\n";
		}	
		$sToRender .= "\t\t</tr></table></td>\n";
	}    
    if (!DocumentCollaboration::documentIsPendingWebPublishing($oDocument->getID())) {
        // only display for unpublished documents        
        // if there is collaboration
        if ($oDocument->hasCollaboration()) {
            // only display publish button if collaboration is complete and you're the last user in the collaboration process
            if (DocumentCollaboration::documentCollaborationDone($oDocument->getID()) && ($_SESSION["userID"] == DocumentCollaboration::getLastCollaboratorID($oDocument->getID()))) {
                $sToRender .= "<td><a href=" . $_SERVER['PHP_SELF'] . "?fDocumentID=" . $oDocument->getID() . "&fForPublish=1><img src=\"$default->graphicsUrl/widgets/publish.gif\" border=\"0\"/></a></td>\n";
            }
        } else if ($_SESSION["userID"] == $oDocument->getCreatorID()) {
            // no collaboration for this folder, so only the creator can request publication
            // ??: or an administrator?
            $sToRender .= "<td><a href=" . $_SERVER['PHP_SELF'] . "?fDocumentID=" . $oDocument->getID() . "&fForPublish=1><img src=\"$default->graphicsUrl/widgets/publish.gif\" border=\"0\"/></a></td>\n";
        }
    }
    $sToRender .= "\t</tr>";
	$sToRender .= "\t</table>\n";
	
    return $sToRender;    
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
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500", "$default->rootUrl/control.php?action=modifyDocumentRouting",$aDBColumnArray,$aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading("Document Routing");
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);

   	$sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternTableSqlQuery->render() . "</td>\n";
    $sToRender .= "\t</tr>";
    
	if (DocumentCollaboration::userIsPerformingCurrentCollaborationStep($oDocument->getID())) {
		//if the current user is responsible for an active step in the collaboration process		
		$sToRender .= "\t\t<tr><td><table border=\"0\"><tr>\n";
		$sToRender .= "\t\t<td><b>You currently have an active role<br> in the collaboration process</b></td>\n";				
		$sToRender .= "\t\t<td align=\"center\"><a href=" . $_SERVER['PHP_SELF'] . "?fDocumentID=" . $oDocument->getID() . "&fCollaborationStepComplete=1><img src=\"$default->graphicsUrl/widgets/approve.gif\" border=\"0\"/></a></td>\n";
		$sToRender .= "\t\t<td align=\"center\"><a href=\"$default->rootUrl/control.php?action=collaborationStepReject&fDocumentID=$iDocumentID\"><img src=\"$default->graphicsUrl/widgets/reject.gif\" border=\"0\"/></a></td>\n";
		$sToRender .= "\t\t</tr></table></td></tr>\n";
	}
	    
	$sToRender .= "\t</table>\n";
	
    return $sToRender;
}

function renderEditableLinkedDocuments($oDocument) {
	global $default;
	
	$sQuery = "SELECT D.id AS child_document_id, D.name, DL.id as document_link_id, DL.parent_document_id AS parent_document_id, 'Unlink' AS unlink " .
			"FROM documents AS D INNER JOIN document_link AS DL ON D.id = DL.child_document_id " .
			"WHERE DL.parent_document_id = " . $oDocument->getID();
	
	$aColumns = array("name", "unlink");
    $aColumnHeaders = array("Document");
    $aColumnTypes = array(3,3);
    $aDBColumnArray = array("parent_document_id", "child_document_id","document_link_id", "child_document_id");
    $aQueryStringVariableNames = array("fParentDocumentID","fChildDocumentID", "fDocumentLinkID", "fDocumentID");
    $aLinkURLs = array(0=>"$default->rootUrl/control.php?action=viewDocument", 1=>"$default->rootUrl/control.php?action=removeDocumentLink");

    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500", $aLinkURLs, $aDBColumnArray, $aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading("Linked documents");
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);    
    
    $sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";    
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternTableSqlQuery->render() . "</td>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=addDocumentLink&fDocumentID=" . $oDocument->getID() ."\"><img src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\"/></a></td>\n";
    $sToRender .= "\t</tr>";     
	$sToRender .= "\t</table>\n";
    return $sToRender;
}

function renderNonEditableLinkedDocuments($oDocument) {
	global $default;
	
	$sQuery = "SELECT D.id, D.name " .
			"FROM documents AS D INNER JOIN document_link AS DL ON D.id = DL.child_document_id " .
			"WHERE DL.parent_document_id = " . $oDocument->getID();
	
	$aColumns = array("name");
    $aColumnHeaders = array("Document");
    $aColumnTypes = array(3);
    $aDBColumnArray = array("id");
    $aQueryStringVariableNames = array("fDocumentID");
    $aLinkURLs = array(0=>"$default->rootUrl/control.php?action=viewDocument");
    
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500", $aLinkURLs, $aDBColumnArray, $aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading("Linked documents");
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);
    
    $sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";    
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternTableSqlQuery->render() . "</td>\n";
    $sToRender .= "\t</tr>";
	$sToRender .= "\t</table>\n";
    return $sToRender;    
}


function displayActionButtons($oDocument, $bEdit) {
    global $default;
    
    // TODO: change to view
    $sToRender .= "<tr><td align=\"left\"><a onClick=\"alert('This will download a copy of the document and is not the same as Checking Out a document.  Changes to this downloaded file will not be managed in the DMS.'); return true;\" href=\"" . generateControllerLink("downloadDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/docactions/view.gif\" border=\"0\" /></a></td></tr>\n";    
    if ($bEdit) {
         // display the check in button if the document is checked out and you checked the document out
        if ($oDocument->getIsCheckedOut() && ($oDocument->getCheckedOutUserID() == $_SESSION["userID"])) {
            $sToRender .= "<tr><td align=\"left\"><a href=\"" . generateControllerLink("checkInDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/docactions/checkin.gif\" border=\"0\" /></a></td></tr>\n";
        // otherwise display the check out button
        } else {
            $sToRender .= "<tr><td align=\"left\"><a href=\"" . generateControllerLink("checkOutDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/docactions/checkout.gif\" border=\"0\" /></a></td></tr>\n";
        }
    }
    
    $sToRender .= "<tr><td align=\"left\"><a href=\"" . generateControllerLink("emailDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/docactions/email.gif\" border=\"0\" /></a></td></tr>\n";    
    if ($bEdit) {
        $sToRender .= "<tr><td align=\"left\"><a href=\"" . generateControllerLink("deleteDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/docactions/delete.gif\" border=\"0\" /></a></td></tr>\n";
    }
    $sToRender .= "<tr><td align=\"left\"><a href=\"" . generateControllerLink("viewHistory", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/docactions/history.gif\" border=\"0\" /></a></td></tr>\n";
    if ($bEdit) {
    	// documents in collaboration and checked out documents can't be moved
		if (!$oDocument->getIsCheckedOut() && (!DocumentCollaboration::documentCollaborationStarted($oDocument->getID()))) {
        	$sToRender .= "<tr><td align=\"left\"><a href=\"" . generateControllerLink("moveDocument", "fFolderID=" . $oDocument->getFolderID() . "&fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/docactions/move.gif\" border=\"0\" /></a></td></tr>\n";
		}
    }
    
    // display the unsubscribe button if the user is subscribed to the document
    if (Subscription::exists($_SESSION["userID"], $oDocument->getID(), SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
        $sToRender .= "<tr><td align=\"left\"><a href=\"" . generateControllerLink("removeSubscription", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/docactions/unsubscribe.gif\" border=\"0\" /></a></td></tr>\n";
    // otherwise display the subscribe button
    } else {
        $sToRender .= "<tr><td align=\"left\"><a href=\"" . generateControllerLink("addSubscription", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/docactions/subscribe.gif\" border=\"0\" /></a></td></tr>\n";
    }   
    $sToRender .= "<tr><td align=\"left\"><a href=\"" . generateControllerLink("viewDiscussion", "fDocumentID=" . $oDocument->getID() . "&fForDiscussion=1") . "\"><img src=\"$default->graphicsUrl/widgets/docactions/discussion.gif\" border=\"0\" /></a></td></tr>\n";
    // only display the archive button for available documents
    if (!$oDocument->getIsCheckedOut() && !$oDocument->hasCollaboration() && (!DocumentCollaboration::documentCollaborationStarted($oDocument->getID()))) {
    	$sToRender .= "<tr><td align=\"left\"><a href=\"" . generateControllerLink("archiveDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/docactions/archive.gif\" border=\"0\" /></a></td></tr>\n";
    }
    $sToRender .= "<td align=\"left\"><a href=\"" . generateControllerLink("createDependantDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/docactions/dependentdoc.gif\" border=\"0\" /></a></td>\n";

	// spacing hack
	if (!$bEdit) {
		$sToRender .= "<tr><td align=\"left\">&nbsp;</td></tr>\n";
		$sToRender .= "<tr><td align=\"left\">&nbsp;</td></tr>\n";
		$sToRender .= "<tr><td align=\"left\">&nbsp;</td></tr>\n";
		$sToRender .= "<tr><td align=\"left\">&nbsp;</td></tr>\n";
	}
	    			
    return $sToRender;
}

function renderSectionDiv($sDivName, $sHtml) {
	global $default;
	$bNN4 = (($default->browser == "moz") && ($default->version <= "4.79"));
	if ($bNN4) {	
		return "<div id=\"$sDivName\" style=\"position:absolute;visibility:hidden;left:135px;\">$sHtml</div>";
	} else {
		return "<div id=\"$sDivName\" style=\"position:absolute;visibility:hidden;left:5px;\">$sHtml</div>";
	}		
}

function renderDocumentSection($sSectionName, $sHeadingText, $bDisplayLink, $iDocumentID) {	
	if ($bDisplayLink) {
		$sLink = generateControllerLink("viewDocument", "fDocumentID=$iDocumentID&fShowSection=$sSectionName", $sHeadingText);
	} else {
		$sLink = "<a href=\"#\" onClick=\"switchDiv('$sSectionName', 'document');\">$sHeadingText</a>";
	}
	return "<tr bgcolor=\"" . getColour($iColour) . "\"><td width=\"100%\">$sLink</td></tr>\n";
}
	
function getPage($oDocument, $bEdit, $sStatusMessage = "") {
    global $default;
   	  		
	$sToRender = "<div id=\"headings\" style=\"position:relative;visibility:visible;top:2px;left:2px;\">";
    $sToRender .= renderHeading("Document Detail");
    $sToRender .= renderDocumentPath($oDocument, true) . "\n\n";
	$sToRender .= "<table cellspacing=\"0\" cellpadding=\"0\" id=\"headingTable\">";
	$sToRender .= "<tr><td valign=\"top\">";	
	$sToRender .= "<table border=\"0\" width=\"530\">";

	// if we have a status message, then make the section links refresh to viewDocument with the fShowSection variable
	// ie. effectively removes statusMessage on next click
	$bDisplayLink = ($sStatusMessage) ? true : false;

	$sToRender .= renderDocumentSection("documentData", "Document Data", $bDisplayLink, $oDocument->getID());	
	$sToRender .= renderDocumentSection("genericMetaData", "Generic Meta Data", $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("typeSpecificMetaData", "Type Specific Meta Data", $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("archiveSettings", "Archive Settings", $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("documentRouting", "Document Routing", $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("linkedDocuments", "Linked Documents", $bDisplayLink, $oDocument->getID());
	$sToRender .= "</table>";		
	$sToRender .= "</td><td>";
    $sToRender .= "<table cellspacing=\"0\" cellpadding=\"0\">\n";
	$sToRender .= displayActionButtons($oDocument, $bEdit);	    
    $sToRender .= "</table>\n";
	$sToRender .= "</td></tr>";
	$sToRender .= "</table>";
    $sToRender .= "</div>";

	// ugly netscape hacks
	$bNN4 = (($default->browser == "moz") && ($default->version <= "4.79"));
	if (!$bNN4) {
		$sToRender .= "<div id=\"contentDiv\" style=\"position:relative;visibility:hidden;top:-80px;width:500px;\">";
	}
	$sToRender .= renderSectionDiv("documentData", renderDocumentData($oDocument, $bEdit, $sStatusMessage));
    $sToRender .= renderSectionDiv("genericMetaData", renderGenericMetaData($oDocument, $bEdit));
   	$sToRender .= renderSectionDiv("typeSpecificMetaData", renderTypeSpecificMetaData($oDocument, $bEdit));
    $sToRender .= renderSectionDiv("archiveSettings", renderDocumentArchiveSettings($oDocument, $bEdit));
    if ($bEdit) {       
    	$sToRender .= renderSectionDiv("documentRouting", renderEditableDocumentRouting($oDocument));
    	$sToRender .= renderSectionDiv("linkedDocuments", renderEditableLinkedDocuments($oDocument));
    } else {
    	$sToRender .= renderSectionDiv("documentRouting", renderNonEditableDocumentRouting($oDocument));
    	$sToRender .= renderSectionDiv("linkedDocuments", renderNonEditableLinkedDocuments($oDocument, $bEdit));
    }
	if (!$bNN4) {
		$sToRender .= "</div>";
	}    
    return $sToRender;	
}

function getStatusPage($oDocument, $sStatusMessage) {
    global $default;
       
	$sToRender = "<div id=\"headings\" style=\"position:relative;visibility:visible;top:2px;left:2px;\">";       
    $sToRender .= renderHeading("Document Detail");
    $sToRender .= renderDocumentPath($oDocument, false) . "\n\n";
    $sToRender .= "<table border=\"0\" width=\"610\">";

	// if we have a status message, then make the section links refresh to viewDocument with the fShowSection variable
	// ie. effectively removes statusMessage on next click
	$bDisplayLink = ($sStatusMessage) ? true : false;

	$sToRender .= renderDocumentSection("documentData", "Document Data", $bDisplayLink, $oDocument->getID());	
	$sToRender .= renderDocumentSection("genericMetaData", "Generic Meta Data", $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("typeSpecificMetaData", "Type Specific Meta Data", $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("archiveSettings", "Archive Settings", $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("documentRouting", "Document Routing", $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("linkedDocuments", "Linked Documents", $bDisplayLink, $oDocument->getID());
	$sToRender .= "</table>";
    $sToRender .= "</div>";
	$sToRender .= renderDocumentData($oDocument, false, $sStatusMessage);
	return $sToRender;
}

function getWebPublishPage($oDocument) {
    global $default;
    
    $oPatternListBox = & new PatternListBox($default->owl_web_sites_table, "web_site_name", "id", "fWebSiteID");
    
    $sToRender .= renderHeading("Document Detail");
    $sToRender .= renderDocumentPath($oDocument, false) . "\n\n";
    
    $sToRender .= "<table>\n";
    $sToRender .= "\t<tr>";    
    $sToRender .= "\t\t<th align=\"left\">Choose the website to publish to:</th>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternListBox->render() . "</td>\n";
    $sToRender .= "\t</tr>";    
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<th nowrap align=\"left\">Enter a comment for the web master:</th>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td><input type=\"text\" name=\"fComment\" size=\"30\"/></td>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t\t<input type=\"hidden\" name=\"fForPublish\" value=\"1\"/>\n";    
    $sToRender .= "\t\t<input type=\"hidden\" name=\"fSubmit\" value=\"1\"/>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td><input type=\"image\" src=\"$default->graphicsUrl/widgets/publish.gif\" border=\"0\"/></a>";
    $sToRender .= "\t\t<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a></td>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "</table>\n";

	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(document.MainForm.fWebSiteID,'Website'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
    $sToRender .= "\tif (!(validRequired(document.MainForm.fComment,'Publish comment'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";    
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";

   	$sToRender .= renderDocumentData($oDocument, $bEdit);   
    	
    return $sToRender;
}

function wrapInTable($sHtml) {
    return "\n\t\t\t<table border = 1, width = 100%><tr><td>$sHtml</td></tr></table>\n";
}
?>