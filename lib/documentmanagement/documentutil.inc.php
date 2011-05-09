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
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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

    public static function checkin($document, $tmpFilename, $checkInComment, $user, $options = false, $bulkAction = false)
    {
        $storageManager = KTStorageManagerUtil::getSingleton();

        $fileSize = $storageManager->fileSize($tmpFilename);
        $previousMetadataVersion = $document->getMetadataVersionId();

        $success = $document->startNewContentVersion($user);
        if (PEAR::isError($success)) {
            return $success;
        }

        KTDocumentUtil::copyMetadata($document, $previousMetadataVersion);

        if (is_array($options)) {
            $newFilename = KTUtil::arrayGet($options, 'newfilename', '');
            if (!empty($newFilename)) {
                global $default;
                $document->setFileName($newFilename);
                $default->log->info('renamed document ' . $document->getId() . ' to ' . $newFilename);

                // detection of mime types needs to be refactored. this stuff is damn messy!
                // If the filename has changed then update the mime type
                $mimeTypeId = KTMime::getMimeTypeID('', $newFilename);
                $document->setMimeTypeId($mimeTypeId);
            }
        }

        $options['temp_file'] = $tmpFilename;
        $res = KTDocumentUtil::storeContents($document, '', $options);
        if (PEAR::isError($res)) {
            return $res;
        }

        $document->setLastModifiedDate(getCurrentDateTime());
        $document->setModifiedUserId($user->getId());
        $document->setIsCheckedOut(false);
        $document->setCheckedOutUserID(-1);

        if ($options['major_update']) {
            $document->setMajorVersionNumber($document->getMajorVersionNumber() + 1);
            $document->setMinorVersionNumber('0');
        }
        else {
            $document->setMinorVersionNumber($document->getMinorVersionNumber() + 1);
        }

        $document->setFileSize($fileSize);

        $success = $document->update();
        if ($success !== true) {
            if (PEAR::isError($success)) {
                return $success;
            }
            return PEAR::raiseError(_kt('An error occurred while storing this document in the database'));
        }

        // create the document transaction record
        $documentTransaction = new DocumentTransaction($document, $checkInComment, 'ktcore.transactions.check_in');
        $documentTransaction->create();

        $KTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $triggers = $KTTriggerRegistry->getTriggers('content', 'scan');
        foreach ($triggers as $trigger) {
            $triggerName = $trigger[0];
            $trigger = new $triggerName;
            $trigger->setDocument($document);
            $ret = $trigger->scan();
            if (PEAR::isError($ret)) {
                $document->delete();
                return $ret;
            }
        }

        $KTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $triggers = $KTTriggerRegistry->getTriggers('checkin', 'postValidate');

        foreach ($triggers as $trigger) {
            $triggerName = $trigger[0];
            $trigger = new $triggerName;
            $info = array(
                        'document' => $document,
                        'aOptions' => $originalOptions,
            );
            $trigger->setInfo($info);
            $ret = $trigger->postValidate();
        }

        Indexer::index($document);
        if (!$bulkAction) {
            // fire subscription alerts for the checked in document
            $subscriptionEvent = new SubscriptionEvent();
            $folder = Folder::get($document->getFolderID());
            $subscriptionEvent->CheckinDocument($document, $folder);
        }

        return true;
    }

    public static function checkout($document, $checkoutComment, $user, $bulkAction = false)
    {
        //automatically check out the linked document if this is a shortcut
        if ($document->isSymbolicLink()) {
            $document->switchToLinkedCore();
        }

        if ($document->getIsCheckedOut()) {
            return PEAR::raiseError(_kt('Already checked out.'));
        }

        if ($document->getImmutable()) {
            return PEAR::raiseError(_kt('Document cannot be checked out as it is immutable'));
        }

        // Check if the action is restricted by workflow on the document
        if (!KTWorkflowUtil::actionEnabledForDocument($document, 'ktcore.actions.document.checkout')) {
            return PEAR::raiseError(_kt('Checkout is restricted by the workflow state.'));
        }

        // FIXME at the moment errors this _does not_ rollback.

        $document->setIsCheckedOut(true);
        $document->setCheckedOutUserID($user->getId());
        if (!$document->update()) {
            return PEAR::raiseError(_kt('There was a problem checking out the document.'));
        }

        $KTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $triggers = $KTTriggerRegistry->getTriggers('checkout', 'postValidate');
        foreach ($triggers as $trigger) {
            $triggerName = $trigger[0];
            $trigger = new $triggerName;
            $info = array('document' => $document);
            $trigger->setInfo($info);
            $ret = $trigger->postValidate(true);
            if (PEAR::isError($ret)) {
                return $ret;
            }
        }

        $documentTransaction = new DocumentTransaction($document, $checkoutComment, 'ktcore.transactions.check_out');
        $documentTransaction->create();

        if (!$bulkAction) {
            // fire subscription alerts for the downloaded document
            $subscriptionEvent = new SubscriptionEvent();
            $folder = Folder::get($document->getFolderID());
            $subscriptionEvent->CheckOutDocument($document, $folder);
        }

        return true;
    }

    public static function archive($document, $reason, $bulkAction = false)
    {
        if ($document->isSymbolicLink()) {
            return PEAR::raiseError(_kt("It is not possible to archive a shortcut. Please archive the target document."));
        }

        // Ensure the action is not blocked
        if (!KTWorkflowUtil::actionEnabledForDocument($document, 'ktcore.actions.document.archive')) {
            return PEAR::raiseError(_kt('Document cannot be archived as it is restricted by the workflow.'));
        }

        $document->setStatusID(ARCHIVED);
        $res = $document->update();

        if (PEAR::isError($res) || ($res === false)) {
            return PEAR::raiseError(_kt('There was a database error while trying to archive this file'));
        }

        //delete all shortcuts linking to this document
        $symlinks = $document->getSymbolicLinks();
        foreach($symlinks as $symlink) {
            $shortcutDocument = Document::get($symlink['id']);
            $ownerUser = User::get($shortcutDocument->getOwnerID());

            KTDocumentUtil::deleteSymbolicLink($symlink['id']);

            //send an email to the owner of the shortcut
            if ($ownerUser->getEmail() != null && $ownerUser->getEmailNotification() == true) {
                $data = array(
                            'user_name' => $this->oUser->getName(),
                            'url' => KTUtil::ktLink(KTBrowseUtil::getUrlForDocument($shortcutDocument)),
                            'title' => $shortcutDocument->getName()
                        );
                $emailTemplate = new EmailTemplate('kt3/notifications/notification.SymbolicLinkArchived', $data);
                $email = new EmailAlert($ownerUser->getEmail(), _kt("KnowledgeTree Notification"), $emailTemplate->getBody());
                $email->send();
            }
        }

        $documentTransaction = & new DocumentTransaction($document, sprintf(_kt('Document archived: %s'), $reason), 'ktcore.transactions.update');
        $documentTransaction->create();

        $KTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $triggers = $KTTriggerRegistry->getTriggers('archive', 'postValidate');
        foreach ($triggers as $trigger) {
            $triggerName = $trigger[0];
            $trigger = new $triggerName;
            $info = array('document' => $document);
            $trigger->setInfo($info);
            $ret = $trigger->postValidate(true);
            if (PEAR::isError($ret)) {
                $document->delete();
                return $ret;
            }
        }

        if (!$bulkAction) {
            // fire subscription alerts for the archived document
            $subscriptionEvent = new SubscriptionEvent();
            $folder = Folder::get($document->getFolderID());
            $subscriptionEvent->ArchivedDocument($document, $folder);
        }

        return true;
    }

    public static function &_add($folder, $filename, $user, $options)
    {
        global $default;

        //$oContents = KTUtil::arrayGet($options, 'contents');
        $metadata = KTUtil::arrayGet($options, 'metadata', null, false);
        $documentType = KTUtil::arrayGet($options, 'documenttype');
        $description = KTUtil::arrayGet($options, 'description', '');

        if (empty($description)) {
            // If no document name is provided use the filename minus the extension
            $fileInfo = pathinfo($filename);
            $description = (isset($fileInfo['filename']) && !empty($fileInfo['filename'])) ? $fileInfo['filename'] : $filename;
        }

        $uploadChannel =& KTUploadChannel::getSingleton();

        $documentTypeId = $documentType ? KTUtil::getId($documentType) : 1;

        $uploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Creating database entry')));
        $document =& Document::createFromArray(array(
            'name' => $description,
            'description' => $description,
            'filename' => $filename,
            'folderid' => $folder->getID(),
            'creatorid' => $user->getID(),
            'documenttypeid' => $documentTypeId
        ));

        $uploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Storing contents')));
        $res = KTDocumentUtil::storeContents($document, '', $options);
        if (PEAR::isError($res)) {
            if (!PEAR::isError($document)) {
                $document->delete();
            }
            return $res;
        }

        if (is_null($metadata)) {
            $res = KTDocumentUtil::setIncomplete($document, 'metadata');
            if (PEAR::isError($res)) {
                $document->delete();
                return $res;
            }
        }
        else {
            $uploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Saving metadata')));
            $res = KTDocumentUtil::saveMetadata($document, $metadata, $options);
            if (PEAR::isError($res)) {
                $document->delete();
                return $res;
            }
        }

        // setIncomplete and storeContents may change the document's status or
        // storage_path, so now is the time to update
        $res = $document->update();

        if (PEAR::isError($res) || ($res == false)) {
            return PEAR::raiseError(_kt('Unable to finalise the document status.'));
        }

        return $document;
    }

    /**
     * Create a symbolic link in the target folder
     *
     * @param Document $sourceDocument the document to create a link to
     * @param Folder $targetFolder the folder to place the link in
     * @param User $user current user
     */
    public static function createSymbolicLink($sourceDocument, $targetFolder, $user = null) // added/
    {
        //validate input
        if (is_numeric($sourceDocument)) {
            $sourceDocument = Document::get($sourceDocument);
        }

        if (!($sourceDocument instanceof Document)) {
            return PEAR::raiseError(_kt('Source document not specified'));
        }

        if (is_numeric($targetFolder)) {
            $targetFolder = Folder::get($targetFolder);
        }

        if (!($targetFolder instanceof Folder)) {
            return PEAR::raiseError(_kt('Target folder not specified'));
        }

        if (is_null($user)) {
            $user = $_SESSION['userID'];
        }

        if (is_numeric($user)) {
            $user = User::get($user);
        }

        //check for permissions
        $writePermission =& KTPermission::getByName("ktcore.permissions.write");
        $readPermission =& KTPermission::getByName("ktcore.permissions.read");
        if (KTBrowseUtil::inAdminMode($user, $targetFolder)) {
            if (!KTPermissionUtil::userHasPermissionOnItem($user, $writePermission, $targetFolder)) {
                return PEAR::raiseError(_kt('You\'re not authorized to create shortcuts'));
            }
        }

        if (!KTBrowseUtil::inAdminMode($user, $sourceDocument->getParentID())) {
            if (!KTPermissionUtil::userHasPermissionOnItem($user, $readPermission, $sourceDocument)) {
                return PEAR::raiseError(_kt('You\'re not authorized to create a shortcut to this document'));
            }
        }

        //check if the shortcut doesn't already exists in the target folder
        $symlinks = $sourceDocument->getSymbolicLinks();
        foreach ($symlinks as $symlink) {
            $symlink = Document::get($symlink['id']);
            $symlink->switchToRealCore();
            if ($symlink->getFolderID() == $targetFolder->getID()) {
                return PEAR::raiseError(_kt('There already is a shortcut to this document in the target folder.'));
            }
        }

        //create the actual shortcut
        $oCore = KTDocumentCore::createFromArray(array(
            'iCreatorId' => $user->getId(),
            'iFolderId' => $targetFolder->getId(),
            'iLinkedDocumentId' => $sourceDocument->getId(),
            'sFullPath' =>  $targetFolder->getFullPath() . '/' . $sourceDocument->getName(),
            'iPermissionObjectId' => $targetFolder->getPermissionObjectID(),
            'iPermissionLookupId' => $targetFolder->getPermissionLookupID(),
            'iStatusId' => 1,
            'iMetadataVersionId' => $sourceDocument->getMetadataVersionId(),
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
    public static function deleteSymbolicLink($document, $user = null) // added/
    {
        //validate input
        if (is_numeric($document)) {
            $document = Document::get($document);
        }

        if (!($document instanceof Document)) {
            return PEAR::raiseError(_kt('Document not specified'));
        }

        if (!$document->isSymbolicLink()) {
            return PEAR::raiseError(_kt('Document must be a symbolic link entity'));
        }

        if (is_null($user)) {
            $user = $_SESSION['userID'];
        }

        if (is_numeric($user)) {
            $user = User::get($user);
        }

        //check permissions
        $deletePermission = KTPermission::getByName('ktcore.permissions.delete');
        if (!KTBrowseUtil::inAdminMode($user, $document->getParentID())) {
            if (!KTPermissionUtil::userHasPermissionOnItem($user, $deletePermission, $document)) {
                return PEAR::raiseError(_kt('You\'re not authorized to delete this shortcut'));
            }
        }

        // we only need to delete the document entry for the link
        $sql = "DELETE FROM documents WHERE id=?";
        DBUtil::runQuery(array($sql, array($document->getId())));
    }

    // Overwrite the document
    public static function overwrite($document, $filename, $tempFileName, $user, $options)
    {
        //$document, $filename, $checkInComment, $user, $options = false
        $storageManager = KTStorageManagerUtil::getSingleton();
        $fileSize = $storageManager->fileSize($tempFileName);

        // Check that document is not checked out
        if ($document->getIsCheckedOut()) {
            return PEAR::raiseError(_kt('Document is checkout and cannot be overwritten'));
        }

        if (!$storageManager->upload($document, $tempFileName)) {
            return PEAR::raiseError(_kt('An error occurred while storing the new file'));
        }

        $document->setLastModifiedDate(getCurrentDateTime());
        $document->setModifiedUserId($user->getId());

        $document->setFileSize($fileSize);

        $originalFilename = $document->getFileName();

        // change file name
        if ($originalFilename != $filename) {
            if (strlen($filename)) {
                global $default;
                $document->setFileName($filename);
                // rename file in storage driver
                $res = $storageManager->renameDocument($document, $document->_oDocumentContentVersion, $filename);
                if (!$res) {
                    return PEAR::raiseError(_kt('An error occurred while storing the new file'));
                }
                $default->log->info('renamed document ' . $document->getId() . ' to ' . $filename);
            }
            $document->setMinorVersionNumber($document->getMinorVersionNumber()+1);
        }

        $mimeType = KTMime::getMimeTypeFromFile($filename);
        $mimeTypeId = KTMime::getMimeTypeID($mimeType, $document->getFileName());
        $document->setMimeTypeId($mimeTypeId);

        $success = $document->update();
        if ($success !== true) {
            if (PEAR::isError($success)) {
                return $success;
            }
            return PEAR::raiseError(_kt('An error occurred while storing this document in the database'));
        }

        return true;
    }

    public static function validateMetadata(&$document, $metadata)
    {
        $fieldsets =& KTFieldset::getGenericFieldsets();
        $fieldsets =& kt_array_merge($fieldsets, KTFieldset::getForDocumentType($document->getDocumentTypeId()));
        $simpleMetadata = array();
        foreach ($metadata as $singleMetadatum) {
            list($field, $value) = $singleMetadatum;
            if (is_null($field)) {
                continue;
            }
            $simpleMetadata[$field->getId()] = $value;
        }

        $failed = array();
        foreach ($fieldsets as $fieldset) {
            $fields =& $fieldset->getFields();
            $fieldValues = array();
            $isRealConditional = ($fieldset->getIsConditional() && KTMetadataUtil::validateCompleteness($fieldset));
            foreach ($fields as $field) {
                $values = KTUtil::arrayGet($simpleMetadata, $field->getId());
                if ($field->getIsMandatory() && !$isRealConditional) {
                    if (empty($values)) {
                        // XXX: What I'd do for a setdefault...
                        $failed['field'][$field->getId()] = 1;
                    }
                }

                if (!empty($values)) {
                    $fieldValues[$field->getId()] = $values;
                }
            }

            if ($isRealConditional) {
                $res = KTMetadataUtil::getNext($fieldset, $fieldValues);
                if ($res) {
                    foreach ($res as $metadatSet) {
                        if ($metadatSet['field']->getIsMandatory()) {
                            $failed['fieldset'][$fieldset->getId()] = 1;
                        }
                    }
                }
            }
        }

        if (!empty($failed)) {
            return new KTMetadataValidationError($failed);
        }

        return $metadata;
    }

    /*
    * Function to sanitize the date input from any textual date representation to a valid KT date format
    * - Will check for any string supported by strtotime which can be any US English date format.
    * - Further corrects any quote descrepancies and checks the textual description again.
    * - If still no valid date then takes the integers and separators to produce a best guess.
    */
    public static function sanitizeDate($date)
    {
        // if the date is empty - don't sanitise otherwise it fills in today's date
        if ($date == '') {
            return $date;
        }

        //Checking for Normal Strings, e.g. 13 August 2009 etc. All formats accepted by strtotime()
        $datetime = date_create($date);
        $resDate = date_format($datetime, 'Y-m-d');

        if (!trim($resDate) == '') {
            return $resDate;
        }
        else {
            //If null then removing quotes e.g. 14'th doesn't yield a valid date but 14th does
            $date = str_replace("'", '', $date);
            $date = str_replace('"', '', $date);

            $datetime = date_create($date);
            $resDate = date_format($datetime, 'Y-m-d');

            if (!trim($resDate) == '') {
                return $resDate;
            }
            else {
                //If null then trying with numeric data
                //Stripping non-numerics
                $date = preg_replace('/[^0-9]/', '-', $date);
                $token = strpos($date, '--');

                while ($token != 0) {
                    $date = str_replace('--', '-', $date);
                    $token = strpos($date, '--');
                }

                $datetime = date_create($date);
                $resDate = date_format($datetime, 'Y-m-d');

                return $resDate;
            }
        }
    }

    // Forcefully sanitize metadata, specifically date values, to account for client tools that submit unvalidated date input
    // Will produce a best effort match to a valid date format.
    public static function sanitizeMetadata($document, $metadata)
    {
        $fieldsets =& KTFieldset::getGenericFieldsets();
        $fieldsets =& kt_array_merge($fieldsets, KTFieldset::getForDocumentType($document->getDocumentTypeId()));
        $simpleMetadata = array();
        foreach ($metadata as $singleMetadatum) {
            list($field, $value) = $singleMetadatum;
            if (is_null($field)) {
                continue;
            }
            $simpleMetadata[$field->getId()] = $value;
        }

        $metadataPack = array();
        foreach ($fieldsets as $fieldset) {
            $fields =& $fieldset->getFields();
            $fieldValues = array();
            foreach ($fields as $field) {
                $id = $field->getId();
                $values = KTUtil::arrayGet($simpleMetadata, $id);
                if (!empty($values)) {
                    $fieldValues[$field->getId()] = $values;
                }

                //Sanitizing Date Values
                if ($field->getDataType() == 'DATE') {
                    $values = KTDocumentUtil::sanitizeDate($values);
                }

                if (!is_null($values)) {
                    $metadataPack[] = array($field, $values);
                }

            }
        }

        return $metadataPack;
    }

    public static function saveMetadata(&$document, $metadata, $options = null)
    {
        $table = 'document_fields_link';

        //Sanitizing Date Fields
        if (!empty($metadata)) {
            $metadata = KTDocumentUtil::sanitizeMetadata($document, $metadata);
        }

        $noValidate = KTUtil::arrayGet($options, 'novalidate', false);
        if ($noValidate !== true) {
            $res = KTDocumentUtil::validateMetadata($document, $metadata);
            if (PEAR::isError($res)) {
                return $res;
            }
            $metadata = empty($res) ? array() : $res;
        }

        $metadataVersionId = $document->getMetadataVersionId();
        $res = DBUtil::runQuery(array("DELETE FROM $table WHERE metadata_version_id = ?", array($metadataVersionId)));
        if (PEAR::isError($res)) {
            return $res;
        }

        // XXX: Metadata refactor
        $metadata = (is_array($metadata)) ? $metadata : array();
        foreach ($metadata as $info) {
            list($oMetadata, $value) = $info;
            if (is_null($oMetadata)) {
                continue;
            }

            $res = DBUtil::autoInsert($table, array(
                    'metadata_version_id' => $metadataVersionId,
                    'document_field_id' => $oMetadata->getID(),
                    'value' => $value,
            ));
            if (PEAR::isError($res)) {
                return $res;
            }
        }
        KTDocumentUtil::setComplete($document, 'metadata');
        DocumentFieldLink::clearAllCaches();
        return true;
    }

    public static function copyMetadata($document, $previousMetadataVersionId)
    {
        $newMetadataVersion = $document->getMetadataVersionId();
        $table = KTUtil::getTableName('document_fields_link');
        $fields = DBUtil::getResultArray(array("SELECT * FROM $table WHERE metadata_version_id = ?", array($previousMetadataVersionId)));
        foreach ($fields as $row) {
            unset($row['id']);
            $row['metadata_version_id'] = $newMetadataVersion;
            DBUtil::autoInsert($table, $row);
        }

    }

    public static function setIncomplete(&$document, $reason)
    {
        $document->setStatusID(STATUS_INCOMPLETE);
        $table = 'document_incomplete';
        $id = $document->getId();
        $incomplete = DBUtil::getOneResult(array("SELECT * FROM $table WHERE id = ?", array($id)));
        if (PEAR::isError($incomplete)) {
            return $incomplete;
        }

        if (is_null($incomplete)) {
            $incomplete = array('id' => $id);
        }

        $incomplete[$reason] = true;

        $res = DBUtil::autoDelete($table, $id);
        if (PEAR::isError($res)) {
            return $res;
        }

        $res = DBUtil::autoInsert($table, $incomplete);
        if (PEAR::isError($res)) {
            return $res;
        }

        return true;
    }

    public static function setComplete(&$document, $reason)
    {
        $table = 'document_incomplete';
        $id = $document->getID();
        $incomplete = DBUtil::getOneResult(array("SELECT * FROM $table WHERE id = ?", array($id)));
        if (PEAR::isError($incomplete)) {
            return $incomplete;
        }

        if (is_null($incomplete)) {
            $document->setStatusID(LIVE);
            return true;
        }

        $incomplete[$reason] = false;

        $isIncomplete = false;

        foreach ($incomplete as $k => $v) {
            if ($k === 'id') {
                continue;
            }

            if ($v) {
                $isIncomplete = true;
            }
        }

        if ($isIncomplete === false) {
            DBUtil::autoDelete($table, $id);
            $document->setStatusID(LIVE);
            return true;
        }

        $res = DBUtil::autoDelete($table, $id);
        if (PEAR::isError($res)) {
            return $res;
        }

        $res = DBUtil::autoInsert($table, $incomplete);
        if (PEAR::isError($res)) {
            return $res;
        }
    }

    /*
     * Document Add
     * Author      :   KnowledgeTree Team
     * Modified    :   28/04/09
     *
     * @params     :   KTFolderUtil $folder
     *                 string $filename
     *                 KTUser $user
     *                 array $options
     *                 boolean $bulkAction
     */
    public static function &add($folder, $filename, $user, $options, $bulkAction = false)
    {
        $GLOBALS['_IN_ADD'] = true;
        $ret = KTDocumentUtil::_in_add($folder, $filename, $user, $options, $bulkAction);
        unset($GLOBALS['_IN_ADD']);
        return $ret;
    }

    public static function getUniqueFilename($folder, $filename)
    {
        // this is just a quick refactoring. We should look at a more optimal way of doing this as there are
        // quite a lot of queries.
        $folderId = $folder->getId();
        while (KTDocumentUtil::fileExists($folder, $filename)) {
            $document = Document::getByFilenameAndFolder($filename, $folderId);
            $filename = KTDocumentUtil::generateNewDocumentFilename($document->getFileName());
        }

        return $filename;
    }

    public static function getUniqueDocumentName($folder, $filename)
    {
        // this is just a quick refactoring. We should look at a more optimal way of doing this as there are
        // quite a lot of queries.
        $folderId = $folder->getId();
        while(KTDocumentUtil::nameExists($folder, $filename)) {
            $document = Document::getByNameAndFolder($filename, $folderId);
            $filename = KTDocumentUtil::generateNewDocumentName($document->getName());
        }

        return $filename;
    }

    /**
    * Document Add
    *
    * @author KnowledgeTree Team
    * @access public
    * @param KTFolderUtil $folder
    * @param string $filename
    * @param KTUser $user
    * @param array $options
    * @param boolean $bulkAction
    *
    * @return Document $document
    */
    public static function &_in_add($folder, $filename, $user, $options, $bulkAction = false)
    {
        $originalOptions = $options;

        $filename = KTDocumentUtil::getUniqueFilename($folder, $filename);
        $name = KTUtil::arrayGet($options, 'description', $filename);
        $name = KTDocumentUtil::getUniqueDocumentName($folder, $name);
        $options['description'] = $name;

        $uploadChannel =& KTUploadChannel::getSingleton();
        $uploadChannel->sendMessage(new KTUploadNewFile($filename));
        DBUtil::startTransaction();
        $document =& KTDocumentUtil::_add($folder, $filename, $user, $options);

        $uploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Document created')));
        if (PEAR::isError($document)) {
            return $document;
        }

        $uploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Scanning file')));
        $KTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $triggers = $KTTriggerRegistry->getTriggers('content', 'scan');
        foreach ($triggers as $trigger) {
            $triggerName = $trigger[0];
            $trigger = new $triggerName;
            $trigger->setDocument($document);
            // $uploadChannel->sendMessage(new KTUploadGenericMessage(sprintf(_kt("    (trigger %s)"), $triggerName)));
            $ret = $trigger->scan();
            if (PEAR::isError($ret)) {
                $document->delete();
                return $ret;
            }
        }

        DBUtil::commit();

        $document->clearAllCaches();

        // NEW SEARCH
        Indexer::index($document);

        $uploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Creating transaction')));
        $options = array('user' => $user);

        //create the document transaction record
        $documentTransaction = new DocumentTransaction($document, _kt('Document created'), 'ktcore.transactions.create', $options);
        $res = $documentTransaction->create();
        if (PEAR::isError($res)) {
            $document->delete();
            return $res;
        }

        $uploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Sending subscriptions')));
        // TODO : better way of checking if its a bulk upload
        if (!$bulkAction) {
            // fire subscription alerts for the checked in document
            $subscriptionEvent = new SubscriptionEvent();
            $folder = Folder::get($document->getFolderID());
            $subscriptionEvent->AddDocument($document, $folder);
        }

        $KTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $triggers = $KTTriggerRegistry->getTriggers('add', 'postValidate');

        foreach ($triggers as $trigger) {
            $triggerName = $trigger[0];
            $trigger = new $triggerName;
            $info = array(
                'document' => $document,
                'aOptions' => $originalOptions,
            );
            $trigger->setInfo($info);
            $ret = $trigger->postValidate();

        }

        // update document object with additional fields / data from the triggers
        $document = Document::get($document->iId);

        $uploadChannel->sendMessage(new KTUploadGenericMessage(_kt('Checking permissions...')));

        // Check if there are any dynamic conditions / permissions that need to be updated on the document
        // If there are dynamic conditions then update the permissions on the document
        // The dynamic condition test fails unless the document exists in the DB therefore update permissions after committing the transaction.
        include_once(KT_LIB_DIR.'/permissions/permissiondynamiccondition.inc.php');
        $permissionObjectId = $folder->getPermissionObjectID();
        $dynamicCondition = KTPermissionDynamicCondition::getByPermissionObjectId($permissionObjectId);

        if (!PEAR::isError($dynamicCondition) && !empty($dynamicCondition)) {
            $res = KTPermissionUtil::updatePermissionLookup($document);
        }

        $uploadChannel->sendMessage(new KTUploadGenericMessage(_kt('All done...')));

        return $document;
    }

    public static function incrementNameCollissionNumbering($docFilename, $skipExtension = false)
    {
        $dot = strpos($docFilename, '.');
        if ($skipExtension || $dot === false) {
            if (preg_match("/\(([0-9]+)\)$/", $docFilename, $matches, PREG_OFFSET_CAPTURE)) {

                $count = $matches[1][0];
                $pos = $matches[1][1];

                $newCount = $count + 1;
                $docFilename = substr($docFilename, 0, $pos) . $newCount .  substr($docFilename, $pos + strlen($count));
            }
            else {
                $docFilename = $docFilename . '(1)';
            }
        }
        else {
            if (preg_match("/\(([0-9]+)\)(\.[^\.]+)+$/", $docFilename, $matches, PREG_OFFSET_CAPTURE)) {
                $count = $matches[1][0];
                $pos = $matches[1][1];

                $newCount = $count + 1;
                $docFilename = substr($docFilename, 0, $pos) . $newCount .  substr($docFilename, $pos + strlen($count));
            }
            else {
                $docFilename = substr($docFilename, 0, $dot) . '(1)' . substr($docFilename, $dot);
            }
        }

        return $docFilename;
    }

    public static function generateNewDocumentFilename($docFilename)
    {
        return self::incrementNameCollissionNumbering($docFilename, false);
    }

    public static function generateNewDocumentName($sDocName)
    {
        return self::incrementNameCollissionNumbering($sDocName, true);

    }

    public static function fileExists($folder, $filename)
    {
        return Document::fileExists($filename, $folder->getID());
    }

    public static function nameExists($folder, $name)
    {
        return Document::nameExists($name, $folder->getID());
    }

    /**
     * Stores contents (filelike) from source into the document storage
     */
    public static function storeContents(&$document, $oContents = null, $options = null)
    {
        $storageManager = KTStorageManagerUtil::getSingleton();
        if (is_null($options)) {
            $options = array();
        }

        if (PEAR::isError($document)) {
            return PEAR::raiseError(sprintf(_kt("Couldn't store contents: %s : "), $document->getMessage()));
        }

        $canMove = KTUtil::arrayGet($options, 'move');
        $KTConfig =& KTConfig::getSingleton();
        $basedir = $KTConfig->get('urls/tmpDirectory');

        $filename = (isset($options['temp_file'])) ? $options['temp_file'] : '';

        if (empty($filename)) {
            return PEAR::raiseError(sprintf(_kt("Couldn't store contents: %s"), _kt('The uploaded file does not exist.')));
        }

        $md5hash = $storageManager->md5File($filename);
        $content = $document->_oDocumentContentVersion;
        $content->setStorageHash($md5hash);
        $content->setHasRendition(0);   // new version so no pdf / thumbnail exists
        $content->update();

        if (empty($options)) {
            $options = array();
        }

        $options['md5hash'] = $md5hash;

        // detection of mime types needs to be refactored. this stuff is damn messy!
        $mimeType = KTMime::getMimeTypeFromFile($filename);
        $mimeTypeId = KTMime::getMimeTypeID($mimeType, $document->getFileName(), $filename);
        $document->setMimeTypeId($mimeTypeId);

        $res = $storageManager->upload($document, $filename, $options);
        if ($res === false) {
            return PEAR::raiseError(sprintf(_kt("Couldn't store contents: %s"), _kt('No reason given')));
        }

        if (PEAR::isError($res)) {
            return PEAR::raiseError(sprintf(_kt("Couldn't store contents: %s"), $res->getMessage()));
        }

        KTDocumentUtil::setComplete($document, 'contents');

        if ($options['cleanup_initial_file'] && $storageManager->file_exists($filename)) {
            $storageManager->unlink($filename);
        }

        return true;
    }

    /*
     * Document Delete
     * Author      :   KnowledgeTree Team
     * Modified    :   28/04/09
     *
     * @params     :   KTDocumentUtil $document
     *                 string $reason
     *                 int $destFolderId
     *                 boolean $bulkAction
     */
    public static function delete($document, $reason, $destFolderId = null, $bulkAction = false)
    {
        global $default;
        $storageManager = KTStorageManagerUtil::getSingleton();

        // use the deleteSymbolicLink function is this is a symlink
        if ($document->isSymbolicLink()) {
            return KTDocumentUtil::deleteSymbolicLink($document);
        }

        $document =& KTUtil::getObject('Document', $document);
        if (is_null($destFolderId)) {
            $destFolderId = $document->getFolderID();
        }

        if (count(trim($reason)) == 0) {
            return PEAR::raiseError(_kt('Deletion requires a reason'));
        }

        if (PEAR::isError($document) || ($document == false)) {
            return PEAR::raiseError(_kt('Invalid document object.'));
        }

        if ($document->getIsCheckedOut() == true) {
            return PEAR::raiseError(sprintf(_kt('The document is checked out and cannot be deleted: %s'), $document->getName()));
        }

        if (!KTWorkflowUtil::actionEnabledForDocument($document, 'ktcore.actions.document.delete')) {
            return PEAR::raiseError(_kt('Document cannot be deleted as it is restricted by the workflow.'));
        }

        if ($document->getStatusID() == DELETED) {
            return true;
        }

        $originalFolder = Folder::get($document->getFolderId());

        DBUtil::startTransaction();

        // flip the status id
        $document->setStatusID(DELETED);

        // $destFolderId is DEPRECATED.
        $document->setFolderID(null);
        $document->setRestoreFolderId($originalFolder->getId());
        $document->setRestoreFolderPath(Folder::generateFolderIDs($originalFolder->getId()));

        $res = $document->update();

        if (PEAR::isError($res) || ($res == false)) {
            DBUtil::rollback();
            return PEAR::raiseError(_kt('There was a problem deleting the document from the database.'));
        }

        // now move the document to the delete folder
        $res = $storageManager->delete($document);
        if (PEAR::isError($res) || ($res == false)) {
            //could not delete the document from the file system
            $default->log->error('Deletion: Filesystem error deleting document ' .
            $document->getFileName() . ' from folder ' .
            Folder::getFolderPath($document->getFolderID()) . ' id=' . $document->getFolderID());

            DBUtil::rollback();

            return PEAR::raiseError(_kt('There was a problem deleting the document from storage.'));
        }

        // get the user object
        $user = User::get($_SESSION['userID']);

        //delete all shortcuts linking to this document
        $symlinks = $document->getSymbolicLinks();
        foreach ($symlinks as $symlink) {
            $shortcutDocument = Document::get($symlink['id']);
            $ownerUser = User::get($shortcutDocument->getOwnerID());

            KTDocumentUtil::deleteSymbolicLink($symlink['id']);

            //send an email to the owner of the shortcut
            if ($ownerUser->getEmail()!=null && $ownerUser->getEmailNotification() == true) {
                $emailTemplate = new EmailTemplate("kt3/notifications/notification.SymbolicLinkDeleted", array('user_name' => $user->getName(),
                'url' => KTUtil::ktLink(KTBrowseUtil::getUrlForDocument($shortcutDocument)),
                'title' => $shortcutDocument->getName()));
                $email = new EmailAlert($ownerUser->getEmail(), _kt("KnowledgeTree Notification"), $emailTemplate->getBody());
                $email->send();
            }
        }

        //$GLOBALS['default']->log->debug('Document transaction folder id '.$document->getFolderID().' or '.$originalFolder->getId());

        $documentTransaction = new DocumentTransaction($document, _kt('Document deleted: ') . $reason, 'ktcore.transactions.delete');
        $documentTransaction->create();

        $document->setFolderID(1);

        DBUtil::commit();

        //now update the document_transactions table to reflect the parent folder id
        $fieldValues = array('parent_id' => $originalFolder->getId());
        // $wtfFieldValues is so named because these vars were $aFV and $aWFV;
        // could not (!) figure out what the W was intended for, hence WTF :).
        $wtfFieldValues = array('document_id' => $document->getId());

        $res = DBUtil::whereUpdate(KTUtil::getTableName('document_transactions'), $fieldValues, $wtfFieldValues);

        // TODO : better way of checking if its a bulk delete
        if (!$bulkAction) {
            // we weren't doing notifications on this one
            $subscriptionEvent = new SubscriptionEvent();
            $subscriptionEvent->RemoveDocument($document, $originalFolder);
        }

        // document is now deleted:  triggers are best-effort.

        $KTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $triggers = $KTTriggerRegistry->getTriggers('delete', 'postValidate');
        foreach ($triggers as $trigger) {
            $triggerName = $trigger[0];
            $trigger = new $triggerName;
            $info = array('document' => $document);
            $trigger->setInfo($info);
            $ret = $trigger->postValidate(true);
            if (PEAR::isError($ret)) {
                // FIXME nbm: review that on-fail => delete is correct ?!
                $document->delete();
                return $ret;
            }
        }


    }

    public static function reindexDocument($document)
    {
        Indexer::index($document);
    }

    public static function canBeCopied($document, &$errorMessage)
    {
        if ($document->getIsCheckedOut()) {
            $errorMessage = PEAR::raiseError(_kt('Document cannot be copied as it is checked out.'));
            return false;
        }

        if (!KTWorkflowUtil::actionEnabledForDocument($document, 'ktcore.actions.document.copy')) {
            $errorMessage = PEAR::raiseError(_kt('Document cannot be copied as it is restricted by the workflow.'));
            return false;
        }

        return true;
    }

    public static function canBeMoved($document, &$errorMessage)
    {
        if ($document->getImmutable()) {
            $errorMessage = PEAR::raiseError(_kt('Document cannot be moved as it is immutable.'));
            return false;
        }

        if ($document->getIsCheckedOut()) {
            $errorMessage = PEAR::raiseError(_kt('Document cannot be moved as it is checked out.'));
            return false;
        }

        if (!KTWorkflowUtil::actionEnabledForDocument($document, 'ktcore.actions.document.move')) {
            $errorMessage = PEAR::raiseError(_kt('Document cannot be moved as it is restricted by the workflow.'));
            return false;
        }

        return true;
    }

    public static function canBeDeleted($document, &$errorMessage)
    {
        if ($document->getImmutable()) {
            $errorMessage = PEAR::raiseError(_kt('Document cannot be deleted as it is immutable.'));
            return false;
        }

        if ($document->getIsCheckedOut()) {
            $errorMessage = PEAR::raiseError(_kt('Document cannot be deleted as it is checked out.'));
            return false;
        }

        if (!KTWorkflowUtil::actionEnabledForDocument($document, 'ktcore.actions.document.delete')) {
            $errorMessage = PEAR::raiseError(_kt('Document cannot be deleted as it is restricted by the workflow.'));
            return false;
        }

        return true;
    }

    public static function canBeArchived($document, &$errorMessage)
    {
        if ($document->getIsCheckedOut()) {
            $errorMessage = PEAR::raiseError(_kt('Document cannot be archived as it is checked out.'));
            return false;
        }

        if (!KTWorkflowUtil::actionEnabledForDocument($document, 'ktcore.actions.document.archive')) {
            $errorMessage = PEAR::raiseError(_kt('Document cannot be archived as it is restricted by the workflow.'));
            return false;
        }

        return true;
    }

    public static function copy($document, $destinationFolder, $reason = null, $destinationDocName = null, $bulkAction = false)
    {
        $storageManager = KTStorageManagerUtil::getSingleton();
        // 1. generate a new triad of content, metadata and core objects.
        // 2. update the storage path.
        //print '--------------------------------- BEFORE';
        //print_r($document);

        // TODO: this is not optimal. we have get() functions that will do SELECT when we already have the data in arrays

        // get the core record to be copied
        $documentTable = KTUtil::getTableName('documents');
        $query = 'SELECT * FROM ' . $documentTable . ' WHERE id = ?';
        $params = array($document->getId());
        $coreRow = DBUtil::getOneResult(array($query, $params));
        // we unset the id as a new one will be created on insert
        unset($coreRow['id']);
        // we unset immutable since a new document will be created on insert
        unset($coreRow['immutable']);
        // get a copy of the latest metadata version for the copied document
        $oldMetadataId = $coreRow['metadata_version_id'];
        $metadataTable = KTUtil::getTableName('document_metadata_version');
        $query = 'SELECT * FROM ' . $metadataTable . ' WHERE id = ?';
        $params = array($oldMetadataId);
        $metadataRow = DBUtil::getOneResult(array($query, $params));
        // we unset the id as a new one will be created on insert
        unset($metadataRow['id']);

        // set the name for the document, possibly using name collission
        if (empty($destinationDocName)) {
            $metadataRow['name'] = KTDocumentUtil::getUniqueDocumentName($destinationFolder, $metadataRow['name']);
        }
        else {
            $metadataRow['name'] = $destinationDocName;
        }

        // get a copy of the latest content version for the copied document
        $oldContentId = $metadataRow['content_version_id'];
        $contentTable = KTUtil::getTableName('document_content_version');
        $query = 'SELECT * FROM ' . $contentTable . ' WHERE id = ?';
        $params = array($oldContentId);
        $contentRow = DBUtil::getOneResult(array($query, $params));
        // we unset the id as a new one will be created on insert
        unset($contentRow['id']);

        // set the filename for the document, possibly using name collission
        if (empty($destinationDocName)) {
            $contentRow['filename'] = KTDocumentUtil::getUniqueFilename($destinationFolder, $contentRow['filename']);
        }
        else {
            $contentRow['filename'] = $destinationDocName;
        }

        // create the new document record
        $coreRow['modified'] = date('Y-m-d H:i:s');
        $coreRow['folder_id'] = $destinationFolder->getId(); // new location.
        $id = DBUtil::autoInsert($documentTable, $coreRow);
        if (PEAR::isError($id)) {
            return $id;
        }

        $newDocumentId = $id;

        // create the new metadata record
        $metadataRow['document_id'] = $newDocumentId;
        $metadataRow['description'] = $metadataRow['name'];
        $id = DBUtil::autoInsert($metadataTable, $metadataRow);
        if (PEAR::isError($id)) {
            return $id;
        }

        $newMetadataId = $id;

        // the document metadata version is still pointing to the original
        $coreUpdate = array();
        $coreUpdate['metadata_version_id'] = $newMetadataId;
        $coreUpdate['metadata_version'] = 0;

        // create the new content version
        $contentRow['document_id'] = $newDocumentId;
        $id = DBUtil::autoInsert($contentTable, $contentRow);
        if (PEAR::isError($id)) {
            return $id;
        }

        $newContentId = $id;

        // the metadata content version is still pointing to the original
        $metadataUpdate = array();
        $metadataUpdate['content_version_id'] = $newContentId;
        $metadataUpdate['metadata_version'] = 0;

        // apply the updates to the document and metadata records
        $res = DBUtil::autoUpdate($documentTable, $coreUpdate, $newDocumentId);
        if (PEAR::isError($res)) {
            return $res;
        }

        $res = DBUtil::autoUpdate($metadataTable, $metadataUpdate, $newMetadataId);
        if (PEAR::isError($res)) {
            return $res;
        }

        // now, we have a semi-sane document object. get it.
        $newDocument = Document::get($newDocumentId);

        // copy the metadata from old to new.
        $res = KTDocumentUtil::copyMetadata($newDocument, $oldMetadataId);
        if (PEAR::isError($res)) {
            return $res;
        }

        // Ensure the copied document is not checked out
        $newDocument->setIsCheckedOut(false);
        $newDocument->setCheckedOutUserID(-1);

        // finally, copy the actual file.
        $res = $storageManager->copyDocument($document, $newDocument);

        $originalFolder = Folder::get($document->getFolderId());
        $originalFolderPermissionObjectId = $originalFolder->getPermissionObjectId();
        $documentPermissionObjectId = $document->getPermissionObjectId();

        if ($documentPermissionObjectId === $originalFolderPermissionObjectId) {
            $newDocument->setPermissionObjectId($destinationFolder->getPermissionObjectId());
        }

        $res = $newDocument->update();
        if (PEAR::isError($res)) {
            return $res;
        }

        KTPermissionUtil::updatePermissionLookup($newDocument);

        if (is_null($reason)) {
            $reason = '';
        }

        $documentTransaction = new DocumentTransaction($document, sprintf(_kt("Copied to folder \"%s\". %s"), $destinationFolder->getName(), $reason), 'ktcore.transactions.copy');
        $documentTransaction->create();

        $srcFolder = Folder::get($document->getFolderID());
        $documentTransaction = new DocumentTransaction($newDocument, sprintf(_kt("Copied from original in folder \"%s\". %s"), $srcFolder->getName(), $reason), 'ktcore.transactions.copy');
        $documentTransaction->create();

        $KTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $triggers = $KTTriggerRegistry->getTriggers('copyDocument', 'postValidate');
        foreach ($triggers as $trigger) {
            $triggerName = $trigger[0];
            $trigger = new $triggerName;
            $info = array(
                'document' => $newDocument,
                'old_folder' => $srcFolder,
                'new_folder' => $destinationFolder,
            );
            $trigger->setInfo($info);
            $ret = $trigger->postValidate();
            if (PEAR::isError($ret)) {
                return $ret;
            }
        }

        // Action creates a whole new document so we need to index & process it
        Indexer::index($newDocument);

        if (!$bulkAction) {
            // fire subscription alerts for the copied document
            $subscriptionEvent = new SubscriptionEvent();
            $folder = Folder::get($document->getFolderID());
            $subscriptionEvent->MoveDocument($document, $destinationFolder, $srcFolder, 'CopiedDocument');
        }

        return $newDocument;
    }

    public static function rename($document, $sNewFilename, $user)
    {
        $storageManager = KTStorageManagerUtil::getSingleton();
        $KTConfig = KTConfig::getSingleton();
        $updateVersion = $KTConfig->get('tweaks/incrementVersionOnRename', true);
        $previousMetadataVersion = $document->getMetadataVersionId();
        $oldContentVersion = $document->_oDocumentContentVersion;

        // We only need to start a new content version if the version is in fact changing.
        if ($updateVersion) {
            $success = $document->startNewContentVersion($user);
            if (PEAR::isError($success)) {
                return $success;
            }

            KTDocumentUtil::copyMetadata($document, $previousMetadataVersion);
        }

        // rename file in storage driver
        $res = $storageManager->renameDocument($document, $oldContentVersion, $sNewFilename);
        if (!$res) {
            return PEAR::raiseError(_kt('An error occurred while storing the new file'));
        }

        $document->setLastModifiedDate(getCurrentDateTime());
        $document->setModifiedUserId($user->getId());

        if ($updateVersion) { // Update version number
            $document->setMinorVersionNumber($document->getMinorVersionNumber()+1);
        }

        $oldFilename = $document->_oDocumentContentVersion->getFilename();
        $document->_oDocumentContentVersion->setFilename($sNewFilename);
        $mimeType = KTMime::getMimeTypeFromFile($sNewFilename);
        $mimeTypeId = KTMime::getMimeTypeID($mimeType, $sNewFilename);
        $document->setMimeTypeId($mimeTypeId);

        $success = $document->update();
        if ($success !== true) {
            if (PEAR::isError($success)) {
                return $success;
            }
            return PEAR::raiseError(_kt('An error occurred while storing this document in the database'));
        }

        // create the document transaction record
        $comment = sprintf(_kt("Document renamed from %s to %s."), $oldFilename, $sNewFilename);
        $documentTransaction = new DocumentTransaction($document, $comment, 'ktcore.transactions.rename');
        $documentTransaction->create();

        $KTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $triggers = $KTTriggerRegistry->getTriggers('renameDocument', 'postValidate');
        foreach ($triggers as $trigger) {
            $triggerName = $trigger[0];
            $trigger = new $triggerName;
            $info = array('document' => $document);
            $trigger->setInfo($info);
            $ret = $trigger->postValidate();
            if (PEAR::isError($ret)) {
                return $ret;
            }
        }

        // fire subscription alerts for the checked in document
        $subscriptionEvent = new SubscriptionEvent();
        $folder = Folder::get($document->getFolderID());
        $subscriptionEvent->ModifyDocument($document, $folder);

        return true;
    }

    /**
     * Document Move
     * Author      :   KnowledgeTree Team
     * Modified    :   28/04/09
     *
     * @params     :   KTDocumentUtil $document
     *                 KTFolderUtil $destFolder
     *                 KTUser $user
     *                 string $reason
     *                 boolean $bulkAction
     */
    public static function move($document, $destFolder, $user = null, $reason = null, $bulkAction = false)
    {
        $storageManager = KTStorageManagerUtil::getSingleton();
        //make sure we move the symlink, and the document it's linking to
        if ($document->isSymbolicLink()) {
            $document->switchToRealCore();
        }
        else {
            $document->switchToLinkedCore();
        }
        $folder = $destFolder; // alias.

        $originalFolder = Folder::get($document->getFolderId());
        $originalFolderPermissionObjectId = $originalFolder->getPermissionObjectId();
        $documentPermissionObjectId = $document->getPermissionObjectId();

        if ($documentPermissionObjectId === $originalFolderPermissionObjectId) {
            $document->setPermissionObjectId($folder->getPermissionObjectId());
        }

        //put the document in the new folder
        $document->setFolderID($folder->getId());
        $name = $document->getName();
        $filename = $document->getFileName();
        $document->setFileName(KTDocumentUtil::getUniqueFilename($destFolder, $filename));
        $document->setName(KTDocumentUtil::getUniqueDocumentName($destFolder, $name));

        $res = $document->update();
        if (PEAR::isError($res)) {
            return $res;
        }

        //move the document on the file system(not if it's a symlink)
        if (!$document->isSymbolicLink()) {
            $res = $storageManager->moveDocument($document, $folder, $originalFolder);
            if (PEAR::isError($res) || ($res === false)) {
                $document->setFolderID($originalFolder->getId());
                $res = $document->update();
                if (PEAR::isError($res)) {
                    return $res;
                }
                return $res; // we failed, bail.
            }
        }

        // Display the folder path in the move message - for the root folder, display the name
        $sourcePath = ($originalFolder->iId == 1) ? $originalFolder->getName() : $originalFolder->getFullPath();
        $targetPath = ($folder->iId == 1) ? $folder->getName() : $folder->getFullPath();

        $moveMessage = sprintf(_kt("Moved from %s to %s. %s"), $sourcePath, $targetPath, $reason);

        // create the document transaction record
        $documentTransaction = new DocumentTransaction($document, $moveMessage, 'ktcore.transactions.move');
        $documentTransaction->create();

        $KTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $triggers = $KTTriggerRegistry->getTriggers('moveDocument', 'postValidate');
        foreach ($triggers as $trigger) {
            $triggerName = $trigger[0];
            $trigger = new $triggerName;
            $info = array(
                'document' => $document,
                'old_folder' => $originalFolder,
                'new_folder' => $folder,
            );
            $trigger->setInfo($info);
            $ret = $trigger->postValidate(true);
            if (PEAR::isError($ret)) {
                return $ret;
            }
        }

        if (!$bulkAction) {
            // fire subscription alerts for the moved document
            $subscriptionEvent = new SubscriptionEvent();
            $subscriptionEvent->MoveDocument($document, $folder, $originalFolder);
        }

        return KTPermissionUtil::updatePermissionLookup($document);
    }

    /**
    * Delete a selected version of the document.
    */
    public static function deleteVersion($document, $versionId, $reason)
    {
        global $default;
        $storageManager = KTStorageManagerUtil::getSingleton();

        $document =& KTUtil::getObject('Document', $document);
        $version =& KTDocumentMetadataVersion::get($versionId);

        if (empty($reason)) {
            return PEAR::raiseError(_kt('Deletion requires a reason'));
        }

        if (PEAR::isError($document) || ($document == false)) {
            return PEAR::raiseError(_kt('Invalid document object.'));
        }

        if (PEAR::isError($version) || ($version == false)) {
            return PEAR::raiseError(_kt('Invalid document version object.'));
        }

        $contentId = $version->getContentVersionId();
        $contentVersion = KTDocumentContentVersion::get($contentId);

        if (PEAR::isError($contentVersion) || ($contentVersion == false)) {
            return PEAR::raiseError(_kt('Invalid document content version object.'));
        }

        // Check that the document content is not the same as the current content version
        $docStoragePath = $document->getStoragePath();
        $versionStoragePath = $contentVersion->getStoragePath();

        if ($docStoragePath == $versionStoragePath) {
            return PEAR::raiseError(_kt("Can't delete version: content is the same as the current document content."));
        }

        DBUtil::startTransaction();

        // now delete the document version
        $res = $storageManager->deleteVersion($version);
        if (PEAR::isError($res) || ($res == false)) {
            //could not delete the document version from the file system
            $default->log->error('Deletion: Filesystem error deleting the metadata version ' .
            $version->getMetadataVersion() . ' of the document ' .
            $document->getFileName() . ' from folder ' .
            Folder::getFolderPath($document->getFolderID()) . ' id=' . $document->getFolderID());

            DBUtil::rollback();

            return PEAR::raiseError(_kt('There was a problem deleting the document from storage.'));
        }

        // change status for the metadata version
        $version->setStatusId(VERSION_DELETED);
        $version->update();

        // set the storage path to empty
        // $contentVersion->setStoragePath('');

        DBUtil::commit();
    }

    public static function getDocumentContent($document)
    {
        global $default;
        $storageManager = KTStorageManagerUtil::getSingleton();
        //get the path to the document on the server
        //$docRoot = $default->documentRoot;
        $config =& KTConfig::getSingleton();
        $docRoot  = $config->get('urls/documentRoot');
        //get the path to the document on the server
        $path = $docRoot . '/' . $document->getStoragePath();

        // Ensure the file exists
        if ($storageManager->file_exists($path)) {
            // Get the mime type - this is not relevant at the moment...
            $mimeId = $document->getMimeTypeID();
            $mimetype = KTMime::getMimeTypeName($mimeId);

            if ($isCheckedOut && $default->fakeMimetype) {
                // note this does not work for "image" types in some browsers
                $mimetype = 'application/x-download';
            }

            $filename = $document->getFileName();
            $fileSize = $document->getFileSize();
        }
        else {
            return null;
        }

        $content = $storageManager->file_get_contents($path);

        return $content;
    }

    public static function getDocumentsByPO($objectId)
    {
        $sql = "SELECT d.id, d.owner_id, d.folder_id, d.parent_folder_ids, d.permission_lookup_id, m.workflow_state_id, d.restore_folder_path
                FROM documents d, document_metadata_version m
                WHERE d.metadata_version_id = m.id AND permission_object_id = {$objectId}";

        $results = DBUtil::getResultArray($sql);
        if (PEAR::isError($results)) {
            return 0;
        }

        return $results;
    }

}

class KTMetadataValidationError extends PEAR_Error {

    function KTMetadataValidationError ($failed)
    {
        $this->aFailed = $failed;
        $message = _kt('Please be sure to enter information for all the Required fields below');
        parent::PEAR_Error($message);
    }

}

class KTUploadChannel {

    var $observers = array();

    function &getSingleton()
    {
        if (!KTUtil::arrayGet($GLOBALS, 'KT_UploadChannel')) {
            $GLOBALS['KT_UploadChannel'] = new KTUploadChannel;
        }

        return $GLOBALS['KT_UploadChannel'];
    }

    function sendMessage(&$msg)
    {
        foreach ($this->observers as $observer) {
            $observer->receiveMessage($msg);
        }
    }

    function addObserver(&$obs)
    {
        array_push($this->observers, $obs);
    }

}

class KTUploadGenericMessage {

    function KTUploadGenericMessage($sMessage)
    {
        $this->sMessage = $sMessage;
    }

    function getString()
    {
        return $this->sMessage;
    }

}

class KTUploadNewFile {

    function KTUploadNewFile($filename)
    {
        $this->sFilename = $filename;
    }

    function getString()
    {
        return $this->sFilename;
    }

}

?>
