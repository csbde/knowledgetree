<?php
/**
* Business logic to email a link to a document to a colleauge
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 31 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*
*/

function renderHeading($sHeading) {
    global $default;
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender  = "<table border=\"0\" width=\"600\">\n";
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\">$sHeading</th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

function getDocumentPath($oDocument) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");	    
	$sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($oDocument->getFolderID()), Folder::getFolderPathNamesAsArray($oDocument->getFolderID()), "$default->rootUrl/control.php?action=browse") . " > " . $oDocument->getName();
	return "<table border=\"0\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sDocumentPath</td></tr></table>\n";	
}

function getDocumentEmailPage($oDocument,$oUserArray) {
	global $default;
    $sToRender = renderHeading("Email document link");
	$sToRender .= getDocumentPath($oDocument) . "\n<br>\n";	 
	$sToRender .= "<table>\n";	
	$sToRender .= "<tr><td>Recipient name:</td><td>\n";
	$sToRender .= "<select name=\"UserSelect\" id=\"UserSelect\" onchange=\"javascript: var sColon; var oTemp = document.getElementsByName('fToEmail')[0]; if(oTemp.value.length>0) sColon=';'; else sColon=''; if (this.value!=1 && this.value!='' && this.value!=' ') {oTemp.value=oTemp.value + sColon + this.value ;}\">\n";
	$sToRender .= "<OPTION value=\"1\">\n";
	$sToRender .= "Select email adresses</OPTION>\n";	
	for ($i = 0; $i < count($oUserArray); $i++) {
		if(strlen($oUserArray[$i]->getEmail())>0){ // if an amil address exists
			$sToRender .= "<OPTION value=\"" . $oUserArray[$i]->getEmail() . "\" onclick=\"document.fToEmail.value=document.fToEmail.value + ;\" >\n";
			$sToRender .= $oUserArray[$i]->getName();
			$sToRender .= "</OPTION>\n";
		}
	}	
	$sToRender .= "</select></td></tr>\n";	
	$sToRender .= "<tr><td>Select Recipient:</td><td><input type=\"text\" name=\"fToEmail\" style=\"width:270\"/></td><td><img src=\"$default->graphicsUrl/widgets/clear.gif\" onclick=\"document.getElementsByName('fToEmail')[0].value='';document.getElementsByName('UserSelect')[0].selectedIndex=0\" onmouseover=\"this.style.cursor='hand'\"></td></tr>\n";	
	$sToRender .= "<tr><td></td><td>(separate multiple addresses with a semicolon)</td></tr>";
	$sToRender .= "<tr><td>&nbsp</td><td>&nbsp</td></tr>\n";
	$sToRender .= "<tr><td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/email.gif\" border=\"0\"/></td><td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/></a></td></tr></table></td>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}
	
?>
