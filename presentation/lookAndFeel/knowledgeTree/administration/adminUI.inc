<?php

/**
 * Displays heading
 */
function getHeading($sHeading, $sSectionName = "") {
    global $default;
    
    if ($sSectionName == "") {
        $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    }
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sColor = $sColor == "" ? $sSectionName : $sColor;
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">$sHeading</font></th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    return $sToRender;
}

function renderHeading($sHeading, $sSectionName = "") {
	//global $default;
	
	
    $sCenter .= getHeading($sHeading, $sSectionName);
    if ($sHeading == "Administration"){
	    $sCenter .=	"<tr/><tr/><tr><td><b> Welcome to the Administration Section</b></td></tr>\n";
	    $sCenter .=	"<tr><td>Please make a selection from the sidemenu.</td></tr>\n";
    }
    
    return $sCenter;	
}
?>
