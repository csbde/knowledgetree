<?php
/**
 * $Id$
 *
 * Document-handling utility functions
 *
 * Simplifies and canonicalises operations such as adding, updating, and
 * deleting documents from the repository.
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2008 The Jam Warehouse Software (Pty) Limited
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
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
require_once(KT_LIB_DIR . '/subscriptions/subscriptions.inc.php');
require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');

// WORKFLOW
require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');

class KTDocumentUtil {
    function checkin($oDocument, $sFilename, $sCheckInComment, $oUser, $aOptions = false) {
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $iFileSize = filesize($sFilename);

        $iPreviousMetadataVersion = $oDocument->getMetadataVersionId();

        $bSuccess = $oDocument->startNewContentVersion($oUser);
        if (PEAR::isError($bSuccess)) {
            return $bSuccess;
        }

        KTDocumentUtil::copyMetadata($oDocument, $iPreviousMetadataVersion);

        if ( !$oStorage->upload( $oDocument, $sFilename)) {
            return PEAR::raiseError(_kt('An error occurred while storing the new file'));
        }

        $oDocument->setLastModifiedDate(getCurrentDateTime());
        $oDocument->setModifiedUserId($oUser->getId());
        $oDocument->setIsCheckedOut(false);
        $oDocument->setCheckedOutUserID(-1);
        if ($aOptions['major_update']) {
            $oDocument->setMajorVersionNumber($oDocument->getMajorVersionNumber()+1);
            $oDocument->setMinorVersionNumber('0');
        } else {
            $oDocument->setMinorVersionNumber($oDocument->getMinorVersionNumber()+1);
        }
        $oDocument->setFileSize($iFileSize);

        $sFilename = $oDocument->getFileName();

        if(is_array($aOptions)) {
            $sFilename = KTUtil::arrayGet($aOptions, 'newfilename', '');
            if(strlen($sFilename)) {
        	global $default;
        	$oDocument->setFileName($sFilename);
        	$default->log->info('renamed document ' . $oDocument->getId() . ' to ' . $sFilename);
            }
        }

        $sType = KTMime::getMimeTypeFromFile($sFilename);
        $iMimeTypeId = KTMime::getMimeTypeID($sType, $oDocument->getFileName());
        $oDocument->setMimeTypeId($iMimeTypeId);

        $bSuccess = $oDocument->update();
        if ($bSuccess !== true) {
            if (PEAR::isError($bSuccess)) {
                return $bSuccess;
            }
            return PEAR::raiseError(_kt('An error occurred while storing this document in the database'));
        }

        // create the document transaction record
        $oDocumentTransaction = new DocumentTransaction($oDocument, $sCheckInComment, 'ktcore.transactions.check_in');
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

        // NEW SEARCH

        Indexer::index($oDocument);

        /*
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
        */

        // fire subscription alerts for the checked in document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        $oSubscriptionEvent->CheckinDocument($oDocument, $oFolder);

        KTDocumentUtil::updateSearchableText($oDocument);

        return true;
    }

    function checkout($oDocument, $sCheckoutComment, $oUser) {
        if ($oDocument->getIsCheckedOut()) {
            return PEAR::raiseError(_kt('Already checked out.'));
        }

        // FIXME at the moment errors this _does not_ rollback.

        $oDocument->setIsCheckedOut(true);
        $oDocument->setCheckedOutUserID($oUser->getId());
        if (!$oDocument->update()) { return PEAR::raiseError(_kt('There was a problem checking out the document.')); }

        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('checkout', 'postValidate');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                'document' => $oDocument,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
            if (PEAR::isError($ret)) {
                return $ret;
            }
        }

        $oDocumentTransaction = new DocumentTransaction($oDocument, $sCheckoutComment, 'ktcore.transactions.check_out');
        $oDocumentTransaction->create();

        // fire subscription alerts for the downloaded document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        $oSubscriptionEvent->CheckOutDocument($oDocument, $oFolder);

        return true;
    }

    function archive($oDocument, $sReason) {

        $this->startTransaction();
        $oDocument->setStatusID(ARCHIVED);
        $res = $oDocument->update();

        if (PEAR::isError($res) || ($res === false)) {
            return PEAR::raiseError(_kt('There was a database error while trying to archive this file'));
        }

        $oDocumentTransaction = & new DocumentTransaction($oDocument, sprintf(_kt('Document archived: %s'), $sReason), 'ktcore.transactions.update');
        $oDocumentTransaction->create();

        $this->commitTransaction();

        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('archive', 'postValidate');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                'document' => $oDocument,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
            if (PEAR::isError($ret)) {
                $oDocument->delete();
                return $ret;
            }
        }

        // fire subscription alerts for the archived document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        $oSubscriptionEvent->ArchivedDocument($oDocument, $oFolder);

        return true;
    }

    function &_add($oFolder, $sFilename, $oUser, $aOptions) {
        global $default;

        //$oContents = KTUtil::arrayGet($aOptions, 'contents');
        $aMetadata = KTUtil::arrayGet($aOptions, 'metadata', null, false);
        $oDocumentType = KTUtil::arrayGet($aOptions, 'documenttype');
        $sDescription = KTUtil::arrayGet($aOptions, 'description', '');

        if(empty($sDescription)){
            // If no document name is provided use the filename minus the extension
            $aFile = pathinfo($sFilename);
            $sDescription = (isset($aFile['filename']) && !empty($aFile['filename'])) ? $aFile['filename'] : $sFilename;
        }

        $oUploadChannel =& KTUploadChannel::getSingleton();

        if ($oDocumentType) {
            $iDocumentTypeId = KTUtil::getId($oDocumentType);
        } else {
            $iDocumentTypeId = 1;
        }
        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Creating database entry')));
        $oDocument =& Document::createFromArray(array(
            'name' => $sDescription,
            'description' => $sDescription,
            'filename' => $sFilename,
            'folderid' => $oFolder->getID(),
            'creatorid' => $oUser->getID(),
            'documenttypeid' => $iDocumentTypeId,
            ));

        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Storing contents')));
        $res = KTDocumentUtil::storeContents($oDocument, '', $aOptions);
        if (PEAR::isError($res)) {
            if (!PEAR::isError($oDocument)) {
                $oDocument->delete();
            }
            return $res;
        }

        if (is_null($aMetadata)) {
            $res = KTDocumentUtil::setIncomplete($oDocument, 'metadata');
            if (PEAR::isError($res)) {
                $oDocument->delete();
                return $res;
            }
        } else {
            $oUploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Saving metadata')));
            $res = KTDocumentUtil::saveMetadata($oDocument, $aMetadata, $aOptions);
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

    // Overwrite the document
    function overwrite($oDocument, $sFilename, $sTempFileName, $oUser, $aOptions) {
        //$oDocument, $sFilename, $sCheckInComment, $oUser, $aOptions = false
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $iFileSize = filesize($sTempFileName);

        // Check that document is not checked out
        if($oDocument->getIsCheckedOut()) {
            return PEAR::raiseError(_kt('Document is checkout and cannot be overwritten'));
        }

        if (!$oStorage->upload($oDocument, $sTempFileName)) {
            return PEAR::raiseError(_kt('An error occurred while storing the new file'));
        }

        $oDocument->setLastModifiedDate(getCurrentDateTime());
        $oDocument->setModifiedUserId($oUser->getId());

        $oDocument->setFileSize($iFileSize);

        $sOriginalFilename = $oDocument->getFileName();

        if($sOriginalFilename != $sFilename){
            if(strlen($sFilename)) {
        	global $default;
        	$oDocument->setFileName($sFilename);
        	$default->log->info('renamed document ' . $oDocument->getId() . ' to ' . $sFilename);
            }
            $oDocument->setMinorVersionNumber($oDocument->getMinorVersionNumber()+1);
        }

        $sType = KTMime::getMimeTypeFromFile($sFilename);
        $iMimeTypeId = KTMime::getMimeTypeID($sType, $oDocument->getFileName());
        $oDocument->setMimeTypeId($iMimeTypeId);

        $bSuccess = $oDocument->update();
        if ($bSuccess !== true) {
            if (PEAR::isError($bSuccess)) {
                return $bSuccess;
            }
            return PEAR::raiseError(_kt('An error occurred while storing this document in the database'));
        }
/*
        // create the document transaction record
        $oDocumentTransaction = new DocumentTransaction($oDocument, $sCheckInComment, 'ktcore.transactions.check_in');
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

        // NEW SEARCH

        Indexer::index($oDocument);


        // fire subscription alerts for the checked in document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        $oSubscriptionEvent->CheckinDocument($oDocument, $oFolder);

        KTDocumentUtil::updateSearchableText($oDocument);
*/
        return true;
    }

    // {{{ validateMetadata
    function validateMetadata(&$oDocument, $aMetadata) {
        $aFieldsets =& KTFieldset::getGenericFieldsets();
        $aFieldsets =& kt_array_merge($aFieldsets,
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
            $isRealConditional = ($oFieldset->getIsConditional() && KTMetadataUtil::validateCompleteness($oFieldset));
            foreach ($aFields as $oField) {
                $v = KTUtil::arrayGet($aSimpleMetadata, $oField->getId());
                if ($oField->getIsMandatory() && !$isRealConditional) {
                    if (empty($v)) {
                        // XXX: What I'd do for a setdefault...
                        $aFailed['field'][$oField->getId()] = 1;
                    }
                }
                if (!empty($v)) {
                    $aFieldValues[$oField->getId()] = $v;
                }
            }

            if ($isRealConditional) {
                $res = KTMetadataUtil::getNext($oFieldset, $aFieldValues);
                if ($res) {
                    foreach ($res as $aMDSet) {
                        if ($aMDSet['field']->getIsMandatory()) {
                            $aFailed['fieldset'][$oFieldset->getId()] = 1;
                        }
                    }
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
    function saveMetadata(&$oDocument, $aMetadata, $aOptions = null) {
        $table = 'document_fields_link';
        $bNoValidate = KTUtil::arrayGet($aOptions, 'novalidate', false);
        if ($bNoValidate !== true)
        {
            $res = KTDocumentUtil::validateMetadata($oDocument, $aMetadata);
            if (PEAR::isError($res))
            {
            	return $res;
       		}
	        $aMetadata = empty($res)?array():$res;
        }

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
                'metadata_version_id' => $iMetadataVersionId,
                'document_field_id' => $oMetadata->getID(),
                'value' => $sValue,
            ));
            if (PEAR::isError($res)) {
                return $res;
            }
        }
        KTDocumentUtil::setComplete($oDocument, 'metadata');
        KTDocumentUtil::updateSearchableText($oDocument);
        DocumentFieldLink::clearAllCaches();
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
        $table = 'document_incomplete';
        $iId = $oDocument->getId();
        $aIncomplete = DBUtil::getOneResult(array("SELECT * FROM $table WHERE id = ?", array($iId)));
        if (PEAR::isError($aIncomplete)) {
            return $aIncomplete;
        }
        if (is_null($aIncomplete)) {
            $aIncomplete = array('id' => $iId);
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
        $table = 'document_incomplete';
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
            if ($k === 'id') { continue; }

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
        $GLOBALS['_IN_ADD'] = true;
        $ret = KTDocumentUtil::_in_add($oFolder, $sFilename, $oUser, $aOptions);
        unset($GLOBALS['_IN_ADD']);
        return $ret;
    }
    // }}}


    // {{{ _in_add
    function &_in_add($oFolder, $sFilename, $oUser, $aOptions) {
        $aOrigOptions = $aOptions;
        while(KTDocumentUtil::fileExists($oFolder, $sFilename)) {
          $oDoc = Document::getByFilenameAndFolder($sFilename, $oFolder->getId());
          $sFilename = KTDocumentUtil::generateNewDocumentFilename($oDoc->getFileName());
        }
        $sName = KTUtil::arrayGet($aOptions, 'description', $sFilename);
        while(KTDocumentUtil::nameExists($oFolder, $sName)) {
          $oDoc = Document::getByNameAndFolder($sName, $oFolder->getId());
          $aOptions['description'] = KTDocumentUtil::generateNewDocumentName($oDoc->getName());
          $sName = KTDocumentUtil::generateNewDocumentName($oDoc->getName());
        }

        $oUploadChannel =& KTUploadChannel::getSingleton();
        $oUploadChannel->sendMessage(new KTUploadNewFile($sFilename));
        DBUtil::startTransaction();
        $oDocument =& KTDocumentUtil::_add($oFolder, $sFilename, $oUser, $aOptions);

        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Document created')));
        if (PEAR::isError($oDocument)) {
            return $oDocument;
        }

        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Scanning file')));
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('content', 'scan');
        $iTrigger = 0;
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $oTrigger->setDocument($oDocument);
            // $oUploadChannel->sendMessage(new KTUploadGenericMessage(sprintf(_kt("    (trigger %s)"), $sTrigger)));
            $ret = $oTrigger->scan();
            if (PEAR::isError($ret)) {
                $oDocument->delete();
                return $ret;
            }
        }

        // NEW SEARCH

        Indexer::index($oDocument);

        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Creating transaction')));
        $aOptions = array('user' => $oUser);
        //create the document transaction record
        $oDocumentTransaction = new DocumentTransaction($oDocument, _kt('Document created'), 'ktcore.transactions.create', $aOptions);
        $res = $oDocumentTransaction->create();
        if (PEAR::isError($res)) {
            $oDocument->delete();
            return $res;
        }

        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Sending subscriptions')));
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
                'document' => $oDocument,
                'aOptions' => $aOrigOptions,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();

        }
        KTDocumentUtil::updateSearchableText($oDocument, true);

        DBUtil::commit();

        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Checking permissions...')));

        // Check if there are any dynamic conditions / permissions that need to be updated on the document
        // If there are dynamic conditions then update the permissions on the document
        // The dynamic condition test fails unless the document exists in the DB therefore update permissions after committing the transaction.
        include_once(KT_LIB_DIR.'/permissions/permissiondynamiccondition.inc.php');
        $iPermissionObjectId = $oFolder->getPermissionObjectID();
        $dynamicCondition = KTPermissionDynamicCondition::getByPermissionObjectId($iPermissionObjectId);

        if(!PEAR::isError($dynamicCondition) && !empty($dynamicCondition)){
            $res = KTPermissionUtil::updatePermissionLookup($oDocument);
        }

        $oUploadChannel->sendMessage(new KTUploadGenericMessage(_kt('All done...')));

        return $oDocument;
    }
    // }}}

	function generateNewDocumentFilename($sDocFilename){
		if(preg_match("/\([0-9]+\)(\.[^\.]+){1,}$/", $sDocFilename)){
		  preg_match("/\([0-9]+\)\./", $sDocFilename, $matches);
		  $new_one = substr($matches[0], 1);
		  $new_two = explode(')', $new_one);
		  $new = $new_two[0]+1;

		  $pattern[0] = '/\([0-9]+\)\./';
		  $replacement[0] = ' ('.$new.').';
		  $sFilename = preg_replace($pattern, $replacement, $sDocFilename);
		}else{
		  $matches = explode('.', $sDocFilename);
		  $prefix = $matches[0].' (2)';
		  for($i = 1; $i < count($matches); $i++ ){
		    $suffix .= '.'.$matches[$i];
		  }
		  $sFilename = $prefix.$suffix;
		}

		return $sFilename;
	}

	function generateNewDocumentName($sDocName){
		if(preg_match("/\([0-9]+\)$/", $sDocName)){
		  preg_match("/\([0-9]+\)$/", $sDocName, $matches);
		  $new_one = substr($matches[0], 1);
		  $new_two = explode(')', $new_one);
		  $new = $new_two[0]+1;

		  $pattern[0] = '/\([0-9]+\)$/';
		  $replacement[0] = '('.$new.')';
		  $sName = preg_replace($pattern, $replacement, $sDocName);
		}else{
		  $sName =  $sDocName.' (2)';
		}

		return $sName;
	}

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
    function storeContents(&$oDocument, $oContents = null, $aOptions = null) {
        if (is_null($aOptions)) {
            $aOptions = array();
        }
        if (PEAR::isError($oDocument)) {
            return PEAR::raiseError(sprintf(_kt("Couldn't store contents: %s"), $oDocument->getMessage()));
        }

        $bCanMove = KTUtil::arrayGet($aOptions, 'move');
        $oStorage =& KTStorageManagerUtil::getSingleton();

        $oKTConfig =& KTConfig::getSingleton();
        $sBasedir = $oKTConfig->get('urls/tmpDirectory');

        $sFilename = (isset($aOptions['temp_file'])) ? $aOptions['temp_file'] : '';

//        $oOutputFile = new KTFSFileLike($sFilename);
//        $res = KTFileLikeUtil::copy_contents($oContents, $oOutputFile);
//        if (($res === false)) {
//            return PEAR::raiseError(_kt("Couldn't store contents, and no reason given."));
//        } else if (PEAR::isError($res)) {
//            return PEAR::raiseError(sprintf(_kt("Couldn't store contents: %s"), $res->getMessage()));
//        }

        if(empty($sFilename)){
            return PEAR::raiseError(sprintf(_kt("Couldn't store contents: %s"), _kt('The uploaded file does not exist.')));
        }

        $md5hash = md5_file($sFilename);
        $content = $oDocument->_oDocumentContentVersion;
        $content->setStorageHash($md5hash);
        $content->update();

        if (empty($aOptions)) $aOptions = array();
        $aOptions['md5hash'] = $md5hash;

        $sType = KTMime::getMimeTypeFromFile($sFilename);
        $iMimeTypeId = KTMime::getMimeTypeID($sType, $oDocument->getFileName());
        $oDocument->setMimeTypeId($iMimeTypeId);

        $res = $oStorage->upload($oDocument, $sFilename, $aOptions);
        if ($res === false) {
            return PEAR::raiseError(sprintf(_kt("Couldn't store contents: %s"), _kt('No reason given')));
        }
        if (PEAR::isError($res)) {
            return PEAR::raiseError(sprintf(_kt("Couldn't store contents: %s"), $res->getMessage()));
        }
        KTDocumentUtil::setComplete($oDocument, 'contents');

        if ($aOptions['cleanup_initial_file'] && file_exists($sFilename)) {
            @unlink($sFilename);
        }

        return true;
    }
    // }}}

    // {{{ updateTransactionText
    function updateTransactionText($oDocument) {

        // NEW SEARCH

        return;

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
            'document_id' => $iDocumentId,
            'document_text' => $sAllComments,
        );
        return DBUtil::autoInsert($sTable, $aInsert, array('noid' => true));
    }
    // }}}

    // {{{ updateSearchableText
    function updateSearchableText($oDocument, $bOverride = false) {

        // NEW SEARCH
        return;

        if (isset($GLOBALS['_IN_ADD']) && empty($bOverride)) {
            return;
        }
        $sMetadata = KTUtil::arrayGet( $_REQUEST, 'metadata_2');
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
        $sAllFieldText = join(' ', $aFieldValues);
        $sDocumentFilename = $oDocument->getFilename();
        $sDocumentTitle = $oDocument->getName();
        $sSearchableText = $sAllDocumentText . ' ' . $sAllFieldText . ' ' . $sAllComments . ' ' . $sDocumentFilename . ' ' . $sDocumentTitle . ' ' . $sMetadata;
        $sTable = KTUtil::getTableName('document_searchable_text');
        $aDelete = array(
            'document_id' => $iDocumentId,
        );
        DBUtil::whereDelete($sTable, $aDelete);
        $aInsert = array(
            'document_id' => $iDocumentId,
            'document_text' => $sSearchableText,
        );
        return DBUtil::autoInsert($sTable, $aInsert, array('noid' => true));
    }
    // }}}

    // {{{ delete
    function delete($oDocument, $sReason, $iDestFolderId = null) {
        $oDocument =& KTUtil::getObject('Document', $oDocument);
        if (is_null($iDestFolderId)) {
            $iDestFolderId = $oDocument->getFolderID();
        }
        $oStorageManager =& KTStorageManagerUtil::getSingleton();

        global $default;

        if (count(trim($sReason)) == 0) {
            return PEAR::raiseError(_kt('Deletion requires a reason'));
        }

        if (PEAR::isError($oDocument) || ($oDocument == false)) {
            return PEAR::raiseError(_kt('Invalid document object.'));
        }

        if ($oDocument->getIsCheckedOut() == true) {
            return PEAR::raiseError(sprintf(_kt('The document is checked out and cannot be deleted: %s'), $oDocument->getName()));
        }

        // IF we're deleted ...
        if ($oDocument->getStatusID() == DELETED) {
            return true;
        }

        $oOrigFolder = Folder::get($oDocument->getFolderId());

        DBUtil::startTransaction();

        // flip the status id
        $oDocument->setStatusID(DELETED);

        // $iDestFolderId is DEPRECATED.
        $oDocument->setFolderID(null);
        $oDocument->setRestoreFolderId($oOrigFolder->getId());
        $oDocument->setRestoreFolderPath(Folder::generateFolderIDs($oOrigFolder->getId()));

        $res = $oDocument->update();

        if (PEAR::isError($res) || ($res == false)) {
            DBUtil::rollback();
            return PEAR::raiseError(_kt('There was a problem deleting the document from the database.'));
        }

        // now move the document to the delete folder
        $res = $oStorageManager->delete($oDocument);
        if (PEAR::isError($res) || ($res == false)) {
            //could not delete the document from the file system
            $default->log->error('Deletion: Filesystem error deleting document ' .
                $oDocument->getFileName() . ' from folder ' .
                Folder::getFolderPath($oDocument->getFolderID()) .
                ' id=' . $oDocument->getFolderID());

            // we use a _real_ transaction here ...

            DBUtil::rollback();

            /*
            //reverse the document deletion
            $oDocument->setStatusID(LIVE);
            $oDocument->update();
            */

            return PEAR::raiseError(_kt('There was a problem deleting the document from storage.'));
        }

        $oDocumentTransaction = new DocumentTransaction($oDocument, _kt('Document deleted: ') . $sReason, 'ktcore.transactions.delete');
        $oDocumentTransaction->create();

        $oDocument->setFolderID(1);

        DBUtil::commit();


	// we weren't doing notifications on this one
        $oSubscriptionEvent = new SubscriptionEvent();
        $oSubscriptionEvent->RemoveDocument($oDocument, $oOrigFolder);


        // document is now deleted:  triggers are best-effort.

        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('delete', 'postValidate');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                'document' => $oDocument,
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

        // NEW SEARCH

        Indexer::index($oDocument);

        return;

        /*
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
        KTDocumentUtil::updateSearchableText($oDocument);*/
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


    function copy($oDocument, $oDestinationFolder, $sReason = null, $sDestinationDocName = null) {
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

        // Get the metadata version for the source document
        $sTable = KTUtil::getTableName('document_metadata_version');
        $sQuery = 'SELECT * FROM ' . $sTable . ' WHERE id = ?';
        $aParams = array($oDocument->getMetadataVersionId());
        $aMDRow = DBUtil::getOneResult(array($sQuery, $aParams));
        unset($aMDRow['id']);

        // Copy the source metadata into the destination document
        $aMDRow['document_id'] = $oCore->getId();
        if(!empty($sDestinationDocName)){
            $aMDRow['name'] = $sDestinationDocName;
            $aMDRow['description'] = $sDestinationDocName;
        }
        $id = DBUtil::autoInsert($sTable, $aMDRow);
        if (PEAR::isError($id)) { return $id; }
        $oCore->setMetadataVersionId($id);
        $oMDV = KTDocumentMetadataVersion::get($id);

        // Get the content version for the source document
        $sTable = KTUtil::getTableName('document_content_version');
        $sQuery = 'SELECT * FROM ' . $sTable . ' WHERE id = ?';
        $aParams = array($oDocument->_oDocumentContentVersion->getId());
        $aContentRow = DBUtil::getOneResult(array($sQuery, $aParams));
        unset($aContentRow['id']);

        // Copy the source content into the destination document
        $aContentRow['document_id'] = $oCore->getId();
        if(!empty($sDestinationDocName)){
            $aContentRow['filename'] = $sDestinationDocName;
        }
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

        // Ensure the copied document is not checked out
        $oNewDocument->setIsCheckedOut(false);
        $oNewDocument->setCheckedOutUserID(-1);

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

        // NEW SEARCH


        /*

        $sTable = KTUtil::getTableName('document_text');
        $aQuery = array("SELECT document_text FROM $sTable WHERE document_id = ?", array($oDocument->getId()));
        $sData = DBUtil::getOneResultKey($aQuery, 'document_text');

        $aInsertValues = array(
            'document_id' => $oNewDocument->getId(),
            'document_text' => $contents,
        );
        DBUtil::autoInsert($sTable, $aInsertValues, array('noid' => true));

        */
        KTDocumentUtil::updateSearchableText($oNewDocument);
        KTPermissionUtil::updatePermissionLookup($oNewDocument);

        if (is_null($sReason)) {
            $sReason = '';
        }

        $oDocumentTransaction = new DocumentTransaction($oDocument, sprintf(_kt("Copied to folder \"%s\". %s"), $oDestinationFolder->getName(), $sReason), 'ktcore.transactions.copy');
        $oDocumentTransaction->create();

        $oSrcFolder = Folder::get($oDocument->getFolderID());
        $oDocumentTransaction = new DocumentTransaction($oNewDocument, sprintf(_kt("Copied from original in folder \"%s\". %s"), $oSrcFolder->getName(), $sReason), 'ktcore.transactions.copy');
        $oDocumentTransaction->create();


        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('copyDocument', 'postValidate');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                'document' => $oNewDocument,
                'old_folder' => $oSrcFolder,
                'new_folder' => $oDestinationFolder,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
            if (PEAR::isError($ret)) {
                return $ret;
            }
        }

        // fire subscription alerts for the copied document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        $oSubscriptionEvent->MoveDocument($oDocument, $oDestinationFolder, $oSrcFolder, 'CopiedDocument');

        return $oNewDocument;
    }

    function rename($oDocument, $sNewFilename, $oUser) {
        $oStorage =& KTStorageManagerUtil::getSingleton();

        $iPreviousMetadataVersion = $oDocument->getMetadataVersionId();
        $oOldContentVersion = $oDocument->_oDocumentContentVersion;
        $bSuccess = $oDocument->startNewContentVersion($oUser);
        if (PEAR::isError($bSuccess)) {
            return $bSuccess;
        }
        KTDocumentUtil::copyMetadata($oDocument, $iPreviousMetadataVersion);
        $res = $oStorage->renameDocument($oDocument, $oOldContentVersion, $sNewFilename);

        if (!$res) {
            return PEAR::raiseError(_kt('An error occurred while storing the new file'));
        }

        $oDocument->setLastModifiedDate(getCurrentDateTime());
        $oDocument->setModifiedUserId($oUser->getId());
        $oDocument->setMinorVersionNumber($oDocument->getMinorVersionNumber()+1);
		$oDocument->_oDocumentContentVersion->setFilename($sNewFilename);

		$sType = KTMime::getMimeTypeFromFile($sNewFilename);
		$iMimeTypeId = KTMime::getMimeTypeID($sType, $sNewFilename);
        $oDocument->setMimeTypeId($iMimeTypeId);

        $bSuccess = $oDocument->update();
        if ($bSuccess !== true) {
            if (PEAR::isError($bSuccess)) {
                return $bSuccess;
            }
            return PEAR::raiseError(_kt('An error occurred while storing this document in the database'));
        }

        // create the document transaction record
        $oDocumentTransaction = new DocumentTransaction($oDocument, _kt('Document renamed'), 'ktcore.transactions.update');
        $oDocumentTransaction->create();

        // fire subscription alerts for the checked in document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        $oSubscriptionEvent->ModifyDocument($oDocument, $oFolder);

        return true;
    }

    function move($oDocument, $oToFolder, $oUser = null, $sReason = null) {

        $oFolder = $oToFolder; // alias.

        $oOriginalFolder = Folder::get($oDocument->getFolderId());
        $iOriginalFolderPermissionObjectId = $oOriginalFolder->getPermissionObjectId();
        $iDocumentPermissionObjectId = $oDocument->getPermissionObjectId();

        if ($iDocumentPermissionObjectId === $iOriginalFolderPermissionObjectId) {
            $oDocument->setPermissionObjectId($oFolder->getPermissionObjectId());
        }

        //put the document in the new folder
        $oDocument->setFolderID($oFolder->getId());
        $res = $oDocument->update();
        if (PEAR::isError($res)) {
            return $res;
        }


        //move the document on the file system
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $res = $oStorage->moveDocument($oDocument, $oFolder, $oOriginalFolder);
        if (PEAR::isError($res) || ($res === false)) {
            $oDocument->setFolderID($oOriginalFolder->getId());
            $res = $oDocument->update();
            if (PEAR::isError($res)) {
                return $res;
            }
            return $res; // we failed, bail.
        }

        $sMoveMessage = sprintf(_kt("Moved from %s/%s to %s/%s. %s"),
            $oOriginalFolder->getFullPath(),
            $oOriginalFolder->getName(),
            $oFolder->getFullPath(),
            $oFolder->getName(),
            $sReason);

        // create the document transaction record

        $oDocumentTransaction = new DocumentTransaction($oDocument, $sMoveMessage, 'ktcore.transactions.move');
        $oDocumentTransaction->create();


        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('moveDocument', 'postValidate');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                'document' => $oDocument,
                'old_folder' => $oOriginalFolder,
                'new_folder' => $oFolder,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
            if (PEAR::isError($ret)) {
                return $ret;
            }
        }

        // fire subscription alerts for the moved document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oSubscriptionEvent->MoveDocument($oDocument, $oFolder, $oOriginalFolder);

        return KTPermissionUtil::updatePermissionLookup($oDocument);
    }

    /**
    * Delete a selected version of the document.
    */
    function deleteVersion($oDocument, $iVersionID, $sReason){

        $oDocument =& KTUtil::getObject('Document', $oDocument);
        $oVersion =& KTDocumentMetadataVersion::get($iVersionID);

        $oStorageManager =& KTStorageManagerUtil::getSingleton();

        global $default;

        if (empty($sReason)) {
            return PEAR::raiseError(_kt('Deletion requires a reason'));
        }

        if (PEAR::isError($oDocument) || ($oDocument == false)) {
            return PEAR::raiseError(_kt('Invalid document object.'));
        }

        if (PEAR::isError($oVersion) || ($oVersion == false)) {
            return PEAR::raiseError(_kt('Invalid document version object.'));
        }

        $iContentId = $oVersion->getContentVersionId();
        $oContentVersion = KTDocumentContentVersion::get($iContentId);

        if (PEAR::isError($oContentVersion) || ($oContentVersion == false)) {
            DBUtil::rollback();
            return PEAR::raiseError(_kt('Invalid document content version object.'));
        }

        DBUtil::startTransaction();

        // now delete the document version
        $res = $oStorageManager->deleteVersion($oVersion);
        if (PEAR::isError($res) || ($res == false)) {
            //could not delete the document version from the file system
            $default->log->error('Deletion: Filesystem error deleting the metadata version ' .
                $oVersion->getMetadataVersion() . ' of the document ' .
                $oDocument->getFileName() . ' from folder ' .
                Folder::getFolderPath($oDocument->getFolderID()) .
                ' id=' . $oDocument->getFolderID());

            // we use a _real_ transaction here ...

            DBUtil::rollback();

            return PEAR::raiseError(_kt('There was a problem deleting the document from storage.'));
        }

        // change status for the metadata version
        $oVersion->setStatusId(VERSION_DELETED);
        $oVersion->update();

        // set the storage path to empty
//        $oContentVersion->setStoragePath('');

        DBUtil::commit();
    }
}

class KTMetadataValidationError extends PEAR_Error {
    function KTMetadataValidationError ($aFailed) {
        $this->aFailed = $aFailed;
        $message = _kt('Please be sure to enter information for all the Required fields below');
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
