<?php /* vim: set expandtab softtabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 * $Id$
 *
 * Document-handling utility functions
 * 
 * Simplifies and canonicalises operations such as adding, updating, and
 * deleting documents from the repository.
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

// LEGACY PATHS
require_once(KT_LIB_DIR . '/documentmanagement/DocumentFieldLink.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');

require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');

// NEW PATHS
require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
require_once(KT_LIB_DIR . '/filelike/filelikeutil.inc.php');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . "/subscriptions/subscriptions.inc.php"); 
require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');
require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");

// WORKFLOW
require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');

class KTDocumentUtil {
    function checkin($oDocument, $sFilename, $sCheckInComment, $oUser) {
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $iFileSize = filesize($sFilename);

        $iPreviousMetadataVersion = $oDocument->getMetadataVersionId();

        $oDocument->startNewContentVersion($oUser);

        KTDocumentUtil::copyMetadata($oDocument, $iPreviousMetadataVersion);

        if (!$oStorage->upload($oDocument, $sFilename)) {
            // reinstate the backup
            copy($sBackupPath, $oDocument->getPath());
            // remove the backup
            unlink($sBackupPath);
            return PEAR::raiseError(_("An error occurred while storing the new file"));
        }

        $oDocument->setLastModifiedDate(getCurrentDateTime());
        $oDocument->setModifiedUserId($oUser->getId());
        $oDocument->setIsCheckedOut(false);
        $oDocument->setCheckedOutUserID(-1);
        $oDocument->setMinorVersionNumber($oDocument->getMinorVersionNumber()+1);
        $oDocument->setFileSize($iFileSize);

        $bSuccess = $oDocument->update();
        if ($bSuccess !== true) {
            if (PEAR::isError($bSuccess)) {
                return $bSuccess;
            }
            return PEAR::raiseError(_("An error occurred while storing this document in the database"));
        }

        // create the document transaction record
        $oDocumentTransaction = & new DocumentTransaction($oDocument, $sCheckInComment, 'ktcore.transactions.check_in');
        $oDocumentTransaction->create();
        
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('content', 'scan');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $oTrigger->setDocument($oDocument);
            $ret = $oTrigger->scan();
            if (PEAR::isError($ret)) {
                $oDocument->delete();
                return $ret;
            }
        }
        
        $aTriggers = $oKTTriggerRegistry->getTriggers('content', 'transform');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            if ($aTrigger[1]) {
                require_once($aTrigger[1]);
            }
            $oTrigger = new $sTrigger;
            $oTrigger->setDocument($oDocument);
            $oTrigger->transform();
        }
        
        // fire subscription alerts for the checked in document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        $oSubscriptionEvent->CheckinDocument($oDocument, $oFolder);
        
        KTDocumentUtil::updateSearchableText($oDocument);
        
        return true;
    }

    function checkout($oDocument, $sCheckoutComment, $oUser) {
        if ($oDocument->getIsCheckedOut()) {
            return PEAR::raiseError('Already checked out.');
        }
        
        // FIXME at the moment errors this _does not_ rollback.
        
        $oDocument->setIsCheckedOut(true);
        $oDocument->setCheckedOutUserID($oUser->getId());
        if (!$oDocument->update()) { return PEAR::raiseError(_("There was a problem checking out the document.")); }

        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('checkout', 'postValidate');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                "document" => $oDocument,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
            if (PEAR::isError($ret)) {
                return $ret;
            }
        }

        $oDocumentTransaction = & new DocumentTransaction($oDocument, $sCheckoutComment, 'ktcore.transactions.check_out');
        $oDocumentTransaction->create();
    
        return true;
    }

    function &_add($oFolder, $sFilename, $oUser, $aOptions) {
        global $default;
        
        $oContents = KTUtil::arrayGet($aOptions, 'contents');
        $aMetadata = KTUtil::arrayGet($aOptions, 'metadata', null, false);
        $oDocumentType = KTUtil::arrayGet($aOptions, 'documenttype');
        $sDescription = KTUtil::arrayGet($aOptions, 'description', $sFilename);

        $oUploadChannel =& KTUploadChannel::getSingleton();

        if ($oDocumentType) {
            $iDocumentTypeId = KTUtil::getId($oDocumentType);
        } else {
            $iDocumentTypeId = 1;
        }
        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_("Creating database entry")));
        $oDocument =& Document::createFromArray(array(
            'name' => $sDescription,
            'description' => $sDescription,
            'filename' => $sFilename,
            'folderid' => $oFolder->getID(),
            'creatorid' => $oUser->getID(),
            'documenttypeid' => $iDocumentTypeId,
        ));
        if (PEAR::isError($oDocument)) {
            return $oDocument;
        }

        if (is_null($oContents)) {
            $res = KTDocumentUtil::setIncomplete($oDocument, "contents");
            if (PEAR::isError($res)) {
                $oDocument->delete();
                return $res;
            }
        } else {
            $oUploadChannel->sendMessage(new KTUploadGenericMessage(_("Storing contents")));
            $res = KTDocumentUtil::storeContents($oDocument, $oContents, $aOptions);
            if (PEAR::isError($res)) {
                $oDocument->delete();
                return $res;
            }
        }

        if (is_null($aMetadata)) {
            $res = KTDocumentUtil::setIncomplete($oDocument, "metadata");
            if (PEAR::isError($res)) {
                $oDocument->delete();
                return $res;
            }
        } else {
            $oUploadChannel->sendMessage(new KTUploadGenericMessage(_("Saving metadata")));
            $res = KTDocumentUtil::saveMetadata($oDocument, $aMetadata);
            if (PEAR::isError($res)) {
                $oDocument->delete();
                return $res;
            }
        }

        // setIncomplete and storeContents may change the document's status or
        // storage_path, so now is the time to update
        $oDocument->update();
        return $oDocument;
    }

    // {{{ validateMetadata
    function validateMetadata(&$oDocument, $aMetadata) {
        $aFieldsets =& KTFieldset::getGenericFieldsets();
        $aFieldsets =& array_merge($aFieldsets,
                KTFieldset::getForDocumentType($oDocument->getDocumentTypeId()));
        $aSimpleMetadata = array();
        foreach ($aMetadata as $aSingleMetadatum) {
            list($oField, $sValue) = $aSingleMetadatum;
            if (is_null($oField)) {
                continue;
            }
            $aSimpleMetadata[$oField->getId()] = $sValue;
        }
        $aFailed = array();
        foreach ($aFieldsets as $oFieldset) {
            $aFields =& $oFieldset->getFields();
            $aFieldValues = array();
            foreach ($aFields as $oField) {
                $v = KTUtil::arrayGet($aSimpleMetadata, $oField->getId());
                if ($oField->getIsMandatory()) {
                    if (empty($v)) {
                        // XXX: What I'd do for a setdefault...
                        $aFailed["field"][$oField->getId()] = 1;
                    }
                }
                if (!empty($v)) {
                    $aFieldValues[$oField->getId()] = $v;
                }
            }
            if ($oFieldset->getIsConditional() && KTMetadataUtil::validateCompleteness($oFieldset)) {
                $res = KTMetadataUtil::getNext($oFieldset, $aFieldValues);
                if ($res) {
                    $aFailed["fieldset"][$oFieldset->getId()] = 1;
                }
            }
        }
        if (!empty($aFailed)) {
            return new KTMetadataValidationError($aFailed);
        }
        return $aMetadata;
    }
    // }}}

    // {{{ saveMetadata
    function saveMetadata(&$oDocument, $aMetadata) {
        $table = "document_fields_link";
        $res = KTDocumentUtil::validateMetadata($oDocument, $aMetadata);
        if (PEAR::isError($res)) {
            return $res;
        }
        $aMetadata = $res;

        $iMetadataVersionId = $oDocument->getMetadataVersionId();
        $res = DBUtil::runQuery(array("DELETE FROM $table WHERE metadata_version_id = ?", array($iMetadataVersionId)));
        if (PEAR::isError($res)) {
            return $res;
        }
        // XXX: Metadata refactor
        foreach ($aMetadata as $aInfo) {
            list($oMetadata, $sValue) = $aInfo;
            if (is_null($oMetadata)) {
                continue;
            }
            $res = DBUtil::autoInsert($table, array(
                "metadata_version_id" => $iMetadataVersionId,
                "document_field_id" => $oMetadata->getID(),
                "value" => $sValue,
            ));
            if (PEAR::isError($res)) {
                return $res;
            }
        }
        KTDocumentUtil::setComplete($oDocument, "metadata");
        KTDocumentUtil::updateSearchableText($oDocument);
        return true;
    }
    // }}}

    function copyMetadata($oDocument, $iPreviousMetadataVersionId) {
        $iNewMetadataVersion = $oDocument->getMetadataVersionId();
        $sTable = KTUtil::getTableName('document_fields_link');
        $aFields = DBUtil::getResultArray(array("SELECT * FROM $sTable WHERE metadata_version_id = ?", array($iPreviousMetadataVersionId)));
        foreach ($aFields as $aRow) {
            unset($aRow['id']);
            $aRow['metadata_version_id'] = $iNewMetadataVersion;
            DBUtil::autoInsert($sTable, $aRow);
        }
        
    }

    // {{{ setIncomplete
    function setIncomplete(&$oDocument, $reason) {
        $oDocument->setStatusID(STATUS_INCOMPLETE);
        $table = "document_incomplete";
        $iId = $oDocument->getId();
        $aIncomplete = DBUtil::getOneResult(array("SELECT * FROM $table WHERE id = ?", array($iId)));
        if (PEAR::isError($aIncomplete)) {
            return $aIncomplete;
        }
        if (is_null($aIncomplete)) {
            $aIncomplete = array("id" => $iId);
        }
        $aIncomplete[$reason] = true;
        $res = DBUtil::autoDelete($table, $iId);
        if (PEAR::isError($res)) {
            return $res;
        }
        $res = DBUtil::autoInsert($table, $aIncomplete);
        if (PEAR::isError($res)) {
            return $res;
        }
        return true;
    }
    // }}}

    // {{{ setComplete
    function setComplete(&$oDocument, $reason) {
        $table = "document_incomplete";
        $iId = $oDocument->getID();
        $aIncomplete = DBUtil::getOneResult(array("SELECT * FROM $table WHERE id = ?", array($iId)));
        if (PEAR::isError($aIncomplete)) {
            return $aIncomplete;
        }

        if (is_null($aIncomplete)) {
            $oDocument->setStatusID(LIVE);
            return true;
        }

        $aIncomplete[$reason] = false;

        $bIncomplete = false;

        foreach ($aIncomplete as $k => $v) {
            if ($k === "id") { continue; }

            if ($v) {
                $bIncomplete = true;
            }
        }

        if ($bIncomplete === false) {
            DBUtil::autoDelete($table, $iId);
            $oDocument->setStatusID(LIVE);
            return true;
        }

        $res = DBUtil::autoDelete($table, $iId);
        if (PEAR::isError($res)) {
            return $res;
        }
        $res = DBUtil::autoInsert($table, $aIncomplete);
        if (PEAR::isError($res)) {
            return $res;
        }
    }
    // }}}

    // {{{ add
    function &add($oFolder, $sFilename, $oUser, $aOptions) {
        if (KTDocumentUtil::fileExists($oFolder, $sFilename)) {
		    $oDoc = Document::getByFilenameAndFolder($sFilename, $oFolder->getId());
			if (PEAR::isError($oDoc)) {
                return PEAR::raiseError(_("Document with that filename already exists in this folder, and appears to be invalid.  Please contact the system administrator."));			
			} else {
			    if ($oDoc->getStatusID != LIVE) {
                    return PEAR::raiseError(_("Document with that filename already exists in this folder, but it has been archived or deleted and is still available for restoration.  To prevent it being overwritten, you are not allowed to add a document with the same title or filename."));
				} else {
				    return PEAR::raiseError(_("Document with that filename already exists in this folder."));
				}
			}
        }
        $sName = KTUtil::arrayGet($aOptions, 'description', $sFilename);
        if (KTDocumentUtil::nameExists($oFolder, $sName)) {
   		    $oDoc = Document::getByNameAndFolder($sName, $oFolder->getId());
			if (PEAR::isError($oDoc)) {
                return PEAR::raiseError(_("Document with that title already exists in this folder, and appears to be invalid.  Please contact the system administrator."));			
			} else {
			    if ($oDoc->getStatusID != LIVE) {
                    return PEAR::raiseError(_("Document with that title already exists in this folder, but it has been archived or deleted and is still available for restoration.  To prevent it being overwritten, you are not allowed to add a document with the same title or filename."));
				} else {
				    return PEAR::raiseError(_("Document with that title already exists in this folder."));
				}
			}

        }
        $oUploadChannel =& KTUploadChannel::getSingleton();
        $oUploadChannel->sendMessage(new KTUploadNewFile($sFilename));
        $oDocument =& KTDocumentUtil::_add($oFolder, $sFilename, $oUser, $aOptions);
        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_("Document created")));
        if (PEAR::isError($oDocument)) {
            return $oDocument;
        }

        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_("Scanning file")));
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('content', 'scan');
        $iTrigger = 0;
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $oTrigger->setDocument($oDocument);
            $oUploadChannel->sendMessage(new KTUploadGenericMessage(sprintf(_("    (trigger %s)"), $sTrigger)));
            $ret = $oTrigger->scan();
            if (PEAR::isError($ret)) {
                $oDocument->delete();
                return $ret;
            }
        }

        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_("Transforming file")));
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('content', 'transform');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            if ($aTrigger[1]) {
                require_once($aTrigger[1]);
            }
            $oTrigger = new $sTrigger;
            $oTrigger->setDocument($oDocument);
            $oUploadChannel->sendMessage(new KTUploadGenericMessage(sprintf(_("    (trigger %s)"), $sTrigger)));
            $oTrigger->transform();
        }

        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_("Creating transaction")));
        $aOptions = array('user' => $oUser);
        //create the document transaction record
        $oDocumentTransaction = & new DocumentTransaction($oDocument, "Document created", 'ktcore.transactions.create', $aOptions);
        $res = $oDocumentTransaction->create();
        if (PEAR::isError($res)) {
            $oDocument->delete();
            return $res;
        }

        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_("Sending subscriptions")));
        // fire subscription alerts for the checked in document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        $oSubscriptionEvent->AddDocument($oDocument, $oFolder);
        
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('add', 'postValidate');
                
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                "document" => $oDocument,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
            
        }

        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_("All done...")));

        return $oDocument;
    }
    // }}}

    // {{{ fileExists
    function fileExists($oFolder, $sFilename) {
        return Document::fileExists($sFilename, $oFolder->getID());
    }
    // }}}

    // {{{ nameExists
    function nameExists($oFolder, $sName) {
        return Document::nameExists($sName, $oFolder->getID());
    }
    // }}}

    // {{{ storeContents
    /**
     * Stores contents (filelike) from source into the document storage
     */
    function storeContents(&$oDocument, $oContents, $aOptions = null) {
        if (is_null($aOptions)) {
            $aOptions = array();
        }
        $bCanMove = KTUtil::arrayGet($aOptions, 'move');
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $sFilename = tempnam('/tmp', 'kt_storecontents');
        $oOutputFile = new KTFSFileLike($sFilename);
        $res = KTFileLikeUtil::copy_contents($oContents, $oOutputFile);
        $sType = KTMime::getMimeTypeFromFile($sFilename);
        $iMimeTypeId = KTMime::getMimeTypeID($sType, $oDocument->getFileName());
        $oDocument->setMimeTypeId($iMimeTypeId);
        if (!$oStorage->upload($oDocument, $sFilename)) {
            return PEAR::raiseError("Couldn't store contents");
        }
        KTDocumentUtil::setComplete($oDocument, "contents");
        return true;
    }
    // }}}

    // {{{ updateTransactionText
    function updateTransactionText($oDocument) {
        $iDocumentId = KTUtil::getId($oDocument);
        $aTransactions = DocumentTransaction::getByDocument($iDocumentId);
        foreach ($aTransactions as $oTransaction) {
            $aComments[] = $oTransaction->getComment();
        }
        $sAllComments = join("\n\n", $aComments);
        $sTable = KTUtil::getTableName('document_transaction_text');
        $aQuery = array("DELETE FROM $sTable WHERE document_id = ?", array($iDocumentId));
        $res = DBUtil::runQuery($aQuery);
        if (PEAR::isError($res)) {
            return $res;
        }
        $aInsert = array(
            "document_id" => $iDocumentId,
            "document_text" => $sAllComments,
        );
        return DBUtil::autoInsert($sTable, $aInsert, array('noid' => true));
    }
    // }}}

    // {{{ updateSearchableText
    function updateSearchableText($oDocument) {
        $oDocument = KTUtil::getObject('Document', $oDocument);
        $iDocumentId = $oDocument->getId();
        $sTable = KTUtil::getTableName('document_transaction_text');
        $aQuery = array("SELECT document_text FROM $sTable WHERE
                document_id = ?", array($iDocumentId));
        $sAllComments = DBUtil::getOneResultKey($aQuery, 'document_text');
        $sTable = KTUtil::getTableName('document_text');
        $aQuery = array("SELECT document_text FROM $sTable WHERE
                document_id = ?", array($iDocumentId));
        $sAllDocumentText = DBUtil::getOneResultKey($aQuery, 'document_text');
        $aFieldLinks = DocumentFieldLink::getByDocument($iDocumentId);
        $aFieldValues = array();
        foreach ($aFieldLinks as $oFieldLink) {
            $aFieldValues[] = $oFieldLink->getValue();
        }
        $sAllFieldText = join(" ", $aFieldValues);
        $sDocumentFilename = $oDocument->getFilename();
        $sDocumentTitle = $oDocument->getName();
        $sSearchableText = $sAllDocumentText . " " . $sAllFieldText . " " . $sAllComments . " " . $sDocumentFilename . " " . $sDocumentTitle;
        $sTable = KTUtil::getTableName('document_searchable_text');
        $aDelete = array(
            "document_id" => $iDocumentId,
        );
        DBUtil::whereDelete($sTable, $aDelete);
        $aInsert = array(
            "document_id" => $iDocumentId,
            "document_text" => $sSearchableText,
        );
        return DBUtil::autoInsert($sTable, $aInsert, array('noid' => true));
    }
    // }}}
    
    // {{{ delete
    function delete($oDocument, $sReason, $iDestFolderId = null) {
        $oDocument =& KTUtil::getObject('Document', $oDocument);
        if (is_null($iDestFolderId)) { $iDestFolderId = $oDocument->getFolderID(); }
        $oStorageManager =& KTStorageManagerUtil::getSingleton();
        
        global $default;
        
        if (count(trim($sReason)) == 0) { 
            return PEAR::raiseError('Deletion requires a reason'); 
        }
        if (PEAR::isError($oDocument) || ($oDocument == false)) { return PEAR::raiseError('Invalid document object.'); }
        
        if ($oDocument->getIsCheckedOut() == true) { return PEAR::raiseError(sprintf(_('The document is checked out and cannot be deleted: %s'), $oDocument->getName())); }
        
        // IF we're deleted ...
        if ($oDocument->getStatusID() == DELETED) { return true; }
        
        DBUtil::startTransaction();
        
                // flip the status id
        $oDocument->setStatusID(DELETED);
        $oDocument->setFolderID($iDestFolderId); // try to keep it in _this_ folder, otherwise move to root.
        
        $res = $oDocument->update();
        if (PEAR::isError($res) || ($res == false)) {
            DBUtil::rollback();
            return PEAR::raiseError(_("There was a problem deleting the document from the database."));
        }

        // now move the document to the delete folder
        $res = $oStorageManager->delete($oDocument);
        if (PEAR::isError($res) || ($res == false)) {
            //could not delete the document from the file system
            $default->log->error("Deletion: Filesystem error deleting document " .
                $oDocument->getFileName() . " from folder " .
                Folder::getFolderPath($oDocument->getFolderID()) .
                " id=" . $oDocument->getFolderID());
            
            // we use a _real_ transaction here ...
            
            DBUtil::rollback();
            
            /*
            //reverse the document deletion
            $oDocument->setStatusID(LIVE);
            $oDocument->update();
            */
            
            return PEAR::raiseError(_("There was a problem deleting the document from storage."));
        }
        
        $oDocumentTransaction = & new DocumentTransaction($oDocument, "Document deleted: " . $sReason, 'ktcore.transactions.delete');
        $oDocumentTransaction->create();
        
        $oDocument->setFolderID(1);
        
        DBUtil::commit();
        
        // document is now deleted:  triggers are best-effort.
        
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('delete', 'postValidate');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                "document" => $oDocument,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
            if (PEAR::isError($ret)) {
                $oDocument->delete();          // FIXME nbm: review that on-fail => delete is correct ?!
                return $ret;
            }
        }
        
        
    }
    // }}}
    
    function reindexDocument($oDocument) {
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('content', 'transform');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            if ($aTrigger[1]) {
                require_once($aTrigger[1]);
            }
            $oTrigger = new $sTrigger;
            $oTrigger->setDocument($oDocument);
            $oTrigger->transform();
        }
        KTDocumentUtil::updateSearchableText($oDocument);
    }


    function canBeMoved($oDocument) {
        if ($oDocument->getIsCheckedOut()) {
            return false;
        }
        if (!KTWorkflowUtil::actionEnabledForDocument($oDocument, 'ktcore.actions.document.move')) {
            return false;
        }
        return true;
    }


    function copy($oDocument, $oDestinationFolder) {
        // 1. generate a new triad of content, metadata and core objects.
        // 2. update the storage path.
		//print '--------------------------------- BEFORE';
        //print_r($oDocument);
        
        // grab the "source "data
        $sTable = KTUtil::getTableName('documents');
        $sQuery = 'SELECT * FROM ' . $sTable . ' WHERE id = ?';
        $aParams = array($oDocument->getId());
        $aCoreRow = DBUtil::getOneResult(array($sQuery, $aParams));
        unset($aCoreRow['id']);

        $aCoreRow['folder_id'] = $oDestinationFolder->getId(); // new location.
        $id = DBUtil::autoInsert($sTable, $aCoreRow);
        if (PEAR::isError($id)) { return $id; }
        // we still have a bogus md_version, but integrity holds, so fix it now.
        $oCore = KTDocumentCore::get($id);
        
        $sTable = KTUtil::getTableName('document_metadata_version');
        $sQuery = 'SELECT * FROM ' . $sTable . ' WHERE id = ?';
        $aParams = array($oDocument->getMetadataVersionId());
        $aMDRow = DBUtil::getOneResult(array($sQuery, $aParams));
        unset($aMDRow['id']);
        $aMDRow['document_id'] = $oCore->getId();
        $id = DBUtil::autoInsert($sTable, $aMDRow);
        if (PEAR::isError($id)) { return $id; }
        $oCore->setMetadataVersionId($id);
        $oMDV = KTDocumentMetadataVersion::get($id);
        
        $sTable = KTUtil::getTableName('document_content_version');
        $sQuery = 'SELECT * FROM ' . $sTable . ' WHERE id = ?';
        $aParams = array($oDocument->_oDocumentContentVersion->getId());
        $aContentRow = DBUtil::getOneResult(array($sQuery, $aParams));
        unset($aContentRow['id']);
        $aContentRow['document_id'] = $oCore->getId();
        $id = DBUtil::autoInsert($sTable, $aContentRow);
        if (PEAR::isError($id)) { return $id; }
        $oMDV->setContentVersionId($id);
        
        $res = $oCore->update();
        if (PEAR::isError($res)) { return $res; }
        $res = $oMDV->update();
        if (PEAR::isError($res)) { return $res; }
        
        // now, we have a semi-sane document object. get it.
        $oNewDocument = Document::get($oCore->getId());
        
        //print '--------------------------------- AFTER';
        //print_r($oDocument);
		//print '======';
        //print_r($oNewDocument);
        
        // copy the metadata from old to new.
        $res = KTDocumentUtil::copyMetadata($oNewDocument, $oDocument->getMetadataVersionId());
        if (PEAR::isError($res)) { return $res; }
        
        // finally, copy the actual file.
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $res = $oStorage->copy($oDocument, $oNewDocument);


        $oOriginalFolder = Folder::get($oDocument->getFolderId());
        $iOriginalFolderPermissionObjectId = $oOriginalFolder->getPermissionObjectId();
        $iDocumentPermissionObjectId = $oDocument->getPermissionObjectId();

        if ($iDocumentPermissionObjectId === $iOriginalFolderPermissionObjectId) {
            $oNewDocument->setPermissionObjectId($oDestinationFolder->getPermissionObjectId());
        }
        
        $res = $oNewDocument->update();
        if (PEAR::isError($res)) { return $res; }
        
        return $oNewDocument;
    }

    function rename($oDocument, $sNewFilename, $oUser) {
        $oStorage =& KTStorageManagerUtil::getSingleton();

        $iPreviousMetadataVersion = $oDocument->getMetadataVersionId();
        $oOldContentVersion = $oDocument->_oDocumentContentVersion;
        $oDocument->startNewContentVersion($oUser);
        KTDocumentUtil::copyMetadata($oDocument, $iPreviousMetadataVersion);
        $res = $oStorage->renameDocument($oDocument, $oOldContentVersion, $sNewFilename);

        if (!$res) {
            return PEAR::raiseError(_("An error occurred while storing the new file"));
        }

        $oDocument->setLastModifiedDate(getCurrentDateTime());
        $oDocument->setModifiedUserId($oUser->getId());
        $oDocument->setMinorVersionNumber($oDocument->getMinorVersionNumber()+1);
		$oDocument->_oDocumentContentVersion->setFilename($sNewFilename);
        $bSuccess = $oDocument->update();
        if ($bSuccess !== true) {
            if (PEAR::isError($bSuccess)) {
                return $bSuccess;
            }
            return PEAR::raiseError(_("An error occurred while storing this document in the database"));
        }

        // create the document transaction record
        $oDocumentTransaction = & new DocumentTransaction($oDocument, 'Document renamed', 'ktcore.transactions.update');
        $oDocumentTransaction->create();
        
        // fire subscription alerts for the checked in document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        $oSubscriptionEvent->ModifyDocument($oDocument, $oFolder);
        
        return true;    
    }

}

class KTMetadataValidationError extends PEAR_Error {
    function KTMetadataValidationError ($aFailed) {
        $this->aFailed = $aFailed;
        $message = _('Validation Failed');
        parent::PEAR_Error($message);
    }
}

class KTUploadChannel {
    var $observers = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS, 'KT_UploadChannel')) {
            $GLOBALS['KT_UploadChannel'] = new KTUploadChannel;
        }
        return $GLOBALS['KT_UploadChannel'];
    }

    function sendMessage(&$msg) {
        foreach ($this->observers as $oObserver) {
            $oObserver->receiveMessage($msg);
        }
    }

    function addObserver(&$obs) {
        array_push($this->observers, $obs);
    }
}

class KTUploadGenericMessage {
    function KTUploadGenericMessage($sMessage) {
        $this->sMessage = $sMessage;
    }

    function getString() {
        return $this->sMessage;
    }
}

class KTUploadNewFile {
    function KTUploadNewFile($sFilename) {
        $this->sFilename = $sFilename;
    }

    function getString() {
        return $this->sFilename;
    }
}

?>
