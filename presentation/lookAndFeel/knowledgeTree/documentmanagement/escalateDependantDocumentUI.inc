<?php
/**
 * $Id$
 *
 * UI functions for sending a reminder message to the user that was tasked with
 * creating a dependant document.
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
 * @author Michael Joseph, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */

function getPage($oDependantDocument) {
	global $default;
	$sToRender = renderHeading(_("Dependant Document Send Escalation Message"));
	$oDocument = Document::get($oDependantDocument->getParentDocumentID());
	$iFolderID =  $oDocument->getFolderID();
	$sToRender .= renderFolderPath($iFolderID, generateControllerUrl("browse", "fFolderID=$iFolderID"), false);
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";	
	$sToRender .= "<tr><td colspan=\"2\"><strong>" . _("You have requested that the following document be created:") . "</strong></td></tr>";	
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>" . _("Document title") . "</td><td>" . $oDependantDocument->getDocumentTitle() . "</td>\n";
	$sToRender .= "</tr>\n";
	
	$oUser = User::get($oDependantDocument->getUserID());
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>User</td><td>" . ($oUser ? $oUser->getName() : "<font color=\"red\">" . _("Error! No user specified") . "</font>") . "</td>\n";
	$sToRender .= "</tr>\n";

	$oTemplateDocument = Document::get($oDependantDocument->getTemplateDocumentID());
	$sToRender .= "<tr><td>" . _("Template document") . "</td><td>" . ($oTemplateDocument ? $oTemplateDocument->getDisplayPath() : _("No template document")) . "</td></tr>\n";
	$sToRender .= "<tr><td>&nbsp;</td></tr>";
	
	$sToRender .= "<tr><td colspan=\"2\">" . _("To send the user a reminder message, fill in the text box below and click 'Done'") . "</td></tr>";
	$sToRender .= "<tr><td>" . _("Reminder Message") . "</td><td><textarea rows=\"5\" cols=\"30\" name=\"fReminderMessage\"></textarea></td></tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/done.gif\" onClick='return validateForm(document.MainForm);' border=\"0\"/></td>";
	$sToRender .= "<td>" . generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID() . "&fShowSection=linkedDocuments", "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/>") . "</td></tr></table>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<input type=\"hidden\" name=\"fSendMessage\" value=\"1\" />\n";	
	$sToRender .= "<input type=\"hidden\" name=\"fInstanceID\" value=\"" . $oDependantDocument->getID() . "\" />\n";
	
	return $sToRender . "\n\n" . getValidationJavaScript();
}

function getValidationJavaScript() {
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(theForm.fReminderMessage,'Reminder message'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";
	return $sToRender;
}
?>
