<?php
/**
 * $Id$
 *
 * Presentation logic used to link one document to another
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */

function getPage($iDocumentID) {
	global $default;
	
	$sToRender = renderHeading("Document Linking");
	$sToRender .= displayDocumentPath($iDocumentID);	
	$sToRender .= "<table>";
	$sToRender .= "<tr>";
	//had a slight problem with netscape 4.7x - it doesn't support disabled.  So I had to use
	//javascript to set the onFocus attribute to blur 
	if (!$default->bNN4) {
		//for any other browser but netscape 4.7 do this	
		$sToRender .= "<td>Document to link</td><td><input type=\"text\" DISABLED name=\"fTargetDocument\" value=\"\" /><input type=\"button\" value=\"Browse\" onClick=\"newWindow('" . generateControllerUrl("templateBrowse") . "','window2')\"></td>\n";
	} else {
		//for netscape 4.7 do this
		$sToRender .= "<td>Document to link</td><td><input type=\"text\"  value=\"\" name=\"fTargetDocument\" onblur=\"disable(document.MainForm.fTargetDocument);\" /><input type=\"button\" value=\"Browse\" onClick=\"disable(document.MainForm.fTargetDocument);newWindow('" . generateControllerUrl("templateBrowse") . "','window2')\"></td>\n";
	}		
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" border=\"0\"/></td><td><a href=\"" . $default->rootUrl . "/control.php?action=viewDocument&fDocumentID=$iDocumentID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/></a></td></tr></table>\n";	
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