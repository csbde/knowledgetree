<?php
require_once("../../../../config/dmsDefaults.php");

function renderHeading() {
    global $default;
    $sColor = $default->siteMap->getSectionColour("Administration", "td");
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\">Administration</th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    return $sToRender;
}

if(checkSession()) {
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

    $sCenter  = "<table width=\"600\">\n";
    $sCenter .= renderHeading();
    $sCenter .=	"<tr/><tr/><tr><td><b> Welcome to the Administration Section</b></td></tr>\n";
    $sCenter .=	"<br></br>\n";
    $sCenter .=	"<tr><td>Please make a selection from the sidemenu.</td></tr>\n";

	$oPatternCustom = & new PatternCustom();
	$oPatternCustom->setHtml($sCenter);
	$main->setCentralPayload($oPatternCustom);
	$main->render();
}
?>
