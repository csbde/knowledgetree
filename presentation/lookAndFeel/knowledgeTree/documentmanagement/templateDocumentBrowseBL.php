<?php

require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/browse/BrowserFactory.inc");
require_once("$default->fileSystemRoot/lib/browse/Browser.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentType.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->uiDirectory/documentmanagement/templateDocumentBrowseUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
/**
 * $Id$
 *
 * This page very closely follows browseBL.php.  This page is ONLY used when a user
 * browses for a template document when setting up document linking in folder 
 * collaboration.  This page is launched as a separate window by javascript.  The
 * user browses for the document that will serve as a template and then selects it.
 * This causes this window to close and set the template document value in the
 * the parent window.
 *
 * The main difference between this file and browseBL.php is the way the document
 * links are generated.  When clicking on a document link, instead of being taken
 * to the document, the document values are sent to the parent window and the 
 * window is closed
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

// only if we have a valid session
if (checkSession()) {		
    require_once("../../../../phpSniff/phpTimer.class.php");
    $timer = new phpTimer();
    $timer->start();
    
    // retrieve variables
    if (!$fBrowseType) {
        // required param not set- internal error or user querystring hacking
        // set it to default= folder
        $fBrowseType = "folder";
    }
    
    // retrieve field to sort by
    if (!$fSortBy) {
    	// no sort field specified- default is document name
    	$fSortBy = "name";
    }
    // retrieve sort direction
    if (!$fSortDirection) {
    	$fSortDirection = "asc";
    }
       
    // fire up the document browser 
    $oBrowser = BrowserFactory::create($fBrowseType, $fSortBy, $fSortDirection);
     
    // instantiate my content pattern
    $oContent = new PatternCustom();
	
	$aResults = $oBrowser->browse();
    
    require_once("../../../webpageTemplate.inc");    
	// display the browse results
    $oContent->addHtml(renderPage($aResults, $fBrowseType, $fSortBy, $fSortDirection));
    
    $sToRender = "<html>\n";
	$sToRender .= "<head>\n";
	$sToRender .= "<meta http-equiv=\"refresh\" content=\"" . ($default->sessionTimeout+3) . "\">\n";
    $sToRender .= "<link rel=\"SHORTCUT ICON\" href=\"$default->graphicsUrl/tree.ico\">\n";
    $sToRender .= "<link rel=\"stylesheet\" href=\"$default->uiUrl/stylesheet.php\">\n";
    $sToRender .= "</head>\n";
    $sToRender .= "<body>\n";
    $sToRender .= $oContent->render() . "\n";
    $sToRender .= "</body>";
    $sToRender .= "</html>\n";
    
    echo $sToRender . "\n\n" . getSendInfoToParentJavaScript();   
     
}

function getSendInfoToParentJavaScript() {
	$sToRender = "<script language=\"JavaScript\"><!--\n";
	$sToRender .= "function load(documentName, documentID, target) {\n";
    $sToRender .= "\tif (target != '') {\n";    
    //$sToRender .= "\t\ttarget.window.document.MainForm.fTemplateDocument.value = documentName;\n";    
    //$sToRender .= "\t\ttarget.window.document.MainForm.fTemplateDocumentID.value = documentID;\n";    
    $sToRender .= "\t\ttarget.window.document.MainForm.fTargetDocumentID.value = documentID;\n";
    $sToRender .= "\t\ttarget.window.document.MainForm.fTargetDocument.value = documentName;\n";
    $sToRender .= "\t}\n";
    $sToRender .= "\telse {\n";
    $sToRender .= "\t\twindow.location.href = file;\n";
    $sToRender .= "\t}\n";
    $sToRender .= "\twindow.close();\n";
	$sToRender .= "}\n";
	$sToRender .= "//--></script>\n\n";
	return $sToRender;
	
}

/*function renderBrowsePage($oContent) {
	global $default;
	$sToRender = "<html>\n";
	$sToRender .= "<head>\n";
	$sToRender .= "<meta http-equiv=\"refresh\" content=\"" . ($default->sessionTimeout+3) . "\">\n";
    $sToRender .= "<link rel=\"SHORTCUT ICON\" href=\"$default->graphicsUrl/tree.ico\">\n";
    $sToRender .= "<link rel=\"stylesheet\" href=\"$default->uiUrl/stylesheet.php\">\n";
    $sToRender .= "</head>\n";
    $sToRender .= "<body>\n";
    $sToRender .= $oContent->render() . "\n";
    $sToRender .= "</body>";
    $sToRender .= "</html>\n";
    return $sToRender;
     
	
}*/
?>