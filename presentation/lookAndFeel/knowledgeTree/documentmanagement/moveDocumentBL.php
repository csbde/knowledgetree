<?php

/**
 * $Id$
 *
 * Move document page.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */
 
require_once("../../../../config/dmsDefaults.php");

require_once("$default->fileSystemRoot/lib/security/permission.inc");

require_once("$default->fileSystemRoot/lib/users/User.inc");

require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");

require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");

require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/moveDocumentUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
 
if (checkSession()) {
	
	if (isset($fDocumentID) && isset($fFolderID)) {		
		if (isset($fForMove)) {
			if ($fConfirmed) {
				//we're trying to move a document
				$oDocument = & Document::get($fDocumentID);
				$oFolder = & Folder::get($fFolderID);
				$iOldFolderID = $oDocument->getFolderID();
				if (Permission::userHasDocumentWritePermission($fDocumentID) && Permission::userHasFolderWritePermission($fFolderID)) {
					//if the user has both document and folder write permissions				
					//get the old document path
					$sOldDocumentFileSystemPath = Folder::getFolderPath($iOldFolderID) . $oDocument->getFileName();
					//put the document in the new folder
					$oDocument->setFolderID($fFolderID);
					if ($oDocument->update(true)) {
						//get the old document path
						$sOldDocumentFileSystemPath = Folder::getFolderPath($iOldFolderID) . $oDocument->getFileName();
						//move the document on the file system
						if (PhysicalDocumentManager::moveDocument($sOldDocumentFileSystemPath, $oDocument, $oFolder)) {							
	                        
	                        // fire subscription alerts for the moved document (and the folder its in)
	                        $count = SubscriptionEngine::fireSubscription($fDocumentID, SubscriptionConstants::subscriptionAlertType("MovedDocument"),
	                                 SubscriptionConstants::subscriptionType("DocumentSubscription"),
	                                 array( "folderID" => $iOldFolderID,
	                                        "modifiedDocumentName" => $oDocument->getName(),
	                                        "oldFolderName" => Folder::getFolderName($iOldFolderID),
	                                        "newFolderName" => Folder::getFolderName($fFolderID) ));
	                        $default->log->info("moveDocumentBL.php fired $count subscription alerts for moved document " . $oDocument->getName());
	                        
	                        // fire folder subscriptions for the destination folder
	                        $count = SubscriptionEngine::fireSubscription($oDocument->getFolderID(), SubscriptionConstants::subscriptionAlertType("MovedDocument"),
	                                 SubscriptionConstants::subscriptionType("FolderSubscription"),
	                                 array( "modifiedDocumentName" => $oDocument->getName(),
	                                        "oldFolderName" => Folder::getFolderName($iOldFolderID),
	                                        "newFolderName" => Folder::getFolderName($fFolderID) ));
	                        $default->log->info("moveDocumentBL.php fired $count (folderID=$fFolderID) folder subscription alerts for moved document " . $oDocument->getName());
	                        
	                        
							//redirect to the view path
							redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID");
						} else {
							require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
							//we couldn't move the document on the file system
							//so reset the database values
							$oDocument->setFolderID($iOldFolderID);
							$oDocument->update();						
							$oPatternCustom = & new PatternCustom();
							$oPatternCustom->setHtml(getPage($fFolderID, $fDocumentID));
							$main->setCentralPayload($oPatternCustom);   
							$main->setErrorMessage("Could not move document on file system");
							$main->setFormAction($_SERVER["PHP_SELF"] . "?fForMove=1");
							$main->render();
						}
					} else {
						require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
						//had a problem with the database					
						$oPatternCustom = & new PatternCustom();
						$oPatternCustom->setHtml(getPage($fFolderID, $fDocumentID));
						$main->setCentralPayload($oPatternCustom);   
						$main->setErrorMessage("Could not update document in database");
						$main->setFormAction($_SERVER["PHP_SELF"] . "?fForMove=1&fDocumentID=$fDocumentID&fFolderID=$fFolderID");
						$main->render();
					}
				} else {
					require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getPage($fFolderID, $fDocumentID));
					$main->setCentralPayload($oPatternCustom);   
					$main->setFormAction($_SERVER["PHP_SELF"] . "?fForMove=1&fDocumentID=$fDocumentID&fFolderID=$fFolderID");
					$main->setErrorMessage("You do not have rights to move this document");
					$main->render();
				}
			} else {
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				$oPatternCustom = & new PatternCustom();

				$oDocument = Document::get($fDocumentID);
				
				// check if the selected folder has the same document type as the document we're moving
				if (Folder::folderIsLinkedToDocType($fFolderID, $oDocument->getDocumentTypeID())) {
					// check that there is no filename collision in the destination directory				
					$sNewDocumentFileSystemPath = Folder::getFolderPath($fFolderID) . $oDocument->getFileName();
					if (!file_exists($sNewDocumentFileSystemPath)) {
						// display confirmation page
						$oPatternCustom->setHtml(getConfirmationPage($fFolderID, $fDocumentID));
					} else {
						// filename collision
						$oPatternCustom->setHtml(getPage($fFolderID, $fDocumentID, "This folder already contains a document of the same name.  Please choose another directory"));
					}
				} else {
					// the right document type isn't mapped
					$oPatternCustom->setHtml(getPage($fFolderID, $fDocumentID, "You can't move the document to this folder because it cannot store the document type of your document.  Please choose another directory"));
				}
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fForMove=1&fDocumentID=$fDocumentID&fFolderID=$fFolderID");				
				$main->setCentralPayload($oPatternCustom);
				$main->render();				
			}			
		} else {		
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getPage($fFolderID, $fDocumentID));				
			$main->setCentralPayload($oPatternCustom);   
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fForMove=1&fDocumentID=$fDocumentID&fFolderID=$fFolderID");
			$main->render();
		}
	} else {
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml("");
		$main->setCentralPayload($oPatternCustom);
		$main->setErrorMessage("No document/folder selected");
		$main->render();
	}
}
?>