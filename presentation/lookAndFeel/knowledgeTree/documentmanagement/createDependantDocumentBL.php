<?php

/** Business logic for requesting the creation of a new document that
* will be linked to an existing one
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 10 June 2003
*/


require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("createDependantDocumentUI.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/lib/email/Email.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DependantDocumentInstance.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	
	
	//TODO REMOVE THIS LINE - FOR TESTING ONLY!!!!	
	if (!isset($fDocumentID)) {
		$fDocumentID = 1;
	}
	
	if (isset($fForStore)) {
		$oDependantDocument = & new DependantDocumentInstance($fDocumentTitle, $fUserID, $fTargetDocumentID, $fDocumentID);		
		if ($oDependantDocument->create()) {		
			$oUser = User::get($fUserID);			
			if ($oUser->getEmailNotification()) {
				//notify the user by email if they wish to be notified by email	            
	            $oTemplateDocument = & Document::get($fTargetDocumentID);	            
	            
	            
				$sBody = $oUser->getName() . ", a step in the document collaboration process requires you to create a new document.  " .
								generateLink("/control.php","action=dashboard","Log onto KnowledgeTree") . " and select the relevant link under the 'Dependant Documents' heading on your dashboard when you are ready to upload it.  ";
								//if we have a template document
				if (!($oTemplateDocument === false)) {
					$sBody .= "The document entitled " . generateLink("/control.php", "action=viewDocument&fDocumentID=" . $oTemplateDocument->getID(), $oTemplateDocument->getName()) . " " .
								"can be used as a template";									
				}
						
				$oEmail = & new Email();
				$oEmail->send($oUser->getEmail(), "Dependant document creation required", $sBody);
			}
			//go back to the document page you were viewing
			redirect($default->rootUrl . "/control.php?action=viewDocument&fDocumentID=$fDocumentID");			
		} else {
			//dependant document creation failed - display an error message
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
			$oDocument = Document::get($fDocumentID);
		
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getPage($oDocument->getFolderID(), $fDocumentID, $fUnitID, $fUserID, $fDocumentTitle, $fTemplateDocument));			
			$main->setCentralPayload($oPatternCustom);
	        $main->setOnLoadJavaScript("disable(document.MainForm.fTargetDocument)");			
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fForStore=1");
			$main->setErrorMessage("An error occurred whilst trying to create the dependant document");
			$main->render(); 
			
		}		
	} else {
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		//we're browsing, so just display the page	
		$oDocument = Document::get($fDocumentID);
		
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml(getPage($oDocument->getFolderID(), $fDocumentID, $fUnitID, $fUserID, $fDocumentTitle, $fTemplateDocument));
	    $main->setOnLoadJavaScript("disable(document.MainForm.fTargetDocument)");		
		$main->setCentralPayload($oPatternCustom);
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fForStore=1");
		$main->render();			
	}
}

?>