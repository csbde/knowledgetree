<?php
require_once("../../../../config/dmsDefaults.php");

function renderHeading() {
    global $default;
    
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">Administration</font></th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    return $sToRender;
}

if(checkSession()) {
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

    $sCenter  = "<table width=\"600\">\n";
    $sCenter .= renderHeading();
    $sCenter .=	"<tr/><tr/><tr><td><b> Welcome to the Administration Section</b></td></tr>\n";
    $sCenter .=	"<tr><td>Please make a selection from the sidemenu.</td></tr>\n";
    $sCenter .=	"</table>";

	$oPatternCustom = & new PatternCustom();
	$oPatternCustom->setHtml($sCenter);
	$main->setCentralPayload($oPatternCustom);
	$main->render();
}
?>
