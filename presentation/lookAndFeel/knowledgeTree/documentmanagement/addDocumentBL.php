<?php
/**
 * $Id$
 *
 * Business Logic to add a new document to the 
 * database.  Will use addDocumentUI.inc for presentation
 *
 * Expected form variable:
 * o $fFolderID - primary key of folder user is currently browsing
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */

require_once("../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fFolderID', 'fStore', 'fDocumentTypeID', 'fName', 'fDependantDocumentID');
if (!checkSession()) {
    exit(0);
}

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

$postExpected = KTUtil::arrayGet($_REQUEST, "postExpected");
$postReceived = KTUtil::arrayGet($_REQUEST, "postReceived");
if (!is_null($postExpected) && is_null($postReceived)) {
    // A post was to be initiated by the client, but none was received.
    // This means post_max_size was violated.
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();
    $errorMessage = _("You tried to upload a file that is larger than the PHP post_max_size setting.");
    $oPatternCustom->setHtml(getStatusPage($fFolderID, $errorMessage . "</td><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a>")); 
    $main->setCentralPayload($oPatternCustom);
    $main->render();
    exit(0);
}

if (!isset($fFolderID)) {
    //no folder id was set when coming to this page,
    //so display an error message
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml("<p class=\"errorText\">" . _("You haven't selected a folder to add a document to.") . "</p>\n");
    $main->setCentralPayload($oPatternCustom);
    $main->render();
    exit(0);
}

$oFolder = Folder::get($fFolderID);
if (!Permission::userHasFolderWritePermission($oFolder)) {
    //user does not have write permission for this folder,
    //so don't display add button
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml(getPage($fFolderID, $fDocumentTypeID, $fDependantDocumentID, _("You do not have permission to add a document to this folder") . "</td><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a>"));
    $main->setCentralPayload($oPatternCustom);
    $main->render();
    exit(0);
}

//user has permission to add document to this folder
if (!isset($fStore)) {
    //we're still just browsing                
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml(getPage($fFolderID, $fDocumentTypeID, $fDependantDocumentID));
    $main->setCentralPayload($oPatternCustom);
    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&postExpected=1" . 
                         (isset($fDependantDocumentID) ? "&fDependantDocumentID=$fDependantDocumentID" : "") . 
                         (isset($fDocumentTypeID) ? "&fDocumentTypeID=$fDocumentTypeID" : ""));
    $main->setFormEncType("multipart/form-data");
    $main->setHasRequiredFields(true);
    $main->render();
    exit(0);
}

// check that a document type has been selected
if (!$fDocumentTypeID) {
    // no document type was selected
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml(getStatusPage($fFolderID, _("A valid document type was not selected.") . "</td><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a>"));
    $main->setCentralPayload($oPatternCustom);
    $main->render();
    exit(0);
}

// make sure the user actually selected a file first
// and that something was uploaded
if (!((strlen($_FILES['fFile']['name']) > 0) && $_FILES['fFile']['size'] > 0)) {
    // no uploaded file
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();
    $message = _("You did not select a valid document to upload");

    $errors = array(
       1 => _("The uploaded file is larger than the PHP upload_max_filesize setting"),
       2 => _("The uploaded file is larger than the MAX_FILE_SIZE directive that was specified in the HTML form"),
       3 => _("The uploaded file was not fully uploaded to KnowledgeTree"),
       4 => _("No file was selected to be uploaded to KnowledgeTree"),
       6 => _("An internal error occurred receiving the uploaded document"),
    );
    $message = KTUtil::arrayGet($errors, $_FILES['fFile']['error'], $message);
    $oPatternCustom->setHtml(getStatusPage($fFolderID, $message . "</td><td><a href=\"$default->rootUrl/control.php?action=addDocument&fFolderID=$fFolderID&fDocumentTypeID=$fDocumentTypeID\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\"></a>"));                        
    $main->setCentralPayload($oPatternCustom);
    $main->render();
    exit(0);
}

//if the user selected a file to upload
//create the document in the database
$oDocument = & PhysicalDocumentManager::createDocumentFromUploadedFile($_FILES['fFile'], $fFolderID);
// set the document title
$oDocument->setName($fName);
// set the document type id
$oDocument->setDocumentTypeID($fDocumentTypeID);

if (Document::documentExists($oDocument->getFileName(), $oDocument->getFolderID())) {
    // document already exists in folder
    $default->log->error("addDocumentBL.php Document exists with name " . $oDocument->getFileName() . " in folder " . Folder::getFolderPath($fFolderID) . " id=$fFolderID");                        	
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml(getStatusPage($fFolderID, _("A document with this file name already exists in this folder") . "</td><td><a href=\"$default->rootUrl/control.php?action=addDocument&fFolderID=$fFolderID&fDocumentTypeID=$fDocumentTypeID\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\"></a>"));                            
    $main->setCentralPayload($oPatternCustom);
    $main->render();
    exit(0);
}

if (!$oDocument->create()) {
    // couldn't store document on fs
    $default->log->error("addDocumentBL.php DB error storing document in folder " . Folder::getFolderPath($fFolderID) . " id=$fFolderID");                            	
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml(getStatusPage($fFolderID, _("An error occured while storing the document in the database, please try again.") . "</td><td><a href=\"$default->rootUrl/control.php?action=addDocument&fFolderID=$fFolderID&fDocumentTypeID=$fDocumentTypeID\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\"></a>"));                                
    $main->setCentralPayload($oPatternCustom);
    $main->render();
    exit(0);
}

//if the document was successfully created in the db, then store it on the file system
if (!PhysicalDocumentManager::uploadPhysicalDocument($oDocument, $fFolderID, "None", $_FILES['fFile']['tmp_name'])) {
    // couldn't store document on filesystem
    $default->log->error("addDocumentBL.php Filesystem error attempting to store document " . $oDocument->getFileName() . " in folder " . Folder::getFolderPath($fFolderID) . "; id=$fFolderID");                                	
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    // delete the document from the database
    $oDocument->delete();
    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml(getStatusPage($fFolderID, _("An error occured while storing the document on the file system, please try again.") . "</td><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a>"));                                    
    $main->setCentralPayload($oPatternCustom);
    $main->render();
    exit(0);
}

// ALL SYSTEMS GO!


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
                "  and selecting the link button.  " . $oDocument->getName() . " can be found at " . $oDocument->getDisplayPath();
        
        $oEmail = & new Email();
        $oEmail->send($oUserDocCreator->getEmail(), "Automatic document linking failed", $sBody);
        
        //document no longer dependant document, but must be linked manually
        $oDependantDocument->delete();                                    				
    }
}

// now handle meta data, pass new document id to queries
$aQueries = constructQuery(array_keys($_POST), array("document_id" =>$oDocument->getID()));
for ($i=0; $i<count($aQueries); $i++) {
    $sql = $default->db;
    if ($sql->query($aQueries[$i])) {
        $default->log->info("addDocumentBL.php query succeeded=" . $aQueries[$i]);
    } else {
        $default->log->error("addDocumentBL.php query failed=" . $aQueries[$i]);
    }										
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

?>
