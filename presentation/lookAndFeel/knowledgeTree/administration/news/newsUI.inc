<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->uiDirectory/administration/adminUI.inc");

/**
 * $Id$
 *  
 * This page controls browsing for documents- this can be done either by
 * folder, category or document type.
 * The relevant permission checking is performed, calls to the business logic
 * layer to retrieve the details of the documents to view are made and the user
 * interface is contructed.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */

/**
 * Displays an error message
 */
function renderErrorMessage($sErrorMessage) {
	$sToRender  = "<table>";
	$sToRender .= "<tr><td><p><font color=\"red\">$sErrorMessage</font></p></td></tr>";
	$sToRender .= "</table>";
	return $sToRender;
}

function renderNewsPopupJavascript() {
	global $default;
	return "\n\n<SCRIPT LANGUAGE=\"javascript\">\n " .
	"<!--\n" .		
	"function popupNews() {\n" .			
		"window.open ('" . generateControllerUrl("displayNews", "fNewsID=" ) . "', 'newwindow', config='height=400,width=600,left=400,top=300, toolbar=no, menubar=no, scrollbars=yes, resizable=no,location=no, directories=no, status=no');\n" .
		"return false;\n" .
	"}\n" .
	"//-->\n" .
	"</SCRIPT>\n\n";	
}

/**
 * Displays the current news items
 */	
function renderNewsTable() {
    global $default;
    $sQuery = "SELECT id, synopsis, body, rank, if((length(image)>0),'yes','no') as image, 'Edit' AS edit, 'Preview' AS preview, 'Delete' AS del " .   
              "FROM $default->owl_news_table ORDER BY rank ASC";
    $aColumns = array("synopsis", "body", "rank", "image", "edit", "preview", "del");
    $aColumnHeaders = array("Synopsis", "Body", "Rank", "Image");
    $aColumnTypes = array(1,1,1,1,3,3,3);
    $aDBColumnArray = array("id");
    $aQueryStringVariableNames = array("fNewsID");
    $aLinkURLs = array(4=> "$default->rootUrl/control.php?action=editNews",
                       5=> "$default->rootUrl/control.php?action=previewNews",
                       6=> "$default->rootUrl/control.php?action=removeNews");

    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "10%", $aLinkURLs,$aDBColumnArray,$aQueryStringVariableNames);
    $oPatternTableSqlQuery->setTableHeading("Dashboard News");
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);        
    return $oPatternTableSqlQuery->render();
}

/**
 * Displays the edit/add news item form
 *
 * @param DashboardNews the news item to modify
 */
function renderModifyAddNewsForm($oDashboardNews = null) {
	global $default;
	
	$sToRender  = "<table>";
	$sToRender .= "<tr><td>Synopsis</td><td><input type=\"text\" name=\"fSynopsis\" value=\"" . ($oDashboardNews ? $oDashboardNews->getSynopsis() : "") . "\"></td></tr>";
	$sToRender .= "<tr><td>Body</td><td><input type=\"textarea\" name=\"fBody\" value=\"" . ($oDashboardNews ? $oDashboardNews->getBody() : "") . "\"></td></tr>";
	$sToRender .= "<tr><td>Rank</td><td><input type=\"text\" name=\"fRank\" value=\"" . ($oDashboardNews ? $oDashboardNews->getRank() : "") . "\"></td></tr>";	
	$sToRender .= "<tr><td>Image</td><td><input type=\"file\" name=\"fImage\"></td></tr>";
	$sToRender .= (isset($oDashboardNews) ? "<input type=\"hidden\" name=\"fNewsID\" value=\"" . $oDashboardNews->getID() . "\">" : "");
	$sToRender .= "<input type=\"hidden\" name=\"fUpdate\" value=\"1\">";	
	$sToRender .= "<tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/submit.gif\" border=\"0\">\n";
	$sToRender .= "<a href=\"$default->rootUrl/control.php?action=viewNews\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td></tr>\n";    
	$sToRender .= "</table>";
	return $sToRender; 
} 

/**
 * Displays the edit news page
 */
function renderEditNewsPage($oDashboardNews) {
	global $default;
	
	$sToRender .= "<table width=\"600\">" . renderHeading("Edit Dashboard News") . "</table>";
    $sToRender .= "<table>\n";
	$sToRender .= renderModifyAddNewsForm($oDashboardNews);
	$sToRender .= "</table>\n";
	return $sToRender;
}

/**
 * Displays the add new new item page
 */
function renderNewNewsPage() {
	global $default;
	
	$sToRender .= "<table width=\"600\">" . renderHeading("View Dashboard News") . "</table>";
    $sToRender .= "<table>\n";
	$sToRender .= renderModifyAddNewsForm();
	$sToRender .= "</table>\n";
	return $sToRender;	
}
/**
 * Displays a list of news items
 */
function renderViewNewsPage() {
	global $default;
	
	$sToRender .= "<table width=\"600\">" . renderHeading("View Dashboard News") . "</table>";
    $sToRender .= "<table>\n";
	$sToRender .= renderNewsTable();
	$sToRender .= "</table>\n";
	return $sToRender;
} 
?>