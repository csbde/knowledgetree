<?php
/**
 * $Id$
 *
 * This page holds all presentation code for administering dashboard news..
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
 * @package administration.news
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

/**
 * Displays the current news items
 */	
function renderNewsTable() {
    global $default;
    /*ok*/ $sQuery = "SELECT id, synopsis, IF(LENGTH(body) > 25, CONCAT(LEFT(body,25),'...'), body) AS body, rank, IF(LENGTH(image)>0, 'yes', 'no') as image, active, 'Edit' AS edit, 'Preview' AS preview, 'Delete' AS del " .   
              "FROM $default->news_table ORDER BY rank ASC";
    $aColumns = array("synopsis", "body", "rank", "image", "active", "edit", "preview", "del");
    $aColumnHeaders = array(_("Synopsis"), _("Body"), _("Rank"), _("Image"), _("Active"));
    $aColumnTypes = array(1,1,1,1,2,3,3,3);
    $aDBColumnArray = array("id");
    $aQueryStringVariableNames = array("fNewsID");
    $aLinkURLs = array(5=> "$default->rootUrl/control.php?action=editNews",
                       6=> "$default->rootUrl/control.php?action=previewNews",
                       7=> "$default->rootUrl/control.php?action=removeNews");

    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "100%", $aLinkURLs,$aDBColumnArray,$aQueryStringVariableNames);
    $oPatternTableSqlQuery->setDisplayColumnHeadings(true);        
    return $oPatternTableSqlQuery->render();
}

/**
 * Displays a news item
 */
function renderNewsItem($oDashboardNews) {
	global $default;
	
	$sToRender .= "<tr><td>" . _("Synopsis") . ":</td><td>" . $oDashboardNews->getSynopsis() . "</td></tr>";
	
	$sToRender .= "<tr><td>" . _("Body") . ":</td><td>" . $oDashboardNews->getBody() . "</td></tr>";
	$sToRender .= "<tr><td>" . _("Rank") . ":</td><td>" . $oDashboardNews->getRank() . "</td></tr>";	
	$sToRender .= "<tr><td colspan=\"2\">" . $oDashboardNews->getImageLink() . "</td></tr>";	
		
	$sToRender .= "<input type=\"hidden\" name=\"fNewsID\" value=\"" . $oDashboardNews->getID() . "\">";
    return $sToRender;
}

/**
 * Displays the edit/add news item form
 *
 * @param DashboardNews the news item to modify
 */
function renderEditAddNewsForm($oDashboardNews) {
	global $default;
	
	$sToRender  = "<table>";
	
	$sToRender .= "<tr><td>" . _("Synopsis") . ":</td><td><input type=\"text\" name=\"fSynopsis\" value=\"" . ($oDashboardNews ? $oDashboardNews->getSynopsis() : "") . "\"></td></tr>";
	$sToRender .= "<tr><td>" . _("Body") . ":</td><td><textarea name=\"fBody\" cols=\"35\" rows=\"10\">" . ($oDashboardNews ? $oDashboardNews->getBody() : "") . "</textarea></td></tr>";
	$sToRender .= "<tr><td>" . _("Rank") . ":</td><td><input type=\"text\" name=\"fRank\" value=\"" . ($oDashboardNews ? $oDashboardNews->getRank() : "") . "\"></td></tr>";	
	$sToRender .= "<tr><td>" . _("Image") . ":</td><td><input type=\"file\" name=\"fImage\"></td></tr>";
	$sToRender .= "<tr><td>" . _("Active") . ":</td><td><input type=\"checkbox\" name=\"fActive\" " . ($oDashboardNews ? ($oDashboardNews->getActive() ? "checked" : "") : "") . "></td></tr>";
	
	$sToRender .= ($oDashboardNews ? "<input type=\"hidden\" name=\"fNewsID\" value=\"" . $oDashboardNews->getID() . "\">" : "");
			
	$sToRender .= "<tr><td><input type=\"image\" src=\"" . KTHtml::getSubmitButton() . "\" border=\"0\">\n";
	$sToRender .= getCancelButton("listNews") . "</td></tr>\n";    
	$sToRender .= "</table>";
	
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(theForm.fSynopsis,'Synopsis'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
    $sToRender .= "\tif (!(validRequired(theForm.fBody,'Body'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "\tif (!(validNum(theForm.fRank,'Rank')) || !(validRequired(theForm.fRank,'Rank'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";	
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";
		
	return $sToRender; 
} 
	
/**
 * Displays the edit news page
 */
function renderEditNewsPage($oDashboardNews, $sErrorMessage = "") {
	global $default;
	
	$sToRender .= renderHeading(_("Edit Dashboard News"));
	if (strlen($sErrorMessage) > 0) {
		$sToRender .= renderErrorMessage($sErrorMessage);
	}
    $sToRender .= "<table>\n";
	$sToRender .= renderEditAddNewsForm($oDashboardNews);
	$sToRender .= "</table>\n";
	return $sToRender;
}

/**
 * Displays the add new new item page
 */
function renderAddNewsPage($oDashboardNews, $sErrorMessage = "") {
	global $default;
	
	$sToRender .= renderHeading(_("Add Dashboard News"));
	if (strlen($sErrorMessage) > 0) {
		$sToRender .= renderErrorMessage($sErrorMessage);
	}	
    $sToRender .= "<table>\n";
	$sToRender .= renderEditAddNewsForm($oDashboardNews);
	$sToRender .= "</table>\n";
	return $sToRender;	
}
/**
 * Displays a list of news items
 */
function renderListNewsPage() {
	global $default;
	
	$sToRender .= renderHeading(_("Dashboard News Management"));
	$sToRender .= getAddLink("addNews", "Add Dashboard News Item");	
    $sToRender .= "<table>\n";
	$sToRender .= renderNewsTable();
	$sToRender .= "</table>\n";
	return $sToRender;
}

/**
 * Displays the preview page
 */
function renderPreviewNewsPage($oDashboardNews) {
	global $default;
		
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";
	$sToRender .= "<tr><td><strong>" . $oDashboardNews->getSynopsis() . "</strong></td></tr>\n";	
	$sToRender .= "<tr><td>" . $oDashboardNews->getImageLink() . "</td></tr>\n";
	$sToRender .= "<tr><td>" . $oDashboardNews->getBody() . "</td></tr>\n";
	$sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=listNews\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\"></a></td></tr>\n";
	$sToRender .= "</table>";
	return $sToRender;		
} 

/**
 * Prompts for confirmation when deleting a news item
 */
function renderDeleteNewsConfirmationPage($oDashboardNews) {
	global $default;
	
	$sToRender .= renderHeading(_("Delete Dashboard News"));
    $sToRender .= "<table>\n";
	$sToRender .= "<tr><td colspan=\"2\">" . _("Are you sure you wish to delete this news item?") . "</td></tr>";
	
	$sToRender .= renderNewsItem($oDashboardNews);	
	
	$sToRender .= "<input type=\"hidden\" name=\"fDeleteConfirmed\" value=\"1\">";	
	$sToRender .= "<tr><td><input type=\"image\" src=\"" . KTHtml::getDeleteButton() . "\" border=\"0\">\n";
	$sToRender .= getCancelButton("listNews") . "</td></tr>\n";    
	
	$sToRender .= "</table>\n";
	return $sToRender;	
}
?>
