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
    //require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
    //require_once("addUnitUI.inc");
    //require_once("../adminUI.inc");
    require_once("addCommentUI.inc"); //###
    
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/lib/users/User.inc");
    //require_once("../viewDiscussionUI.inc"); //###
    //require_once("$default->fileSystemRoot/lib/unitmanagement/Unit.inc");
    //require_once("$default->fileSystemRoot/lib/unitmanagement/UnitOrganisationLink.inc");
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");    
    require_once("$default->fileSystemRoot/lib/discussions/DiscussionThread.inc");  //###
    require_once("$default->fileSystemRoot/lib/discussions/DiscussionComment.inc");  //###
    //require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    //require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    $oPatternCustom = & new PatternCustom();
          
	if(checksession) {	
		if (isset($fAddComment)) {		
			if ($fDocumentID > 0) { 	
				$main->setFormAction($_SERVER['PHP_SELF'] . "?fAddCommentSubmit=1&iDocumentID=$fDocumentID");
				$oPatternCustom->setHtml(getAddComment($fDocumentID,$sSubject,$sBody));
			}
			else {
			  //nothing	
			}
		} else if (isset($fViewComment)){		
			if (isset($iCommentID)) {
				$oComment = DiscussionComment::get($iCommentID);
			  	$oUser =  User::get($oComment->getUserID());
			  	
			  	$oPatternCustom->setHtml(getCommentBody($oComment->getBody(), $oComment->getSubject(),$oComment->getDate(),$iDocumentID,$oUser->getUserName())) ;			  	
			}		
		} else if (isset($fAddCommentSubmit)) {								
				$iThreadID = DiscussionThread::getThreadIDforDoc($iDocumentID);
				
				if ($iThreadID > 0){
					if ($_POST["NewComment"] != "" and $_POST["NewCommentSubject"] != "")
					{
						// Create the new comment					
						$oComment = & new DiscussionComment(urlencode($_POST["NewComment"]),$_POST["NewCommentSubject"],$_SESSION["userID"],$iDocumentID);			
						$oComment->setThreadID($iThreadID);
						$oComment->create();
						
						if($oComment->getID() > 0) {
							$oThread = DiscussionThread::get($iThreadID);
							$oThread->setLastCommentID($oComment->getID());
							$oThread->setNumberOfReplies();
							if ($oThread->Update()) {						
								$oPatternCustom->addHtml(getSubmitSuccessPage($iDocumentID));
							}else {
								$oPatternCustom->addHtml(getViewFailPage("Thread Object failed to update."));	
							}
						}else {
							$oPatternCustom->addHtml(getViewFailPage("Comment Object failed in creation.") );
						}
					}else {
						$oPatternCustom->addHtml(getViewFailPage("The subject line and/or body may be empty.") );
						$main->setFormAction($_SERVER['PHP_SELF'] . "?fAddCommentSubmit=1&iDocumentID=$fDocumentID");
						$oPatternCustom->addHtml(getAddComment($fDocumentID,$_POST["NewCommentSubject"],$_POST["NewComment"]));
					}
				}else{
					$oPatternCustom->addHtml(getViewFailPage("No threadID($iThreadID) exists for this document"));	
				}
				
		} else if (isset($fReplyComment)){  // if user is replying to existing comment
			$main->setFormAction($_SERVER['PHP_SELF'] . "?fAddCommentSubmit=1&iDocumentID=$fDocumentID");
			$oPatternCustom->addHtml(getAddComment($fDocumentID,"Re: " . $CommentSubject , "\n\n\n[Start Text Body]\n\n" . $Comment  . "\n\n[End Text Body]"));			
				
		}else { // If no discussion exists		
			$oPatternCustom->setHtml(getViewFailPage("No discussions exist"));
		}
	
	} // end of if checksession
	
    $main->setCentralPayload($oPatternCustom);
    $main->render();    
}
?>
