<?php
/**
 * $Id$
 *
 * Contains HTML information required to build the document view page.
 * Will be used by documentViewBL.
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
    $iVersionID = KTUtil::arrayGet($_REQUEST, 'fVersionID');
    $aDocuments = array($oDocument->getID());
    if (!empty($iVersionID)) {
        $aDocuments[] = $iVersionID;
    }
    $sWhere = DBUtil::paramArray($aDocuments);
    /*ok*/ $sQuery = "SELECT D.id, D.name, D.modified, D.created, D.size, U.name AS initiator, D.metadata_version, CONCAT(CONCAT(D.major_version, '.'), D.minor_version) AS version, DTL.name AS document_type, D.is_checked_out,  COALESCE(U2.name, '') AS c_user " .
              "FROM $default->documents_table AS D " .
              "LEFT JOIN $default->users_table AS U ON U.id = D.creator_id " .
			  "LEFT OUTER JOIN $default->document_types_table AS DTL ON DTL.id = D.document_type_id " .
              "LEFT OUTER JOIN $default->users_table AS U2 ON U2.id = D.checked_out_user_id " .
              "WHERE D.id = ?";

    // $aColumns = array("metadata_version", "name", "modified", "created", "size", "initiator", "document_type", "c_user", "id", "version");
    $aColumns = array(
        "metadata_version" =>_("Metadata Version"),
        "name" => _("Document title"),
        "modified" => _("Last updated"),
        "created" => _("Created"),
        "size" => _("File Size"),
        "initiator" => _("Document initiator"),
        "document_type" => _("Document Type"),
        "c_user" => _("Checked out by"),
        "id" => _("ID"),
        "document_version" => _("Document Version"),
    );

    $oTemplating =& KTTemplating::getSingleton();
    $oTemplate = $oTemplating->loadTemplate('ktcore/document_generic_metadata');

    $iDocumentsLen = count($aDocuments);
    $map = array();
    $iDocumentCount = 0;
    foreach ($aDocuments as $iDocumentID) {
        $map['Metadata Version'] = KTUtil::arrayGet($map, 'Metadata Version', array_fill(0, $iDocumentsLen, null));
        $oThisDocument =& Document::get($iDocumentID);
        if (PEAR::isError($oThisDocument)) {
            continue;
        }
        if ($oThisDocument === false) {
            continue;
        }
        $map['Metadata Version'][$iDocumentCount] = $oThisDocument->getMetadataVersion();
        $aRow = DBUtil::getOneResult(array($sQuery, array($iDocumentID)));
        foreach ($aColumns as $dbname => $prettyname) {
            $newarray = KTUtil::arrayGet($map, $prettyname, array_fill(0, $iDocumentsLen, null));
            $newarray[$iDocumentCount] = $aRow[$dbname];
            $map[$prettyname] = $newarray;
        }
        $iDocumentCount++;
    }

    $oTemplate->setData(array(
        'map' => $map,
    ));

    return $oTemplate->render();
}


function renderGenericMetaData($oDocument, $bEditable) {
    global $default;
    $iVersionID = KTUtil::arrayGet($_REQUEST, 'fVersionID');
    $aDocuments = array($oDocument->getID());
    if (!empty($iVersionID)) {
        $aDocuments[] = $iVersionID;
    }
    /*ok*/ $sQuery = "SELECT DF.name AS name, DFL.value as value " .
              "FROM $default->documents_table AS D INNER JOIN $default->document_fields_link_table AS DFL ON D.id = DFL.document_id " .
              "INNER JOIN $default->document_fields_table AS DF ON DF.id = DFL.document_field_id " .
              "WHERE document_id = ? " .
              "AND DF.is_generic = 1";


    $iDocumentsLen = count($aDocuments);
    $map = array();
    $iDocumentCount = 0;
    foreach ($aDocuments as $iDocumentID) {
        $map['Metadata Version'] = KTUtil::arrayGet($map, 'Metadata Version', array_fill(0, $iDocumentsLen, null));
        $oThisDocument =& Document::get($iDocumentID);
        if (PEAR::isError($oThisDocument)) {
            continue;
        }
        if ($oThisDocument === false) {
            continue;
        }
        $map['Metadata Version'][$iDocumentCount] = $oThisDocument->getMetadataVersion();
        $aTDRows = DBUtil::getResultArray(array($sQuery, array($iDocumentID)));
        foreach ($aTDRows as $aRow) {
            $newarray = KTUtil::arrayGet($map, $aRow['name'], array_fill(0, $iDocumentsLen, null));
            $newarray[$iDocumentCount] = $aRow['value'];
            $map[$aRow['name']] = $newarray;
        }
        $iDocumentCount++;
    }

    $oTemplating =& KTTemplating::getSingleton();
    $oTemplate = $oTemplating->loadTemplate('ktcore/document_generic_metadata');
    if ($bEditable) {
        $editable = "<a href=\"$default->rootUrl/control.php?action=modifyDocumentGenericMetaData&fDocumentID=" . $oDocument->getID() . "\"><img src=\"" . KTHtml::getEditButton() . "\" border=\"0\"></a>";
    } else {
        $editable = "";
    }
    $sToRender .= $oTemplate->render(array(
        'map' => $map,
        'editable' => $editable,
    ));
    
    return $sToRender;
}

function renderTypeSpecificMetaData($oDocument, $bEditable) {
	global $default;
    $iVersionID = KTUtil::arrayGet($_REQUEST, 'fVersionID');
    $aDocuments = array($oDocument->getID());
    if (!empty($iVersionID)) {
        $aDocuments[] = $iVersionID;
    }
	
    /*ok*/ $sQuery = "SELECT DF.name AS name, DFL.value AS value " .
              "FROM $default->documents_table AS D INNER JOIN document_fields_link AS DFL ON D.id = DFL.document_id " .
              "INNER JOIN $default->document_fields_table AS DF ON DF.ID = DFL.document_field_id " .
              "WHERE D.id = ? " .
              "AND DF.name NOT LIKE 'Category' " .
              "AND DF.is_generic = 0";

    $iDocumentsLen = count($aDocuments);
    $map = array();
    $iDocumentCount = 0;
    foreach ($aDocuments as $iDocumentID) {
        $map['Metadata Version'] = KTUtil::arrayGet($map, 'Metadata Version', array_fill(0, $iDocumentsLen, null));
        $oThisDocument =& Document::get($iDocumentID);
        if (PEAR::isError($oThisDocument)) {
            continue;
        }
        if ($oThisDocument === false) {
            continue;
        }
        $map['Metadata Version'][$iDocumentCount] = $oThisDocument->getMetadataVersion();
        $aTDRows = DBUtil::getResultArray(array($sQuery, array($iDocumentID)));
        foreach ($aTDRows as $aRow) {
            $newarray = KTUtil::arrayGet($map, $aRow['name'], array_fill(0, $iDocumentsLen, null));
            $newarray[$iDocumentCount] = $aRow['value'];
            $map[$aRow['name']] = $newarray;
        }
        $iDocumentCount++;
    }

    $oTemplating =& KTTemplating::getSingleton();
    $oTemplate = $oTemplating->loadTemplate('ktcore/document_specific_metadata');
    if ($bEditable) {
        $editable = "<a href=\"$default->rootUrl/control.php?action=modifyDocumentTypeMetaData&fDocumentID=" . $oDocument->getID() . "\"><img src=\"" . KTHtml::getEditButton() . "\" border=\"0\"></a>";
    } else {
        $editable = "";
    }
    $sToRender = $oTemplate->render(array(
        'map' => $map,
        'editable' => $editable,
    ));

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
    /*ok*/ $sQuery = array("SELECT D.id as document_id, GFAL.id as id, R.name AS role_name, COALESCE(U.name, U2.name) AS name, GFAL.precedence AS precedence, COALESCE(FURL.active,0) AS active, COALESCE(FURL.done, 0) AS done, 'Edit User' as edit " .
              "FROM $default->documents_table AS D INNER JOIN $default->groups_folders_approval_table AS GFAL ON D.folder_id = GFAL.folder_id " .
              "INNER JOIN $default->roles_table AS R ON GFAL.role_id = R.id " .
              "LEFT OUTER JOIN $default->folders_user_roles_table AS FURL ON FURL.group_folder_approval_id = GFAL.id AND FURL.document_id = D.id " .
              "LEFT OUTER JOIN $default->users_table AS U ON FURL.user_id = U.id " .
              "LEFT OUTER JOIN $default->users_table AS U2 ON GFAL.user_id = U2.id " .              
              "WHERE D.id = ? " .
              "ORDER BY GFAL.precedence, role_name ASC", $oDocument->getID());
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
    /*ok*/ $sQuery = array("SELECT D.id as document_id, GFAL.id as id, R.name AS role_name, COALESCE(U.Name, 'Not assigned') AS name, GFAL.precedence AS precedence, COALESCE(FURL.active,0) AS active, COALESCE(FURL.done, 0) AS done " .
              "FROM $default->documents_table AS D INNER JOIN $default->groups_folders_approval_table AS GFAL ON D.folder_id = GFAL.folder_id " .
              "INNER JOIN $default->roles_table AS R ON GFAL.role_id = R.id " .
              "LEFT OUTER JOIN $default->folders_user_roles_table AS FURL ON FURL.group_folder_approval_id = GFAL.id AND FURL.document_id = D.id " .
              "LEFT OUTER JOIN $default->users_table AS U ON FURL.user_id = U.id " .
              "WHERE D.id = ? " .
              "ORDER BY GFAL.precedence, role_name ASC", $oDocument->getID());

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
	
	/*ok*/ $sQuery = array("SELECT D.id AS child_document_id, D.name, DL.id as document_link_id, " .
            "DL.parent_document_id AS parent_document_id, DLT.name AS link_type, 'Unlink' AS unlink " .
			  "FROM $default->documents_table AS D INNER JOIN $default->document_link_table AS DL ON D.id = DL.child_document_id " .
              "INNER JOIN $default->document_link_types_table AS DLT ON DL.link_type_id = DLT.id " .
			  "WHERE DL.parent_document_id = ?", $oDocument->getID());
	
	$aColumns = array("name", "link_type", "unlink");
    $aColumnHeaders = array(_("Document"), _("Document Link Type"));
    $aColumnTypes = array(3,3,3);
    $aDBColumnArray = array("parent_document_id", "child_document_id","document_link_id", "child_document_id");
    $aQueryStringVariableNames = array("fParentDocumentID","fChildDocumentID", "fDocumentLinkID", "fDocumentID");
    $aLinkURLs = array(0=>"$default->rootUrl/control.php?action=viewDocument", 2=>"$default->rootUrl/control.php?action=removeDocumentLink");

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
	
	/*ok*/ $sQuery = array("SELECT D.id, D.name " .
			  "FROM $default->documents_table AS D INNER JOIN $default->document_link_table AS DL ON D.id = DL.child_document_id " .
			  "WHERE DL.parent_document_id = ?", $oDocument->getID());
	
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
	/*ok*/ $sQuery = array("SELECT DDI.id AS instance_id, DDI.document_title, U.name AS user_name, 'Escalate' AS escalate " .
			  "FROM $default->dependant_document_instance_table DDI " . 
			  "INNER JOIN $default->users_table AS U ON DDI.user_id = U.id " .
			  "WHERE DDI.parent_document_id = ?", $oDocument->getID());
	
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

function renderDocumentVersions ($oDocument) {
    $aVersions = Document::getByLiveDocument($oDocument);
    $sToRender = "<table class=\"pretty\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
    $sToRender .= "<thead><tr><th>Metadata Version</th><th>Modified date</th><th>Document Version</th></tr></thead>\n";
    $sToRender .= "<tbody>\n";
    $iDocumentID = $oDocument->getID();
    $sLink = generateControllerLink('viewDocument', "fDocumentID=$iDocumentID");
    $sDocumentLink = generateControllerLink("downloadDocument", "fDocumentID=$iDocumentID");
    $sToRender .= sprintf('<tr><td><a href="%s">%s</a> (Live)</td><td>%s</td><td><a href="%s">%s</a></tr>%s', $sLink, $oDocument->getMetadataVersion(), $oDocument->getLastModifiedDate(), $sDocumentLink, $oDocument->getVersion(), "\n");
    foreach ($aVersions as $oVersion) {
        $iDocumentVersionID = $oVersion->getVersion();
        if ($iDocumentVersionID == $oDocument->getVersion()) {
            $sDocumentLink = generateControllerLink("downloadDocument", "fDocumentID=$iDocumentID");
        } else {
            $sDocumentLink = generateControllerLink("downloadDocument", "fDocumentID=$iDocumentID&fVersion=$iDocumentVersionID");
        }
        $sToRender .= "<tr>\n";
        $iVersionID = $oVersion->getID();
        $sLink = generateControllerLink('viewDocument', "fDocumentID=$iDocumentID&fVersionID=$iVersionID");
        $sToRender .= sprintf('<td><a href="%s">%s</a></td>%s', $sLink, $oVersion->getMetadataVersion(), "\n");
        $sToRender .= sprintf('<td>%s</td>%s', $oVersion->getLastModifiedDate(), "\n");
        $sToRender .= sprintf('<td><a href="%s">%s</td>%s', $sDocumentLink, $oVersion->getVersion(), "\n");
        $sToRender .= "</tr>\n";
    }
    $sToRender .= "</tbody>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

// This array exists only to give gettext a hint that these buttons
// should be added to the .po file.

$aTranslatedButtons = array(
    _("View"),
    _("Email"),
    _("Checkout"),
    _("Delete"),
    _("History"),
    _("Move"),
    _("Subscribe"),
    _("Unsubscribe"),
    _("Discussion"),
    _("Archive"),
    _("Link New Doc"),
    _("Publish"),
);


$aImageNameToLabel = array(
    "dependentdoc" => "Link new doc",
);

function displayButton($sAction, $sQueryString, $sImageName, $sDisabledText = "", $sJS = "") {
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
            $sTranslatedWords = _(ucwords($sLabel));
            $sLabel = strtoupper($sTranslatedWords);
            if ($bActive) {
                    $sClass = "button";
                    $sJS = $sJS . " onMouseOver=\"javascript:this.style.backgroundColor='#EEEEEE';\" onMouseOut=\"javascript:this.style.backgroundColor='#FFFFFF';\"";
            } else {
                    $sClass = "disabledbutton";
            }
            $sImage = "<span class=\"$sClass\" $sJS>$sLabel</span>";
        }
	if ($bActive) {
		return generateControllerLink($sAction, $sQueryString, $sImage);
	} else {
		return $sImage;
	}
}

/**
 * Displays document action buttons
 */

function displayActionButtons($oDocument, $bEdit) {
    require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
    $oTemplating =& KTTemplating::getSingleton();
    $oTemplate = $oTemplating->loadTemplate('ktcore/document_action');

    $sToRender = "";

    // XXX: Anonymous/Guest interaction
    $oUser =& User::get($_SESSION["userID"]);

    foreach (KTDocumentActionUtil::getDocumentActionsForDocument($oDocument, $oUser) as $oAction) {
        $aInfo = $oAction->getInfo();
        if (is_null($aInfo)) {
            continue;
        }
        $sToRender .= $oTemplate->render($aInfo);
    }

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
	$sToRender .= renderDocumentSection("metadataVersions", _("Metadata Versions"), $bDisplayLink, $oDocument->getID());

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
    $sToRender .= renderSectionDiv("metadataVersions", renderDocumentVersions($oDocument));

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
	$sToRender .= renderDocumentSection("metadataVersions", _("Document Versions"), $bDisplayLink, $oDocument->getID());
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
