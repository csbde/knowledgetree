<?php
/**
 * $Id$
 *
 * Contains the business logic required to build the bulk uploadpage.
 * Will use bulkUploadUI.inc for presentation logic
 *
 * Expected form variable:
 * o $fFolderID - primary key of folder user is currently browsing
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
 * @author Adam Monsen
 * @package documentmanagement
 */

require_once("../../../../config/dmsDefaults.php");

KTUTil::extractGPC('fDocumentTypeID', 'fFolderID', 'fStore');

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/database/datetime.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/BulkUploadManager.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/lib/security/Permission.inc");
    require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMetaData.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableTableSqlQuery.inc");
    require_once("$default->fileSystemRoot/lib/web/WebDocument.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/store.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("bulkUploadUI.inc");

    $bContinue = TRUE; // flag to stop processing before next CHECK
    $oPatternCustom = & new PatternCustom();

    /* CHECK: system has required features to handle bulk upload */
    if (!BulkUploadManager::isBulkUploadCapable()) {
        // can't do bulk uploading
        $sErrorMessage = "This system is not capable of handling bulk uploads. <br/>\n"
            . "Please contact your system administrator.<br />\n"
            . getCancelButton($fFolderID);
        $bContinue = FALSE;
    }

    /* CHECK: folder ID passed in */
    if (isset($fFolderID)) {
        $oFolder = Folder::get($fFolderID);
    } else {
        // no folder id was set when coming to this page,
        // so display an error message
        $sErrorMessage = "You haven't selected a folder to bulk upload to.";
        $bContinue = FALSE;
    }

    /* CHECK: user has write perms for current folder */
    if ($bContinue and !Permission::userHasFolderWritePermission($oFolder)) {
        // user does not have write permission for this folder
        $sErrorMessage = getCancelButton($fFolderID)
            . "You do not have permission to add a document to this folder.";
        $bContinue = FALSE;
    }

    /* CHECK: user selected a document type */
    if ($bContinue and !$fDocumentTypeID) {
        // show document type chooser form
        $oPatternCustom->setHtml(getChooseDocumentTypePage($fFolderID));
        $main->setFormAction($_SERVER["PHP_SELF"]);
        $bContinue = FALSE;
    }

    /* CHECK: user submitted a file for upload */
    if ($bContinue and !$fStore) {
        // show upload/metatdata form
        $oPatternCustom->setHtml(getPage($fFolderID, $fDocumentTypeID));
        $main->setFormAction($_SERVER["PHP_SELF"]);
        $main->setFormEncType("multipart/form-data");
        $main->setHasRequiredFields(true);
        $bContinue = FALSE;
    }

    /* CHECK: bulk upload is valid */
    if ($bContinue and isValidBulkUpload()) {
        /* create temp dir, extract contents of .zip file to temp dir */
        // pass path of ZIP file to bulk upload manager to get names, paths of uploaded files
        // manager returns an array of ZipFile objects
        $aIndividualFiles = BulkUploadManager::unzipToTempDir($_FILES['fFile']['tmp_name'], $_FILES['fFile']['name']);
    } elseif ($bContinue) {
        // no uploaded file
        $sErrorMessage = getInvalidBulkUploadErrorMsg() . getRetryUploadButton($fFolderID, $fDocumentTypeID);
    }

    /* CHECK: found good stuff in ZIP file */
    $aFileStatus = array();
    if ($bContinue and $aIndividualFiles) {
        while ($aIndividualFiles) {
            /* perform uploading logic as in addDocumentBL.php for each ZipFile */
            // DON'T die if uploading a particular file fails, keep errors for displaying later
            $oFile = array_pop($aIndividualFiles);

            $sBasename = basename($oFile->sFilename);
            $aFileFake = array(
                'name' => $sBasename,
                'type' => PhysicalDocumentManager::getMimeTypeFromFile($oFile->sFilename),
                'tmp_name' => $oFile->sFilename,
                'error' => 0,
                'size' => $oFile->iSize,
            );

            // create the document in the database
            $oDocument = & PhysicalDocumentManager::createDocumentFromUploadedFile($aFileFake, $fFolderID);
            // set the document title
            $oDocument->setName($sBasename);
            // set the document type
            $oDocument->setDocumentTypeID($fDocumentTypeID);

            if (Document::documentExists($oDocument->getFileName(), $oDocument->getFolderID())) {
                $aFileStatus[$sBasename] = "A document with this file name already exists in this folder.";
                continue;
            }

            $sFolderPath = Folder::getFolderPath($fFolderID);

            if (!$oDocument->create()) {
                $default->log->error("bulkUploadBL.php DB error storing document in folder $sFolderPath id=$fFolderID");
                $aFileStatus[$sBasename] = "An error occured while storing the document in the database, please try again. Error code 0127.";
                continue;
            }

            // if the document was successfully created in the db, store it on the file system
            if (!PhysicalDocumentManager::uploadPhysicalDocument($oDocument, $fFolderID, "None", $oFile->sFilename)) {
                $default->log->error("bulkUploadBL.php DB error storing document in folder $sFolderPath id=$fFolderID");
                $aFileStatus[$sBasename] = "An error occured while storing the document in the database, please try again. Error code 0128.";
                continue;
            }

            // create the web document link
            $oWebDocument = & new WebDocument($oDocument->getID(), -1, 1, NOT_PUBLISHED, getCurrentDateTime());
            if ($oWebDocument->create()) {
                $default->log->info("bulkUploadBL.php created web document for document ID=" . $oDocument->getID());
            } else {
                $default->log->error("bulkUploadBL.php couldn't create web document for document ID=" . $oDocument->getID());
            }

            // create the document transaction record
            $oDocumentTransaction = & new DocumentTransaction($oDocument->getID(), "Document created", CREATE);
            if ($oDocumentTransaction->create()) {
                $default->log->debug("bulkUploadBL.php created create document transaction for document ID=" . $oDocument->getID());
            } else {
                $default->log->error("bulkUploadBL.php couldn't create create document transaction for document ID=" . $oDocument->getID());
            }

            // now handle meta data, pass new document id to queries
            $aQueries = constructQuery(array_keys($_POST), array("document_id" =>$oDocument->getID()));
            for ($i=0; $i<count($aQueries); $i++) {
                $sql = $default->db;
                if ($sql->query($aQueries[$i])) {
                    $default->log->info("bulkUploadBL.php query succeeded=" . $aQueries[$i]);
                } else {
                    $default->log->error("bulkUploadBL.php query failed=" . $aQueries[$i]);
                }
            }

            // fire subscription alerts for the new document
            $count = SubscriptionEngine::fireSubscription($fFolderID, SubscriptionConstants::subscriptionAlertType("AddDocument"),
                     SubscriptionConstants::subscriptionType("FolderSubscription"),
                     array( "newDocumentName" => $oDocument->getName(),
                            "folderName" => Folder::getFolderName($fFolderID)));
            $default->log->info("bulkUploadBL.php fired $count subscription alerts for new document " . $oDocument->getName());

            /* display a status page with per-file results for bulk upload */
            $default->log->info("bulkUploadBL.php successfully added document " . $oDocument->getFileName() . " to folder $sFolderPath id=$fFolderID");
            /* store status for this document for later display */ 
            $aFileStatus[$oDocument->getName()] = "Successfully added document";
        }
        $oPatternCustom->setHtml(getStatusPage($fFolderID, $aFileStatus));

    } elseif ($bContinue) {
        // error extracting from ZIP file
        $sErrorMessage = getInvalidBulkUploadErrorMsg() . getRetryUploadButton($fFolderID, $fDocumentTypeID);
    }

    /* DONE. RENDER OUTPUT. */
    // render error message if necessary
    if ($sErrorMessage) {
        $main->setErrorMessage($sErrorMessage);
    }

    // render main page
    $main->setCentralPayload($oPatternCustom);
    $main->render();
}

// if changing this function, also change related error message
function isValidBulkUpload() {
    return (strlen($_FILES['fFile']['name']) > 0)
      && file_exists($_FILES['fFile']['tmp_name'])
      && $_FILES['fFile']['size'] > 0
      && (!$_FILES['fFile']['error'])
      && preg_match('/\.zip/i', $_FILES['fFile']['name']);
}

?>
