<?php
/**
* Presentation information for adding a unit
*
* @author Omar Rahbeeni
* @date 5 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/


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

/** 
 *	Page to create a new thread
 *
 *	@param $fDocumentID -> a valid document ID
 */
function getNewThreadOption($fDocumentID) {
	   global $default;   
	   
	   $sToRender .= "Would you like to create a new Discussion thread for this document?";
	   $sToRender .= "&nbsp;&nbsp;&nbsp;";
	   $sToRender .= "<a href=\"" . generateControllerLink("addComment", "fDocumentID=" . $fDocumentID . "&fNewThread=1") . "\"><img src=\"$default->graphicsUrl/widgets/new.gif\" border=\"0\"></a>";
	   $sToRender .= "<a href=\"" . generateControllerLink("viewDocument", "fDocumentID=" . $fDocumentID) . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a>";
	   	   
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
 *	Views the list of comments for a document
 *  Prints out 1 comment 
 *  User needs to call this function many times to list all comments  
 *
 *	@param $iNum -> its the iterative index of the current comment
 *	@param $iThread -> a valid Thread Object
 *	@param $oComment -> a valid Comment Object
 *	@param $oUser -> a valid User Object (Logged in user)
 */
function getViewComment($iNum,$oThread,$oComment,$oUser) {
	global $default;
		
	$iSubjectChars = 59;
	// If iNum is odd then highlight it
	if (($iNum%2) == 1) $BGcolor = "#F5F6EE";
	if (($iNum%2) == 0) $UserBGcolor = "#F5F6EE";
	
	// If the Subject string is too long
	if (strlen($oComment->getSubject())>$iSubjectChars ) $dotdot = " ..."; 
	if (strlen($oUser->getUserName())> 13 ) $Userdotdot = " ...";
	
	// If this is the first Subject line then create a table
	if ($iNum == 1) {		
		$sHeading = "Document Discussion Comments";
	
		$sToRender .= renderHeading($sHeading );
		$sToRender .= renderDocumentPath($oThread->getDocumentID());		
		$sToRender .= "<table border=0 width=100%><tr>"; 
		$sToRender .= "<td width=100><b>Views: </b> " . $oThread->getNumberOfViews() . "</td>";    		
		$sToRender .= "<td width=20></td>";
		$sToRender .= "<td><b>Replies: </b> " . $oThread->getNumberOfReplies() . "</td>";
		$sToRender .= "<td  align=right><a href=\"" . generateControllerLink("addComment", "fDocumentID=" . $oThread->getDocumentID() . "&fAddComment=1") . "\"><img src=\"$default->graphicsUrl/widgets/addcomment.gif\" border=\"0\" /></a>";
		$sToRender .= "<img src=\"$default->graphicsUrl/widgets/back.gif\" onmouseover=\"this.style.cursor='hand'\" onclick=\"javascript: history.go(-1);\" ></td>";
		$sToRender .= "</tr></table><br>\n";			
		$sToRender .= "<table border=\"0\" width=\"100%\" cellspacing=0><tr>\n";
		$sToRender .= "<td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Subject</b></td>\n";
		$sToRender .= "<td><b>Author</b></td>\n";
		$sToRender .= "<td><b>Date</b></td>\n"; 
		$sToRender .= "</tr>\n";
	}

	$sToRender .= "<tr><td style=\"background-color:$BGcolor\" width=450>$iNum. &nbsp;<a style=\"width:94%\" href=\"" . $_SERVER['PHP_SELF'] . "?fViewComment=1&iCommentID=" . $oComment->getID() . "&iDocumentID=" . $oThread->getDocumentID() . "\" title=\"" . $oComment->getSubject() . "\">" .  substr($oComment->getSubject(),0,$iSubjectChars ) . $dotdot . "</a></td>\n";
	$sToRender .= "<td style=\"background-color:$UserBGcolor\" width=\"100\" title=\"$oUser->getUserName()\">" . substr($oUser->getUserName(),0,10) . $Userdotdot . "</td>\n"; 
	$sToRender .= "<td style=\"background-color:$BGcolor\" width=\"70\">" . $oComment->getDate() . "<td></tr>\n";
	
	// If this is the last subject line then close the table
	if ($iNum == $iNumMax) { $sToRender .= "</table>\n\n";}	
	return $sToRender; 
}

/** 
 *	Views a single thread comment with its text body  
 * 
 *	@param $iDocumentID -> a valid Document ID
 *	@param $oComment -> a valid Comment Object
 *	@param $oUser -> a valid User Object (Logged in user)
 */
function getCommentBody($oComment, $iDocumentID, $oUser) {
	global $default;
	
	$sHeading = "Document Comment";
		
	$sToRender .= renderHeading($sHeading);
	$sToRender .= renderDocumentPath($iDocumentID);
	
	$sToRender .= "<br><table width=100% border=0><tr ><td colspan=2><b>Author: </b>" . $oUser->getUserName() . "</td>\n";	
	$sToRender .= "<td align=right>\n"; 
	$sToRender .= "<input onmouseover=\"this.style.cursor='hand'\" type=\"submit\" style=\"BORDER-RIGHT: 0px groove; BORDER-TOP: 0px groove; BACKGROUND-IMAGE: url($default->graphicsUrl/widgets/reply.gif); BORDER-LEFT: 0px groove; WIDTH: 40px; BORDER-BOTTOM: 0px groove; HEIGHT: 18; BACKGROUND-COLOR: white\" height=\"20\" value=\" \" width=\"200\"></td>";
	$sToRender .= "<td width=30><img src=\"$default->graphicsUrl/widgets/back.gif\" onmouseover=\"this.style.cursor='hand'\" onclick=\"javascript: history.go(-1);\" ></td></tr>\n"; 
	$sToRender .= "<tr><td width=\"1\" style=\"background-color:#F5F6EE;\" valign=\"top\"><b>Subject: </b></td><td width=405 style=\"background-color:#F5F6EE;\">";
	$sToRender .= "<input name=\"CommentSubject\" id=\"CommentSubject\" style=\"background-color:#F5F6EE;BORDER-TOP-WIDTH: 0px; BORDER-LEFT-WIDTH: 0px; BORDER-BOTTOM-WIDTH: 0px; WIDTH: 100%; BORDER-RIGHT-WIDTH: 0px;width=80%\" readOnly  value=\"" . $oComment->getSubject() . "\">";
	$sToRender .= "</td><td colspan=2><b>Date: </b><font color=\"#056DCE\">" . $oComment->getDate() . "</font>&nbsp;&nbsp;&nbsp;</td></tr><tr><td colspan=4 valign=top>";	
	$sToRender .= "<TEXTAREA readOnly name=\"Comment\" id=\"Comment\"  rows=40 cols=100 style=\"BORDER-WIDTH: 0px;FONT-FAMILY: sans-serif;\"> ";
	$sToRender .= urldecode($oComment->getBody());
	$sToRender .= "</textarea></td>";
	$sToRender .= "</tr></table> ";		
	
	return $sToRender;	
}

?>