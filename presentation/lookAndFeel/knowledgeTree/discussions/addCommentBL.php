<?php
/**
 * $Id$
 *
 * Add a comment.
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
 
require_once("../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fAddCommentSubmit', 'fComment', 'fCommentID', 'fDocumentID', 'fInReplyTo', 'fNewThread', 'fReplyComment', 'fSubject', 'fThreadID');

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");    
require_once("addCommentUI.inc"); //###    
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");    
require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");    
require_once("$default->fileSystemRoot/lib/discussions/DiscussionThread.inc");   //###
require_once("$default->fileSystemRoot/lib/discussions/DiscussionComment.inc");  //###
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentUI.inc");    
require_once("$default->fileSystemRoot/presentation/Html.inc");   
          
if(checkSession()) {
	$oPatternCustom = & new PatternCustom();
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	if (isset($fAddCommentSubmit)) {
		$default->log->info("adding comment: subject=$fSubject; comment=$fComment");
		if ( (strlen($fSubject) > 0) && (strlen($fComment) > 0) ) {
			// create a new thread, unless we're replying
			if (isset($fNewThread)) {
				$oThread = & new DiscussionThread(-1, $fDocumentID, $_SESSION["userID"]);
				$oThread->create();
				$iThreadID = $oThread->getID();
				// if this is a new thread, then set inReplyTo to -1
				$fInReplyTo = -1;
			} else {
				// replying
				// retrieve the thread id
 				//$iThreadID = DiscussionThread::getThreadIDforDoc($fDocumentID);
 				$default->log->info("adding comment: SEtting thread id: " . $fThreadID);	
 				$iThreadID = $fThreadID;				
			}
			if ($iThreadID) {
				$default->log->info("addComment fInReplyTo=$fInReplyTo, threadID=$iThreadID");
				// Create the new comment					
				$oComment = & new DiscussionComment($fComment, $fSubject, $_SESSION["userID"], $iThreadID, $fInReplyTo);			
				$oComment->setThreadID($iThreadID);
				$oComment->create();
				
				if($oComment->getID() > 0) {
					$oThread = DiscussionThread::get($iThreadID);
					$oThread->setLastCommentID($oComment->getID());
					if ($oThread->getFirstCommentID() == -1){ // if it is a new Thread
						$oThread->setFirstCommentID($oComment->getID());								
					}
					// Session variable is set to true if user views the thread
					if ($_SESSION['Discussion' . $fDocumentID][0]->bViews != true ){
						$oThread->incrementNumberOfViews();							
						$_SESSION['Discussion' . $fDocumentID][0]->bViews = true;								
					}							
					$oThread->incrementNumberOfReplies();
					
					if ($oThread->Update()) {  //
						controllerRedirect("viewDiscussion", "fForDiscussion=1&fDocumentID=$fDocumentID");		
						//$oPatternCustom->addHtml(getSubmitSuccessPage($fDocumentID));
					} else {
						$main->setErrorMessage(_("Thread Object failed to update"));
					}
				} else {
					$main->setErrorMessage(_("Comment Object failed in creation"));
				}
			} else {  
				$main->setErrorMessage(_("Could not create a new discussion thread."));
			}
		} else { // the user has not entered BOTH a subject and a text body
			$main->setErrorMessage(_("The subject line and/or body should not be empty."));
			$main->setFormAction($_SERVER['PHP_SELF'] . "?fAddCommentSubmit=1&fDocumentID=$fDocumentID");
			$oPatternCustom->addHtml(getAddComment($fDocumentID, $fSubject, $fComment, $fCommentID, 1, $fThreadID));
		} // end of IF for Subject and Body test	 
	} else if (isset($fReplyComment)) {  // if user is replying to existing comment			
		$main->setFormAction($_SERVER['PHP_SELF'] . "?fAddCommentSubmit=1&fDocumentID=$fDocumentID");
		
		$oComment = DiscussionComment::get($fCommentID);						
		$oUser = User::get($oComment->getUserID());
		
		$sReplyBody = $oComment->getBody();
		
		$sReplyBodyHeader .= "\n\n> ------ Original Message ------";
		$sReplyBodyHeader .= "\n> User:     " . $oUser->getName();
		$sReplyBodyHeader .= "\n> Date:     " . $oComment->getDate();
		$sReplyBodyHeader .= "\n> Subject:  " . $oComment->getSubject();
		$sReplyBodyHeader .= "\n> ---------------------------------------";
		$default->log->info("replyBody before=$sReplyBody; replyBodyAfter=" . str_replace("%0D%0A" ,"%0D%0A>", $sReplyBody));
		$sReplyBody = $sReplyBodyHeader . "\n>" .  str_replace(">" ,"> >", $sReplyBody); // Put in ">" as indentation for the reply
		//$sReplyBody = $sReplyBodyHeader . "\n>" .  str_replace("%0D%0A" ,"%0D%0A>", $sReplyBody); // Put in ">" as indentation for the reply
		
		if (strpos($oComment->getSubject(), "Re:") != " "){
			$sReply = "Re: ";
		} else { $sReply = ""; }
		
		$oPatternCustom->addHtml(getAddComment($fDocumentID, $sReply . $oComment->getSubject() , urldecode($sReplyBody), $fCommentID, "-1" , $fThreadID));	
							
	} else if (isset($fNewThread)){ // Start adding a new Thread 
		$main->setFormAction($_SERVER['PHP_SELF'] . "?fAddCommentSubmit=1&fDocumentID=$fDocumentID&fNewThread=1");
		$oPatternCustom->addHtml(getAddComment($fDocumentID, $CommentSubject ,$Comment, $fCommentID, "1", $fThreadID));
	} else {
		// input validation	
		if (isset($fDocumentID)) {
			$main->setFormAction($_SERVER['PHP_SELF'] . "?fAddCommentSubmit=1&fDocumentID=$fDocumentID");
			$oPatternCustom->setHtml(getAddComment($fDocumentID,$sSubject,$sBody, $fCommentID, 1, $fThreadID));
		} else {
			$main->setErrorMessage(_("You did not specify a document to add a comment to."));
		}		
	}	    
	$main->setCentralPayload($oPatternCustom);
	$main->render();    
}
?>
