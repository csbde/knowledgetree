<?php
/**
 * $Id$
 *
 * Presentation information used for folder editing.  Used by editBL.php
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 * 
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @date 2 February 2003
 * @package presentation.lookAndFeel.knowledgeTree.foldermanagement
 */

function getFolderPath($iFolderID) {
	global $default;
    global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
    return "<table border=\"0\" width=\"610\"><tr><td bgcolor=\"$sTDBGColour\">" . displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse") . "</td></tr></table>\n";
}

function getFolderData($iFolderID, $sDivName, $sStatusMessage = "") {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	$sQuery = "SELECT F.id, F.name, F.description, F.is_public " .
			"FROM $default->owl_folders_table AS F " .
			"WHERE F.id = $iFolderID";
			
	$sql = $default->db;
	$sql->query($sQuery);
	if ($sStatusMessage) {
		$sToRender .= "<font color=\"red\">$sStatusMessage</font>";
	}
	$sToRender .= "<table>\n";
	$sToRender .= "<caption><strong>Folder Data</strong></caption>";
	if ($sql->next_record()) {
		$sToRender .= "<tr>\n";
		$sToRender .= "<td bgcolor=\"" . $sTDBGColour . "\">Name</td>\n";
		if (Folder::folderIsUnitRootFolder($iFolderID)) {
			$sToRender .= "<td>" . $sql->f("name") . "</td>\n";
		} else {
			$sToRender .= "<td><input type=\"text\" name=\"fFolderName\" value=\"" . $sql->f("name") . "\" size=\"30\"></td>\n";
		}
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td bgcolor=\"" . $sTDBGColour . "\">Description</td>\n";
		$sToRender .= "<td><input type=\"text\" name=\"fDescription\" value=\"" . $sql->f("description") . "\" size=\"30\"></td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td bgcolor=\"" . $sTDBGColour . "\">Public</td>\n";
		$sToRender .= "<td><input type=\"checkbox\" name=\"fIsPublic\" " . ($sql->f("is_public") ? "CHECKED" : "") ." ></td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td></td>\n";
		$sToRender .= "</tr>\n";
		
	}
	$sToRender .= "<tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" onClick=\"setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fFolderID=$iFolderID&fForUpdate=1')\" border=\"0\" /></td></tr>\n";
	$sToRender .= "</table>\n";

    return renderSectionDiv($sDivName, $sToRender);		
}

function getFolderRouting($iFolderID, $sDivName, $bCollaboration) {
	global $default;
	//had to use coalesce and left outer join for user_id because this column was a new addition
	//and the user_ids for existing documents before this change will be null
	$sQuery = "SELECT GFAL.id as id, GFAL.folder_id AS folder_id, G.name AS group_name, G.id AS group_id, GFAL.role_id AS role_id, R.name AS role_name, GFAL.precedence AS precedence, COALESCE(U.id, -1) AS user_id, COALESCE(U.name, 'Not Assigned') AS user_name, 'Edit' as edit, 'Delete' as del, 'Dependant docs' as depn " .
				"FROM $default->owl_groups_folders_approval_table AS GFAL INNER JOIN roles AS R ON GFAL.role_id = R.id " .
				"INNER JOIN $default->owl_groups_table AS G ON G.id = GFAL.group_id " .
				"LEFT OUTER JOIN $default->owl_users_table AS U ON U.id = GFAL.user_id " .				 
				"WHERE GFAL.folder_id = $iFolderID " .
				"ORDER BY GFAL.precedence, role_name ASC";
	
	$aColumns = array("group_name", "role_name", "precedence", "user_name", "edit", "del", "depn");
	$aColumnHeaders = array("Group", "Role", "Seq", "User", "", "");
	$aColumnTypes = array(1,1,1,1,3,3,3);
	$aDBColumnArray = array("id","folder_id","group_id","user_id","precedence","role_id");
	$aQueryStringVariableNames = array("fFolderCollaborationID", "fFolderID", "fGroupID","fUserID","fSequenceNumber","fRoleID");
	$aLinkURLs = array(4=>"$default->rootUrl/control.php?action=modifyFolderCollaboration",5=>"$default->rootUrl/control.php?action=deleteFolderCollaboration", 6=>"$default->rootUrl/control.php?action=viewDependantDocument");
	
	$oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "610", $aLinkURLs, $aDBColumnArray,$aQueryStringVariableNames);
	$oPatternTableSqlQuery->setTableHeading("Document Routing");
	$oPatternTableSqlQuery->setDisplayColumnHeadings(true);
	
	$sToRender .= "<table>";
	$sToRender .= $oPatternTableSqlQuery->render();
    if (!$bCollaboration) {
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>\n";
        $sToRender .=  "<a href=\"$default->rootUrl/control.php?action=addFolderCollaboration&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\" /></a>\n";
        $sToRender .= "</td>\n";
        $sToRender .= "</tr>\n";
    }
    $sToRender .= "</table>";	
    return renderSectionDiv($sDivName, $sToRender);	
}

function getFolderDocTypes($iFolderID, $sDivName) {
	global $default;
	$sQuery = "SELECT FDL.id, FDL.folder_id, FDL.document_type_id, DTL.name, 'Remove' AS del " .
				"FROM $default->owl_folder_doctypes_table AS FDL INNER JOIN $default->owl_document_types_table AS DTL ON FDL.document_type_id = DTL.id " .
				"WHERE FDL.folder_id = $iFolderID " .
				"ORDER BY DTL.name ASC";
	
	$aColumns = array("name", "del");
	$aColumnHeaders = array("","");
	$aColumnTypes = array(1,3,3);
	$aDBColumnArray = array("id","folder_id");
	$aQueryStringVariableNames = array("fFolderDocTypeID", "fFolderID");
	$aLinkURLs = array(1=>"$default->rootUrl/control.php?action=deleteFolderDocType");
	
	$oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "610", $aLinkURLs, $aDBColumnArray,$aQueryStringVariableNames);
	$oPatternTableSqlQuery->setTableHeading("Document Types");
	$oPatternTableSqlQuery->setDisplayColumnHeadings(true);
	
	$sToRender .= "<table>";
	$sToRender .= $oPatternTableSqlQuery->render();
	$sToRender .=  "<tr><td><a href=\"$default->rootUrl/control.php?action=addFolderDocType&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\" /></a></td></tr>";
    $sToRender .= "</table>";		
    return renderSectionDiv($sDivName, $sToRender);	
}

function getFolderGroupAccess($iFolderID, $sDivName) {
	global $default;
	$sQuery = "SELECT GFL.id as id, GFL.group_id AS group_id, GFL.folder_id AS folder_id, GFL.can_read AS can_read, GFL.can_write AS can_write, GL.name AS group_name, 'Edit' as edit, 'Delete' as del " .	
			  "FROM $default->owl_groups_folders_table AS GFL " .
              "INNER JOIN $default->owl_groups_table AS GL ON GFL.group_id = GL.id " .
			  "WHERE GFL.folder_id = $iFolderID";
	
	$aColumns = array("group_name", "can_read", "can_write", "edit", "del");
	$aColumnHeaders = array("Group", "Read", "Write", "", "");
	$aColumnTypes = array(1,1,1,3,3);
	$aDBColumnArray = array("id","folder_id");
	$aQueryStringVariableNames = array("fGroupFolderLinkID", "fFolderID");
	$aLinkURLs = array(3=>"$default->rootUrl/control.php?action=modifyGroupFolderLink",4=>"$default->rootUrl/control.php?action=deleteGroupFolderLink");
	
	$oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "610", $aLinkURLs, $aDBColumnArray,$aQueryStringVariableNames);
	$oPatternTableSqlQuery->setTableHeading("Folder Permissions");
	$oPatternTableSqlQuery->setDisplayColumnHeadings(true);
   
	$sToRender .= "<table>";
	$sToRender .= $oPatternTableSqlQuery->render();
	$sToRender .=  "<tr><td><a href=\"$default->rootUrl/control.php?action=addGroupFolderLink&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\" /></a></td></tr>";
    $sToRender .= "</table>";		
    return renderSectionDiv($sDivName, $sToRender);		 	
}

function renderSectionDiv($sDivName, $sHtml) {
	global $default;
	
	if ( $default->browser == "ie" ) {
		return "<div id=\"$sDivName\" style=\"position:absolute;visibility:hidden;top:250px;left:135px;\">$sHtml</div>";
	} else {
		return "<div id=\"$sDivName\" style=\"position:absolute;visibility:hidden;top:230px;left:133px;\">$sHtml</div>";		
	}
}

function renderFolderSection($sHeading) {
	return "<tr bgcolor=\"" . getColour($iColour) . "\"><td width=\"100%\">$sHeading</td></tr>\n";
}

function getPage($iFolderID, $sStatusMessage = "", $bCollaboration = false) {
	global $default;
       
	if ( $default->browser == "ie" ) {
		$sToRender = "<div id=\"headings\" style=\"position:absolute;visibility:visible;top:120px;left:134px;\">";		
	} else {
		$sToRender = "<div id=\"headings\" style=\"position:absolute;visibility:visible;top:106px;left:134px;\">";
	}
    $sToRender .= renderHeading("Modify Folder Properties");
    $sToRender .= getFolderPath($iFolderID);    

	$sToRender .= "<table border=\"0\" width=\"610\">";		
	$sToRender .= renderFolderSection("<a href=\"javascript:void();\" onClick=\"switchDiv('folderData', 'folder');\">Folder Data</a>");
	$sToRender .= renderFolderSection("<a href=\"javascript:void();\" onClick=\"switchDiv('folderRouting', 'folder');\">Document Routing</a>");
	$sToRender .= renderFolderSection("<a href=\"javascript:void();\" onClick=\"switchDiv('documentTypes', 'folder');\">Document Types</a>");
	$sToRender .= renderFolderSection("<a href=\"javascript:void();\" onClick=\"switchDiv('folderPermissions', 'folder');\">Folder Permissions</a>");
	$sToRender .= "</table>";		
    $sToRender .= "</div>";
        
	$sToRender .= getFolderData($iFolderID, "folderData", $sStatusMessage);
	$sToRender .= getFolderRouting($iFolderID, "folderRouting", $bCollaboration);
	$sToRender .= getFolderDocTypes($iFolderID, "documentTypes");
	$sToRender .= getFolderGroupAccess($iFolderID, "folderPermissions");
	return $sToRender;
}

function getLeftSide($iFolderID, $bCollaboration) {
	global $default;
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .= getFolderData($iFolderID);
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";	
	$sToRender .= "<input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" onClick=\"setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fFolderID=$iFolderID&fForUpdate=1')\" border=\"0\" />\n";	
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .=  getFolderRouting($iFolderID) . "\n";
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
    if (!$bCollaboration) {
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>\n";
        $sToRender .=  "<a href=\"$default->rootUrl/control.php?action=addFolderCollaboration&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\" /></a>\n";
        $sToRender .= "</td>\n";
        $sToRender .= "</tr>\n";
    }
    
	$sToRender .= "</table>\n";
	return $sToRender;
}

function getRightSide($iFolderID) {
	global $default;
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .= getFolderDocTypes($iFolderID);
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .=  "<a href=\"$default->rootUrl/control.php?action=addFolderDocType&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\" /></a>\n";
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
    
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .= getFolderGroupAccess($iFolderID);
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .=  "<a href=\"$default->rootUrl/control.php?action=addGroupFolderLink&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\" /></a>\n";
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";

    
	$sToRender .= "</table>\n";
	return $sToRender;
}

function wrapInTable($sHtml) {
	return "\n\t\t\t<table border = 1, width = 100%><tr><td>$sHtml</td></tr></table>\n";	
}
?>
