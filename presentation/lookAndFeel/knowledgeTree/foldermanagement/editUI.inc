<?php
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
/**
 * $Id$
 *
 * Presentation information used for folder editing.  Used by editBL.php.
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
 * @package foldermanagement
 */

function getFolderPath($iFolderID) {
	global $default;
    global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
    return "<table border=\"0\" cellpadding=\"5\" width=\"610\"><tr><td bgcolor=\"$sTDBGColour\">" . displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse") . "</td></tr></table>\n";
}

function getFolderData($iFolderID, $sDivName, $sStatusMessage = "") {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	$sQuery = "SELECT F.id, F.name, F.description, F.is_public " .
			"FROM $default->folders_table AS F " .
			"WHERE F.id = $iFolderID";
			
			
	$aColumns = array("name", "description", "is_public");
	$aColumnHeaders = array("Name", "Description", "Public");
	$aColumnTypes = array(1,1,2);
	
	$oPatternListFromQuery = & new PatternListFromQuery($sQuery, $aColumns, $aColumnHeaders, $aColumnTypes);
	$oPatternListFromQuery->setTableHeading("Folder Data");
	$oPatternListFromQuery->setTableWidth("610");
	
	$sToRender .= "<table>";
	if (strlen($sStatusMessage) > 0) {
		$sToRender .= "<tr><td><font color=\"red\">$sStatusMessage</font></td></tr>";
	}
	$sToRender .= "<tr><td>" . $oPatternListFromQuery->render() . "</td></tr>";	
	$sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=modifyFolderData&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/edit.gif\" border=\"0\" /></a></td></tr>";
    $sToRender .= "</table>";;

	if ($sStatusMessage) {
		return $sToRender;
	} else {
    	return renderSectionDiv($sDivName, $sToRender);
	}
}

function getFolderRouting($iFolderID, $sDivName, $bCollaboration) {
	global $default;
	//had to use coalesce and left outer join for user_id because this column was a new addition
	//and the user_ids for existing documents before this change will be null
	$sQuery = "SELECT GFAL.id as id, GFAL.folder_id AS folder_id, COALESCE(G.name, 'Not Assigned') AS group_name, G.id AS group_id, GFAL.role_id AS role_id, R.name AS role_name, GFAL.precedence AS precedence, COALESCE(U.id, -1) AS user_id, COALESCE(U.name, 'Not Assigned') AS user_name, 'Edit' as edit, 'Delete' as del, 'Dependant docs' as depn " .
			  "FROM $default->groups_folders_approval_table AS GFAL INNER JOIN roles AS R ON GFAL.role_id = R.id " .
			  "LEFT OUTER JOIN $default->groups_table AS G ON G.id = GFAL.group_id " .
			  "LEFT OUTER JOIN $default->users_table AS U ON U.id = GFAL.user_id " .				 
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
	if ($bCollaboration) {
		$sToRender .= "<tr><td><font color=\"red\">Documents in this folder are currently undergoing this collaboration process, so these steps can't be altered</font></td></tr>";
	}
	$sToRender .= "<tr><td>" . $oPatternTableSqlQuery->render() . "</td></tr>";
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
				"FROM $default->folder_doctypes_table AS FDL INNER JOIN $default->document_types_table AS DTL ON FDL.document_type_id = DTL.id " .
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
	$sToRender .= "<tr><td>" . $oPatternTableSqlQuery->render() . "</td></tr>";	
	$sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=addFolderDocType&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\" /></a></td></tr>";
    $sToRender .= "</table>";		
    return renderSectionDiv($sDivName, $sToRender);	
}

function getFolderGroupAccess($iFolderID, $sDivName) {
	global $default;
	$sQuery = "SELECT GFL.id as id, GFL.group_id AS group_id, GFL.folder_id AS folder_id, GFL.can_read AS can_read, GFL.can_write AS can_write, GL.name AS group_name, 'Edit' as edit, 'Delete' as del " .	
			  "FROM $default->groups_folders_table AS GFL " .
              "INNER JOIN $default->groups_table AS GL ON GFL.group_id = GL.id " .
			  "WHERE GFL.folder_id = $iFolderID";
	
	$aColumns = array("group_name", "can_read", "can_write", "edit", "del");
	$aColumnHeaders = array("Group", "Read", "Write", "", "");
	$aColumnTypes = array(1,2,2,3,3);
	$aDBColumnArray = array("id","folder_id");
	$aQueryStringVariableNames = array("fGroupFolderLinkID", "fFolderID");
	$aLinkURLs = array(3=>"$default->rootUrl/control.php?action=modifyGroupFolderLink",4=>"$default->rootUrl/control.php?action=deleteGroupFolderLink");
	
	$oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "610", $aLinkURLs, $aDBColumnArray,$aQueryStringVariableNames);
	$oPatternTableSqlQuery->setTableHeading("Folder Permissions");
	$oPatternTableSqlQuery->setDisplayColumnHeadings(true);
   
	$sToRender .= "<table>";
	$sToRender .= "<tr><td>" . $oPatternTableSqlQuery->render() . "</td></tr>";
	$sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=addGroupFolderLink&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\" /></a></td></tr>";
    $sToRender .= "</table>";		
    return renderSectionDiv($sDivName, $sToRender);		 	
}


function renderSectionDiv($sDivName, $sHtml) {
	global $default;
	if ($default->bNN4) {	
		return "<div id=\"$sDivName\" style=\"position:absolute;visibility:hidden;top:250px;left:130px;\">" .  $sHtml . " </div>";
	} else {
		return "<div id=\"$sDivName\" style=\"position:absolute;visibility:hidden;left:5px;\">$sHtml</div>";
	}		
}

function renderFolderSection($sSectionName, $sHeadingText, $bDisplayLink, $iFolderID) {	
	if ($bDisplayLink) {
		$sLink = generateControllerLink("editFolder", "fFolderID=$iFolderID&fShowSection=$sSectionName", $sHeadingText);
	} else {
		$sLink = "<a href=\"#\" onClick=\"switchDiv('$sSectionName', 'folder');\">$sHeadingText</a>";
	}
	return "<tr bgcolor=\"" . getColour($iColour) . "\"><td width=\"100%\">$sLink</td></tr>\n";
}

function getPage($iFolderID, $sStatusMessage, $bCollaboration = false) {
    global $default;
   	  		
	$sToRender = "<div id=\"headings\" style=\"position:relative;visibility:visible;top:2px;left:2px;\">";
    $sToRender .= renderHeading("Modify Folder Properties");
    $sToRender .= getFolderPath($iFolderID);    
	$sToRender .= "<table border=0 cellspacing=\"0\" cellpadding=\"0\" width=\"610\">";
	$sToRender .= "<tr><td valign=\"top\">";	
	$sToRender .= "<table border=\"0\" width=\"100%\">";

	// if we have a status message, then make the section links refresh to viewDocument with the fShowSection variable
	// ie. effectively removes statusMessage on next click
	$bDisplayLink = ($sStatusMessage) ? true : false;

	$sToRender .= renderFolderSection("folderData", "Folder Data", $bDisplayLink, $iFolderID);
	$sToRender .= renderFolderSection("folderRouting", "Document Routing", $bDisplayLink, $iFolderID);
	$sToRender .= renderFolderSection("documentTypes", "Document Types", $bDisplayLink, $iFolderID);
	$sToRender .= renderFolderSection("folderPermissions", "Folder Permissions", $bDisplayLink, $iFolderID);
	$sToRender .= "</table>";
	$sToRender .= "</td><td><table border=1>";
	$sToRender .= "</table></td></tr></table>";		
    $sToRender .= "</div>";

	// ugly netscape hacks
	if (!$default->bNN4) {
		$sToRender .= "<div id=\"contentDiv\" style=\"position:relative;visibility:hidden;width:500px;\">";
	}
	$sToRender .= getFolderData($iFolderID, "folderData");
	$sToRender .= getFolderRouting($iFolderID, "folderRouting", $bCollaboration);
	$sToRender .= getFolderDocTypes($iFolderID, "documentTypes");
	$sToRender .= getFolderGroupAccess($iFolderID, "folderPermissions");
	if (!$default->bNN4) {
		$sToRender .= "</div>";
	}    
    return $sToRender;	
}


function getStatusPage($iFolderID, $sStatusMessage) {
    global $default;
       
	$sToRender = "<div id=\"headings\" style=\"position:relative;visibility:visible;top:2px;left:2px;\">";       
    $sToRender .= renderHeading("Modify Folder Properties");
    $sToRender .= getFolderPath($iFolderID);
    $sToRender .= "<table border=\"0\" width=\"610\">";

	// if we have a status message, then make the section links refresh to viewDocument with the fShowSection variable
	// ie. effectively removes statusMessage on next click
	$bDisplayLink = ($sStatusMessage) ? true : false;

	$sToRender .= renderFolderSection("folderData", "Folder Data", $bDisplayLink, $iFolderID);
	$sToRender .= renderFolderSection("folderRouting", "Document Routing", $bDisplayLink, $iFolderID);
	$sToRender .= renderFolderSection("documentTypes", "Document Types", $bDisplayLink, $iFolderID);
	$sToRender .= renderFolderSection("folderPermissions", "Folder Permissions", $bDisplayLink, $iFolderID);

	$sToRender .= "</table>";
	$sToRender .= "</div>";
	$sToRender .= getFolderData($iFolderID, "folderData", $sStatusMessage);
	return $sToRender;
}
?>