<?php
/**
 * $Id$
 *
 * Contains HTML information required to build the document view page.
 * Will be used by documentViewBL.
 *
 * Variables expected:
 *   o $fDocumentID  Primary key of document to view
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */

function renderDocumentPath($oDocument, $bDisplayActions = false) {
    global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	
	$sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($oDocument->getFolderID()), Folder::getFolderPathNamesAsArray($oDocument->getFolderID()), "$default->rootUrl/control.php?action=browse") . " > ";
	// #3425 for consistency
	if ($bDisplayActions) {
	    $sDocumentPath .= "<a onClick=\"alert('" . _("This will view a copy of the current version of this document in the DMS.  Any changes to this file will not be managed in the DMS.") . "'); return true;\" href=\"" . generateControllerUrl("downloadDocument", "fDocumentID=" . $oDocument->getID() . "&fForInlineView=1") . "\">" . $oDocument->getFileName() . "</a>";
	} else {
		$sDocumentPath .= $oDocument->getFileName();
	}    
	return "<table border=\"0\" cellpadding=\"5\" width=\"610\"><tr bgcolor=\"$sTDBGColour\"><td width=\"87%\">" . $sDocumentPath . "</td>" . ($bDisplayActions ? "<td width=\"15%\" align=\"center\" valign=\"middle\"><strong>Actions</strong></td>" : "") . "</tr></table>\n";
}

function renderDocumentData($oDocument, $bEditable, $sStatusMessage = "") {
    global $default;
    $sQuery = "SELECT D.name, D.modified, DTT.datetime AS created, U.name AS initiator, CONCAT(CONCAT(D.major_version, '.'), D.minor_version) AS version, WDSL.name AS status, DTL.name AS document_type, D.is_checked_out,  COALESCE(U2.name, '') AS c_user " .
              "FROM $default->documents_table AS D INNER JOIN $default->web_documents_table AS WD ON WD.document_id = D.ID " .
              "INNER JOIN $default->web_documents_status_table AS WDSL ON WD.status_id = WDSL.id " .
              "INNER JOIN $default->users_table AS U ON U.id = D.creator_id " .
              "INNER JOIN $default->document_transactions_table AS DTT ON DTT.document_id = D.id " .
              "INNER JOIN $default->transaction_types_table AS TT ON DTT.transaction_id = TT.id " .
			  "INNER JOIN $default->document_types_table AS DTL ON DTL.id = D.document_type_id " .
              "LEFT OUTER JOIN $default->users_table AS U2 ON U2.id = D.checked_out_user_id " .
              "WHERE D.id = " . $oDocument->getID() . " " .
              "AND TT.name LIKE 'Create'";

    $aColumns = array("name", "modified", "created", "initiator", "document_type", "version", "status", "c_user");
    $aColumnNames = array(_("Document title"), _("Last updated"), _("Created"), _("Document initiator"), _("Document Type"), _("Version"), _("Status"), _("Checked out by"));
    $aColumnTypes = array(1,1,1,1,1,1,1,1);
    $oPatternListFromQuery = & new PatternListFromQuery($sQuery, $aColumns, $aColumnNames, $aColumnTypes);
    $oPatternListFromQuery->setTableHeading(_("Document Data"));
    $oPatternListFromQuery->setEmptyTableMessage(_("No Document Data"));
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
	    $sToRender .= "<td colspan=\"2\"><a href=\"$default->rootUrl/control.php?action=modifyDocument&fDocumentID=" . $oDocument->getID() . "\"><image src=\"" . KTHtml::getEditButton() . "\" border=\"0\"></a></td>\n";
		$sToRender .= "\t</tr>\n";
    }
	$sToRender .= "\t</table>\n";

    return $sToRender;
}


function renderGenericMetaData($oDocument, $bEditable) {
    global $default;
    $sQuery = "SELECT DF.name AS name, DFL.value as value " .
              "FROM $default->documents_table AS D INNER JOIN $default->document_fields_link_table AS DFL ON D.id = DFL.document_id " .
              "INNER JOIN $default->document_fields_table AS DF ON DF.id = DFL.document_field_id " .
              "WHERE document_id = " . $oDocument->getID() . " " .
              "AND DF.is_generic = 1";
    $aColumns = array("name", "value");
    $aColumnHeaders = array("Tag", "Value");
    $aColumnTypes = array(1,1);
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500");
    $oPatternTableSqlQuery->setTableHeading(_("Generic Meta Data"));
    $oPatternTableSqlQuery->setEmptyTableMessage(_("No Generic Meta Data"));
    
    $sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";    	
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<td>" . $oPatternTableSqlQuery->render() . "</td>\n";
    $sToRender .= "\t</tr>\n";
    if ($bEditable) {
		$sToRender .= "\t<tr>\n";
	    $sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=modifyDocumentGenericMetaData&fDocumentID=" . $oDocument->getID() . "\"><image src=\"" . KTHtml::getEditButton() . "\" border=\"0\"></a></td>\n";    
		$sToRender .= "\t</tr>\n";
    }
	$sToRender .= "\t</table>\n";
    
    return $sToRender;
}

function renderTypeSpecificMetaData($oDocument, $bEditable) {
	global $default;
	
    $sQuery = "SELECT DF.name AS name, DFL.value AS value " .
              "FROM $default->documents_table AS D INNER JOIN document_fields_link AS DFL ON D.id = DFL.document_id " .
              "INNER JOIN $default->document_fields_table AS DF ON DF.ID = DFL.document_field_id " .
              "WHERE D.id = " . $oDocument->getID() . " " .
              "AND DF.name NOT LIKE 'Author' " .
              "AND DF.name NOT LIKE 'Category' " .
              "AND DF.is_generic = 0";
    $aColumns = array("name", "value");
    $aColumnHeaders = array("Tag", "Value");
    $aColumnTypes = array(1,1);
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500");
    $oPatternTableSqlQuery->setTableHeading(_("Type Specific Meta Data"));
	$oPatternTableSqlQuery->setEmptyTableMessage(_("No Type Specific Meta Data"));
    
    $sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";	
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternTableSqlQuery->render() . "</td>\n";    
    $sToRender .= "\t</tr>\n";
    if ($bEditable) {
		$sToRender .= "\t<tr>\n";
	    $sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=modifyDocumentTypeMetaData&fDocumentID=" . $oDocument->getID() . "\"><image src=\"" . KTHtml::getEditButton() . "\" border=\"0\"></a></td>\n";
		$sToRender .= "\t</tr>\n";
    }
    $sToRender .= "\t</table>\n";
    return $sToRender;    
}
function renderDocumentArchiveSettingsDetails($oDocument, $bEditable) {
	global $default;
	
	// retrieve the appropriate settings given the document id
	$oDocumentArchiving = DocumentArchiving::getFromDocumentID($oDocument->getID());
	if ($oDocumentArchiving) {
		// retrieve the settings
		$oArchiveSettings = ArchivingSettings::get($oDocumentArchiving->getArchivingSettingsID());  			
		// switch on archiving type
		$sArchivingType = lookupName($default->archiving_type_lookup_table, $oArchiveSettings->getArchivingTypeID());
		$oTimePeriod = TimePeriod::get($oArchiveSettings->getTimePeriodID());
			
		$sDisplayText = "<td>";
		switch ($sArchivingType) {
			case "Date" : 
				$sDisplayText  .= _("Expiration Date") . ": " . $oArchiveSettings->getExpirationDate();
				if ($oTimePeriod) {
					$sDisplayText .= "<br/>Expires after " . $oTimePeriod->getUnits() . " ";
					$sDisplayText .= lookupName($default->time_unit_lookup_table, $oTimePeriod->getTimeUnitID()) . " from document creation date";
				}
				break;		
			case "Utilisation" :
				$sDisplayText .= "Archive document " . $oTimePeriod->getUnits() . " ";
				$sDisplayText .= lookupName($default->time_unit_lookup_table, $oTimePeriod->getTimeUnitID());
				$sDisplayText .= " after the last " . lookupName($default->transaction_types_table, $oArchiveSettings->getDocumentTransactionID()) . " transaction";
				break;		
		}
		$sDisplayText .= "</td>" . ($bEditable ? "<td>" . generateControllerLink("modifyDocumentArchiveSettings", "fDocumentID=" . $oDocument->getID(), "Edit") . "</td>" : "");
		$sArchivingType = _("Archiving Type") . ": " . 	$sArchivingType;
	} else {
		$sArchivingType = "";
		$sDisplayText = _("No archiving settings");
	}

	$sToRender  = "<table cellpadding=\"5\" border=\"0\" width=\"500\">";
	$sToRender .= "<caption align=\"top\" colspan=\"2\" align=\"left\"><b>" . _("Archiving Settings") . "</b></caption>";
	$sToRender .= "<th align=\"left\">" . $sArchivingType . "</th>";
	$sToRender .= "<tr bgcolor=\"F5F6EE\">";
	$sToRender .= $sDisplayText;
	$sToRender .= "</tr>";
	$sToRender .= "</table>";
    return  $sToRender;	
}

function renderDocumentArchiveSettings($oDocument, $bEditable) {
    global $default;   

    $sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";
    $sToRender .= "\t<tr>\n";
    
	$sToRender .= "\t\t<td>" . renderDocumentArchiveSettingsDetails($oDocument, $bEditable) . "</td>\n";    
    $sToRender .= "\t</tr>";
    if ($bEditable) {
	   	$sToRender .= "\t<tr>\n";
	    // if there are no archiving settings then allow their addition
	    $oDocumentArchiving = DocumentArchiving::getFromDocumentID($oDocument->getID());
	    $sToRender .= "\t\t<td>" . ($oDocumentArchiving ? "" : generateControllerLink("addDocumentArchiveSettings", "fDocumentID=" . $oDocument->getID(), "<img src=\"" . KTHtml::getAddButton() . "\" border=\"0\"/>")) . "</td>\n";
		$sToRender .= "\t</tr>";
    }        	    
	$sToRender .= "\t</table>\n";

    return $sToRender;
}

function renderEditableDocumentRouting($oDocument) {
    global $default;
    $sQuery = "SELECT D.id as document_id, GFAL.id as id, R.name AS role_name, COALESCE(U.name, U2.name) AS name, GFAL.precedence AS precedence, COALESCE(FURL.active,0) AS active, COALESCE(FURL.done, 0) AS done, 'Edit User' as edit " .
              "FROM $default->documents_table AS D INNER JOIN $default->groups_folders_approval_table AS GFAL ON D.folder_id = GFAL.folder_id " .
              "INNER JOIN $default->roles_table AS R ON GFAL.role_id = R.id " .
              "LEFT OUTER JOIN $default->folders_user_roles_table AS FURL ON FURL.group_folder_approval_id = GFAL.id AND FURL.document_id = D.id " .
              "LEFT OUTER JOIN $default->users_table AS U ON FURL.user_id = U.id " .
              "LEFT OUTER JOIN $default->users_table AS U2 ON GFAL.user_id = U2.id " .              
              "WHERE D.id = " . $oDocument->getID() . " " .
              "ORDER BY GFAL.precedence, role_name ASC";
    $aColumns = array("role_name", "name", "precedence", "active", "done", "edit");
    $aColumnHeaders = array(_("Role"), _("User"), _("Seq"), _("Active"), _("Done"), _("Edit"));
    $aColumnTypes = array(1,1,1,2,2,3);
    $aDBColumnArray = array("id","document_id","active","done");
    $aQueryStringVariableNames = array("fFolderCollaborationID", "fDocumentID","fIsActive","fIsDone");
    $aLinkURLs = array(5=>"$default->rootUrl/control.php?action=modifyDocumentRouting");

    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500", $aLinkURLs,$aDBColumnArray,$aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading(_("Document Routing"));
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);
    
    $sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternTableSqlQuery->render() . "</td>\n";
    $sToRender .= "\t</tr>";            
    $sToRender .= "\t<tr>\n";    
    // display the begin collaboration button if:
    // collaboration cannot be started or approved/rejected unless the document is not checked out
    // the document has collaboration
    // collaboration hasn't started
    // the current user created the document, or is a system adminstrator    
	if (!$oDocument->getIsCheckedOut() && 
		$oDocument->hasCollaboration() && 
		(!DocumentCollaboration::documentCollaborationStarted($oDocument->getID())) && 
		( ($_SESSION["userID"] == $oDocument->getCreatorID())  || Permission::userIsSystemAdministrator() ) ) {
		//if not all collaboration steps have been set, then you cannot start the collaboration process
		//only the creator of the document can start the collaboration process		
		$sToRender .= "\t\t<td><a href=" . $_SERVER['PHP_SELF'] . "?fDocumentID=" . $oDocument->getID() . "&fBeginCollaboration=1><img src=\"" . KTHtml::getBeginButton() . "\" border=\"0\"/></a>";
		$sToRender .= "</td>\n";
	// else if collboration has started and the current user has been assigned this step, display the approve/reject buttons
	} else if (DocumentCollaboration::userIsPerformingCurrentCollaborationStep($oDocument->getID())) {
		//if the current user is responsible for an active step in the collaboration process		
		$sToRender .= "\t\t<td><table border=\"0\"><tr>\n";
		$sToRender .= "\t\t<td><b>" . _("You currently have an active role in the collaboration process") . "</b></td>\n";
		// collaboration cannot be started or approved/rejected if the document is checked out
		if (!$oDocument->getIsCheckedOut()) {				
			$sToRender .= "\t\t<td align=\"center\"><a onClick=\"return confirm('" . _("Are you sure you want to approve?  You will not have access to this document until the collaboration process is complete (unless the document is rejected in a subsequent step)") . "');\" href=" . $_SERVER['PHP_SELF'] . "?fDocumentID=" . $oDocument->getID() . "&fCollaborationStepComplete=1><img src=\"" . KTHtml::getApproveButton() . "\" border=\"0\"/></a></td>\n";
			$sToRender .= "\t\t<td align=\"center\"><a href=\"$default->rootUrl/control.php?action=collaborationStepReject&fDocumentID=" . $oDocument->getID() . "\"><img src=\"" . KTHtml::getRejectButton() . "\" border=\"0\"/></a></td>\n";
		}	
		$sToRender .= "\t\t</tr></table></td>\n";
	}

    $sToRender .= "\t</tr>";
	$sToRender .= "\t</table>\n";
	
    return $sToRender;    
}

function renderNonEditableDocumentRouting($oDocument) {
    global $default;
    $sQuery = "SELECT D.id as document_id, GFAL.id as id, R.name AS role_name, COALESCE(U.Name, 'Not assigned') AS name, GFAL.precedence AS precedence, COALESCE(FURL.active,0) AS active, COALESCE(FURL.done, 0) AS done " .
              "FROM $default->documents_table AS D INNER JOIN $default->groups_folders_approval_table AS GFAL ON D.folder_id = GFAL.folder_id " .
              "INNER JOIN $default->roles_table AS R ON GFAL.role_id = R.id " .
              "LEFT OUTER JOIN $default->folders_user_roles_table AS FURL ON FURL.group_folder_approval_id = GFAL.id AND FURL.document_id = D.id " .
              "LEFT OUTER JOIN $default->users_table AS U ON FURL.user_id = U.id " .
              "WHERE D.id = " . $oDocument->getID() . " " .
              "ORDER BY GFAL.precedence, role_name ASC";

    $aColumns = array("role_name", "name", "precedence", "active", "done");
    $aColumnHeaders = array(_("Role"), _("User"), _("Seq"), _("Active"), _("Done"));
    $aColumnTypes = array(1,1,1,2,2);
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500", "$default->rootUrl/control.php?action=modifyDocumentRouting",$aDBColumnArray,$aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading(_("Document Routing"));
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);

   	$sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternTableSqlQuery->render() . "</td>\n";
    $sToRender .= "\t</tr>";
    
	if (DocumentCollaboration::userIsPerformingCurrentCollaborationStep($oDocument->getID())) {
		//if the current user is responsible for an active step in the collaboration process		
		$sToRender .= "\t\t<tr><td><table border=\"0\"><tr>\n";
		$sToRender .= "\t\t<td><b>" . _("You currently have an active role in the collaboration process") . "</b></td>\n";				
		$sToRender .= "\t\t<td align=\"center\"><a href=" . $_SERVER['PHP_SELF'] . "?fDocumentID=" . $oDocument->getID() . "&fCollaborationStepComplete=1><img src=\"" . KTHtml::getApproveButton() . "\" border=\"0\"/></a></td>\n";
		$sToRender .= "\t\t<td align=\"center\"><a href=\"$default->rootUrl/control.php?action=collaborationStepReject&fDocumentID=" . $oDocument->getID() . "\"><img src=\"" . KTHtml::getRejectButton() . "\" border=\"0\"/></a></td>\n";
		$sToRender .= "\t\t</tr></table></td></tr>\n";
	}
	    
	$sToRender .= "\t</table>\n";
	
    return $sToRender;
}

function renderEditableLinkedDocuments($oDocument) {
	global $default;
	
	$sQuery = "SELECT D.id AS child_document_id, D.name, DL.id as document_link_id, DL.parent_document_id AS parent_document_id, 'Unlink' AS unlink " .
			  "FROM $default->documents_table AS D INNER JOIN $default->document_link_table AS DL ON D.id = DL.child_document_id " .
			  "WHERE DL.parent_document_id = " . $oDocument->getID();
	
	$aColumns = array("name", "unlink");
    $aColumnHeaders = array(_("Document"));
    $aColumnTypes = array(3,3);
    $aDBColumnArray = array("parent_document_id", "child_document_id","document_link_id", "child_document_id");
    $aQueryStringVariableNames = array("fParentDocumentID","fChildDocumentID", "fDocumentLinkID", "fDocumentID");
    $aLinkURLs = array(0=>"$default->rootUrl/control.php?action=viewDocument", 1=>"$default->rootUrl/control.php?action=removeDocumentLink");

    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500", $aLinkURLs, $aDBColumnArray, $aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading(_("Linked documents"));
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);    
    
    $sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";    
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternTableSqlQuery->render() . "</td>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=addDocumentLink&fDocumentID=" . $oDocument->getID() ."\"><img src=\"" . KTHtml::getAddButton() . "\" border=\"0\"/></a></td>\n";
    $sToRender .= "\t</tr>";     
	$sToRender .= "\t</table>\n";
    return $sToRender;
}

function renderNonEditableLinkedDocuments($oDocument) {
	global $default;
	
	$sQuery = "SELECT D.id, D.name " .
			  "FROM $default->documents_table AS D INNER JOIN $default->document_link_table AS DL ON D.id = DL.child_document_id " .
			  "WHERE DL.parent_document_id = " . $oDocument->getID();
	
	$aColumns = array("name");
    $aColumnHeaders = array(_("Document"));
    $aColumnTypes = array(3);
    $aDBColumnArray = array("id");
    $aQueryStringVariableNames = array("fDocumentID");
    $aLinkURLs = array(0=>"$default->rootUrl/control.php?action=viewDocument");
    
    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500", $aLinkURLs, $aDBColumnArray, $aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading(_("Linked documents"));
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);
    
    $sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";    
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternTableSqlQuery->render() . "</td>\n";
    $sToRender .= "\t</tr>";
	$sToRender .= "\t</table>\n";
    return $sToRender;    
}

function renderDependantDocuments($oDocument, $bEdit) {
	global $default;
	// FIXME: only allow escalation if you have write access and are the same user that requested the
	// dependant document?
	$sQuery = "SELECT DDI.id AS instance_id, DDI.document_title, U.name AS user_name, 'Escalate' AS escalate " .
			  "FROM $default->dependant_document_instance_table DDI " . 
			  "INNER JOIN $default->users_table AS U ON DDI.user_id = U.id " .
			  "WHERE DDI.parent_document_id = " . $oDocument->getID();
	
	$aColumns = array("document_title", "user_name", "escalate");
    $aColumnHeaders = array(_("Document Title"), _("User"));
    $aColumnTypes = array(1,1,3);
    $aDBColumnArray = array("instance_id");
    $aQueryStringVariableNames = array("fInstanceID");
    $aLinkURLs = array(2=>generateControllerUrl("escalateDependantDocument"));

    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "500", $aLinkURLs, $aDBColumnArray, $aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading(_("Dependant documents"));
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);    
    
    $sToRender .= "\t<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n";    
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternTableSqlQuery->render() . "</td>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=createDependantDocument&fDocumentID=" . $oDocument->getID() ."\"><img src=\"" . KTHtml::getAddButton() . "\" border=\"0\"/></a></td>\n";
    $sToRender .= "\t</tr>";     
	$sToRender .= "\t</table>\n";
    return $sToRender;
}

$aImageNameToLabel = array(
    "dependentdoc" => "Link new doc",
);

function displayButton($sAction, $sQueryString, $sImageName, $sDisabledText = "") {
	global $default;
        global $aImageNameToLabel;
	// the active is active if there is no disabled text
	$bActive = !strlen($sDisabledText) > 0;
        if ($default->useTextButtons === false) {
            $sImage = "<img border=\"0\" src=\"$default->graphicsUrl/widgets/docactions/";
            if ($bActive) {
                $sImage .= "$sImageName.gif\"";
            } else {
                $sImage .= "disabled-$sImageName.gif\" title=\"$sDisabledText\"";
            }
            $sImage .= "/>";
        } else {
            if (array_key_exists($sImageName, $aImageNameToLabel)) {
                $sLabel = $aImageNameToLabel[$sImageName];
            } else {
                $sLabel = $sImageName;
            }
            $sLabel = strtoupper($sLabel);
            if ($bActive) {
                    $sClass = "button";
            } else {
                    $sClass = "disabledbutton";
            }
            $sImage = "<span class=\"$sClass\">$sLabel</span>";
        }
	if ($bActive) {
		return generateControllerLink($sAction, $sQueryString, $sImage);
	} else {
		return $sImage;
	}
}

function displayViewButton($oDocument, $bEdit) {
	global $default;
	$sViewAlert = _("This will download a copy of the document and is not the same as Checking Out a document.  Changes to this downloaded file will not be managed in the DMS.");
        if ($default->useTextButtons === false) {
    return "<tr><td align=\"left\"><a onClick=\"alert('$sViewAlert '); return true;\" href=\"" . generateControllerLink("downloadDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/docactions/view.gif\" border=\"0\" title=\"" . _("View the document") . "\"/></a></td></tr>\n";
        } else {
    return "<a onClick=\"alert('$sViewAlert '); return true;\" href=\"" . generateControllerLink("downloadDocument", "fDocumentID=" . $oDocument->getID()) . "\"><span class=\"button\" title=\"" . _("View the document") . "\">VIEW</span></a>\n";
        }
}

function displayCheckInOutButton($oDocument, $bEdit) {

	$sQueryString = "fDocumentID=" . $oDocument->getID();
	// display the check in button if the document is checked out and you checked the document out
	if ($oDocument->getIsCheckedOut()) {
  		$sAction = "checkInDocument";
  		$sImageName = "checkin";		
		if ($oDocument->getCheckedOutUserID() <> $_SESSION["userID"]) {
	  		$oUser = User::get($oDocument->getCheckedOutUserID());
	  		$sDisabledText = sprintf(_("The document can only be checked back in by %s"), $oUser->getName());
	  	} 
	// otherwise display the check out button
	} else {
	  	$sAction = "checkOutDocument";
	  	$sImageName = "checkout";
    	// #3481 if the document is published don't allow checkout
    	if (DocumentCollaboration::documentIsPublished($oDocument->getID())) {
    		$sDisabledText = _("This document has been published and cannot be checked out");
    	}	  	
	}
    if (!$bEdit) {
    	$sDisabledText = _("You do not have write access to this document");
    }
    return displayButton($sAction, $sQueryString, $sImageName, $sDisabledText);
}

function displayEmailButton($oDocument) {
	global $default;
	return displayButton("emailDocument", "fDocumentID=" . $oDocument->getID(), "email");
}

function displayDeleteButton($oDocument, $bEdit) {
    if ($bEdit) {
    	$sQueryString = "fDocumentIDs[]=" . $oDocument->getID();
    	if ($oDocument->getIsCheckedOut()) {
    		$sDisabledText = _("This document can't be deleted because its checked out");
    	}
    } else {
    	$sDisabledText = _("You do not have write access to this document");
    }
	return displayButton("deleteDocument", $sQueryString, "delete", $sDisabledText);
}

function displayHistoryButton($oDocument) {
	global $default;
	return displayButton("viewHistory", "fDocumentID=" . $oDocument->getID(), "history");
}	
function displayMoveButton($oDocument, $bEdit) {
    if ($bEdit) {
    	$sQueryString = "fFolderID=" . $oDocument->getFolderID() . "&fDocumentIDs[]=" . $oDocument->getID();
    	// documents in collaboration and checked out documents can't be moved
		if ($oDocument->getIsCheckedOut()) {
			$sDisabledText = _("The document is checked out and cannot be moved.");
		}
		if (DocumentCollaboration::documentCollaborationStarted($oDocument->getID()) &&
		    !DocumentCollaboration::documentCollaborationDone($oDocument->getID())) {
			$sDisabledText = _("The document is in collaboration and cannot be moved.");
		}
    } else {
     	$sDisabledText = _("You do not have write access to this document");
    }
	return displayButton("moveDocument", $sQueryString, "move", $sDisabledText);
}

function displaySubscriptionButton($oDocument) {
    // display the unsubscribe button if the user is subscribed to the document
    if (Subscription::exists($_SESSION["userID"], $oDocument->getID(), SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
    	$sAction = "removeSubscription";
    	$sImageName = "unsubscribe";
    // otherwise display the subscribe button
    } else {
		$sAction = "addSubscription";
		$sImageName = "subscribe";    	
    }
	return displayButton($sAction, "fDocumentID=" . $oDocument->getID(), $sImageName);
}

function displayDiscussionButton($oDocument, $bEdit) {
	global $default;
    return displayButton("viewDiscussion", "fDocumentID=" . $oDocument->getID() . "&fForDiscussion=1", "discussion");
}
	
function displayArchiveButton($oDocument, $bEdit) {
    // only display the archive button for available documents
    // if the document is not checked out
    // and if the document is not in collaboration
    if ($bEdit) {
	    if (!$oDocument->getIsCheckedOut()) {
	    	if ($oDocument->hasCollaboration() && 
	    		DocumentCollaboration::documentCollaborationStarted($oDocument->getID()) &&
	    		!DocumentCollaboration::documentCollaborationDone($oDocument->getID())) {
	    		$sDisabledText = _("This document is in collaboration and cannot be archived");
	    	}
	    } else {
	    	$sDisabledText = _("This document is checked out and cannot be archived.");
	    }
    } else {
     	$sDisabledText = _("You do not have write access to this document");
    }
	return displayButton("archiveDocument", "fDocumentID=" . $oDocument->getID(), "archive", $sDisabledText);
}

function displayDependantDocumentButton($oDocument, $bEdit) {
	global $default;
	return displayButton("createDependantDocument", "fDocumentID=" . $oDocument->getID(), "dependentdoc");
}

function displayPublishButton($oDocument, $bEdit) {
    // only display the publish button for unpublished documents	
    if (!DocumentCollaboration::documentIsPublished($oDocument->getID()) && 
    	!DocumentCollaboration::documentIsPendingWebPublishing($oDocument->getID())) {      
        // if there is collaboration
        if ($oDocument->hasCollaboration()) {
            // only display publish button if collaboration is complete and you're the last user in the collaboration process (or a sysadmin)
            if (DocumentCollaboration::documentCollaborationDone($oDocument->getID())) { 
                if ( !($_SESSION["userID"] == DocumentCollaboration::getLastCollaboratorID($oDocument->getID())) && !Permission::userIsSystemAdministrator() ) {
                	$sDisabledText = _("You are not the last collaborator and hence cannot publish this document");
                }
            } else {
            	$sDisabledText = _("You cannot publish this document until collaboration is complete");
            }
        } else {
        	// no collaboration, check the user permissions
        	if ( !($_SESSION["userID"] == $oDocument->getCreatorID()) && !Permission::userIsSystemAdministrator() ) {
        		$sDisabledText = _("You do not have permission to publish this document.");
        	}
        }
    } else {
    	if (DocumentCollaboration::documentIsPublished($oDocument->getID())) {
    		$sDisabledText = _("This document is already published.");
    	} else if (DocumentCollaboration::documentIsPendingWebPublishing($oDocument->getID())) {
    		$sDisabledText = _("This document has been marked as pending publishing and the web publisher has been notified.");
    	}
    }
	return displayButton("viewDocument", "fDocumentID=" . $oDocument->getID() . "&fForPublish=1", "publish", $sDisabledText);
}

/**
 * Displays document action buttons
 */
function displayActionButtons($oDocument, $bEdit) {
    $sToRender .= displayViewButton($oDocument, $bEdit);
    $sToRender .= displayEmailButton($oDocument);
    $sToRender .= displayCheckInOutButton($oDocument, $bEdit);
    $sToRender .= displayDeleteButton($oDocument, $bEdit);
    $sToRender .= displayHistoryButton($oDocument);
	$sToRender .= displayMoveButton($oDocument, $bEdit);    	
	$sToRender .= displaySubscriptionButton($oDocument);
	$sToRender .= displayDiscussionButton($oDocument, $bEdit);
    $sToRender .= displayArchiveButton($oDocument, $bEdit);
	$sToRender .= displayDependantDocumentButton($oDocument, $bEdit);
	$sToRender .= displayPublishButton($oDocument, $bEdit);
	
    return $sToRender;
}

function renderSectionDiv($sDivName, $sHtml) {
	global $default;
	return "<div id=\"$sDivName\" style=\"visibility:hidden;position:absolute;top:5px;left:5px;\">$sHtml</div>";
}

function renderDocumentSection($sSectionName, $sHeadingText, $bDisplayLink, $iDocumentID) {	
	if ($bDisplayLink) {
		$sLink = generateControllerLink("viewDocument", "fDocumentID=$iDocumentID&fShowSection=$sSectionName", $sHeadingText);
	} else {
		$sLink = "<a href=\"#\" onClick=\"switchDiv('$sSectionName', 'document');\">$sHeadingText</a>";
	}
	return '<span style="display:block; background-color:' . getColour($iColour) . '">' . $sLink . '</span>' . "\n";
}
	
function getPage($oDocument, $bEdit, $sStatusMessage = "") {
    global $default;
   	  		
    $sToRender .= renderHeading(_("Document Detail"));
    $sToRender .= renderDocumentPath($oDocument, true) . "\n\n";
	$sToRender .= "<table cellspacing=\"0\" cellpadding=\"0\" id=\"headingTable\">";
	$sToRender .= "<tr><td valign=\"top\">";	
	$sToRender .= "<table border=\"0\" width=\"530\">";
        $sToRender .= "<tr><td>";

	// if we have a status message, then make the section links refresh to viewDocument with the fShowSection variable
	// ie. effectively removes statusMessage on next click
	$bDisplayLink = ($sStatusMessage) ? true : false;

	$sToRender .= renderDocumentSection("documentData", _("Document Data"), $bDisplayLink, $oDocument->getID());	
	$sToRender .= renderDocumentSection("genericMetaData", _("Generic Meta Data"), $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("typeSpecificMetaData", _("Type Specific Meta Data"), $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("archiveSettings", _("Archive Settings"), $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("documentRouting", _("Document Routing"), $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("linkedDocuments", _("Linked Documents"), $bDisplayLink, $oDocument->getID());

	$sToRender .= "</td></tr>";
        $sToRender .= "</table>";
	$sToRender .= '</td><td rowspan="2" valign="top">';
        $sToRender .= "<table cellspacing=\"0\" cellpadding=\"0\">\n";
	$sToRender .= displayActionButtons($oDocument, $bEdit);	    
        $sToRender .= "</table>\n";
	$sToRender .= "</td></tr>";

        $sToRender .= "<tr><td>";

        $sToRender .= '<div style="position:relative;">';
	$sToRender .= renderSectionDiv("documentData", renderDocumentData($oDocument, $bEdit, $sStatusMessage));
    $sToRender .= renderSectionDiv("genericMetaData", renderGenericMetaData($oDocument, $bEdit));
   	$sToRender .= renderSectionDiv("typeSpecificMetaData", renderTypeSpecificMetaData($oDocument, $bEdit));
    $sToRender .= renderSectionDiv("archiveSettings", renderDocumentArchiveSettings($oDocument, $bEdit));
    if ($bEdit) {       
    	$sToRender .= renderSectionDiv("documentRouting", renderEditableDocumentRouting($oDocument));
    	$sToRender .= renderSectionDiv("linkedDocuments", renderEditableLinkedDocuments($oDocument) . renderDependantDocuments($oDocument, $bEdit));
    } else {
    	$sToRender .= renderSectionDiv("documentRouting", renderNonEditableDocumentRouting($oDocument));
    	$sToRender .= renderSectionDiv("linkedDocuments", renderNonEditableLinkedDocuments($oDocument, $bEdit) . renderDependantDocuments($oDocument, $bEdit));
    }
        $sToRender .= "<br />";
        $sToRender .= "<br />";
        $sToRender .= "<br />";
        $sToRender .= "<br />";
        $sToRender .= "<br />";
        $sToRender .= "<br />";
        $sToRender .= "<br />";
        $sToRender .= "<br />";
        $sToRender .= "<br />";
        $sToRender .= "<br />";
        $sToRender .= "<br />";
        $sToRender .= "<br />";
        $sToRender .= "<br />";
        $sToRender .= "<br />";
        $sToRender .= "</div>";
    $sToRender .= "</td></tr>";
    $sToRender .= "</table>";
    return $sToRender;	
}

function getStatusPage($oDocument, $sStatusMessage) {
    global $default;
       
	$sToRender = "<div id=\"headings\" style=\"position:relative;visibility:visible;top:2px;left:2px;\">";       
    $sToRender .= renderHeading(_("Document Detail"));
    $sToRender .= renderDocumentPath($oDocument, false) . "\n\n";
    $sToRender .= "<table border=\"0\" width=\"610\">";

	// if we have a status message, then make the section links refresh to viewDocument with the fShowSection variable
	// ie. effectively removes statusMessage on next click
	$bDisplayLink = ($sStatusMessage) ? true : false;

	$sToRender .= renderDocumentSection("documentData", _("Document Data"), $bDisplayLink, $oDocument->getID());	
	$sToRender .= renderDocumentSection("genericMetaData", _("Generic Meta Data"), $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("typeSpecificMetaData", _("Type Specific Meta Data"), $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("archiveSettings", _("Archive Settings"), $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("documentRouting", _("Document Routing"), $bDisplayLink, $oDocument->getID());
	$sToRender .= renderDocumentSection("linkedDocuments", _("Linked Documents"), $bDisplayLink, $oDocument->getID());
	$sToRender .= "</table>";
    $sToRender .= "</div>";
	$sToRender .= renderDocumentData($oDocument, false, $sStatusMessage);
	return $sToRender;
}

function getWebPublishPage($oDocument) {
    global $default;
    
    $oPatternListBox = & new PatternListBox($default->web_sites_table, "web_site_name", "id", "fWebSiteID");
    
    $sToRender .= renderHeading(_("Document Detail"));
    $sToRender .= renderDocumentPath($oDocument, false) . "\n\n";
    
    $sToRender .= "<table>\n";
    $sToRender .= "\t<tr>";    
    $sToRender .= "\t\t<th align=\"left\">" . _("Choose the website to publish to:") . "</th>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . $oPatternListBox->render() . "</td>\n";
    $sToRender .= "\t</tr>";    
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<th nowrap align=\"left\">" . _("Enter a comment for the web master:") . "</th>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td><input type=\"text\" name=\"fComment\" size=\"30\"/></td>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t\t<input type=\"hidden\" name=\"fForPublish\" value=\"1\"/>\n";    
    $sToRender .= "\t\t<input type=\"hidden\" name=\"fSubmit\" value=\"1\"/>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td><input type=\"image\" src=\"" . KTHtml::getPublishButton() . "\" border=\"0\"/></a>";
    $sToRender .= "\t\t<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\" /></a></td>\n";
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
