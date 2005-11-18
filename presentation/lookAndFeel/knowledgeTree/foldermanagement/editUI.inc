<?php
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

require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');

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
	/*ok*/ $sQuery = array("SELECT F.id, F.name, F.description, F.is_public " .
			"FROM $default->folders_table AS F " .
			"WHERE F.id = ?", $iFolderID);
			
			
	$aColumns = array("name", "description", "is_public");
	$aColumnHeaders = array(_("Name"), _("Description"), _("Public"));
	$aColumnTypes = array(1,1,2);
	
	$oPatternListFromQuery = & new PatternListFromQuery($sQuery, $aColumns, $aColumnHeaders, $aColumnTypes);
	$oPatternListFromQuery->setTableHeading(_("Folder Data"));
	$oPatternListFromQuery->setTableWidth("610");
	
	$sToRender .= "<table>";
	if (strlen($sStatusMessage) > 0) {
		$sToRender .= "<tr><td><font color=\"red\">$sStatusMessage</font></td></tr>";
	}
	$sToRender .= "<tr><td>" . $oPatternListFromQuery->render() . "</td></tr>";	
	$sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=modifyFolderData&fFolderID=$iFolderID\"><img src=\"" . KTHtml::getEditButton() . "\" border=\"0\" /></a></td></tr>";
    $sToRender .= "</table>";;

	if ($sStatusMessage) {
		return $sToRender;
	} else {
    	return renderSectionDiv($sDivName, $sToRender);
	}
}

function getFolderDocTypes($iFolderID, $sDivName) {
    $sTable = KTUtil::getTableName('folder_doctypes');
    $aQuery = array(
        "SELECT document_type_id FROM $sTable WHERE folder_id = ?",
        array($iFolderID),
    );
    $aSelectedIds = DBUtil::getResultArrayKey($aQuery, 'document_type_id');

    $sToRender  = '<h2>Document Types</h2>';

    $oFolder =& Folder::get($iFolderID);
    if ($oFolder->getRestrictDocumentTypes()) {
        $sToRender .= '<h3>Document types are restricted to the following:</h3>';
        $sToRender .= '<ul>';
        foreach ($aSelectedIds as $iId) {
            $oDocumentType =& DocumentType::get($iId);
            $sToRender .= '<li>' . $oDocumentType->getName() . '</li>';
        }
        $sToRender .= '</ul>';
    } else {
        $sToRender .= '<h3>Document types are not restricted on this folder</h3>';
    }
    $sToRender .= sprintf(
        '<a href="%s/control.php?action=editFolderDocType&fFolderId=%d"><img src="%s" border="0" /></a>',
        $default->rootUrl, $iFolderID, KTHtml::getEditButton());
    
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

function getPage($iFolderID, $sStatusMessage) {
    global $default;
   	  		
	$sToRender = "<div id=\"headings\" style=\"position:relative;visibility:visible;top:2px;left:2px;\">";
    $sToRender .= renderHeading(_("Modify Folder Properties"));
    $sToRender .= getFolderPath($iFolderID);    
	$sToRender .= "<table border=0 cellspacing=\"0\" cellpadding=\"0\" width=\"610\">";
	$sToRender .= "<tr><td valign=\"top\">";	
	$sToRender .= "<table border=\"0\" width=\"100%\">";

	// if we have a status message, then make the section links refresh to viewDocument with the fShowSection variable
	// ie. effectively removes statusMessage on next click
	$bDisplayLink = ($sStatusMessage) ? true : false;

	$sToRender .= renderFolderSection("folderData", _("Folder Data"), $bDisplayLink, $iFolderID);
	$sToRender .= renderFolderSection("documentTypes", _("Document Types"), $bDisplayLink, $iFolderID);
	$sToRender .= "</table>";
	$sToRender .= "</td><td><table border=1>";
	$sToRender .= "</table></td></tr></table>";		
    $sToRender .= "</div>";

	// ugly netscape hacks
	if (!$default->bNN4) {
		$sToRender .= "<div id=\"contentDiv\" style=\"position:relative;visibility:hidden;width:500px;\">";
	}
	$sToRender .= getFolderData($iFolderID, "folderData");
	$sToRender .= getFolderDocTypes($iFolderID, "documentTypes");
	if (!$default->bNN4) {
		$sToRender .= "</div>";
	}    
    return $sToRender;	
}


function getStatusPage($iFolderID, $sStatusMessage) {
    global $default;
       
	$sToRender = "<div id=\"headings\" style=\"position:relative;visibility:visible;top:2px;left:2px;\">";       
    $sToRender .= renderHeading(_("Modify Folder Properties"));
    $sToRender .= getFolderPath($iFolderID);
    $sToRender .= "<table border=\"0\" width=\"610\">";

	// if we have a status message, then make the section links refresh to viewDocument with the fShowSection variable
	// ie. effectively removes statusMessage on next click
	$bDisplayLink = ($sStatusMessage) ? true : false;

	$sToRender .= renderFolderSection("folderData", _("Folder Data"), $bDisplayLink, $iFolderID);
	$sToRender .= renderFolderSection("documentTypes", _("Document Types"), $bDisplayLink, $iFolderID);
	$sToRender .= renderFolderSection("folderPermissions", _("Folder Permissions"), $bDisplayLink, $iFolderID);

	$sToRender .= "</table>";
	$sToRender .= "</div>";
	$sToRender .= getFolderData($iFolderID, "folderData", $sStatusMessage);
	return $sToRender;
}
?>
