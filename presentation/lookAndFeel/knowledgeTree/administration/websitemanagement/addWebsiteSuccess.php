<?php
/**
* Presentation information when updating group properties is successful
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

require_once("../../../../../config/dmsDefaults.php");

global $default;
	
if(checkSession())
{

// include the page template (with navbar)
require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");  	

$Center = "<br></br>\n" ;
$Center .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
$Center .= "<tr>\n";
if($fWebSiteID != -1){
	$Center .= "<td><b>New Website Added SuccessFully!<b></td>\n";
}
Else
{
	$Center .= "<td><b>Addition Unsuccessful</b>...</td>\n";
	$Center .= "</tr>\n";
	$Center .= "<tr></tr>\n";
	$Center .= "<tr></tr>\n";
	$Center .= "<tr>\n";
	$Center .= "<td>Please Check for duplicates!</td>\n";
	$Center .= "</tr>\n";
	$Center .= "<tr>\n";
	
	
}

	
$Center .= "<tr></tr>\n";
$Center .= "<tr></tr>\n";
$Center .= "<tr></tr>\n";
$Center .= "<tr></tr>\n";
$Center .= "<tr>\n";
$Center .= "<td align = right><a href=\"$default->rootUrl/control.php?action=addWebsite\">".
		"<img src=\"$default->graphicsUrl/widgets/back.gif\" border = \"0\"></a></td>\n";
$Center .= "</tr>\n";
$Center .= "</table>\n";
		

$oPatternCustom = & new PatternCustom();
$oPatternCustom->setHtml($Center); 
$main->setCentralPayload($oPatternCustom);
$main->render();




}

?>