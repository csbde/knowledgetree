<?php

/**
* viewHistoryUI.php
* Contains the business logic required to build the document history view page.
* Will use viewHistoryUI.php for HTML
*
* Expected form varaibles:
*   o $fDocumentID - Primary key of document to view
*
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 12 February 2003
* @package presentation.lookAndFeel.knowledgeTree.documentManager
*/

require_once("../../../../config/dmsDefaults.php");

require_once("$default->fileSystemRoot/lib/security/permission.inc");

require_once("$default->fileSystemRoot/lib/users/User.inc");

require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");

require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewHistoryUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");

if (checkSession()) {	
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    if (isset($fDocumentID)) {		
		if (Permission::userHasDocumentReadPermission($fDocumentID)) {			
			$oDocument = & Document::get($fDocumentID);
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getPage($oDocument->getID(), $oDocument->getFolderID(), $oDocument->getName()));
			$main->setCentralPayload($oPatternCustom);   
			$main->render();
		} else {			
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml("");
			$main->setErrorMessage("You do not have permission to view this document's history");
			$main->setCentralPayload($oPatternCustom);   
			$main->render();
		}
		
	} else {
		$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml("");
			$main->setErrorMessage("No document currently selected");
			$main->setCentralPayload($oPatternCustom);   
			$main->render();
	}
}

?>
