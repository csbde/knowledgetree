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
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMetaData.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableTableSqlQuery.inc");
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
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/store.inc");

    if (isset($fFolderID)) {
        if (Permission::userHasFolderWritePermission($fFolderID)) {
            //user has permission to add document to this folder
            if (isset($fStore)) {
                // check that a document type has been selected
                if ($fDocumentTypeID) {
                // make sure the user actually selected a file first
                // and that something was uploaded
                    if ( (strlen($_FILES['fFile']['name']) > 0) && $_FILES['fFile']['size'] > 0) {
                        //if the user selected a file to upload
                        //create the document in the database
                        $oDocument = & PhysicalDocumentManager::createDocumentFromUploadedFile($_FILES['fFile'], $fFolderID);
                        // set the document title
                        $oDocument->setName($fName);
                        if (!(Document::documentExists($oDocument->getFileName(), $oDocument->getFolderID()))) {
                            if ($oDocument->create()) {
                                //if the document was successfully created in the db, then store it on the file system
                                if (PhysicalDocumentManager::uploadPhysicalDocument($oDocument, $fFolderID, "None", $_FILES['fFile']['tmp_name'])) {
                                    //create the web document link
                                    $oWebDocument = & new WebDocument($oDocument->getID(), -1, 1, NOT_PUBLISHED, getCurrentDateTime());
                                    if ($oWebDocument->create()) {
                                    	$default->log->error("addDocumentBL.php created web document for document ID=" . $oDocument->getID());                                    	
                                    } else {
                                    	$default->log->error("addDocumentBL.php couldn't create web document for document ID=" . $oDocument->getID());
                                    }
                                    //create the document transaction record
                                    $oDocumentTransaction = & new DocumentTransaction($oDocument->getID(), "Document created", CREATE);
                                    if ($oDocumentTransaction->create()) {
                                    	$default->log->debug("addDocumentBL.php created create document transaction for document ID=" . $oDocument->getID());                                    	
                                    } else {
                                    	$default->log->error("addDocumentBL.php couldn't create create document transaction for document ID=" . $oDocument->getID());
                                    }                                    
                                    
                                    //the document was created/uploaded due to a collaboration step in another
                                    //document and must be linked to that document
                                    if (isset($fDependantDocumentID)) {
                                    	$oDependantDocument = DependantDocumentInstance::get($fDependantDocumentID);
                                    	$oDocumentLink = & new DocumentLink($oDependantDocument->getParentDocumentID(), $oDocument->getID());
                                    	if ($oDocumentLink->create()) {
                                    		//no longer a dependant document, but a linked document
                                    		$oDependantDocument->delete();                         
                                    	} else {
                                    		//an error occured whilst trying to link the two documents automatically.  Email the parent document
                                    		//original to inform him/her that the two documents must be linked manually
                                    		$oParentDocument = Document::get($oDependantDocument->getParentDocumentID());
                                    		$oUserDocCreator = User::get($oParentDocument->getCreatorID());
                                    		
                                    		$sBody = $oUserDocCreator->getName() . ", an error occured whilst attempting to automatically link the document, '" .
                                    				$oDocument->getName() . "' to the document, '" . $oParentDocument->getName() . "'.  These two documents " .
                                    				" are meant to be linked for collaboration purposes.  As creator of the document, ' " . $oParentDocument->getName() . "', you are requested to " .
                                    				"please link them manually by browsing to the parent document, " .
                                    				generateControllerLink("viewDocument","fDocumentID=" . $oParentDocument->getID(), $oParentDocument->getName()) . 
                                    				"  and selecting the link button.  " . $oDocument->getName() . " can be found at " . $oDocument->generateFullFolderPath($oDocument->getFolderID());
                                    		
                                    		$oEmail = & new Email();
											$oEmail->send($oUserDocCreator->getEmail(), "Automatic document linking failed", $sBody);
											
											//document no longer dependant document, but must be linked manually
											$oDependantDocument->delete();                                    				
                                    	}
                                    }
                                    
                                    // now handle meta data, pass new document id to queries
									$aQueries = constructQuery(array_keys($_POST), array("document_id" =>$oDocument->getID()));
									for ($i=0; $i<count($aQueries); $i++) {
										$default->log->info("addDocumentBL.php metaDataQuery=" . $aQueries[$i]);
										$sql = $default->db;
										$sql->query($aQueries[$i]);
									}
									                                    
                                    // fire subscription alerts for the new document
                                    $count = SubscriptionEngine::fireSubscription($fFolderID, SubscriptionConstants::subscriptionAlertType("AddDocument"),
                                             SubscriptionConstants::subscriptionType("FolderSubscription"),
                                             array( "newDocumentName" => $oDocument->getName(),
                                                    "folderName" => Folder::getFolderName($fFolderID)));
                                    $default->log->info("addDocumentBL.php fired $count subscription alerts for new document " . $oDocument->getName());
                                    
                                    $default->log->info("addDocumentBL.php successfully added document " . $oDocument->getFileName() . " to folder " . Folder::getFolderPath($fFolderID) . " id=$fFolderID");                                    
                                    //redirect to the document details page
                                    controllerRedirect("viewDocument", "fDocumentID=" . $oDocument->getID());
                                } else {
                                	// couldn't store document in db
                                    $default->log->error("addDocumentBL.php Filesystem error attempting to store document " . $oDocument->getFileName() . " in folder " . Folder::getFolderPath($fFolderID) . "; id=$fFolderID");                                	
                                    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                                    // delete the document from the database
                                    $oDocument->delete();
                                    $oPatternCustom = & new PatternCustom();
                                	$oPatternCustom->setHtml(getStatusPage($fFolderID, "An error occured while storing the document on the file system, please try again.</td><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a>"));                                    
                                    $main->setCentralPayload($oPatternCustom);
                                    $main->render();
                                }
                            } else {
                            	// couldn't store document on fs
                                $default->log->error("addDocumentBL.php DB error storing document in folder " . Folder::getFolderPath($fFolderID) . " id=$fFolderID");                            	
                                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                                $oPatternCustom = & new PatternCustom();
                                $oPatternCustom->setHtml(getStatusPage($fFolderID, "An error occured while storing the document in the database, please try again.</td><td><a href=\"$default->rootUrl/control.php?action=addDocument&fFolderID=$fFolderID&fDocumentTypeID=$fDocumentTypeID\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"></a>"));                                
                                $main->setCentralPayload($oPatternCustom);
                                $main->render();
                            }
                        } else {
                        	// document already exists in folder
                            $default->log->error("addDocumentBL.php Document exists with name " . $oDocument->getFileName() . " in folder " . Folder::getFolderPath($fFolderID) . " id=$fFolderID");                        	
                            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                            $oPatternCustom = & new PatternCustom();
							$oPatternCustom->setHtml(getStatusPage($fFolderID, "A document with this file name already exists in this folder</td><td><a href=\"$default->rootUrl/control.php?action=addDocument&fFolderID=$fFolderID&fDocumentTypeID=$fDocumentTypeID\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"></a>"));                            
                            $main->setCentralPayload($oPatternCustom);
                            $main->render();
                        }
                    } else {
                    	// no uploaded file
                        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                        $oPatternCustom = & new PatternCustom();
						$oPatternCustom->setHtml(getStatusPage($fFolderID, "You did not select a valid document to upload</td><td><a href=\"$default->rootUrl/control.php?action=addDocument&fFolderID=$fFolderID&fDocumentTypeID=$fDocumentTypeID\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"></a>"));                        
                        $main->setCentralPayload($oPatternCustom);
                        $main->render();
                    }
                } else {
                    // no document type was selected
                    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                    $oPatternCustom = & new PatternCustom();
                    $oPatternCustom->setHtml(getStatusPage($fFolderID, "A valid document type was not selected.</td><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a>"));
                    $main->setCentralPayload($oPatternCustom);
                    $main->render();
                }                
            } else {
                //we're still just browsing                
                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                $oPatternCustom = & new PatternCustom();
                $oPatternCustom->setHtml(getPage($fFolderID, $fDocumentTypeID, $fDependantDocumentID));
                $main->setCentralPayload($oPatternCustom);
                $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID" . 
                					 (isset($fDependantDocumentID) ? "&fDependantDocumentID=$fDependantDocumentID" : "") . 
                					 (isset($fDocumentTypeID) ? "&fDocumentTypeID=$fDocumentTypeID" : ""));
                $main->setFormEncType("multipart/form-data");
                $main->setHasRequiredFields(true);
                $main->render();
            }
        } else {
            //user does not have write permission for this folder,
            //so don't display add button
            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
            $oPatternCustom = & new PatternCustom();
            $oPatternCustom->setHtml(getPage($fFolderID, $fDocumentTypeID, $fDependantDocumentID, "You do not have permission to add a document to this folder</td><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a>"));
            $main->setCentralPayload($oPatternCustom);
            $main->render();
        }
    } else {
        //no folder id was set when coming to this page,
        //so display an error message
        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
        $oPatternCustom = & new PatternCustom();
        $oPatternCustom->setHtml("<p class=\"errorText\">You haven't selected a folder to add a document to.</p>\n");
        $main->setCentralPayload($oPatternCustom);
        $main->render();
    }
}
?>