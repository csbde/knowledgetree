<?php
/**
 * $Id$
 *
 * Presentation logic used to link one document to another.
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

function getPage($iDocumentID) {
	global $default;
	
	$sToRender = renderHeading(_("Document Linking"));
	$sToRender .= displayDocumentPath($iDocumentID);	
	$sToRender .= "<table>";
	$sToRender .= "<tr>";
	//had a slight problem with netscape 4.7x - it doesn't support disabled.  So I had to use
	//javascript to set the onFocus attribute to blur 
	if (!$default->bNN4) {
		//for any other browser but netscape 4.7 do this	
		$sToRender .= "<td>" . _("Document to link") . "</td><td><input type=\"text\" DISABLED name=\"fTargetDocument\" value=\"\" /><input type=\"button\" value=\"Browse\" onClick=\"newWindow('" . generateControllerUrl("templateBrowse") . "','window2')\"></td>\n";
	} else {
		//for netscape 4.7 do this
		$sToRender .= "<td>" . _("Document to link") . "</td><td><input type=\"text\"  value=\"\" name=\"fTargetDocument\" onblur=\"disable(document.MainForm.fTargetDocument);\" /><input type=\"button\" value=\"Browse\" onClick=\"disable(document.MainForm.fTargetDocument);newWindow('" . generateControllerUrl("templateBrowse") . "','window2')\"></td>\n";
	}		
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"" . KTHtml::getUpdateButton() . "\" border=\"0\"/></td><td><a href=\"" . $default->rootUrl . "/control.php?action=viewDocument&fDocumentID=$iDocumentID\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"/></a></td></tr></table>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<input type=\"hidden\" name=\"fTargetDocumentID\" value=\"-1\" />\n";	
	
	return $sToRender . "\n\n" . getValidationJavaScript() . "\n\n" . getBrowseJavaScript();
}

function getValidationJavaScript() {
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(theForm.fTargetDocument,'Document'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";
	return $sToRender;
}

function getBrowseJavaScript() {
	$sToRender = "<script language=\"JavaScript\"><!--\n ";
	$sToRender .= "function newWindow(file,window) {\n ";	
    $sToRender .= "\tmsgWindow=open(file,window,config='resizable=yes,scrollbars=yes,width=400,height=600');\n ";
    $sToRender .= "\tif (msgWindow.opener == null) msgWindow.opener = self; \n ";
    $sToRender .= "}\n";
    $sToRender .= "//--></script>\n";	
    return $sToRender;
}
?>
