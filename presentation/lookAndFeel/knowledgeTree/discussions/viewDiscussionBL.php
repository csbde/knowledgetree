<?php
/**
 * $Id$
 *
 * View discussions.
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
require_once("viewDiscussionUI.inc");     
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");    
require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");    
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternBrowsableSearchResults.inc");    
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
require_once("$default->fileSystemRoot/lib/discussions/DiscussionThread.inc");  
require_once("$default->fileSystemRoot/lib/discussions/DiscussionComment.inc");   
require_once("$default->fileSystemRoot/presentation/Html.inc");

if (checkSession()) {
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();	
	if (isset($fForDiscussion)) {		
		if ($fDocumentID > 0) { 	
			$iThreadID = DiscussionThread::getThreadIDforDoc($fDocumentID);						
			if ($iThreadID) {// if thread ID does exist
				$oThread = DiscussionThread::get($iThreadID);					
				if($oThread) { //  if thread object exists
					// Iterate through the number of comments
					$sAllCommentID = $oThread->getAllCommentID();
					$arrAllCommentID = explode(",", $sAllCommentID);										
					$iNumMax = $oThread->getNumberOfReplies();						
					
					$sQuery = 	"SELECT 1 as ForView, subject, username, date, discussion_comments.id as com_id, discussion_threads.document_id as doc_id " .
					 			"FROM  (discussion_comments INNER JOIN users ON discussion_comments.user_id = users.id) INNER JOIN discussion_threads ON discussion_threads.id = discussion_comments.thread_id  " .
					 			"WHERE discussion_threads.id = " . $iThreadID .
					 			" ORDER BY date DESC";

				    $aColumns = array("subject", "username", "date");
				    $aColumnNames = array("<font color=white>Subject </font>", "<font color=white>User</font>", "<font color=white>Date</font>");
				    $aColumnTypes = array(3,1,1);
				    $aQueryStringVars = array("fViewComment", "iCommentID", "iDocumentID");
				    $aQueryStringCols = array("ForView", "com_id", "doc_id");
				    
				    for ($i = 0; $i < $iNumMax; $i++) {
						$aHyperLinkURL[$i] = $_SERVER['PHP_SELF'] ;		
					}
				    
				    $oSearchResults = & new PatternTableSqlQuery ($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%",  $aHyperLinkURL, $aQueryStringCols, $aQueryStringVars);
				    $sToRender .= renderHeading("Document Discussion Thread");
					$sToRender .= renderDocumentPath($oThread->getDocumentID());
				    $oPatternCustom->addHtml($sToRender);
				    $oPatternCustom->addHtml(getPageButtons($oThread));				    
				    $oPatternCustom->addHtml($oSearchResults->render());    
					
					// On opening, increment the number of views of current thread & update database
					if($_SESSION['Discussion' . $fDocumentID][0]->bViews !=true ){
						$oThread->setNumberOfViews();					
						if($oThread->Update() == false) $oPatternCustom->addHtml("Failed to update. Please Contact Database Administrator in this regard") ;
						$_SESSION['Discussion' . $fDocumentID][0]->bViews = true;
					} 																
				} else { 
					$main->setErrorMessage("Error creating discussion thread object");
				}						
			} else { // No current thread, option to create one					
				$main->setErrorMessage("No discussion thread is currently available");
				$oPatternCustom->addHtml(getNewThreadOption($fDocumentID));				
			}	
		} else { // Doument id  is negative 
			$main->setErrorMessage("Invalid Document ID.  ID may not be negative.");
		}
	} else if (isset($fViewComment)){	// User wants to view a comment
		if (isset($iCommentID)) {		// Check if a comment ID exists
			$oComment = DiscussionComment::get($iCommentID);
		  	$oUser = User::get($oComment->getUserID());		
		  	$main->setFormAction("$default->rootUrl/presentation/lookAndFeel/knowledgeTree/discussions/addCommentBL.php?fDocumentID=$iDocumentID&fCommentID=$iCommentID&fReplyComment=1");		
		  	$oPatternCustom->setHtml(getCommentBody($oComment,$iDocumentID,$oUser)) ;	  	
		}
	} else { // If no discussion exists 
		$main->setErrorMessage("Invalid function.  No such functionality exists for this page.");
	}
	$main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>