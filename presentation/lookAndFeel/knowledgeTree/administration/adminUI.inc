<?php

/**
 * Contains UI methods common to all administration screens
 */
 
function getAddLink($sAddAction, $sAddText) {
	global $default;
	$sBGColor = $default->siteMap->getSectionColour("Administration", "td");
	return "<table width=\"100%\"><tr><td bgcolor=\"$sBGColor\">" . generateControllerLink("$sAddAction", "", "<font color=\"#ffffff\">$sAddText</font>") . "</td></tr></table>\n";	
}

function getCancelButton($sCancelAction, $sQueryString = "") {
	global $default;
	return generateControllerLink($sCancelAction, $sQueryString, "<img border=\"0\" src=\"$default->graphicsUrl/widgets/cancel.gif\"/>");
}
?>