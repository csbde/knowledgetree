<?php
/**
* BL information for viewing a Discussion	
*
* @author Omar Rahbeeni
* @date 5 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
	require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
    
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");    
    require_once("addCommentUI.inc"); //###    
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/lib/users/User.inc");    
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");    
    require_once("$default->fileSystemRoot/lib/discussions/DiscussionThread.inc");   //###
    require_once("$default->fileSystemRoot/lib/discussions/DiscussionComment.inc");  //###    
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    $oPatternCustom = & new PatternCustom();
          
	if(checkSession()) {	
		if (isset($fAddComment)) {	// User wishes to add a comment		
			if ($fDocumentID > 0) { // The document ID is positive
				$main->setFormAction($_SERVER['PHP_SELF'] . "?fAddCommentSubmit=1&iDocumentID=$fDocumentID");
				$oPatternCustom->setHtml(getAddComment($fDocumentID,$sSubject,$sBody));
			}
			else {	// And invalid Document ID was sent
				$main->setErrorMessage("The Document id cannot be lss than 0.  ID is invalid.");			
			}			
			
		} else if (isset($fViewComment)){  // User wishes to view a comment
					
			if (isset($iCommentID)) {
				$oComment = DiscussionComment::get($iCommentID);
			  	$oUser =  User::get($oComment->getUserID());
			  	
			  	$oPatternCustom->setHtml(getCommentBody($oComment->getBody(), $oComment->getSubject(),$oComment->getDate(),$iDocumentID,$oUser->getUserName())) ;			  	
			}		
			
		} else if (isset($fAddCommentSubmit)) {					
			if ($_POST["NewComment"] != "" && $_POST["NewCommentSubject"] != "")						{
				
				if (isset($fNewThread)){ // Create a New Thread for this document as it doesn't exist
					 
					// Create the thread Object
					$oThread = & new DiscussionThread(-1,$iDocumentID, $_SESSION["userID"]);
					$oThread->create();					
					$iThreadID = $oThread->getID();					
				}		
				else { // Update the existing thread				
					$iThreadID = DiscussionThread::getThreadIDforDoc($iDocumentID);
				} 	
										
				if ($iThreadID > 0){											
						// Create the new comment					
						$oComment = & new DiscussionComment(urlencode($_POST["NewComment"]),$_POST["NewCommentSubject"],$_SESSION["userID"],$iDocumentID);			
						$oComment->setThreadID($iThreadID);
						$oComment->create();
						
						if($oComment->getID() > 0) {
							
							$oThread = DiscussionThread::get($iThreadID);
							$oThread->setLastCommentID($oComment->getID());
							if ($oThread->getFirstCommentID() == -1){ // if it is a new Thread
								
								$oThread->setFirstCommentID($oComment->getID());								
							}
							if($_SESSION['Discussion' . $iDocumentID][0]->bViews != true ){ // Session variable is set to true if user views the thread
								
								$oThread->setNumberOfViews();							
								$_SESSION['Discussion' . $iDocumentID][0]->bViews = true;								
							}							
							$oThread->setNumberOfReplies();
							
							if ($oThread->Update()) {  //
														
								$oPatternCustom->addHtml(getSubmitSuccessPage($iDocumentID));
							}else {
								$main->setErrorMessage("Thread Object failed to update");								
							}
						
						}else {
							$main->setErrorMessage("Comment Object failed in creation");							
						}
										
				}else{ // There is no thread id for this document  
					$main->setErrorMessage("No threadID($iThreadID) exists for this document");					
				}// End Of if for THREAD ID test
									
			}else { // the user has not entered BOTH a subject and a text body
				$main->setErrorMessage("The subject line and/or body may be empty" . $NewCommentSubject . "#" . $NewComment);				
				$main->setFormAction($_SERVER['PHP_SELF'] . "?fAddCommentSubmit=1&iDocumentID=$fDocumentID");
				$oPatternCustom->addHtml(getAddComment($fDocumentID,$_POST["NewCommentSubject"],$_POST["NewComment"]));
			} // end of IF for Subject and Body test	
				
		} else if (isset($fReplyComment)){  // if user is replying to existing comment			
			$main->setFormAction($_SERVER['PHP_SELF'] . "?fAddCommentSubmit=1&iDocumentID=$fDocumentID");
			
			$oComment = DiscussionComment::get($fCommentID);
			$oPatternCustom->addHtml(getAddComment($fDocumentID,"Re: " . $oComment->getSubject() , "\n\n\n[Start Text Body]\n\n" . urldecode( $oComment->getBody())  . "\n\n[End Text Body]"));	
								
		} else if (isset($fNewThread)){ // Start adding a new Thread 
			$main->setFormAction($_SERVER['PHP_SELF'] . "?fAddCommentSubmit=1&iDocumentID=$fDocumentID&fNewThread=1");
			$oPatternCustom->addHtml(getAddComment($fDocumentID, $CommentSubject , $Comment ));
					
		} else { // If no discussion exists			
			$main->setErrorMessage("Error: No discussion thread available");			
		}	
	} // end of if checksession
	
    $main->setCentralPayload($oPatternCustom);
    $main->render();    
}
?>
