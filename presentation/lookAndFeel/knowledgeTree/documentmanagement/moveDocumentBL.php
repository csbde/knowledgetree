<?php


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
				$iOldFolderID = $oDocument->getFolderID();
				if (Permission::userHasDocumentWritePermission($fDocumentID) && Permission::userHasFolderWritePermission($fFolderID)) {
					//if the user has both document and folder write permissions				
					//get the old document path
					$sOldDocumentFileSystemPath = Folder::getFolderPath($iOldFolderID) . $oDocument->getFileName();
					//put the document in the new folder
					$oDocument->setFolderID($fFolderID);
					if ($oDocument->update(true)) {
						//get the new document path
						$sNewDocumentFileSystemPath = Folder::getFolderPath($oDocument->getFolderID()) . $oDocument->getFileName();
						//move the document on the file system
						if (PhysicalDocumentManager::move($sOldDocumentFileSystemPath, $sNewDocumentFileSystemPath)) {
	                        
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
				// display confirmation page
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getConfirmationPage($fFolderID, $fDocumentID));
				$main->setCentralPayload($oPatternCustom);   
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fForMove=1&fDocumentID=$fDocumentID&fFolderID=$fFolderID");
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
