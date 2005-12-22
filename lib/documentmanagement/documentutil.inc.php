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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

// LEGACY PATHS
require_once(KT_LIB_DIR . '/documentmanagement/DocumentFieldLink.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');

// NEW PATHS
require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
require_once(KT_LIB_DIR . '/filelike/filelikeutil.inc.php');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . "/subscriptions/subscriptions.inc.php"); 
require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');
require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");

class KTDocumentUtil {
    function createMetadataVersion($oDocument) {
        if (is_numeric($oDocument)) {
            $oDocument =& Document::get($oDocument);
            if (PEAR::isError($oDocument)) {
                return $oDocument;
            }
        }
        // XXX: PHP5 clone
        $oVersionDocument = $oDocument;
        $oVersionDocument->iId = -1;
        $oVersionDocument->setStatusID(STATUS_VERSION);
        $oVersionDocument->setLiveDocumentID($oDocument->getID());
        $oVersionDocument->setIsCheckedOut(false);
        $oVersionDocument->setCheckedOutUserID(null);
        $res = $oVersionDocument->create();
        if ($res !== true) {
            if (PEAR::isError($res)) {
                return $res;
            }
            // XXX: Remove when Document uses PEAR Errors
            return PEAR::raiseError($_SESSION["errorMessage"]);
        }

        $aFields =& DocumentFieldLink::getByDocument($oDocument);
        $iVersionDocumentID = $oVersionDocument->getID();
        foreach ($aFields as $oDFL) {
            // XXX: PHP5 clone
            $oVersionDFL = $oDFL;
            $oVersionDFL->iId = -1;
            $oVersionDFL->setDocumentID($iVersionDocumentID);
            $res = $oVersionDFL->create();
        }

        return $oVersionDocument;
    }

    function bumpVersion($oDocument) {
        if (is_numeric($oDocument)) {
            $oDocument =& Document::get($oDocument);
            if (PEAR::isError($oDocument)) {
                return $oDocument;
            }
        }
        $oDocument->setMetadataVersion($oDocument->getMetadataVersion()+1);
        return $oDocument->update();
    }

    function setModified($oDocument, $oUser) {
        $oDocument =& KTUtil::getObject('Document', $oDocument);
        $oDocument->setLastModifiedDate(getCurrentDateTime());
        $oDocument->setModifiedUserId(KTUtil::getId($oUser));
        return $oDocument->update();
    }

    function checkin($oDocument, $sFilename, $sCheckInComment, $oUser) {
        $sBackupPath = $oDocument->getPath() . "-" .  $oDocument->getMajorVersionNumber() . "." .  $oDocument->getMinorVersionNumber();
        $bSuccess = @copy($oDocument->getPath(), $sBackupPath);
        if ($bSuccess === false) {
            return PEAR::raiseError(_("Unable to backup document prior to upload"));
        }
        $oVersionedDocument = KTDocumentUtil::createMetadataVersion($oDocument);
        if (PEAR::isError($oVersionedDocument)) {
            return $oVersionedDocument;
        }

        $oStorage =& KTStorageManagerUtil::getSingleton();
        $iFileSize = filesize($sFilename);

        if (!$oStorage->upload($oDocument, $sFilename)) {
            // reinstate the backup
            copy($sBackupPath, $oDocument->getPath());
            // remove the backup
            unlink($sBackupPath);
            return PEAR::raiseError(_("An error occurred while storing the new file"));
        }

        $oDocument->setMetadataVersion($oDocument->getMetadataVersion()+1);

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
        
        return true;
    }

    function &_add($oFolder, $sFilename, $oUser, $aOptions) {
        $oContents = KTUtil::arrayGet($aOptions, 'contents');
        $aMetadata = KTUtil::arrayGet($aOptions, 'metadata');
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
            if ($oFieldset->getIsConditional()) {
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

        $res = DBUtil::runQuery(array("DELETE FROM $table WHERE document_id = ?", array($oDocument->getID())));
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
                "document_id" => $oDocument->getID(),
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

    // {{{ setIncomplete
    function setIncomplete(&$oDocument, $reason) {
        $oDocument->setStatusID(STATUS_INCOMPLETE);
        $table = "document_incomplete";
        $iId = $oDocument->getID();
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
        if (KTDocumentUtil::exists($oFolder, $sFilename)) {
            return PEAR::raiseError("File already exists");
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

        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_("All done...")));

        return $oDocument;
    }
    // }}}

    // {{{ exists
    function exists($oFolder, $sFilename) {
        return Document::documentExists($sFilename, $oFolder->getID());
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
        $iDocumentId = KTUtil::getId($oDocument);
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
        $aInsert = array(
            "document_id" => $iDocumentId,
            "document_text" => $sSearchableText,
        );
        return DBUtil::autoInsert($sTable, $aInsert, array('noid' => true));
    }
    // }}}
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
