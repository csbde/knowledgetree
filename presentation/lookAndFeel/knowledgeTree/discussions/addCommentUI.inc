<?php
/**
* Presentation information for adding a unit
*
* @author Omar Rahbeeni
* @date 5 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

// On a successful save
function getSubmitSuccessPage($iDocumentID){	
	global $default;
	$sMessage = "Your submission has been successful.";
	
	$sToRender .= "$sMessage<br><br><a href=\"" . generateControllerLink("viewDiscussion", "fDocumentID=" . $iDocumentID . "&fForDiscussion=1") . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>";
//	$sToRender .= "";
//	$sToRender .= "";
 	
	return $sToRender;	
}

// If there are no threads to view for a document
function getViewFailPage($sMessage)
{
	global $default;
	
	if (strlen($sMessage)>0){
		$sToRender = $sMessage;
	}
	else $sToRender = "Current document has no discussion.  Press the ADD button to start a discussion.";
	
	return $sToRender;
}

function renderHeading($sHeading) {
    global $default;
        
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender  = "<table border=\"0\" width=\"600\">\n";
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">$sHeading</font></th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

function renderDocumentPath($iDocumentID) {
    global $default;
	
	$oDocument = & Document::get($iDocumentID);
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");	
    $sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($oDocument->getFolderID()), Folder::getFolderPathNamesAsArray($oDocument->getFolderID()), "$default->rootUrl/control.php?action=browse") . " > ";
    $sDocumentPath .= "<a href=\"$default->rootUrl/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php?fDocumentID=" . $oDocument->getID() . "&fForInlineView=1\">" . $oDocument->getName() . "</a>";    
    return "<table border=0 cellpadding=\"5\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sDocumentPath</td></tr></table>\n";
}


function getAddComment($fDocumentID, $sSubject, $sBody) {
	global $default;
	
	//$iThreadID = DiscussionThread::getThreadIDforDoc($fDocumentID);
	
			
	$sHeading = "Add a Comment";	
	$sToRender .= renderHeading($sHeading);
	$sToRender .= renderDocumentPath($fDocumentID);	
	$sToRender .= "<table width=\"100%\" border=0><tr><td></td>\n";
	$sToRender .= "<td align=right><input type=\"submit\" style=\"BORDER-RIGHT: 0px groove; BORDER-TOP: 0px groove; BACKGROUND-IMAGE: url($default->graphicsUrl/widgets/submit.gif); BORDER-LEFT: 0px groove; WIDTH: 45px; BORDER-BOTTOM: 0px groove; HEIGHT: 18; BACKGROUND-COLOR: white\" width=\"45\" height=\"20\" value=\" \" width=\"100\"></td></tr>\n";
	$sToRender .= "<br><tr><td valign=\"top\" width=10><b>Subject</b></td><td>\n";
	$sToRender .= "<input type=\"text\" style=\"width:100%\" name=\"NewCommentSubject\" id=\"NewCommentSubject\" value=\"$sSubject\"></td></tr>\n";
	$sToRender .= "<tr><td valign=\"top\"><b>Body</b></td><td><textarea style=\"width:100%\" rows=\"21\" name=\"NewComment\" id=\"NewComment\" style=\"FONT-STYLE: normal; FONT-FAMILY: sans-serif; FONT-VARIANT: normal\">$sBody</textarea></td></tr></table>\n";
	$sToRender .= "</table>";	

	return $sToRender;
	
}

function getCommentBody($sCommentBody, $sCommentSubject,$dDate,$iDocumentID, $sUserName) {
	global $default;
	
	$sHeading = "Document Comment";
		
	$sToRender .= renderHeading($sHeading);
	$sToRender .= renderDocumentPath($iDocumentID);
	$sToRender .= "<br>&nbsp;<b>Author: </b>$sUserName";
	$sToRender .= "<table width=\"96%\" border=\"1\"><tr>"; 
	$sToRender .= "<td width=\"1\" style=\"background-color:#F5F6EE;\" valign=\"top\"><b>Subject: </b></td><td width=405 style=\"background-color:#F5F6EE;\"> <font color=#056DCE>$sCommentSubject</font></td>";
	$sToRender .= "<td><b>Date: </b><font color=\"#056DCE\">$dDate</font></td></tr><tr><td colspan=3><br>$sCommentBody</a></td>";
	$sToRender .= "</tr></table>";
	return $sToRender;	
}

function viewDiscussionPage() {
	global $default;
	
	$sToRender = "";
	return $sToRender;	
}
?>