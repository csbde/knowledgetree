<?php
/**
* Presentation logic used to link one document to another
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 22 May 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*/


function renderHeading($sHeading) {
    global $default;
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender  = "<table border=\"0\" width=\"100%\">\n";
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">$sHeading</font></th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

function getPage($iDocumentID) {
	global $default;
	
	$sToRender = renderHeading("Document Linking");
	$sToRender .= "<table>";
	$sToRender .= "<tr>";
	//had a slight problem with netscape 4.7x - it doesn't support disabled.  So I had to use
	//javascript to set the onFocus attribute to blur 
	$bAddNetscapeScript = false;
	if (! (($default->phpSniff->property("browser") == "moz") && ($default->phpSniff->property("version") <= "4.79")) ) {
		//for any other browser but netscape 4.7 do this	
		$sToRender .= "<td>Document to link</td><td><input type=\"text\" DISABLED name=\"fTargetDocument\" value=\"\" /><input type=\"button\" value=\"Browse\" onClick=\"newWindow('" . generateControllerUrl("templateBrowse") . "','window2')\"></td>\n";
	}else {
		//for netscape 4.7 do this
		$sToRender .= "<td>Document to link</td><td><input type=\"text\"  value=\"\" name=\"fTargetDocument\" onblur=\"disable(document.MainForm.fTargetDocument);\" /><input type=\"button\" value=\"Browse\" onClick=\"disable(document.MainForm.fTargetDocument);newWindow('" . generateControllerUrl("templateBrowse") . "','window2')\"></td>\n";
		$bAddNetscapeScript = true;
	}		
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" border=\"0\"/></td><td><a href=\"" . $default->rootUrl . "/control.php?action=viewDocument&fDocumentID=$iDocumentID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/></a></td></tr></table>\n";	
	//$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" border=\"0\"/></td><td><a href=\"" . $_SERVER["PHP_SELF"] . "?fDocumentID=$iDocumentID&fForStore=1\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/></a></td></tr></table>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<input type=\"hidden\" name=\"fTargetDocumentID\" value=\"-1\" />\n";	
	
	return $sToRender . "\n\n" . getValidationJavaScript() . "\n\n" . getBrowseJavaScript() . ($bAddNetscapeScript ? getNetscapeDisableScript() : "");
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
    $sToRender .= "\tmsgWindow=open(file,window,'resizable=yes,scrollbars=yes, width=400,height=600');\n ";
    $sToRender .= "\tif (msgWindow.opener == null) msgWindow.opener = self; \n ";
    $sToRender .= "}\n";
    $sToRender .= "//--></script>\n";	
    return $sToRender;
}

function getNetscapeDisableScript() {
	$sToRender = "<script language=\"JavaScript\"><!--\n ";
	$sToRender .= "function disable(elem) {\n";
  	$sToRender .= "\telem.onfocus=elem.blur;\n";
	$sToRender .= "}\n";
	$sToRender .= "//--></script>\n\n";
	return $sToRender;
	
}


?>