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
$Center .= "<td>Update SuccessFul!</td>\n";
$Center .= "</tr>\n";	
$Center .= "<tr></tr>\n";
$Center .= "<tr></tr>\n";
$Center .= "<tr></tr>\n";
$Center .= "<tr></tr>\n";
$Center .= "<tr>\n";
$Center .= "<td align = right><a href=\"$default->rootUrl/control.php?action=editRole\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
$Center .= "</tr>\n";
$Center .= "</table>\n";
		

$oPatternCustom = & new PatternCustom();
$oPatternCustom->setHtml($Center); 
$main->setCentralPayload($oPatternCustom);
$main->render();




}

?>