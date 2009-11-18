<?php
/**
 * $Id$
 *
 * Document-handling utility functions
 *
 * Simplifies and canonicalises operations such as adding, updating, and
 * deleting documents from the repository.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
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
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
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

// NEW PATHS
require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
require_once(KT_LIB_DIR . '/filelike/filelikeutil.inc.php');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/subscriptions/subscriptions.inc.php');
require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/alert/EmailTemplate.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

// WORKFLOW
require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');

class KTDocumentUtil {
    function checkin($oDocument, $sFilename, $sCheckInComment, $oUser, $aOptions = false, $bulk_action = false) {
        $oStorage =& KTStorageManagerUtil::getSingleton();

        $iFileSize = filesize($sFilename);

        $iPreviousMetadataVersion = $oDocument->getMetadataVersionId();

        $bSuccess = $oDocument->startNewContentVersion($oUser);
        if (PEAR::isError($bSuccess)) {
            return $bSuccess;
        }

        KTDocumentUtil::copyMetadata($oDocument, $iPreviousMetadataVersion);

        $aOptions['temp_file'] = $sFilename;
        $res = KTDocumentUtil::storeContents($oDocument, '', $aOptions);
        if (PEAR::isError($res)) {
            return $res;
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

        if(is_array($aOptions)) {
            $sFilename = KTUtil::arrayGet($aOptions, 'newfilename', '');
            if(!empty($sFilename)) {
                global $default;
                $oDocument->setFileName($sFilename);
                $default->log->info('renamed document ' . $oDocument->getId() . ' to ' . $sFilename);

                // detection of mime types needs to be refactored. this stuff is damn messy!
                // If the filename has changed then update the mime type
                $iMimeTypeId = KTMime::getMimeTypeID('', $sFilename);
                $oDocument->setMimeTypeId($iMimeTypeId);
            }
        }

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

        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('checkin', 'postValidate');

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

        Indexer::index($oDocument);
        if(!$bulk_action) {
            // fire subscription alerts for the checked in document
            $oSubscriptionEvent = new SubscriptionEvent();
            $oFolder = Folder::get($oDocument->getFolderID());
            $oSubscriptionEvent->CheckinDocument($oDocument, $oFolder);
        }

        return true;
    }

    function checkout($oDocument, $sCheckoutComment, $oUser, $bulk_action = false) {
    	//automatically check out the linked document if this is a shortcut
		if($oDocument->isSymbolicLink()){
    		$oDocument->switchToLinkedCore();
    	}
        if ($oDocument->getIsCheckedOut()) {
            return PEAR::raiseError(_kt('Already checked out.'));
        }

        if($oDocument->getImmutable()){
        	return PEAR::raiseError(_kt('Document cannot be checked out as it is immutable'));
        }

        // Check if the action is restricted by workflow on the document
        if(!KTWorkflowUtil::actionEnabledForDocument($oDocument, 'ktcore.actions.document.checkout')){
            return PEAR::raiseError(_kt('Checkout is restricted by the workflow state.'));
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
            $ret = $oTrigger->postValidate(true);
            if (PEAR::isError($ret)) {
                return $ret;
            }
        }

        $oDocumentTransaction = new DocumentTransaction($oDocument, $sCheckoutComment, 'ktcore.transactions.check_out');
        $oDocumentTransaction->create();

        if(!$bulk_action) {
            // fire subscription alerts for the downloaded document
            $oSubscriptionEvent = new SubscriptionEvent();
            $oFolder = Folder::get($oDocument->getFolderID());
            $oSubscriptionEvent->CheckOutDocument($oDocument, $oFolder);
        }

        return true;
    }

    function archive($oDocument, $sReason, $bulk_action = false) {

        if($oDocument->isSymbolicLink()){
        	return PEAR::raiseError(_kt("It is not possible to archive a shortcut. Please archive the target document."));
        }

        // Ensure the action is not blocked
        if(!KTWorkflowUtil::actionEnabledForDocument($oDocument, 'ktcore.actions.document.archive')){
            return PEAR::raiseError(_kt('Document cannot be archived as it is restricted by the workflow.'));
        }

        $oDocument->setStatusID(ARCHIVED);
        $res = $oDocument->update();

        if (PEAR::isError($res) || ($res === false)) {
            return PEAR::raiseError(_kt('There was a database error while trying to archive this file'));
        }

    	//delete all shortcuts linking to this document
        $aSymlinks = $oDocument->getSymbolicLinks();
        foreach($aSymlinks as $aSymlink){
        	$oShortcutDocument = Document::get($aSymlink['id']);
        	$oOwnerUser = User::get($oShortcutDocument->getOwnerID());

        	KTDocumentUtil::deleteSymbolicLink($aSymlink['id']);

        	//send an email to the owner of the shortcut
        	if($oOwnerUser->getEmail()!=null && $oOwnerUser->getEmailNotification() == true){
        		$emailTemplate = new EmailTemplate("kt3/notifications/notification.SymbolicLinkArchived",array('user_name'=>$this->oUser->getName(),
        			'url'=>KTUtil::ktLink(KTBrowseUtil::getUrlForDocument($oShortcutDocument)),
        			'title' =>$oShortcutDocument->getName()));
        		$email = new EmailAlert($oOwnerUser->getEmail(),_kt("KnowledgeTree Notification"),$emailTemplate->getBody());
        		$email->send();
        	}
        }

        $oDocumentTransaction = & new DocumentTransaction($oDocument, sprintf(_kt('Document archived: %s'), $sReason), 'ktcore.transactions.update');
        $oDocumentTransaction->create();

        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('archive', 'postValidate');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                'document' => $oDocument,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate(true);
            if (PEAR::isError($ret)) {
                $oDocument->delete();
                return $ret;
            }
        }
        if(!$bulk_action) {
            // fire subscription alerts for the archived document
            $oSubscriptionEvent = new SubscriptionEvent();
            $oFolder = Folder::get($oDocument->getFolderID());
            $oSubscriptionEvent->ArchivedDocument($oDocument, $oFolder);
        }

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

    /**
     * Create a symbolic link in the target folder
     *
     * @param Document $sourceDocument the document to create a link to
     * @param Folder $targetFolder the folder to place the link in
     * @param User $user current user
     */
    static function createSymbolicLink($sourceDocument, $targetFolder, $user = null) // added/
    {
    	//validate input
        if (is_numeric($sourceDocument))
        {
            $sourceDocument = Document::get($sourceDocument);
        }
        if (!$sourceDocument instanceof Document)
        {
            return PEAR::raiseError(_kt('Source document not specified'));
        }
        if (is_numeric($targetFolder))
        {
            $targetFolder = Folder::get($targetFolder);
        }
        if (!$targetFolder instanceof Folder)
        {
            return PEAR::raiseError(_kt('Target folder not specified'));
        }
        if (is_null($user))
        {
            $user = $_SESSION['userID'];
        }
        if (is_numeric($user))
        {
            $user = User::get($user);
        }

        //check for permissions
        $oPermission =& KTPermission::getByName("ktcore.permissions.write");
		$oReadPermission =& KTPermission::getByName("ktcore.permissions.read");
        if (KTBrowseUtil::inAdminMode($user, $targetFolder)) {
	        if(!KTPermissionUtil::userHasPermissionOnItem($user, $oPermission, $targetFolder)){
	        	return PEAR::raiseError(_kt('You\'re not authorized to create shortcuts'));
	        }
        }
     	if (!KTBrowseUtil::inAdminMode($user, $sourceDocument->getParentID())) {
        	if(!KTPermissionUtil::userHasPermissionOnItem($user, $oReadPermission, $sourceDocument)){
        		return PEAR::raiseError(_kt('You\'re not authorized to create a shortcut to this document'));
       		}
        }

        //check if the shortcut doesn't already exists in the target folder
        $aSymlinks = $sourceDocument->getSymbolicLinks();
        foreach($aSymlinks as $iSymlink){
        	$oSymlink = Document::get($iSymlink['id']);
        	$oSymlink->switchToRealCore();
        	if($oSymlink->getFolderID() == $targetFolder->getID()){
        		return PEAR::raiseError(_kt('There already is a shortcut to this document in the target folder.'));
        	}
        }

		//create the actual shortcut
        $oCore = KTDocumentCore::createFromArray(array(
            'iCreatorId'=>$user->getId(),
            'iFolderId'=>$targetFolder->getId(),
            'iLinkedDocumentId'=>$sourceDocument->getId(),
            'sFullPath'=> $targetFolder->getFullPath() . '/' .
$sourceDocument->getName(),
            'iPermissionObjectId'=>$targetFolder->getPermissionObjectID(),
            'iPermissionLookupId'=>$targetFolder->getPermissionLookupID(),
            'iStatusId'=>1,
            'iMetadataVersionId'=>$sourceDocument->getMetadataVersionId(),

        ));

        $document = Document::get($oCore->getId());

        return $document;
    }

    /**
     * Deletes a document symbolic link
     *
     * @param Document $document the symbolic link document
     * @param User $user the user deleting the link
     * @return unknown
     */
    static function deleteSymbolicLink($document, $user = null) // added/
    {
    	//validate input
        if (is_numeric($document))
        {
            $document = Document::get($document);
        }
        if (!$document instanceof Document)
        {
            return PEAR::raiseError(_kt('Document not specified'));
        }
        if (!$document->isSymbolicLink())
        {
            return PEAR::raiseError(_kt('Document must be a symbolic link entity'));
        }
        if (is_null($user))
        {
            $user = $_SESSION['userID'];
        }
        if (is_numeric($user))
        {
            $user = User::get($user);
        }

        //check permissions
    	$oPerm = KTPermission::getByName('ktcore.permissions.delete');
    	if (!KTBrowseUtil::inAdminMode($user, $document->getParentID())) {
            if(!KTPermissionUtil::userHasPermissionOnItem($user, $oPerm, $document)){
        		return PEAR::raiseError(_kt('You\'re not authorized to delete this shortcut'));
       		}
        }

        // we only need to delete the document entry for the link
        $sql = "DELETE FROM documents WHERE id=?";
        DBUtil::runQuery(array($sql, array($document->getId())));

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
     /*
      * Document Add
      * Author      :   Jarrett Jordaan
      * Modified    :   28/04/09
      *
      * @params     :   KTFolderUtil $oFolder
      *                 string $sFilename
      *                 KTUser $oUser
      *                 array $aOptions
      *                 boolean $bulk_action
      */
    // {{{ add
    function &add($oFolder, $sFilename, $oUser, $aOptions, $bulk_action = false) {
        $GLOBALS['_IN_ADD'] = true;
        $ret = KTDocumentUtil::_in_add($oFolder, $sFilename, $oUser, $aOptions, $bulk_action);
        unset($GLOBALS['_IN_ADD']);
        return $ret;
    }
    // }}}

    function getUniqueFilename($oFolder, $sFilename) {
        // this is just a quick refactoring. We should look at a more optimal way of doing this as there are
        // quite a lot of queries.
        $iFolderId = $oFolder->getId();
        while (KTDocumentUtil::fileExists($oFolder, $sFilename)) {
          $oDoc = Document::getByFilenameAndFolder($sFilename, $iFolderId);
          $sFilename = KTDocumentUtil::generateNewDocumentFilename($oDoc->getFileName());
        }
        return $sFilename;
    }

    function getUniqueDocumentName($oFolder, $sFilename)
    {
        // this is just a quick refactoring. We should look at a more optimal way of doing this as there are
        // quite a lot of queries.
        $iFolderId = $oFolder->getId();
        while(KTDocumentUtil::nameExists($oFolder, $sFilename)) {
          $oDoc = Document::getByNameAndFolder($sFilename, $iFolderId);
          $sFilename = KTDocumentUtil::generateNewDocumentName($oDoc->getName());
        }
        return $sFilename;
    }

     /*
      * Document Add
      * Author      :   Jarrett Jordaan
      * Modified    :   28/04/09
      *
      * @params     :   KTFolderUtil $oFolder
      *                 string $sFilename
      *                 KTUser $oUser
      *                 array $aOptions
      *                 boolean $bulk_action
      */
    // {{{ _in_add
    function &_in_add($oFolder, $sFilename, $oUser, $aOptions, $bulk_action = false) {
        $aOrigOptions = $aOptions;

        $sFilename = KTDocumentUtil::getUniqueFilename($oFolder, $sFilename);
        $sName = KTUtil::arrayGet($aOptions, 'description', $sFilename);
        $sName = KTDocumentUtil::getUniqueDocumentName($oFolder, $sName);
        $aOptions['description'] = $sName;

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
        // refresh the document object
        DBUtil::commit();

        $oDocument->clearAllCaches();

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
        // TODO : better way of checking if its a bulk upload
        if(!$bulk_action) {
            // fire subscription alerts for the checked in document
            $oSubscriptionEvent = new SubscriptionEvent();
            $oFolder = Folder::get($oDocument->getFolderID());
            $oSubscriptionEvent->AddDocument($oDocument, $oFolder);
        }
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

        // update document object with additional fields / data from the triggers
        $oDocument = Document::get($oDocument->iId);

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

    function incrementNameCollissionNumbering($sDocFilename, $skipExtension = false){

        $iDot = strpos($sDocFilename, '.');
        if ($skipExtension || $iDot === false)
        {
            if(preg_match("/\(([0-9]+)\)$/", $sDocFilename, $matches, PREG_OFFSET_CAPTURE)) {

                $iCount = $matches[1][0];
                $iPos = $matches[1][1];

                $iNewCount = $iCount + 1;
                $sDocFilename = substr($sDocFilename, 0, $iPos) . $iNewCount .  substr($sDocFilename, $iPos + strlen($iCount));
            }
            else {
                $sDocFilename = $sDocFilename . '(1)';
            }
        }
        else
        {
            if(preg_match("/\(([0-9]+)\)(\.[^\.]+)+$/", $sDocFilename, $matches, PREG_OFFSET_CAPTURE)) {

                $iCount = $matches[1][0];
                $iPos = $matches[1][1];

                $iNewCount = $iCount + 1;
                $sDocFilename = substr($sDocFilename, 0, $iPos) . $iNewCount .  substr($sDocFilename, $iPos + strlen($iCount));
            }
            else {
                $sDocFilename = substr($sDocFilename, 0, $iDot) . '(1)' . substr($sDocFilename, $iDot);
            }
        }
        return $sDocFilename;
    }


	function generateNewDocumentFilename($sDocFilename) {
	    return self::incrementNameCollissionNumbering($sDocFilename, false);
	}

	function generateNewDocumentName($sDocName){
	    return self::incrementNameCollissionNumbering($sDocName, true);

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

        if(empty($sFilename)){
            return PEAR::raiseError(sprintf(_kt("Couldn't store contents: %s"), _kt('The uploaded file does not exist.')));
        }

        $md5hash = md5_file($sFilename);
        $content = $oDocument->_oDocumentContentVersion;
        $content->setStorageHash($md5hash);
        $content->update();

        if (empty($aOptions)) $aOptions = array();
        $aOptions['md5hash'] = $md5hash;

        // detection of mime types needs to be refactored. this stuff is damn messy!
        $sType = KTMime::getMimeTypeFromFile($sFilename);
        $iMimeTypeId = KTMime::getMimeTypeID($sType, $oDocument->getFileName(), $sFilename);
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

     /*
      * Document Delete
      * Author      :   Jarrett Jordaan
      * Modified    :   28/04/09
      *
      * @params     :   KTDocumentUtil $oDocument
      *                 string $sReason
      *                 int $iDestFolderId
      *                 boolean $bulk_action
      */
    // {{{ delete
    function delete($oDocument, $sReason, $iDestFolderId = null, $bulk_action = false) {
    	// use the deleteSymbolicLink function is this is a symlink
        if ($oDocument->isSymbolicLink())
        {
            return KTDocumentUtil::deleteSymbolicLink($oDocument);
        }

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

        if(!KTWorkflowUtil::actionEnabledForDocument($oDocument, 'ktcore.actions.document.delete')){
            return PEAR::raiseError(_kt('Document cannot be deleted as it is restricted by the workflow.'));
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

        // get the user object
        $oUser = User::get($_SESSION['userID']);

        //delete all shortcuts linking to this document
        $aSymlinks = $oDocument->getSymbolicLinks();
        foreach($aSymlinks as $aSymlink){
        	$oShortcutDocument = Document::get($aSymlink['id']);
        	$oOwnerUser = User::get($oShortcutDocument->getOwnerID());

        	KTDocumentUtil::deleteSymbolicLink($aSymlink['id']);

        	//send an email to the owner of the shortcut
        	if($oOwnerUser->getEmail()!=null && $oOwnerUser->getEmailNotification() == true){
        		$emailTemplate = new EmailTemplate("kt3/notifications/notification.SymbolicLinkDeleted",array('user_name'=>$oUser->getName(),
        			'url'=>KTUtil::ktLink(KTBrowseUtil::getUrlForDocument($oShortcutDocument)),
        			'title' =>$oShortcutDocument->getName()));
        		$email = new EmailAlert($oOwnerUser->getEmail(),_kt("KnowledgeTree Notification"),$emailTemplate->getBody());
        		$email->send();
        	}
        }

        $oDocumentTransaction = new DocumentTransaction($oDocument, _kt('Document deleted: ') . $sReason, 'ktcore.transactions.delete');
        $oDocumentTransaction->create();

        $oDocument->setFolderID(1);

        DBUtil::commit();
        // TODO : better way of checking if its a bulk delete
        if(!$bulk_action) {
            // we weren't doing notifications on this one
            $oSubscriptionEvent = new SubscriptionEvent();
            $oSubscriptionEvent->RemoveDocument($oDocument, $oOrigFolder);
        }

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
            $ret = $oTrigger->postValidate(true);
            if (PEAR::isError($ret)) {
                $oDocument->delete();          // FIXME nbm: review that on-fail => delete is correct ?!
                return $ret;
            }
        }


    }
    // }}}

    function reindexDocument($oDocument) {

        Indexer::index($oDocument);
    }

    function canBeCopied($oDocument, &$sError) {
        if ($oDocument->getIsCheckedOut()) {
            $sError = PEAR::raiseError(_kt('Document cannot be copied as it is checked out.'));
            return false;
        }
        if (!KTWorkflowUtil::actionEnabledForDocument($oDocument, 'ktcore.actions.document.copy')) {
            $sError = PEAR::raiseError(_kt('Document cannot be copied as it is restricted by the workflow.'));
            return false;
        }
        return true;
    }

    function canBeMoved($oDocument, &$sError) {
        if ($oDocument->getImmutable()) {
            $sError = PEAR::raiseError(_kt('Document cannot be moved as it is immutable.'));
            return false;
        }
        if ($oDocument->getIsCheckedOut()) {
            $sError = PEAR::raiseError(_kt('Document cannot be moved as it is checked out.'));
            return false;
        }
        if (!KTWorkflowUtil::actionEnabledForDocument($oDocument, 'ktcore.actions.document.move')) {
            $sError = PEAR::raiseError(_kt('Document cannot be moved as it is restricted by the workflow.'));
            return false;
        }
        return true;
    }

    function canBeDeleted($oDocument, &$sError) {
        if($oDocument->getImmutable())
        {
            $sError = PEAR::raiseError(_kt('Document cannot be deleted as it is immutable.'));
            return false;
        }
        if ($oDocument->getIsCheckedOut()) {
            $sError = PEAR::raiseError(_kt('Document cannot be deleted as it is checked out.'));
            return false;
        }
        if(!KTWorkflowUtil::actionEnabledForDocument($oDocument, 'ktcore.actions.document.delete')){
            $sError = PEAR::raiseError(_kt('Document cannot be deleted as it is restricted by the workflow.'));
            return false;
        }
        return true;
    }

    function canBeArchived($oDocument, &$sError) {
        if ($oDocument->getIsCheckedOut()) {
            $sError = PEAR::raiseError(_kt('Document cannot be archived as it is checked out.'));
            return false;
        }
        if(!KTWorkflowUtil::actionEnabledForDocument($oDocument, 'ktcore.actions.document.archive')){
            $sError = PEAR::raiseError(_kt('Document cannot be archived as it is restricted by the workflow.'));
            return false;
        }
        return true;
    }

    function copy($oDocument, $oDestinationFolder, $sReason = null, $sDestinationDocName = null, $bulk_action = false) {
        // 1. generate a new triad of content, metadata and core objects.
        // 2. update the storage path.
		//print '--------------------------------- BEFORE';
        //print_r($oDocument);

        // TODO: this is not optimal. we have get() functions that will do SELECT when we already have the data in arrays

        // get the core record to be copied
        $sDocumentTable = KTUtil::getTableName('documents');
        $sQuery = 'SELECT * FROM ' . $sDocumentTable . ' WHERE id = ?';
        $aParams = array($oDocument->getId());
        $aCoreRow = DBUtil::getOneResult(array($sQuery, $aParams));
        // we unset the id as a new one will be created on insert
        unset($aCoreRow['id']);
        // we unset immutable since a new document will be created on insert
        unset($aCoreRow['immutable']);
        // get a copy of the latest metadata version for the copied document
        $iOldMetadataId = $aCoreRow['metadata_version_id'];
        $sMetadataTable = KTUtil::getTableName('document_metadata_version');
        $sQuery = 'SELECT * FROM ' . $sMetadataTable . ' WHERE id = ?';
        $aParams = array($iOldMetadataId);
        $aMDRow = DBUtil::getOneResult(array($sQuery, $aParams));
        // we unset the id as a new one will be created on insert
        unset($aMDRow['id']);

        // set the name for the document, possibly using name collission
        if (empty($sDestinationDocName)){
            $aMDRow['name'] = KTDocumentUtil::getUniqueDocumentName($oDestinationFolder, $aMDRow['name']);
        }
        else {
            $aMDRow['name'] = $sDestinationDocName;
        }

        // get a copy of the latest content version for the copied document
        $iOldContentId = $aMDRow['content_version_id'];
        $sContentTable = KTUtil::getTableName('document_content_version');
        $sQuery = 'SELECT * FROM ' . $sContentTable . ' WHERE id = ?';
        $aParams = array($iOldContentId);
        $aContentRow = DBUtil::getOneResult(array($sQuery, $aParams));
        // we unset the id as a new one will be created on insert
        unset($aContentRow['id']);

        // set the filename for the document, possibly using name collission
        if(empty($sDestinationDocName)) {
            $aContentRow['filename'] = KTDocumentUtil::getUniqueFilename($oDestinationFolder, $aContentRow['filename']);
        }
        else {
            $aContentRow['filename'] = $sDestinationDocName;
        }

        // create the new document record
        $aCoreRow['modified'] = date('Y-m-d H:i:s');
        $aCoreRow['folder_id'] = $oDestinationFolder->getId(); // new location.
        $id = DBUtil::autoInsert($sDocumentTable, $aCoreRow);
        if (PEAR::isError($id)) { return $id; }
        $iNewDocumentId = $id;

        // create the new metadata record
        $aMDRow['document_id'] = $iNewDocumentId;
        $aMDRow['description'] = $aMDRow['name'];
        $id = DBUtil::autoInsert($sMetadataTable, $aMDRow);
        if (PEAR::isError($id)) { return $id; }
        $iNewMetadataId = $id;

        // the document metadata version is still pointing to the original
        $aCoreUpdate = array();
        $aCoreUpdate['metadata_version_id'] = $iNewMetadataId;
        $aCoreUpdate['metadata_version'] = 0;

        // create the new content version
        $aContentRow['document_id'] = $iNewDocumentId;
        $id = DBUtil::autoInsert($sContentTable, $aContentRow);
        if (PEAR::isError($id)) { return $id; }
        $iNewContentId = $id;

        // the metadata content version is still pointing to the original
        $aMetadataUpdate = array();
        $aMetadataUpdate['content_version_id'] = $iNewContentId;
        $aMetadataUpdate['metadata_version'] = 0;

        // apply the updates to the document and metadata records
        $res = DBUtil::autoUpdate($sDocumentTable, $aCoreUpdate, $iNewDocumentId);
        if (PEAR::isError($res)) { return $res; }

        $res = DBUtil::autoUpdate($sMetadataTable, $aMetadataUpdate, $iNewMetadataId);
        if (PEAR::isError($res)) { return $res; }

        // now, we have a semi-sane document object. get it.
        $oNewDocument = Document::get($iNewDocumentId);

        //print '--------------------------------- AFTER';
        //print_r($oDocument);
		//print '======';
        //print_r($oNewDocument);

        // copy the metadata from old to new.
        $res = KTDocumentUtil::copyMetadata($oNewDocument, $iOldMetadataId);
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
        if(!$bulk_action) {
            // fire subscription alerts for the copied document
            $oSubscriptionEvent = new SubscriptionEvent();
            $oFolder = Folder::get($oDocument->getFolderID());
            $oSubscriptionEvent->MoveDocument($oDocument, $oDestinationFolder, $oSrcFolder, 'CopiedDocument');
        }

        return $oNewDocument;
    }

    function rename($oDocument, $sNewFilename, $oUser) {
        $oStorage =& KTStorageManagerUtil::getSingleton();

        $oKTConfig = KTConfig::getSingleton();
        $updateVersion = $oKTConfig->get('tweaks/incrementVersionOnRename', true);

        $iPreviousMetadataVersion = $oDocument->getMetadataVersionId();
        $oOldContentVersion = $oDocument->_oDocumentContentVersion;

        if($updateVersion) // We only need to start a new content version if the version is in fact changing.
        {
        	$bSuccess = $oDocument->startNewContentVersion($oUser);

        	if (PEAR::isError($bSuccess)) {
        		return $bSuccess;
        	}

        	KTDocumentUtil::copyMetadata($oDocument, $iPreviousMetadataVersion);
        }

        $res = $oStorage->renameDocument($oDocument, $oOldContentVersion, $sNewFilename);

        if (!$res) {
            return PEAR::raiseError(_kt('An error occurred while storing the new file'));
        }



        $oDocument->setLastModifiedDate(getCurrentDateTime());
        $oDocument->setModifiedUserId($oUser->getId());

        if($updateVersion) { // Update version number
        	$oDocument->setMinorVersionNumber($oDocument->getMinorVersionNumber()+1);
        }

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

        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('renameDocument', 'postValidate');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                'document' => $oDocument
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
            if (PEAR::isError($ret)) {
                return $ret;
            }
        }

        // fire subscription alerts for the checked in document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        $oSubscriptionEvent->ModifyDocument($oDocument, $oFolder);

        return true;
    }

     /*
      * Document Move
      * Author      :   Jarrett Jordaan
      * Modified    :   28/04/09
      *
      * @params     :   KTDocumentUtil $oDocument
      *                 KTFolderUtil $oToFolder
      *                 KTUser $oUser
      *                 string $sReason
      *                 boolean $bulk_action
      */
    function move($oDocument, $oToFolder, $oUser = null, $sReason = null, $bulk_action = false) {
    	//make sure we move the symlink, and the document it's linking to
		if($oDocument->isSymbolicLink()){
    		$oDocument->switchToRealCore();
    	}else{
    		$oDocument->switchToLinkedCore();
    	}
        $oFolder = $oToFolder; // alias.

        $oOriginalFolder = Folder::get($oDocument->getFolderId());
        $iOriginalFolderPermissionObjectId = $oOriginalFolder->getPermissionObjectId();
        $iDocumentPermissionObjectId = $oDocument->getPermissionObjectId();

        if ($iDocumentPermissionObjectId === $iOriginalFolderPermissionObjectId) {
            $oDocument->setPermissionObjectId($oFolder->getPermissionObjectId());
        }

        //put the document in the new folder
        $oDocument->setFolderID($oFolder->getId());
        $sName = $oDocument->getName();
        $sFilename = $oDocument->getFileName();

        $oDocument->setFileName(KTDocumentUtil::getUniqueFilename($oToFolder, $sFilename));
        $oDocument->setName(KTDocumentUtil::getUniqueDocumentName($oToFolder, $sName));

        $res = $oDocument->update();
        if (PEAR::isError($res)) {
            return $res;
        }

        //move the document on the file system(not if it's a symlink)
        if(!$oDocument->isSymbolicLink()){
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
            $ret = $oTrigger->postValidate(true);
            if (PEAR::isError($ret)) {
                return $ret;
            }
        }

        if(!$bulk_action) {
            // fire subscription alerts for the moved document
            $oSubscriptionEvent = new SubscriptionEvent();
            $oSubscriptionEvent->MoveDocument($oDocument, $oFolder, $oOriginalFolder);
        }

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
            return PEAR::raiseError(_kt('Invalid document content version object.'));
        }

        // Check that the document content is not the same as the current content version
        $sDocStoragePath = $oDocument->getStoragePath();
        $sVersionStoragePath = $oContentVersion->getStoragePath();

        if($sDocStoragePath == $sVersionStoragePath){
            return PEAR::raiseError(_kt("Can't delete version: content is the same as the current document content."));
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
    
    public static function getDocumentContent($oDocument)
    {
        global $default;

        //get the path to the document on the server
        //$docRoot = $default->documentRoot;
        $oConfig =& KTConfig::getSingleton();
        $docRoot  = $oConfig->get('urls/documentRoot');

        $path = $docRoot .'/'. $oDocument->getStoragePath();

        // Ensure the file exists
        if (file_exists($path))
        {
            // Get the mime type - this is not relevant at the moment...
            $mimeId = $oDocument->getMimeTypeID();
            $mimetype = KTMime::getMimeTypeName($mimeId);

            if ($bIsCheckout && $default->fakeMimetype) {
                // note this does not work for "image" types in some browsers
                $mimetype = 'application/x-download';
            }

            $sFileName = $oDocument->getFileName( );
            $iFileSize = $oDocument->getFileSize();
        } else {
            return null;
        }

        $content = file_get_contents($path);
        
        return $content;
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
