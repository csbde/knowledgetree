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
                                
                                // fire subscription alerts for the new document
                                $count = SubscriptionEngine::fireSubscription($fFolderID, SubscriptionConstants::subscriptionAlertType("AddDocument"),
                                         SubscriptionConstants::subscriptionType("FolderSubscription"),
                                         array( "newDocumentName" => $oDocument->getName(),
                                                "folderName" => Folder::getFolderName($fFolderID)));
                                $default->log->info("addDocumentBL.php fired $count subscription alerts for new document " . $oDocument->getName());
                                
                                //redirect to the document view page
                                redirect("$default->rootUrl/control.php?action=modifyDocument&fDocumentID=" . $oDocument->getID(). "&fFirstEdit=1");
                            } else {
                                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                                $oDocument->delete();
                                $oPatternCustom = & new PatternCustom();
                                $oPatternCustom->setHtml(getBrowseAddPage($fFolderID));
                                $main->setCentralPayload($oPatternCustom);
                                $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1");
                                $main->setFormEncType("multipart/form-data");
                                $main->setErrorMessage("An error occured while storing the document on the file system");
                                $main->render();
                            }
                        } else {
                            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                            $oPatternCustom = & new PatternCustom();
                            $oPatternCustom->setHtml(getBrowseAddPage($fFolderID));
                            $main->setCentralPayload($oPatternCustom);
                            $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1");
                            $main->setFormEncType("multipart/form-data");
                            $main->setErrorMessage("An error occured while storing the document in the database");
                            $main->render();
                        }
                    } else {
                        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                        $oPatternCustom = & new PatternCustom();
                        $oPatternCustom->setHtml(getBrowseAddPage($fFolderID));
                        $main->setCentralPayload($oPatternCustom);
                        $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1");
                        $main->setFormEncType("multipart/form-data");
                        $main->setErrorMessage("A document with this file name already exists in this folder");
                        $main->render();
                    }
                } else {
                    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                    $oPatternCustom = & new PatternCustom();
                    $oPatternCustom->setHtml(getBrowseAddPage($fFolderID));
                    $main->setCentralPayload($oPatternCustom);
                    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1");
                    $main->setFormEncType("multipart/form-data");
                    $main->setErrorMessage("Please select a document by first clicking on 'Browse'.  Then click 'Add'");
                    $main->render();
                }

            } else {
                //we're still just browsing
                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                $oPatternCustom = & new PatternCustom();
                $oPatternCustom->setHtml(getBrowseAddPage($fFolderID));
                $main->setCentralPayload($oPatternCustom);
                $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1");
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
