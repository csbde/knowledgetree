<?php
/**
* Business Logic to add a new document to the 
* database.  Will use addDocumentUI.inc for presentation
*
* Expected form variable:
* o $fFolderID - primary key of folder user is currently browsing
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 28 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*/

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/DependantDocumentInstance.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentLink.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
    require_once("$default->fileSystemRoot/lib/web/WebDocument.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");
    require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");
    require_once("addDocumentUI.inc");

    if (isset($fFolderID)) {
        if (Permission::userHasFolderWritePermission($fFolderID)) {
            //user has permission to add document to this folder
            if (isset($fForStore)) {
                //user wants to store a document
                
                // check that the folder has a default document type
                if (Folder::getDefaultFolderDocumentType($fFolderID)) {
                //make sure the user actually selected a file first
                    if (strlen($_FILES['fFile']['name']) > 0) {
                        //if the user selected a file to upload
                        //create the document in the database
                        $oDocument = & PhysicalDocumentManager::createDocumentFromUploadedFile($_FILES['fFile'], $fFolderID);
                        if (!(Document::documentExists($oDocument->getFileName(), $oDocument->getFolderID()))) {
                            if ($oDocument->create()) {
                                //if the document was successfully created in the db, then store it on the file system
                                if (PhysicalDocumentManager::uploadPhysicalDocument($oDocument, $fFolderID, "None", $_FILES['fFile']['tmp_name'])) {
                                    //create the web document link
                                    $oWebDocument = & new WebDocument($oDocument->getID(), -1, 1, NOT_PUBLISHED, getCurrentDateTime());
                                    $oWebDocument->create();
                                    //create the document transaction record
                                    $oDocumentTransaction = & new DocumentTransaction($oDocument->getID(), "Document created", CREATE);
                                    $oDocumentTransaction->create();
                                    $default->log->info("addDocumentBL.php successfully added document " . $oDocument->getFileName() . " to folder " . Folder::getFolderPath($fFolderID) . " id=$fFolderID");
                                    
                                    //the document was created/uploaded due to a collaboration step in another
                                    //document and must be linked to that document
                                    if (isset($fDependantDocumentID)) {
                                    	$oDependantDocument = DependantDocumentInstance::get($fDependantDocumentID);
                                    	$oDocumentLink = & new DocumentLink($oDependantDocument->getParentDocumentID(), $oDocument->getID());
                                    	if ($oDocumentLink->create()) {
                                    		//no longer a dependant document, but a linked document
                                    		$oDependantDocument->delete();                         
                                    	}
                                    }
                                    
                                    // fire subscription alerts for the new document
                                    $count = SubscriptionEngine::fireSubscription($fFolderID, SubscriptionConstants::subscriptionAlertType("AddDocument"),
                                             SubscriptionConstants::subscriptionType("FolderSubscription"),
                                             array( "newDocumentName" => $oDocument->getName(),
                                                    "folderName" => Folder::getFolderName($fFolderID)));
                                    $default->log->info("addDocumentBL.php fired $count subscription alerts for new document " . $oDocument->getName());
                                    
                                    //redirect to the document view page
                                    redirect("$default->rootUrl/control.php?action=modifyDocument&fDocumentID=" . $oDocument->getID(). "&fFirstEdit=1");
                                } else {
                                	// couldn't store document in db
                                    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                                    $oDocument->delete();
                                    $oPatternCustom = & new PatternCustom();
                                    $oPatternCustom->setHtml(getBrowseAddPage($fFolderID, $fDependantDocumentID));
                                    $main->setCentralPayload($oPatternCustom);
                                    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1" . (isset($fDependantDocumentID) ? "&fDependantDocumentID=$fDependantDocumentID" : ""));
                                    $main->setFormEncType("multipart/form-data");
                                    $main->setErrorMessage("An error occured while storing the document on the file system");
                                    $default->log->error("addDocumentBL.php Filesystem error attempting to store document " . $oDocument->getFileName() . " in folder " . Folder::getFolderPath($fFolderID) . " id=$fFolderID");
                                    $main->render();
                                }
                            } else {
                            	// couldn't store document on fs
                                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                                $oPatternCustom = & new PatternCustom();
                                $oPatternCustom->setHtml(getBrowseAddPage($fFolderID, $fDependantDocumentID));
                                $main->setCentralPayload($oPatternCustom);
                                $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1"  . (isset($fDependantDocumentID) ? "&fDependantDocumentID=$fDependantDocumentID" : ""));
                                $main->setFormEncType("multipart/form-data");
                                $main->setErrorMessage("An error occured while storing the document in the database");
                                $default->log->error("addDocumentBL.php DB error storing document in folder " . Folder::getFolderPath($fFolderID) . " id=$fFolderID");
                                $main->render();
                            }
                        } else {
                        	// document already exists in folder
                            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                            $oPatternCustom = & new PatternCustom();
                            $oPatternCustom->setHtml(getBrowseAddPage($fFolderID, $fDependantDocumentID));
                            $main->setCentralPayload($oPatternCustom);
                            $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1"  . (isset($fDependantDocumentID) ? "&fDependantDocumentID=$fDependantDocumentID" : ""));
                            $main->setFormEncType("multipart/form-data");
                            $main->setErrorMessage("A document with this file name already exists in this folder");
                            $default->log->error("addDocumentBL.php Document exists with name " . $oDocument->getFileName() . " in folder " . Folder::getFolderPath($fFolderID) . " id=$fFolderID");
                            $main->render();
                        }
                    } else {
                        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                        $oPatternCustom = & new PatternCustom();
                        $oPatternCustom->setHtml(getBrowseAddPage($fFolderID, $fDependantDocumentID));
                        $main->setCentralPayload($oPatternCustom);
                        $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1" . (isset($fDependantDocumentID) ? "&fDependantDocumentID=$fDependantDocumentID" : ""));
                        $main->setFormEncType("multipart/form-data");
                        $main->setErrorMessage("Please select a document by first clicking on 'Browse'.  Then click 'Add'");
                        $main->render();
                    }
                } else {
                    // the folder doesn't have a default document type
                    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                    $oPatternCustom = & new PatternCustom();
                    $oPatternCustom->setHtml(getBrowsePage($fFolderID, $fDependantDocumentID));
                    $main->setCentralPayload($oPatternCustom);
                    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1");
                    $main->setFormEncType("multipart/form-data");
                    $main->setErrorMessage("The folder you're attempting to add the document to doesn't have a default document type.<br>Please correct this and try again.</td><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a>");
                    $main->render();
                }                

            } else {
                //we're still just browsing                
                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                $oPatternCustom = & new PatternCustom();
                $oPatternCustom->setHtml(getBrowseAddPage($fFolderID, $fDependantDocumentID));
                $main->setCentralPayload($oPatternCustom);
                $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1" . (isset($fDependantDocumentID) ? "&fDependantDocumentID=$fDependantDocumentID" : ""));
                $main->setFormEncType("multipart/form-data");
                $main->render();
            }
        } else {
            //user does not have write permission for this folder,
            //so don't display add button
            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
            $oPatternCustom = & new PatternCustom();
            $oPatternCustom->setHtml(getBrowsePage($fFolderID));
            $main->setCentralPayload($oPatternCustom);
            $main->setErrorMessage("You do not have permission to add a document to this folder</td><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a>");
            $main->render();
        }
    } else {
        //no folder id was set when coming to this page,
        //so display an error message
        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
        $oPatternCustom = & new PatternCustom();
        $oPatternCustom->setHtml("<p class=\"errorText\">No folder to which a document can be added is currently selected</p>\n");
        $main->setCentralPayload($oPatternCustom);
        $main->render();
    }
}


?>
