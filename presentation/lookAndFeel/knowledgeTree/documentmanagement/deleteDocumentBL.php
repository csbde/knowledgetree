<?php
/**
 * $Id$
 *
 * Business logic concerned with the deletion of a document.  
 * Will use deleteDocumentUI for presentation information.  
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

KTUtil::extractGPC('fDeleteConfirmed', 'fDocumentIDs', 'fRememberDocumentID');

require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");

require_once("$default->fileSystemRoot/presentation/Html.inc");

require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");

require_once("deleteDocumentUI.inc");

$aNondeletedDocs = array();

if (!checkSession()) {
    die();
}

if (isset($fRememberDocumentID)) {
    $fDocumentIDs = $_SESSION['documents'][$fRememberDocumentID];
} else {
    $sUniqueID = KTUtil::randomString();
    $_SESSION["documents"][$sUniqueID] = $fDocumentIDs;
    $fRememberDocumentID = $sUniqueID;
}

if (!isset($fDocumentIDs)) {
    //no document selected for deletion
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml(renderErrorPage(_("No document currently selected")));
    $main->setCentralPayload($oPatternCustom);
    $main->render();
    exit(0);
}


// Check permission and collaboration for all documents
for ($i = 0; $i < count($fDocumentIDs); $i++) {
    $oDocument = Document::get($fDocumentIDs[$i]);

    if (!Permission::userHasDocumentWritePermission($oDocument)) {
        // user does not have permission to delete the document
        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
        $oPatternCustom = & new PatternCustom();
        $oPatternCustom->setHtml(renderErrorPage(_("You do not have, at least, permission to delete one document") . ": " . 
            $oDocument->getName() . "<br>" . _("Please deselect it and retry.")));
        $main->setCentralPayload($oPatternCustom);
        $main->render();
        exit(0);
    }
}

if (!isset($fDeleteConfirmed)) {
    //get confirmation first
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->addHtml(getPage($fRememberDocumentID));
    $main->setCentralPayload($oPatternCustom);
    $main->render();
    exit(0);
}

    /* Delete all files
       If an error occured while deleting a file, then:
       - make a rollback of the current file
       - insert document object in $aNondeletedDocs array
       - delete the other selected file

       At the end check the $aNondeletedDocs array
       - if is empty then OK
       - if is not empty then show the nondeleted files list
    */
    
for ($i = 0; $i < count($fDocumentIDs); $i++) {
    $oDocument = Document::get($fDocumentIDs[$i]);
    if (!isset($oDocument)) {
        // Store the doc with problem
        array_push($aNondeletedDocs, array($oDocument, _("Could not load document in database")));
    }

    // New transaction
    $sDocumentPath = Folder::getFolderPath($oDocument->getFolderID()) . $oDocument->getFileName();
    $oDocumentTransaction = & new DocumentTransaction($fDocumentIDs[$i], "Document deleted", DELETE);
    $oDocumentTransaction->create();

    // flip the status id
    $oDocument->setStatusID(DELETED);

    // store
    if (!$oDocument->update()) {
        //could not update the documents status in the db
        $default->log->error("deleteDocumentBL.php DB error deleting document " .
            $oDocument->getFileName() . " from folder " .
            Folder::getFolderPath($oDocument->getFolderID()) .
            " id=" . $oDocument->getFolderID());
        
        //get rid of the document transaction
        $oDocumentTransaction->delete();

        // Store the doc with problem
        array_push($aNondeletedDocs, array($oDocument, 
            _("Could not update document in database")));
    }

    // now move the document to the delete folder
    if (PhysicalDocumentManager::delete($oDocument)) {
        //could not delete the document from the file system
        $default->log->error("deleteDocumentBL.php Filesystem error deleting document " .
            $oDocument->getFileName() . " from folder " .
            Folder::getFolderPath($oDocument->getFolderID()) .
            " id=" . $oDocument->getFolderID());
        //reverse the document deletion
        $oDocument->setStatusID(LIVE);
        $oDocument->update();
        //get rid of the document transaction
        $oDocumentTransaction->delete();

        // Store the doc with problem
        array_push($aNondeletedDocs, array($oDocument, _("Could not delete document on file system")));
    }

    // successfully deleted the document
    $default->log->info("deleteDocumentBL.php successfully deleted document " . 
        $oDocument->getFileName() . " from folder " . 
        Folder::getFolderPath($oDocument->getFolderID()) . 
        " id=" . $oDocument->getFolderID());
                            
    // fire subscription alerts for the deleted document
    $count = SubscriptionEngine::fireSubscription($fDocumentIDs[$i], 
        SubscriptionConstants::subscriptionAlertType("RemoveSubscribedDocument"),
        SubscriptionConstants::subscriptionType("DocumentSubscription"),
        array(
            "folderID" => $oDocument->getFolderID(),
            "removedDocumentName" => $oDocument->getName(),
            "folderName" => Folder::getFolderDisplayPath($oDocument->getFolderID()),
        ));
    $default->log->info("deleteDocumentBL.php fired $count subscription alerts for removed document " . $oDocument->getName());
                            
    // remove all document subscriptions for this document
    if (SubscriptionManager::removeSubscriptions($fDocumentIDs[$i], SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
        $default->log->info("deleteDocumentBL.php removed all subscriptions for this document");
    } else {
        $default->log->error("deleteDocumentBL.php couldn't remove document subscriptions");
    }
}
                                
// List nondeleted documents
if (!empty($aNondeletedDocs) ) {
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();

    $sError = _("An error occured deleting the following document(s):") . "<br><br>";
    foreach ($aNondeletedDocs as $oDoc) {
        $sError .= $oDoc[0]->getDisplayPath() . ":&nbsp;&nbsp;&nbsp;" .$oDoc[1] . "<br>";
    } 
    $sError .= "<br>" . _("The other documents are been deleted.");

    $oPatternCustom->addHtml(renderErrorPage($sError));
    $main->setCentralPayload($oPatternCustom);
    $main->render();
    exit(0);
}

// redirect to the browse folder page
redirect("$default->rootUrl/control.php?action=browse&fFolderID=" . $oDocument->getFolderID());
exit(0);
?>
