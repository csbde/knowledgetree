<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->uiDirectory/documentmanagement/documentUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("restoreDeletedDocumentsUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");

/**
 * $Id$
 *  
 * Business logic for restoring deleted documents.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.administration.documentmanagement
 */

if (checkSession()) {	
	global $default;
	
    $oContent = new PatternCustom();
    
    if ($fDocumentID && $fFolderID) {
		if (isset($fForMove)) {
			if ($fConfirmed) {
		    	$oDocument = Document::get($fDocumentID);
		    	$oFolder = Folder::get($fFolderID);
		    	if ($oDocument && $oFolder) {
    				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");					
					// restore the document
					$oDocument->setStatusID(LIVE);
					$oDocument->setFolderID($oFolder->getID());
					
					// first try moving the document on the filesystem
					if (PhysicalDocumentManager::restore($oDocument)) {
						// now update the db
						if ($oDocument->update(true)) {
							// display confirmation page
							$oContent->setHtml(renderStatusPage($oDocument));
						} else {
							$default->log->error("restoreDeletedDocumentBL.php couldn't update db for " . arrayToString($oDocument));
							// TODO: display error
							$oContent->setHtml(renderErrorPage("The document could not be restored.  Please try again later"));
						}
					} else {
						$default->log->error("restoreDeletedDocumentBL.php filesystem restore failed for " . arrayToString($oDocument));
						// TODO: display error
						$oContent->setHtml(renderErrorPage("The document could not be restored.  Please try again later"));
					}
				} else {
		    		// no document
		    		$default->log->error("restoreDeletedDocumentBL.php documentID=$fDocumentID folderID=$fFolderID instantiation failed");
		    		// TODO: redirect to list page with error
		    		controllerRedirect("deletedDocuments", "");
				}
			} else {
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				$oContent->setHtml(renderConfirmationPage($fDocumentID, $fFolderID));
			}
    	} else {
    		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			// display browse page
			$oContent->setHtml(renderFolderBrowsePage($fDocumentID, $fFolderID));
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fForMove=1&fDocumentID=$fDocumentID&fFolderID=$fFolderID");
    	}
    } else { 
    	// no document
    	$default->log->error("restoreDeletedDocumentBL.php no document ID supplied");
    	// TODO: redirect to list page with error
    	controllerRedirect("deletedDocuments", "");
    }
    	
	$main->setCentralPayload($oContent);
	if ($main->getFormAction() == "") {
		$main->setFormAction($_SERVER["PHP_SELF"]);
	}
	$main->render();
}
?>