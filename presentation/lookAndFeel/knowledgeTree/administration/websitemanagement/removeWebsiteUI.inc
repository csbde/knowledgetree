<?php

/**
* Presentation information for Deleting a WebSite 
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// gets the delete stuff
function getDeletePage($iWebSiteID) {	
	global $default;
	$oWebSite = null;
	if (isset($iWebSiteID)) {
		$oWebSite = WebSite::get($iWebSiteID);
		$sToRender .= "<br></br>\n";
		$sToRender .= "<b>Are you sure you wish to Remove this WebSite?:</b>\n";
	}
	else{
		$sToRender .= "<br></br>\n";		
		$sToRender .= "<b>Remove WebSite From System:</b>\n";
	}
	
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>WebSite Name: </td><td>" . getWebSiteDisplay($oWebSite) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getWebSiteUrl($oWebSite) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getWebMasterName($oWebSite) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td>" . getDeleteButton($oWebSite)	. "</td>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {	
	
global $default;

$sToRender  = "<br></br>\n" ;
$sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
$sToRender .= "<tr>\n";
$sToRender .= "<td><b>WebSite SuccessFully Removed!</b></td>\n";
$sToRender .= "</tr>\n";	
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr>\n";
$sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=removeWebSite\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
$sToRender .= "</tr>\n";
$sToRender .= "</table>\n";
		
return $sToRender;
}

// get deletion failed page
function getDeleteFailPage() {	

global $default;	

$sToRender  = "<br></br>\n" ;
$sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
$sToRender .= "<tr>\n";
$sToRender .= "<td><b>Deletion Failed!</b></td>\n";
$sToRender .= "</tr>\n";	
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr><td>Please Ensure that The WebSite has been Removed from any Unit it belongs to</td></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "</tr>\n";
$sToRender .= "<tr><td align = right><a href=\"$default->rootUrl/control.php?action=removeWebSite\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td></tr>\n";
$sToRender .= "</table>\n";
		
return $sToRender;
}

// show listbox...or the text name
function getWebSiteDisplay($oWebSite) {
	global $default;
	if (!isset($oWebSite)) {
		$oPatternListBox = & new PatternListBox($default->owl_web_sites_table, "web_site_name", "id", "fWebSiteID");
		$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fWebSiteID\" value=\"" . $oWebSite->iId . "\">\n" .
		"<b>" . $oWebSite->getWebSiteName() . "</b>";
	}
	
}

// show check box
function getWebSiteUrl($oWebSite) {
	if (!isset($oWebSite)) {
		return "";
	} else {
		return  "WebSite URL: </td><td>" . $oWebSite->getWebSiteUrl();
	}
}

// show text box
function getWebMasterName($oWebSite) {
	
	$oUser = null;
	
	if (!isset($oWebSite)) {
		return "";
	} else {
		$iUserID = $oWebSite->getWebMasterID();
		//$oUser::get($iUserID);		
		//return  "WebMaster: </td><td>" . $oUser->getName();
	}
	
}

// show delete button
function getDeleteButton($oWebSite) {
	global $default;
	if (!isset($oWebSite)) {
		
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" border=\"0\"/></td>\n".
		"<td align = right><a href=\"$default->rootUrl/control.php?action=removeWebSite\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}
?>
