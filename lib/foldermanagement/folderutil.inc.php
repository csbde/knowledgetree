<?php
/**
 * $Id$
 *
 * High-level folder operations
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

require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
require_once(KT_LIB_DIR . '/subscriptions/subscriptions.inc.php');

require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/users/User.inc');

require_once(KT_LIB_DIR . '/foldermanagement/foldertransaction.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/database/dbutil.inc');

class KTFolderUtil {
    function _add($oParentFolder, $sFolderName, $oUser) {
        if (PEAR::isError($oParentFolder)) {
            return $oParentFolder;
        }
        if (PEAR::isError($oUser)) {
            return $oUser;
        }
        $oStorage =& KTStorageManagerUtil::getSingleton();

        $oFolder =& Folder::createFromArray(array(
			'name' => ($sFolderName),
			'description' => ($sFolderName),
			'parentid' => $oParentFolder->getID(),
			'creatorid' => $oUser->getID(),
        ));

        if (PEAR::isError($oFolder)) {
            return $oFolder;
        }
        $res = $oStorage->createFolder($oFolder);
        if (PEAR::isError($res)) {
            $oFolder->delete();
            return $res;
        }
        return $oFolder;
    }

     /*
      * Folder Add
      * Author  :   Jarrett Jordaan
      * Modified    :   28/04/09
      *
      * @params :   KTDocumentUtil $oParentFolder
      *             string $sFolderName
      *             KTUser $oUser
      *             boolean $bulk_action
      */
    function add($oParentFolder, $sFolderName, $oUser, $bulk_action = false) {

        $folderid=$oParentFolder->getId();
        // check for conflicts first
        if (Folder::folderExistsName($sFolderName,$folderid)) {
            return PEAR::raiseError(sprintf(_kt('The folder %s already exists.'), $sFolderName));
        }

        $oFolder = KTFolderUtil::_add($oParentFolder, $sFolderName, $oUser);
        if (PEAR::isError($oFolder)) {
            return $oFolder;
        }

        $oTransaction = KTFolderTransaction::createFromArray(array(
        'folderid' => $oFolder->getId(),
        'comment' => _kt('Folder created'),
        'transactionNS' => 'ktcore.transactions.create',
        'userid' => $oUser->getId(),
        'ip' => Session::getClientIP(),
        ));
        if(!$bulk_action) {
            // fire subscription alerts for the new folder
            $oSubscriptionEvent = new SubscriptionEvent();
            $oSubscriptionEvent->AddFolder($oFolder, $oParentFolder);
        }

        return $oFolder;
    }

    function move($oFolder, $oNewParentFolder, $oUser, $sReason=null, $bulk_action = false) {
    	if ($oFolder->getId() == 1)
    	{
    		return PEAR::raiseError(_kt('Cannot move root folder!'));
    	}
    	if ($oFolder->getParentID() == $oNewParentFolder->getId())
    	{
    		// moved! done.
    		return;
    	}
    	$sFolderParentIds = $oFolder->getParentFolderIDs();
    	$sNewFolderParentIds = $oNewParentFolder->getParentFolderIDs();

    	if (strpos($sNewFolderParentIds, "$sFolderParentIds,") === 0)
    	{
    		return PEAR::raiseError(_kt('Cannot move folder into a descendant folder!'));
    	}
        if (KTFolderUtil::exists($oNewParentFolder, $oFolder->getName())) {
            return PEAR::raiseError(_kt('Folder with the same name already exists in the new parent folder'));
        }
        $oStorage =& KTStorageManagerUtil::getSingleton();

        $iOriginalPermissionObjectId = $oFolder->getPermissionObjectId();
        $iOriginalParentFolderId = $oFolder->getParentID();
        if (empty($iOriginalParentFolderId)) {
            // If we have no parent, then we're the root.  If we're the
            // root - how do we move inside something?
            return PEAR::raiseError(_kt('Folder has no parent'));
        }
        $oOriginalParentFolder = Folder::get($iOriginalParentFolderId);
        if (PEAR::isError($oOriginalParentFolder)) {
            // If we have no parent, then we're the root.  If we're the
            // root - how do we move inside something?
            return PEAR::raiseError(_kt('Folder parent does not exist'));
        }
        $iOriginalParentPermissionObjectId = $oOriginalParentFolder->getPermissionObjectId();
        $iTargetPermissionObjectId = $oFolder->getPermissionObjectId();

        $bChangePermissionObject = false;
        if ($iOriginalPermissionObjectId == $iOriginalParentPermissionObjectId) {
            // If the folder inherited from its parent, we should change
            // its permissionobject
            $bChangePermissionObject = true;
        }

        // First, deal with SQL, as it, at least, is guaranteed to be atomic
        $table = 'folders';

        if ($oNewParentFolder->getId() == 1)
        {
            $sNewFullPath = $oFolder->getName();
            $sNewParentFolderIds = "1";
        }
        else
        {
            $sNewFullPath = $oNewParentFolder->getFullPath() . '/' . $oFolder->getName();
            $sNewParentFolderIds =  $oNewParentFolder->getParentFolderIDs() . ',' . $oNewParentFolder->getID();
        }

        // Update the moved folder first...
        $sQuery = "UPDATE $table SET full_path = ?, parent_folder_ids = ?, parent_id = ? WHERE id = ?";
        $aParams = array(
							$sNewFullPath,
					        $sNewParentFolderIds,
        					$oNewParentFolder->getID(),
        					$oFolder->getID(),
        );
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }

        $sOldFolderPath = $oFolder->getFullPath();

        $sQuery = "UPDATE $table SET full_path = CONCAT(?, SUBSTRING(full_path FROM ?)), parent_folder_ids = CONCAT(?, SUBSTRING(parent_folder_ids FROM ?)) WHERE full_path LIKE ?";
        $aParams = array(
        					$sNewFullPath,
        					strlen($oFolder->getFullPath()) + 1,
        					$sNewParentFolderIds,
        					strlen($oFolder->getParentFolderIDs()) + 1,
        					"$sOldFolderPath%"
        );
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }

        $table = 'documents';
        $sQuery = "UPDATE $table SET full_path = CONCAT(?, SUBSTRING(full_path FROM ?)), parent_folder_ids = CONCAT(?, SUBSTRING(parent_folder_ids FROM ?)) WHERE full_path LIKE ?";
        // use same $aParams as above
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }

        // Regenerate the folder object - ensure the updated information is taken into account
        $oFolder = Folder::get($oFolder->getID());

        $res = $oStorage->moveFolder($oFolder, $oNewParentFolder);
        if (PEAR::isError($res)) {
            return $res;
        }

        $sComment = sprintf(_kt("Folder moved from %s to %s"), $sOldPath, $sNewParentFolderPath);
        if($sReason !== null) {
            $sComment .= sprintf(_kt(" (reason: %s)"), $sReason);
        }

        $oTransaction = KTFolderTransaction::createFromArray(array(
        'folderid' => $oFolder->getId(),
        'comment' => $sComment,
        'transactionNS' => 'ktcore.transactions.move',
        'userid' => $oUser->getId(),
        'ip' => Session::getClientIP(),
        ));

        Document::clearAllCaches();
        Folder::clearAllCaches();
        $GLOBALS["_OBJECTCACHE"] = array();

        if ($bChangePermissionObject) {
            $aOptions = array(
            'evenifnotowner' => true, // Inherit from parent folder, even though not permission owner
            );
            KTPermissionUtil::inheritPermissionObject($oFolder, $aOptions);
        }

        return true;
    }

    function rename($oFolder, $sNewName, $oUser) {
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $sOldName = $oFolder->getName();
        // First, deal with SQL, as it, at least, is guaranteed to be atomic
        $table = "folders";

        if ($oFolder->getId() == 1 || $oFolder->getParentID() == 1) {
            $sOldPath = $oFolder->getName();
            $sNewPath = $sNewName;
        } else {
            $sOldPath = $oFolder->getFullPath();
            $sNewPathDir = !empty($sOldPath) ? dirname($sOldPath) . '/' : '';
            $sNewPath = $sNewPathDir . $sNewName;
        }

        $sQuery = "UPDATE $table SET full_path = CONCAT(?, SUBSTRING(full_path FROM ?)) WHERE full_path LIKE ? OR full_path = ?";
        $aParams = array(
        	"$sNewPath/",
        	mb_strlen(utf8_decode($sOldPath)) + 2,
        	$sOldPath.'/%',
        	$sOldPath,
        );

        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }

        $table = "documents";
        $sQuery = "UPDATE $table SET full_path = CONCAT(?, SUBSTRING(full_path FROM ?)) WHERE full_path LIKE ? OR full_path = ?";

        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }

        $res = $oStorage->renameFolder($oFolder, $sNewName);
        if (PEAR::isError($res)) {
            return $res;
        }

        $oFolder->setName($sNewName);
        $oFolder->setDescription($sNewName);
        $oFolder->setLastModifiedDate(getCurrentDateTime());
        $oFolder->setModifiedUserId($oUser->getId());
        
        $res = $oFolder->update();

        $oTransaction = KTFolderTransaction::createFromArray(array(
        'folderid' => $oFolder->getId(),
        'comment' => sprintf(_kt("Renamed from \"%s\" to \"%s\""), $sOldName, $sNewName),
        'transactionNS' => 'ktcore.transactions.rename',
        'userid' => $_SESSION['userID'],
        'ip' => Session::getClientIP(),
        ));
        if (PEAR::isError($oTransaction)) {
            return $oTransaction;
        }

        Document::clearAllCaches();
        Folder::clearAllCaches();

        return $res;
    }

    function exists($oParentFolder, $sName) {
        return Folder::folderExistsName($sName, $oParentFolder->getID());
    }



    /* folderUtil::delete
    *
    * this function is _much_ more complex than it might seem.
    * we need to:
    *   - recursively identify children
    *   - validate that permissions are allocated correctly.
    *   - step-by-step delete.
    */

    function delete($oStartFolder, $oUser, $sReason, $aOptions = null, $bulk_action = false) {
        require_once(KT_LIB_DIR . '/unitmanagement/Unit.inc');

        $oPerm = KTPermission::getByName('ktcore.permissions.delete');

        $bIgnorePermissions = KTUtil::arrayGet($aOptions, 'ignore_permissions');

        $aFolderIds = array(); // of oFolder
        $aDocuments = array(); // of oDocument
        $aFailedDocuments = array(); // of String
        $aFailedFolders = array(); // of String

        $aRemainingFolders = array($oStartFolder->getId());

        DBUtil::startTransaction();

        while (!empty($aRemainingFolders)) {
            $iFolderId = array_pop($aRemainingFolders);
            $oFolder = Folder::get($iFolderId);
            if (PEAR::isError($oFolder) || ($oFolder == false)) {
                DBUtil::rollback();
                return PEAR::raiseError(sprintf(_kt('Failure resolving child folder with id = %d.'), $iFolderId));
            }

            $oUnit = Unit::getByFolder($oFolder);
            if (!empty($oUnit)) {
                DBUtil::rollback();
                return PEAR::raiseError(sprintf(_kt('Cannot remove unit folder: %s.'), $oFolder->getName()));
            }

            // don't just stop ... plough on.
            if (!$bIgnorePermissions && !KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oFolder)) {
                $aFailedFolders[] = $oFolder->getName();
            } else {
                $aFolderIds[] = $iFolderId;
            }

            // child documents
            $aChildDocs = Document::getList(array('folder_id = ?',array($iFolderId)));
            foreach ($aChildDocs as $oDoc) {
                if (!$bIgnorePermissions && $oDoc->getImmutable()) {
                    if (!KTBrowseUtil::inAdminMode($oUser, $oStartFolder)) {
                        $aFailedDocuments[] = $oDoc->getName();
                        continue;
                    }
                }
                if ($bIgnorePermissions || (KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oDoc) && ($oDoc->getIsCheckedOut() == false)) ) {
                    $aDocuments[] = $oDoc;
                } else {
                    $aFailedDocuments[] = $oDoc->getName();
                }
            }

            // child folders.
            $aCFIds = Folder::getList(array('parent_id = ?', array($iFolderId)), array('ids' => true));
            $aRemainingFolders = kt_array_merge($aRemainingFolders, $aCFIds);
        }

        // FIXME we could subdivide this to provide a per-item display (viz. bulk upload, etc.)

        if ((!empty($aFailedDocuments) || (!empty($aFailedFolders)))) {
            $sFD = '';
            $sFF = '';
            if (!empty($aFailedDocuments)) {
                $sFD = _kt('Documents: ') . implode(', ', $aFailedDocuments) . '. ';
            }
            if (!empty($aFailedFolders)) {
                $sFF = _kt('Folders: ') . implode(', ', $aFailedFolders) . '.';
            }
            return PEAR::raiseError(_kt('You do not have permission to delete these items. ') . $sFD . $sFF);
        }

        // now we can go ahead.
        foreach ($aDocuments as $oDocument) {
            $res = KTDocumentUtil::delete($oDocument, $sReason);
            if (PEAR::isError($res)) {
                DBUtil::rollback();
                return PEAR::raiseError(_kt('Delete Aborted. Unexpected failure to delete document: ') . $oDocument->getName() . $res->getMessage());
            }
        }

        $oStorage =& KTStorageManagerUtil::getSingleton();
        $oStorage->removeFolderTree($oStartFolder);

        // Check for symbolic links to the folder and its sub folders
        $aSymlinks = array();
        foreach($aFolderIds as $iFolder){
        	$oFolder = Folder::get($iFolder);
	        $aLinks = $oFolder->getSymbolicLinks();
	        $aSymlinks = array_merge($aSymlinks, $aLinks);
        }

        // documents all cleared.
        $sQuery = 'DELETE FROM ' . KTUtil::getTableName('folders') . ' WHERE id IN (' . DBUtil::paramArray($aFolderIds) . ')';
        $aParams = $aFolderIds;

        $res = DBUtil::runQuery(array($sQuery, $aParams));

        if (PEAR::isError($res)) {
            DBUtil::rollback();
            return PEAR::raiseError(_kt('Failure deleting folders.'));
        }

        // now that the folder has been deleted we delete all the shortcuts
        if(!empty($aSymlinks)){
            $links = array();
            foreach($aSymlinks as $link){
                $links[] = $link['id'];
            }
            $linkIds = implode(',', $links);

            $query = "DELETE FROM folders WHERE id IN ($linkIds)";
            DBUtil::runQuery($query);
        }

        /*
        foreach($aSymlinks as $aSymlink){
        	KTFolderUtil::deleteSymbolicLink($aSymlink['id']);
        }
        */

        // purge caches
        KTEntityUtil::clearAllCaches('Folder');

        // and store
        DBUtil::commit();

        return true;
    }

    function copy($oSrcFolder, $oDestFolder, $oUser, $sReason, $sDestFolderName = NULL, $copyAll = true) {
        $sDestFolderName = (empty($sDestFolderName)) ? $oSrcFolder->getName() : $sDestFolderName;
        if (KTFolderUtil::exists($oDestFolder, $sDestFolderName)) {
            return PEAR::raiseError(_kt("Folder with the same name already exists in the new parent folder"));
        }
        //
        // FIXME the failure cleanup code here needs some serious work.
        //
        $oPerm = KTPermission::getByName('ktcore.permissions.read');
        $oBaseFolderPerm = KTPermission::getByName('ktcore.permissions.addFolder');

        if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oBaseFolderPerm, $oDestFolder)) {
            return PEAR::raiseError(_kt('You are not allowed to create folders in the destination.'));
        }

        // Check if the source folder inherits its permissions
        // Get source PO id and its parent PO id
        $iSrcPoId = $oSrcFolder->getPermissionObjectID();
        $oSrcParent = Folder::get($oSrcFolder->getParentID());
        $iSrcParentPoId = $oSrcParent->getPermissionObjectID();

        // If the folder defines its own permissions then we copy the permission object
        // If the source folder inherits permissions we must change it to inherit from the new parent folder
        $bInheritPermissions = false;
        if($iSrcPoId == $iSrcParentPoId){
            $bInheritPermissions = true;
        }

        $aFolderIds = array(); // of oFolder
        $aDocuments = array(); // of oDocument
        $aFailedDocuments = array(); // of String
        $aFailedFolders = array(); // of String

        $aRemainingFolders = array($oSrcFolder->getId());

        DBUtil::startTransaction();

        while (!empty($aRemainingFolders) && $copyAll)
        {
            $iFolderId = array_pop($aRemainingFolders);
            $oFolder = Folder::get($iFolderId);
            if (PEAR::isError($oFolder) || ($oFolder == false)) {
                DBUtil::rollback();
                return PEAR::raiseError(sprintf(_kt('Failure resolving child folder with id = %d.'), $iFolderId));
            }

            // don't just stop ... plough on.
            if (KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oFolder)) {
                $aFolderIds[] = $iFolderId;
            } else {
                $aFailedFolders[] = $oFolder->getName();
            }

            // child documents
            $aChildDocs = Document::getList(array('folder_id = ?',array($iFolderId)));
            foreach ($aChildDocs as $oDoc) {
                if (KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oDoc)) {
                    $aDocuments[] = $oDoc;
                } else {
                    $aFailedDocuments[] = $oDoc->getName();
                }
            }

            // child folders.
            $aCFIds = Folder::getList(array('parent_id = ?', array($iFolderId)), array('ids' => true));
            $aRemainingFolders = kt_array_merge($aRemainingFolders, $aCFIds);
        }

        if ((!empty($aFailedDocuments) || (!empty($aFailedFolders)))) {
            $sFD = '';
            $sFF = '';
            if (!empty($aFailedDocuments)) {
                $sFD = _kt('Documents: ') . implode(', ', $aFailedDocuments) . '. ';
            }
            if (!empty($aFailedFolders)) {
                $sFF = _kt('Folders: ') . implode(', ', $aFailedFolders) . '.';
            }
            return PEAR::raiseError(_kt('You do not have permission to copy these items. ') . $sFD . $sFF);
        }

        // first we walk the tree, creating in the new location as we go.
        // essentially this is an "ok" pass.


        $oStorage =& KTStorageManagerUtil::getSingleton();

        $aFolderMap = array();

        $sTable = 'folders';
        $sGetQuery = 'SELECT * FROM ' . $sTable . ' WHERE id = ? ';
        $aParams = array($oSrcFolder->getId());
        $aRow = DBUtil::getOneResult(array($sGetQuery, $aParams));
        unset($aRow['id']);

        $aRow['name'] = $sDestFolderName;
        $aRow['description'] = $sDestFolderName;
        $aRow['parent_id'] = $oDestFolder->getId();
        $aRow['parent_folder_ids'] = sprintf('%s,%s', $oDestFolder->getParentFolderIDs(), $oDestFolder->getId());
        $aRow['full_path'] = $oDestFolder->getFullPath() . '/' . $aRow['name'];

        $id = DBUtil::autoInsert($sTable, $aRow);
        if (PEAR::isError($id)) {
            DBUtil::rollback();
            return $id;
        }
        $sSrcFolderId = $oSrcFolder->getId();
        $aFolderMap[$sSrcFolderId]['parent_id'] = $id;
        $aFolderMap[$sSrcFolderId]['parent_folder_ids'] = $aRow['parent_folder_ids'];
        $aFolderMap[$sSrcFolderId]['full_path'] = $aRow['full_path'];
        $aFolderMap[$sSrcFolderId]['name'] = $aRow['name'];

        $oNewBaseFolder = Folder::get($id);
        $res = $oStorage->createFolder($oNewBaseFolder);
        if (PEAR::isError($res)) {
            // it doesn't exist, so rollback and raise..
            DBUtil::rollback();
            return $res;
        }
        $aRemainingFolders = Folder::getList(array('parent_id = ?', array($oSrcFolder->getId())), array('ids' => true));


        while (!empty($aRemainingFolders) && $copyAll) {
            $iFolderId = array_pop($aRemainingFolders);

            $aParams = array($iFolderId);
            $aRow = DBUtil::getOneResult(array($sGetQuery, $aParams));
            unset($aRow['id']);

            // since we are nested, we will have solved the parent first.
            $sPrevParentId = $aRow['parent_id'];
            $aRow['parent_id'] = $aFolderMap[$aRow['parent_id']]['parent_id'];
            $aRow['parent_folder_ids'] = sprintf('%s,%s', $aFolderMap[$sPrevParentId]['parent_folder_ids'], $aRow['parent_id']);
            $aRow['full_path'] = sprintf('%s/%s', $aFolderMap[$sPrevParentId]['full_path'], $aRow['name']);

            $id = DBUtil::autoInsert($sTable, $aRow);
            if (PEAR::isError($id)) {
                $oStorage->removeFolder($oNewBaseFolder);
                DBUtil::rollback();
                return $id;
            }
            $aFolderMap[$iFolderId]['parent_id'] = $id;
            $aFolderMap[$iFolderId]['parent_folder_ids'] = $aRow['parent_folder_ids'];
            $aFolderMap[$iFolderId]['full_path'] = $aRow['full_path'];
            $aFolderMap[$iFolderId]['name'] = $aRow['name'];

            $oNewFolder = Folder::get($id);
            $res = $oStorage->createFolder($oNewFolder);
            if (PEAR::isError($res)) {
                // first delete, then rollback, then fail out.
                $oStorage->removeFolder($oNewBaseFolder);
                DBUtil::rollback();
                return $res;
            }

            $aCFIds = Folder::getList(array('parent_id = ?', array($iFolderId)), array('ids' => true));
            $aRemainingFolders = kt_array_merge($aRemainingFolders, $aCFIds);
        }

        // now we can go ahead.
        foreach ($aDocuments as $oDocument) {
            $oChildDestinationFolder = Folder::get($aFolderMap[$oDocument->getFolderID()]['parent_id']);
            $res = KTDocumentUtil::copy($oDocument, $oChildDestinationFolder);
            if (PEAR::isError($res) || ($res === false)) {
                $oStorage->removeFolder($oNewBaseFolder);
                DBUtil::rollback();
                return PEAR::raiseError(_kt('Delete Aborted. Unexpected failure to copydocument: ') . $oDocument->getName() . $res->getMessage());
            }
        }

        $sComment = sprintf(_kt("Folder copied from %s to %s"), $oSrcFolder->getFullPath(), $oDestFolder->getFullPath());
        if($sReason !== null) {
            $sComment .= sprintf(_kt(" (reason: %s)"), $sReason);
        }

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $oFolder->getId(),
            'comment' => $sComment,
            'transactionNS' => 'ktcore.transactions.copy',
            'userid' => $oUser->getId(),
            'ip' => Session::getClientIP(),
        ));

        // If the folder inherits its permissions then we set it to inherit from the new parent folder and update permissions
        // If it defines its own then copy the permission object over
        if($bInheritPermissions){
            $aOptions = array(
                'evenifnotowner' => true, // Inherit from parent folder, even though not permission owner
                );
            KTPermissionUtil::inheritPermissionObject($oNewBaseFolder, $aOptions);
        }else{
            KTPermissionUtil::copyPermissionObject($oNewBaseFolder);
        }

        // and store
        DBUtil::commit();

        return true;
    }

/**
     * Create a symbolic link in the target folder
     *
     * @param Folder $sourceFolder Folder to create a link to
     * @param Folder $targetFolder Folder to place the link in
     * @param User $user current user
     * @return Folder the link
     */
    static function createSymbolicLink($sourceFolder, $targetFolder, $user = null) // added/
    {
    	//validate input
        if (is_numeric($sourceFolder))
        {
            $sourceFolder = Folder::get($sourceFolder);
        }
        if (!$sourceFolder instanceof Folder)
        {
            return PEAR::raiseError(_kt('Source folder not specified'));
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
        $oWritePermission =& KTPermission::getByName("ktcore.permissions.write");
		$oReadPermission =& KTPermission::getByName("ktcore.permissions.read");
		if (!KTBrowseUtil::inAdminMode($user, $targetFolder)) {
            if(!KTPermissionUtil::userHasPermissionOnItem($user, $oWritePermission, $targetFolder)){
        		return PEAR::raiseError(_kt('You\'re not authorized to create shortcuts'));
       		}
        }
        if (!KTBrowseUtil::inAdminMode($user, $sourceFolder)) {
        	if(!KTPermissionUtil::userHasPermissionOnItem($user, $oReadPermission, $sourceFolder)){
        		return PEAR::raiseError(_kt('You\'re not authorized to create a shortcut to this folder'));
       		}
        }

    	//check if the shortcut doesn't already exists in the target folder
        $aSymlinks = $sourceFolder->getSymbolicLinks();
        foreach($aSymlinks as $iSymlink){
        	$oSymlink = Folder::get($iSymlink['id']);
        	if($oSymlink->getParentID() == $targetFolder->getID()){
        		return PEAR::raiseError(_kt('There already is a shortcut to this folder in the target folder.'));
        	}
        }

        //Create the link
        $oSymlink = Folder::createFromArray(array(
            'iParentID' => $targetFolder->getId(),
            'iCreatorID' => $user->getId(),
            'sFullPath' => $targetFolder->getFullPath(),
            'sParentFolderIDs' => $targetFolder->getParentFolderIDs(),
            'iPermissionObjectID' => $targetFolder->getPermissionObjectID(),
            'iPermissionLookupID' => $targetFolder->getPermissionLookupID(),
        	'iLinkedFolderId' => $sourceFolder->getId(),
        ));
        return $oSymlink;
    }

    /**
     * Deletes a symbolic link folder
     *
     * @param Folder $folder tthe symbolic link folder to delete
     * @param User $user the current user
     * @return unknown
     */
    static function deleteSymbolicLink($folder, $user = null) // added/
    {
    	//validate input
        if (is_numeric($folder))
        {
            $folder = Folder::get($folder);
        }
        if (!$folder instanceof Folder)
        {
            return PEAR::raiseError(_kt('Folder not specified'));
        }
        if (!$folder->isSymbolicLink())
        {
            return PEAR::raiseError(_kt('Folder must be a symbolic link entity'));
        }
        if (is_null($user))
        {
            $user = $_SESSION['userID'];
        }
        if (is_numeric($user))
        {
            $user = User::get($user);
        }

        //check if the user has sufficient permissions
		$oPerm = KTPermission::getByName('ktcore.permissions.delete');
    	if (!KTBrowseUtil::inAdminMode($user, $folder)) {
            if(!KTPermissionUtil::userHasPermissionOnItem($user, $oPerm, $folder)){
        		return PEAR::raiseError(_kt('You\'re not authorized to delete shortcuts'));
       		}
        }

        // we only need to delete the folder entry for the link
        $sql = "DELETE FROM folders WHERE id=?";
        DBUtil::runQuery(array($sql, array($folder->getId())));

    }


}

?>
