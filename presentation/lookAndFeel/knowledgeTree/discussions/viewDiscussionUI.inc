<?php
/**
 * $Id$
 *
 * View discussion UI functions.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Omar Rahbeeni, Jam Warehouse (Pty) Ltd, South Africa
 * @package discussions
 */

function getPageButtons($oThread){
	global $default;
	
	$sToRender .= "<table width=100%><tr>\n";
	$sToRender .= "<td align=right width=100%><a href=\"" . generateControllerLink("addComment", "fDocumentID=" . $oThread->getDocumentID() . "&fAddComment=1&fNewComment=1") . "\"><img src=\"$default->graphicsUrl/widgets/addcomment.gif\" border=\"0\" /></a></td>\n";
	$sToRender .= "<td align=right width=10><a href=\"" . generateControllerLink("viewDocument", "fDocumentID=" . $oThread->getDocumentID() . "&fAddComment=1") . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\"  border=\"0\" /></a></td>\n";
		
	$sToRender .= "</tr></table>\n";
	
	return $sToRender ; 
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
		$sToRender .= "<td  align=right><a href=\"" . generateControllerLink("addComment", "fDocumentID=" . $oThread->getDocumentID() . "&fAddComment=1&fCommentID=" . $oComment->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/addcomment.gif\" border=\"0\" /></a>";
		$sToRender .= "<img src=\"$default->graphicsUrl/widgets/back.gif\" onmouseover=\"this.style.cursor='hand'\" onclick=\"javascript: history.go(-1);\" ></td>";
		$sToRender .= "</tr></table><br>\n";			
		$sToRender .= "<table border=\"0\" width=\"100%\">\n";
		$sToRender .= "<tr><td></td>\n";
		$sToRender .= "<td><b>Subject</b></td>\n";
		$sToRender .= "<td><b>Author</b></td>\n";
		$sToRender .= "<td><b>Date</b></td>\n"; 
		$sToRender .= "</tr>\n"; 
	}
	
	$sToRender .= "<tr><td>$iNum </td><td style=\"background-color:$BGcolor\" width=450><a style=\"width:94%\" href=\"" . $_SERVER['PHP_SELF'] . "?fViewComment=1&iCommentID=" . $oComment->getID() . "&iDocumentID=" . $oThread->getDocumentID() . "\" title=\"" . $oComment->getSubject() . "\">" .  substr($oComment->getSubject(),0,$iSubjectChars ) . $dotdot . "</a></td>\n";
	$sToRender .= "<td style=\"background-color:$UserBGcolor\" width=\"100\" title=\"$oUser->getUserName()\">" . substr($oUser->getUserName(),0,10) . $Userdotdot . "</td>\n"; 
	$sToRender .= "<td style=\"background-color:$BGcolor\" width=\"80\">" . $oComment->getDate() . "<td></tr>\n";
	
	// If this is the last subject line then close the table
	if ($iNum == ($oThread->getNumberOfReplies())) { $sToRender .= "\n</table>\n\n";}	
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
	$aBody = explode("\n",$oComment->getBody());
	$sBody = "";
	$numChars = 160;  // number of chars in a line
	
	// Used for wrapping text that may be too long on one any line
	for ($i=0; $i <= sizeof($aBody) ;$i++) {
		if (strlen($aBody[$i]) >$numChars){
			$loop = (strlen($aBody[$i]) - (strlen($aBody[$i])) % $numChars)/$numChars +1;			
			
			$j=$numChars;
			for($j=0; $j < $loop; $j++ ) {
				$sBody .= "<br>" . substr($aBody[$i],($j*$numChars), $numChars) . "";							
			}  
		} else { $sBody .= "<br>"  . $aBody[$i];	} 
	}	
			
	$sToRender .= "<br><table width=100% border=0><tr ><td colspan=2><b>Author: </b>" . $oUser->getUserName() . "</td>\n";	
	$sToRender .= "<td align=right>\n";	
	$sToRender .= "<a href=\"" . generateControllerLink("addComment", "fDocumentID=" . $iDocumentID . "&fReplyComment=1&fCommentID=" . $oComment->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/reply.gif\" border=\"0\" /></a>";	
	$sToRender .= "</td><td width=30><a href=\"" . generateControllerLink("viewDiscussion", "fForDiscussion=1&fDocumentID=$iDocumentID") . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=0 ><a></td></tr>\n"; 
	$sToRender .= "<tr><td width=\"1\" style=\"background-color:#F5F6EE;\" ><b>Subject: </b></td><td width=405 style=\"background-color:#F5F6EE;\">";	
	$sToRender .= $oComment->getSubject();
	$sToRender .= "</td><td colspan=2 nowrap style=\"background-color:#F5F6EE;\"><b>Date: </b><font color=\"#056DCE\">" . $oComment->getDate() . "</font>&nbsp;&nbsp;&nbsp;</td></tr><tr><td colspan=4 valign=top>";			
	$sToRender .= "<table border=0><tr><td>";
	$sToRender .= $sBody;
	$sToRender .= "</td></tr></table>";
	$sToRender .= "</td>\n";	
		
	$sToRender .= "</tr></table> ";		
	
	return $sToRender;	
}

?>