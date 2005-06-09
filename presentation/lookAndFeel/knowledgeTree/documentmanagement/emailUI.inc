<?php
/**
 * $Id$
 *
 * Business logic to email a link to a document to a colleague.
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

function getDocumentPath($oDocument) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");	    
	$sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($oDocument->getFolderID()), Folder::getFolderPathNamesAsArray($oDocument->getFolderID()), "$default->rootUrl/control.php?action=browse") . " > " . $oDocument->getName();
	return "<table border=\"0\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sDocumentPath</td></tr></table>\n";	
}

function renderGroupPicker() {	
	
	$sToRender .= "<tr><td valign=\"top\" colspan=\"3\"><strong>" . _("Groups") . "</strong></td></tr>";
	$sToRender .= "<tr><td valign=\"top\" width=1%>\n";	
	$sToRender .= "<select name=\"groupSelect\" size=\"4\" multiple>\n";
	// retrieve all groups
	$aGroups = Group::getList("ORDER BY name");/*ok*/
	for ($i = 0; $i < count($aGroups); $i++) {	
		$sToRender .= "<OPTION value=\"" . $aGroups[$i]->getID() . "\" onDblClick=\"optGroup.transferRight()\" >\n";	
		$sToRender .= $aGroups[$i]->getName();
		$sToRender .= "</OPTION>\n";
	}	
	$sToRender .= "</select></td>";
	$sToRender .= "<td width=1%>";
	$sToRender .= "<input TYPE=\"button\" NAME=\"right\" VALUE=\"&gt;&gt;\" ONCLICK=\"optGroup.transferRight()\">";
	$sToRender .= "<input TYPE=\"button\" NAME=\"left\" VALUE=\"&lt;&lt;\" ONCLICK=\"optGroup.transferLeft()\">";	
	$sToRender .= "</td>";	
	$sToRender .= "<td><select name=\"chosenGroups\" size=\"5\" multiple>";
	$sToRender .= "<option>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>";
	$sToRender .= "</select></td>";
	$sToRender .= "</tr>\n";
	return $sToRender;
}

function renderUserPicker() {
	$sToRender .= "<tr><td colspan=\"3\"><strong>" . _("Users") . "</strong></td><tr>\n";
	$sToRender .= "<tr><td><select name=\"userSelect\" size=\"4\" multiple>\n";
	$oUserArray = User::getList("ORDER BY name");/*ok*/
	for ($i = 0; $i < count($oUserArray); $i++) {
		if ((strlen($oUserArray[$i]->getEmail())>0) && $oUserArray[$i]->getEmailNotification()) {
			$sToRender .= "<OPTION value=\"" . $oUserArray[$i]->getID() . "\" onDblClick=\"optUser.transferRight()\">\n";
			$sToRender .= $oUserArray[$i]->getName();
			$sToRender .= "</OPTION>\n";
		}
	}	
	$sToRender .= "</select></td>";
	$sToRender .= "<td>";
	$sToRender .= "<input TYPE=\"button\" NAME=\"right\" VALUE=\"&gt;&gt;\" ONCLICK=\"optUser.transferRight()\">";
	$sToRender .= "<input TYPE=\"button\" NAME=\"left\" VALUE=\"&lt;&lt;\" ONCLICK=\"optUser.transferLeft()\">";	
	$sToRender .= "</td>";	
	$sToRender .= "<td width=\"30%\"><select name=\"chosenUsers\" size=\"5\" multiple>";
	$sToRender .= "<option>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>";
	$sToRender .= "</select></td>";
	$sToRender .= "</tr>\n";

	return $sToRender;
}

function getDocumentEmailPage($oDocument) {
	global $default;
    $sToRender = renderHeading(_("Email Document Link"));
	$sToRender .= getDocumentPath($oDocument) . "\n<br>\n";
	$sToRender .= "<table width=\"100%\">\n";
	$sToRender .= "<tr><td><table>\n";	
	$sToRender .= renderGroupPicker();
	$sToRender .= renderUserPicker();	
	$sToRender .= "</table></td></tr>";
	$sToRender .= "<tr><td><table>\n";
	$sToRender .= "<tr><td valign=\"top\">" . _("Attach Document") . "</td><td><input type=\"checkbox\" name=\"fAttachDocument\" /></td></tr>";//</tr>\n";
	$sToRender .= "<tr><td valign=\"top\">" . _("Email Addresses") . "</td><td><textarea rows=\"5\" cols=\"30\" name=\"fEmailAddresses\"></textarea></td>";//</tr>\n";
	$sToRender .= "<tr><td valign=\"top\">" . _("Comment") . "</td><td><textarea rows=\"5\" cols=\"30\" name=\"fComment\"></textarea></td>";//</tr>\n";
	$sToRender .= "<td valign=\"bottom\"><table><tr><td><input type=\"image\" src=\"" . KTHtml::getEmailButton() . "\" border=\"0\" /></td><td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"/></a></td></tr></table></td></tr>\n";
	$sToRender .= "</table></td></tr>\n";
	$sToRender .= "</table>";
	$sToRender .= "<input type=\"hidden\" name=\"groupNewLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"groupNewRight\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"groupRemovedLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"groupRemovedRight\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"groupAddedLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"groupAddedRight\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"userNewLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"userNewRight\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"userRemovedLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"userRemovedRight\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"userAddedLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"userAddedRight\"/><br>";	
	
	$sToRender .= "<script language='javascript'>\n";
	$sToRender .= "function validateForm(theForm) {\n";
	$sToRender .= "\tif (isBlank(theForm.groupNewRight) && isBlank(theForm.userNewRight) && isBlank(theForm.fEmailAddresses)) {\n";
	$sToRender .= "\t\talert(\"" . _("Please choose either at least one Group, User, or Email address to email this document.") . "\");return false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "</script >\n";
	return $sToRender;
}
	
?>
