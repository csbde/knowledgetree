<?php
/**
* BL information for viewing a Discussion	
*
* @author Omar Rahbeeni
* @date 8 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

	require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {    
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");    
    require_once("viewDiscussionUI.inc");     
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/lib/users/User.inc");    
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");    
    //require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternBrowsableSearchResults.inc");    
    require_once("$default->fileSystemRoot/lib/discussions/DiscussionThread.inc");  
    require_once("$default->fileSystemRoot/lib/discussions/DiscussionComment.inc");   
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    $oPatternCustom = & new PatternCustom();

if(checkSession()) {	
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
					    $aColumnNames = array("Subject", "User", "Date");
					    $aColumnTypes = array(3,1,1);
					        
					    $aQueryStringVars = array("fViewComment", "iCommentID", "iDocumentID");
					    $aQueryStringCols = array("ForView", "com_id", "doc_id");
					    
					    for ($i = 0; $i < $iNumMax; $i++) {
							$aHyperLinkURL[$i] =$_SERVER['PHP_SELF'] ;		
						}
					    
					    if (!isset($fStartIndex)) { $iStartIndex = 0 ;}
					    else { $iStartIndex = $fStartIndex ;}
					    
					    $oSearchResults = & new PatternBrowseableSearchResults ($sQuery, 13, $aColumns, $aColumnTypes, $aColumnNames, $aHyperLinkURL, $aQueryStringCols, $aQueryStringVars);
					    $oSearchResults->setStartIndex($iStartIndex);
					    $oSearchResults->setQueryString("&fDocumentID=$fDocumentID&fForDiscussion=1");
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
		}else { // If no discussion exists 
			$main->setErrorMessage("Invalid function.  No such functionality exists for this page.");
		}	
	} // end of if checksession
	
	$main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
