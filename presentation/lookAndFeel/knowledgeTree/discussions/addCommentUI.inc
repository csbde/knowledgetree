<?php
/**
* Presentation information for adding a unit
*
* @author Omar Rahbeeni
* @date 5 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

/** 
 *	Submission of comment SUCCESS page   
 *
 *	@param $iDocumentID -> a valid Document ID 
 */
function getSubmitSuccessPage($iDocumentID){	
	global $default;
	$sMessage = "Your submission has been successful.";	
	$sToRender .= "$sMessage<br><br><a href=\"" . generateControllerLink("viewDiscussion", "fDocumentID=" . $iDocumentID . "&fForDiscussion=1") . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>";
 	
	return $sToRender;	
}

/** 
 *	Renders the heading for the page
 *
 *	@param $sHeading -> text heading for the page
 */
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

/** 
 *	Renders the path for the document
 *
 *	@param $fDocumentID -> a valid document ID
 */
function renderDocumentPath($iDocumentID) {
    global $default;
	
	$oDocument = & Document::get($iDocumentID);
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");	
    $sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($oDocument->getFolderID()), Folder::getFolderPathNamesAsArray($oDocument->getFolderID()), "$default->rootUrl/control.php?action=browse") . " > ";
    $sDocumentPath .= "<a href=\"$default->rootUrl/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php?fDocumentID=" . $oDocument->getID() . "&fForInlineView=1\">" . $oDocument->getName() . "</a>";    
    return "<table border=0 cellpadding=\"5\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sDocumentPath</td></tr></table>\n";
}

/** 
 *	Display the ADD COMMENT page   
 *
 *	@param $fDocumentID -> a valid Document ID
 *	@param $sSubject -> a Subject text
 *	@param $sBody -> a Body text 
 */
function getAddComment($fDocumentID, $sSubject, $sBody, $iCommentID, $fNewComment = null) {
	global $default;
				
	$sHeading = "Add a Comment";	
	$sToRender .= renderHeading($sHeading);
	$sToRender .= renderDocumentPath($fDocumentID);	
	$sToRender .= "<table width=\"100%\" border=\"0\" cellpadding=0 ><tr><td></td>\n";	
	$sToRender .= "<td align=right width=100%><input onmouseover=\"this.style.cursor='hand'\" type=\"image\" src=\"$default->graphicsUrl/widgets/submit.gif\" border=0></td>";	
	if($fNewComment>0){	// If user is creating a new comment
		$sToRender .= "<td width=\"10\"><a href=\"" . generateControllerLink("viewDiscussion", "fForDiscussion=1&fDocumentID=$fDocumentID") . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=0 ></a></td></tr>\n";
	}else {				// If the user is replying to a comment
		$sToRender .= "<td width=\"10\"><a href=\"" . generateControllerLink("viewDiscussion", "fViewComment=1&iDocumentID=$fDocumentID&iCommentID=$iCommentID") . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=0 ></a></td></tr>\n";		
	}
	$sToRender .= "<br><tr><td valign=\"top\" width=10><b>Subject</b></td><td colspan=2>\n";
	$sToRender .= "<input type=\"text\" style=\"width:385\"  name=\"NewCommentSubject\" id=\"NewCommentSubject\" value=\"$sSubject\"></td></tr>\n";			
	$sToRender .= "<tr><td valign=\"top\"><b>Body</b></td><td colspan=2><textarea  cols=60 rows=\"21\" name=\"NewComment\" id=\"NewComment\" wrap=\"physical\" style=\"FONT-STYLE: normal; FONT-FAMILY: sans-serif; FONT-VARIANT: normal\">$sBody</textarea></td></tr></table>\n";
	return $sToRender;	
}

/** 
 *	Display the comment: Body text included 
 *  Prints out 1 comment 
 *  User needs to call this function many times to list all comments  
 *
 *	@param $sCommentBody -> Some body text
 *	@param $iComment Subject -> Some subject text
 *	@param $dDate -> Date comment was created
 *	@param $iDocumentID -> a valid document ID
 *	@param $sUserName -> Name of creator of comment
 */
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

?>