<?php

/**
* Business Logic to link a two documents together in a parent child
* relationship
*
* Expected form variable:
* o $fDocumentID - primary key of document user is currently viewing
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 22 May 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*/

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentLink.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("addDocumentLinkUI.inc");
	
	if (Permission::userHasDocumentWritePermission($fDocumentID)) {
		//user has permission to link this document to another
		if (isset($fForStore)) {
			//create a new document link
			$oDocumentLink = & new DocumentLink($fDocumentID, $fTargetDocumentID);			
			if ($oDocumentLink->create()) {
				redirect($default->rootUrl . "/control.php?action=viewDocument&fDocumentID=$fDocumentID");
			} else {
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				//an error occured while trying to create the document link
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getPage($fDocumentID));
				
				$main->setCentralPayload($oPatternCustom);
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fForStore=1");
				$main->setHasRequiredFields(true);
				$main->setErrorMessage("An error occured whilst attempting to link the two documents");	
				$main->render();	
				
			}			
		} else {
			//display the add page
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");										
						
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getPage($fDocumentID));
			$main->setCentralPayload($oPatternCustom);
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fForStore=1");
			$main->setHasRequiredFields(true);				
			$main->render();
		}
		
	}
		
		
}

?>