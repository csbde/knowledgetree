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

function getPageButtons($iDocumentID){
	global $default;
	$sToRender .= "<table width=100%><tr>\n";
	$sToRender .= "<td align=right width=100%><a href=\"" . generateControllerLink("addComment", "fDocumentID=$iDocumentID&fAddComment=1&fNewComment=1") . "\"><img src=\"" . KTHtml::getAddCommentButton() . "\" border=\"0\" /></a></td>\n";
	$sToRender .= "<td align=right width=10><a href=\"" . generateControllerLink("viewDocument", "fDocumentID=$iDocumentID&fAddComment=1") . "\"><img src=\"" . KTHtml::getBackButton() . "\"  border=\"0\" /></a></td>\n";
	$sToRender .= "</tr></table>\n";
	return $sToRender; 
}

// If there are no threads to view for a document
function getViewFailPage($sMessage) {
	global $default;
	if ( strlen($sMessage)>0 ) {
		$sToRender = $sMessage;
	} else {
		$sToRender = _("Current document has no discussion.  Press the ADD button to start a discussion.");
	}
	return $sToRender;
}

/** 
 *	Page to create a new thread
 *
 *	@param $iDocumentID -> a valid document ID
 */
function getNewThreadOption($iDocumentID) {
	global $default;   
	$sToRender .= _("Would you like to create a new Discussion thread for this document?");
	$sToRender .= "&nbsp;&nbsp;&nbsp;";
	$sToRender .= "<a href=\"" . generateControllerLink("addComment", "fDocumentID=" . $iDocumentID . "&fNewThread=1") . "\"><img src=\"" . KTHtml::getNewButton() . "\" border=\"0\"></a>";
	$sToRender .= "<a href=\"" . generateControllerLink("viewDocument", "fDocumentID=" . $iDocumentID) . "\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a>";
	return $sToRender;
}

/** 
 *	Views the list of comments for a document
 *  Prints out 1 comment 
 *  User needs to call this function many times to list all comments  
 *
 *	@param $iNum -> its the iterative index of the current comment
 *	@param $iThread -> a valid Thread Object
 *	@param $oComment -> a valid Comment Object
 */
function getViewComment($iNum, $oThread, $oComment, $sIndent) {
	global $default;
		
	$iSubjectChars = 59;
	// If iNum is odd then highlight it
	if (($iNum%2) == 1) $BGcolor = "#F5F6EE";
	if (($iNum%2) == 0) $UserBGcolor = "#F5F6EE";
	
	$sUserName = "";
	$oUser = User::get($oComment->getUserID());
	if ($oUser) {
		$sUserName = $oUser->getName();
	}
	// If the Subject string is too long
	if (strlen($oComment->getSubject())>$iSubjectChars ) $dotdot = " ...";
	 	
	$sToRender .= "<tr>\n";
	$sToRender .= "\t<td style=\"background-color:$BGcolor\" width=450>\n";
	$sToRender .= "\t\t$sIndent<a href=\"" . generateControllerLink("viewComment", "fCommentID=" . $oComment->getID() . "&fDocumentID=" . $oThread->getDocumentID() . "&fThreadID=" . $oThread->getID()) . "\" title=\"" . $oComment->getSubject() . "\">\n";
	$sToRender .= substr($oComment->getSubject(),0,$iSubjectChars ) . $dotdot . "</a></td>\n";
	$sToRender .= "\t<td style=\"background-color:$UserBGcolor\" width=\"100\" title=\"$sUserName\">\n";
	$sToRender .= "\t\t$sUserName</td>\n";
	$sToRender .= "\t<td style=\"background-color:$BGcolor\" width=\"80\">" . $oComment->getDate() . "<td></tr>\n";

	return $sToRender; 
}

/**
 * Prints out the list of discussion comments
 */
function getPage($iDocumentID, $aDocumentThreads) {
	global $default;
	
	$sToRender .= renderHeading(_("Document Discussion Comments"));
	$sToRender .= displayDocumentPath($iDocumentID);
	$sToRender .= getPageButtons($iDocumentID);		
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><b>" . _("Subject") . "</b></td>\n";
	$sToRender .= "<td><b>" . _("Author") . "</b></td>\n";
	$sToRender .= "<td><b>" . _("Date") . "</b></td>\n"; 
	$sToRender .= "</tr>\n";
	
	$iCount = 0;
	// for each thread, retrieve all comments
	for ($i=0; $i<count($aDocumentThreads); $i++) {			
		if ($aDocumentThreads[$i]) {
			// get comment tree
			$aCommentTree = getCommentTree($aDocumentThreads[$i]->getID(), -1);
			// now loop through the comments, and indent comments that have the same reply_to at the same level .... ??
			for ($j=0; $j<count($aCommentTree); $j++) {
				$default->log->info("commentTree[$j][comment]=" . arrayToString($aCommentTree[$j]["comment"]));
				$sToRender .= getViewComment($iCount++, $aDocumentThreads[$i], $aCommentTree[$j]["comment"], getIndent($aCommentTree[$j]["level"]));
			}
		} 
	}
	$sToRender .= "\n</table>\n\n";	
	return $sToRender;
}
/**
 * Returns the comments for a particular thread
 */
function getCommentTree($iThreadID, $iReplyTo, $iLevel = 0) {
	global $default;
	// find all comments for the specified thread and reply
        $aWhereClause = array("thread_id = ? AND in_reply_to = ? ORDER BY id", array($iThreadID, $iReplyTo));
	$aComments = DiscussionComment::getList($aWhereClause);/*ok*/
	// loop through these and find their children
	for ($i=0; $i<count($aComments); $i++) {
		$aTree[] = array( "comment" => $aComments[$i], "level" => $iLevel);
		$aChildren = getCommentTree($iThreadID, $aComments[$i]->getID(), $iLevel+1);
		if (count($aChildren) > 0) {
			$aTree = array_merge($aTree, $aChildren);
		}
	}
	$default->log->debug("getCommentTree($iThreadID, $iReplyTo, $iLevel), returning " . arrayToString($aTree));	
	return $aTree;
}

/**
 * Returns an indent string of appropriate length for the specified comment level
 */
function getIndent($iLevel) {
	global $default;
	$sToRender = "";
	for ($i=0; $i<$iLevel; $i++) {
		$sToRender .= "&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	$default->log->info("indent($iLevel) = " . $sToRender);
	return $sToRender;
}
?>
