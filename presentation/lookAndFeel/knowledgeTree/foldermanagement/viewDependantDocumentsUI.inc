<?php
/**
 * $Id$
 *
 * Dependent documents UI functions.
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
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package foldermanagement
 */

function getFolderPath($iFolderID) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	$sFolderPathLink = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse");	    
	return "<table border=\"0\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sFolderPathLink</td></tr></table>\n";    
}

function getDocumentLinkFolderCollaborationData($iFolderCollaborationID) {
	global $default;	
	$sQuery = "SELECT DDT.id, GFAL.folder_id AS folder_id, U.name AS username, DDT.document_title, D.name AS template_document_name, D.id AS template_document_id, DDT.group_folder_approval_link_id, 'Edit' AS edit, 'Delete' AS del " .
				"FROM dependant_document_template AS DDT INNER JOIN groups_folders_approval_link AS GFAL ON DDT.group_folder_approval_link_id = GFAL.id " .
				"INNER JOIN users AS U ON U.id = DDT.default_user_id " .
				"LEFT OUTER JOIN documents AS D ON D.id = DDT.template_document_id " . 
				"WHERE DDT.group_folder_approval_link_id = $iFolderCollaborationID";
				
	//echo $sQuery;			
	$aColumns = array("document_title", "username", "template_document_name", "edit", "del");
	$aColumnHeaders = array(_("Document title"), _("Default creator"), _("Template document"));
	$aColumnTypes = array(1,1,3,3,3);
	//$aDBColumnArray = array("id","group_folder_approval_link_id","template_document_id");
	$aDBColumnArray = array("id","group_folder_approval_link_id","folder_id");
	//$aQueryStringVariableNames = array("fDependantDocumentTemplateID", "fFolderCollaborationID", "fTargetDocumentID");
	$aQueryStringVariableNames = array("fDependantDocumentTemplateID","fFolderCollaborationID","fFolderID");
	$aLinkURLs = array(2=>"$default->rootUrl/control.php?action=viewDocument", 3=>"$default->rootUrl/control.php?action=viewDependantDocument&fForEdit=1", 4=>"$default->rootUrl/control.php?action=deleteDependantDocument");	
	
	$oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "90%", $aLinkURLs, $aDBColumnArray,$aQueryStringVariableNames);
	$oPatternTableSqlQuery->setTableHeading(_("Dependant Documents"));
	$oPatternTableSqlQuery->setDisplayColumnHeadings(true);
	$oPatternTableSqlQuery->setEmptyTableMessage(_("No dependant documents"));
	
	return $oPatternTableSqlQuery->render();
	

}

function getViewPage($iFolderCollaborationID, $iFolderID) {
	global $default;	
	$sToRender = renderHeading(_("Dependant documents"));
    $sToRender .= getFolderPath($iFolderID);
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";	
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>" . getDocumentLinkFolderCollaborationData($iFolderCollaborationID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\"/></td><td>" . generateControllerLink("editFolder", "fFolderID=$iFolderID&fShowSection=folderRouting", "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/>") . "</td></tr></table>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";	
	
	return $sToRender;
}

function getEditPage($iFolderID, $iDependantDocumentID, $iFolderCollaborationID, $sDocumentTitle, $sTemplateDocumentName, $iDocumentID, $iUserID) {
	global $default;
	$iDefaultUserUnitID = User::getUnitID($iUserID);
	
	$sToRender = renderHeading(_("Dependant documents"));
    $sToRender .= getFolderPath($iFolderID);    
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";	
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>" . _("Document Title") . "</td><td><input type=\"text\" name=\"fDocumentTitle\" value=\"$sDocumentTitle\" /></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("User's Unit") . "</td><td>" . getUnitDropDown($iFolderCollaborationID, $iFolderID, $iDefaultUserUnitID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("User") . "</td><td>" . getUserDropDown($iDefaultUserUnitID, $iUserID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";

	//had a slight problem with netscape 4.7x - it doesn't support disabled.  So I had to use
	//javascript to set the onFocus attribute to blur 
	if (!$default->bNN4) {
		//for any other browser but netscape 4.7 do this	
		$sToRender .= "<td>" . _("Template document") . "</td><td><input type=\"text\" DISABLED name=\"fTargetDocument\" value=\"$sTemplateDocumentName\" /><input type=\"button\" value=\"Browse\" onClick=\"newWindow('" . generateControllerLink("templateBrowse", "") . "','window2')\"></td>\n";
	} else {
		//for netscape 4.7 do this
		$sToRender .= "<td>" . _("Template document") . "</td><td><input type=\"text\"  value=\"$sTemplateDocumentName\" name=\"fTargetDocument\" onblur=\"disable(document.MainForm.fTargetDocument);\" /><input type=\"button\" value=\"Browse\" onClick=\"disable(document.MainForm.fTargetDocument);newWindow('http://ktdev/control.php?action=templateBrowse','window2')\"></td>\n";
	}		
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" border=\"0\"/></td><td><a href=\"" . $default->rootUrl . "/control.php?action=viewDependantDocument&fFolderID=$iFolderID&fFolderCollaborationID=$iFolderCollaborationID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/></a></td></tr></table>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<input type=\"hidden\" name=\"fTargetDocumentID\" value=\"-1\" />\n";	
	
	return $sToRender . "\n\n" . getValidationJavaScript() . "\n\n" . getBrowseJavaScript();
}


function getAddPage($iFolderCollaborationID, $iFolderID, $iUnitID, $sDocumentTitle, $sTemplateDocument, $iDocumentID) {
	global $default;
	$sToRender = renderHeading(_("Dependant documents"));
    $sToRender .= getFolderPath($iFolderID);    
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";	
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>" . _("Document Title") . "</td><td><input type=\"text\" name=\"fDocumentTitle\" value=\"$sDocumentTitle\" /></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("User's Unit") . "</td><td>" . getUnitDropDown($iFolderCollaborationID, $iFolderID, $iUnitID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("User") . "</td><td>" . getUserDropDown($iUnitID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";

	//had a slight problem with netscape 4.7x - it doesn't support disabled.  So I had to use
	//javascript to set the onFocus attribute to blur 
	if (!$default->bNN4) {
		//for any other browser but netscape 4.7 do this	
		$sToRender .= "<td>" . _("Template document") . "</td><td><input type=\"text\" DISABLED name=\"fTargetDocument\" value=\"$sTemplateDocument\" /><input type=\"button\" value=\"Browse\" onClick=\"newWindow('$default->rootUrl/control.php?action=templateBrowse','window2')\"></td>\n";
	} else {
		//for netscape 4.7 do this
		$sToRender .= "<td>" . _("Template document") . "</td><td><input type=\"text\"  name=\"fTargetDocument\" onblur=\"disable(document.MainForm.fTargetDocument);\" /><input type=\"button\" value=\"Browse\" onClick=\"disable(document.MainForm.fTargetDocument);newWindow('$default->rootUrl/control.php?action=templateBrowse','window2')\"></td>\n";
	}		
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/done.gif\" border=\"0\"/></td><td><a href=\"" . $default->rootUrl . "/control.php?action=viewDependantDocument&fFolderID=$iFolderID&fFolderCollaborationID=$iFolderCollaborationID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/></a></td></tr></table>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<input type=\"hidden\" name=\"fTargetDocumentID\" value=\"-1\" />\n";	
	
	return $sToRender . "\n\n" . getValidationJavaScript() . "\n\n" . getBrowseJavaScript();
}

function getUserDropDown($iUnitID, $iUserID = null) {
	global $default;	
	$oPatternListBox = & new PatternListBox($default->users_table, "Name", "id", "fUserID", "GUL.unit_id = $iUnitID");
	$sFromClause = "INNER join $default->users_groups_table AS UGL ON UGL.user_id = ST.id " .
					"INNER JOIN groups_units_link AS GUL ON GUL.group_id = UGL.group_id ";
	$oPatternListBox->setFromClause($sFromClause);
	if (isset($iUserID)) {
		$oPatternListBox->setSelectedValue($iUserID);
	}
	return $oPatternListBox->render();
	
	
}

function getUnitDropDown($iFolderCollaborationID, $iFolderID, $iUnitID = null) {
	global $default;	
	$oPatternListBox = & new PatternListBox($default->units_table, "Name", "id", "fUnitID");	
	$oPatternListBox->setPostBackOnChange(true);
	$oPatternListBox->setOnChangeAction("setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fFolderID=$iFolderID&fFolderCollaborationID=$iFolderCollaborationID&fForAdd=1')");
	if (isset($iUnitID)) {
		$oPatternListBox->setSelectedValue($iUnitID);
	}
	return $oPatternListBox->render();
}

function getValidationJavaScript() {
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(theForm.fDocumentTitle,'" . _("Document Title") . "') && validRequired(theForm.fUserID,'" . _("User") . "'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";
	return $sToRender;
}

function getBrowseJavaScript() {
	$sToRender = "<script language=\"JavaScript\"><!--\n ";
	$sToRender .= "function newWindow(file,window) {\n ";	
    $sToRender .= "\tmsgWindow=open(file,window,'resizable=yes,scrollbars=yes, width=400,height=600');\n ";
    $sToRender .= "\tif (msgWindow.opener == null) msgWindow.opener = self; \n ";
    $sToRender .= "}\n";
    $sToRender .= "//--></script>\n";	
    return $sToRender;
}
?>
