<?php

/**
* Presentation information used by deleteFolderDocTypeUI.inc
* 
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 27 February 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*/

function getPage($iFolderID) {
	global $default;
	$sToRender = "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td>" . renderFolderPath($iFolderID, "$default->uiUrl/control.php?action=editFolder&fFolderID=$iFolderID") . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    $sToRender .= "<table border=\"0\">\n";    
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=editFolder&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table><br>\n";
	
	return $sToRender;
}

?>
