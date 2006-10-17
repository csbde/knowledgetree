<?php /* vim: set expandtab softtabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 * $Id$
 *
 * High-level folder operations
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 *
 * @version $Revision$
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
require_once(KT_LIB_DIR . "/subscriptions/subscriptions.inc.php"); 

require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/users/User.inc');

require_once(KT_LIB_DIR . '/foldermanagement/foldertransaction.inc.php');

require_once(KT_LIB_DIR . '/database/dbutil.inc');

class KTFolderUtil {
    function _add ($oParentFolder, $sFolderName, $oUser) {
        if (PEAR::isError($oParentFolder)) {
            return $oParentFolder;
        }
        if (PEAR::isError($oUser)) {
            return $oUser;
        }
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $oFolder =& Folder::createFromArray(array(
            'name' => $sFolderName,
            'description' => $sFolderName,
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

    function add ($oParentFolder, $sFolderName, $oUser) {
        $oFolder = KTFolderUtil::_add($oParentFolder, $sFolderName, $oUser);
        if (PEAR::isError($oFolder)) {
            return $oFolder;
        }
        
        // check for conflicts first
        if (Folder::folderExistsName(KTUtil::getId($oParentfolder), $sFolderName)) {
            return PEAR::raiseError(sprintf(_kt('The folder %s already exists.'), $sFolderName));
        }

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $oFolder->getId(),
            'comment' => "Folder created",
            'transactionNS' => 'ktcore.transactions.create',
            'userid' => $oUser->getId(),
            'ip' => Session::getClientIP(),
        ));

        // fire subscription alerts for the new folder
        $oSubscriptionEvent = new SubscriptionEvent();
        $oSubscriptionEvent->AddFolder($oFolder, $oParentFolder);
        
        KTFolderUtil::updateSearchableText($oFolder);
         
        return $oFolder;
    }

    function move($oFolder, $oNewParentFolder, $oUser) {
        if (KTFolderUtil::exists($oNewParentFolder, $oFolder->getName())) {
            return PEAR::raiseError(_kt("Folder with the same name already exists in the new parent folder"));
        }
        $oStorage =& KTStorageManagerUtil::getSingleton();

        $iOriginalPermissionObjectId = $oFolder->getPermissionObjectId();
        $iOriginalParentFolderId = $oFolder->getParentID();
        if (empty($iOriginalParentFolderId)) {
            // If we have no parent, then we're the root.  If we're the
            // root - how do we move inside something?
            return PEAR::raiseError(_kt("Folder has no parent"));
        }
        $oOriginalParentFolder = Folder::get($iOriginalParentFolderId);
        if (PEAR::isError($oOriginalParentFolder)) {
            // If we have no parent, then we're the root.  If we're the
            // root - how do we move inside something?
            return PEAR::raiseError(_kt("Folder parent does not exist"));
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
        $table = "folders";
        
        if ($oNewParentFolder->getId() == 1) {
            $sNewParentFolderPath = $oNewParentFolder->getName();
            $sNewParentFolderIds = "";
        } else {
            $sNewParentFolderPath = sprintf("%s/%s", $oNewParentFolder->getFullPath(), $oNewParentFolder->getName());
            $sNewParentFolderIds = sprintf("%s,%s", $oNewParentFolder->getParentFolderIDs(), $oNewParentFolder->getID());
        }

        $sOldPath = $oFolder->getFullPath();

        if ($oNewParentFolder->getId() == 1) {
        } else {
            $sNewParentFolderPath = sprintf("%s/%s", $oNewParentFolder->getFullPath(), $oNewParentFolder->getName());
        }

        // Update the moved folder first...
        $sQuery = "UPDATE $table SET full_path = ?, parent_folder_ids = ?, parent_id = ? WHERE id = ?";
        $aParams = array(
            sprintf("%s", $sNewParentFolderPath),
            $sNewParentFolderIds,
            $oNewParentFolder->getID(),
            $oFolder->getID(),
        );
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }

        if ($oFolder->getId() == 1) {
            $sOldFolderPath = $oFolder->getName();
        } else {
            $sOldFolderPath = sprintf("%s/%s", $oFolder->getFullPath(), $oFolder->getName());
        }
        
        $sQuery = "UPDATE $table SET full_path = CONCAT(?, SUBSTRING(full_path FROM ?)), parent_folder_ids = CONCAT(?, SUBSTRING(parent_folder_ids FROM ?)) WHERE full_path LIKE ?";
        $aParams = array(
            sprintf("%s", $sNewParentFolderPath),
            strlen($oFolder->getFullPath()) + 1,
            $sNewParentFolderIds,
            strlen($oFolder->getParentFolderIDs()) + 1,
            sprintf("%s%%", $sOldFolderPath),
        );
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }

        $table = "documents";
        $sQuery = "UPDATE $table SET full_path = CONCAT(?, SUBSTRING(full_path FROM ?)), parent_folder_ids = CONCAT(?, SUBSTRING(parent_folder_ids FROM ?)) WHERE full_path LIKE ?";
        $aParams = array(
            sprintf("%s", $sNewParentFolderPath),
            strlen($oFolder->getFullPath()) + 1,
            $sNewParentFolderIds,
            strlen($oFolder->getParentFolderIDs()) + 1,
            sprintf("%s%%", $sOldFolderPath),
        );
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }

        $res = $oStorage->moveFolder($oFolder, $oNewParentFolder);
        if (PEAR::isError($res)) {
            return $res;
        }

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $oFolder->getId(),
            'comment' => sprintf("Folder moved from %s to %s", $sOldPath, $sNewParentFolderPath),
            'transactionNS' => 'ktcore.transactions.move',
            'userid' => $oUser->getId(),
            'ip' => Session::getClientIP(),
        ));

        Document::clearAllCaches();
        Folder::clearAllCaches();

        if ($bChangePermissionObject) {
            $aOptions = array(
                'evenifnotowner' => true, // Inherit from parent folder, even though not permission owner
            );
            KTPermissionUtil::inheritPermissionObject($oFolder, $aOptions);
        }

        return;
    }
    
    function rename($oFolder, $sNewName, $oUser) {
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $sOldName = $oFolder->getName();
        // First, deal with SQL, as it, at least, is guaranteed to be atomic
        $table = "folders";
        
        $sQuery = "UPDATE $table SET full_path = CONCAT(?, SUBSTRING(full_path FROM ?)) WHERE full_path LIKE ?";

        if ($oFolder->getId() == 1) {
            $sOldPath = $oFolder->getName();
            $sNewPath = $sNewName;
        } else {
            $sOldPath = sprintf("%s/%s", $oFolder->getFullPath(), $oFolder->getName());
            $sNewPath = sprintf("%s/%s", $oFolder->getFullPath(), $sNewName);

        }
        $aParams = array(
            sprintf("%s", $sNewPath),
            strlen($sOldPath) + 1,
            sprintf("%s%%", $sOldPath),
        );
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }

        $table = "documents";
        $sQuery = "UPDATE $table SET full_path = CONCAT(?, SUBSTRING(full_path FROM ?)) WHERE full_path LIKE ?";
        $aParams = array(
            sprintf("%s", $sNewPath),
            strlen($sOldPath) + 1,
            sprintf("%s%%", $sOldPath),
        );
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }

        $res = $oStorage->renameFolder($oFolder, $sNewName);
        if (PEAR::isError($res)) {
            return $res;
        }
        
        $oFolder->setName($sNewName);
        $res = $oFolder->update();

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $oFolder->getId(),
            'comment' => sprintf("Renamed from \"%s\" to \"%s\"", $sOldName, $sNewName),
            'transactionNS' => 'ktcore.transactions.rename',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
        ));
        if (PEAR::isError($oTransaction)) {
            return $oTransaction;
        }

        KTFolderUtil::updateSearchableText($oFolder);

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
    
    function delete($oStartFolder, $oUser, $sReason, $aOptions = null) {
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

        // documents all cleared.
        $sQuery = 'DELETE FROM ' . KTUtil::getTableName('folders') . ' WHERE id IN (' . DBUtil::paramArray($aFolderIds) . ')';
        $aParams = $aFolderIds;
        
        $res = DBUtil::runQuery(array($sQuery, $aParams));

        if (PEAR::isError($res)) {
            DBUtil::rollback();
            return PEAR::raiseError(_kt('Failure deleting folders.'));
        }
        
        // purge caches
        KTEntityUtil::clearAllCaches('Folder');   
        
        // and store
        DBUtil::commit();
        
        return true;
    }
    
    function copy($oSrcFolder, $oDestFolder, $oUser, $sReason) {
        if (KTFolderUtil::exists($oDestFolder, $oSrcFolder->getName())) {
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
        
        $aFolderIds = array(); // of oFolder
        $aDocuments = array(); // of oDocument
        $aFailedDocuments = array(); // of String
        $aFailedFolders = array(); // of String
        
        $aRemainingFolders = array($oSrcFolder->getId());
        
        DBUtil::startTransaction();
        
        while (!empty($aRemainingFolders)) {
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
        
        $sTable = KTUtil::getTableName('folders');
        $sGetQuery = 'SELECT * FROM ' . $sTable . ' WHERE id = ? ';
        $aParams = array($oSrcFolder->getId());
        $aRow = DBUtil::getOneResult(array($sGetQuery, $aParams));
        unset($aRow['id']);
        $aRow['parent_id'] = $oDestFolder->getId();
        $id = DBUtil::autoInsert($sTable, $aRow);
        if (PEAR::isError($id)) {
            DBUtil::rollback();
            return $id;
        }
        $aFolderMap[$oSrcFolder->getId()] = $id;
        $oNewBaseFolder = Folder::get($id);
        $res = $oStorage->createFolder($oNewBaseFolder);
        if (PEAR::isError($res)) {
            // it doesn't exist, so rollback and raise..
            DBUtil::rollback();
            return $res;
        }
        $aRemainingFolders = Folder::getList(array('parent_id = ?', array($oSrcFolder->getId())), array('ids' => true));
        
        
        while (!empty($aRemainingFolders)) {
            $iFolderId = array_pop($aRemainingFolders);
            
            $aParams = array($iFolderId);
            $aRow = DBUtil::getOneResult(array($sGetQuery, $aParams));
            unset($aRow['id']);
            
            // since we are nested, we will have solved the parent first.
            $aRow['parent_id'] = $aFolderMap[$aRow['parent_id']]; 
            
            $id = DBUtil::autoInsert($sTable, $aRow);
            if (PEAR::isError($id)) {
                $oStorage->removeFolder($oNewBaseFolder);
                DBUtil::rollback();
                return $id;
            }
            $aFolderMap[$iFolderId] = $id;
            
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
        
        
        var_dump($aFolderMap);
        
        // now we can go ahead.
        foreach ($aDocuments as $oDocument) {
            $oChildDestinationFolder = Folder::get($aFolderMap[$oDocument->getFolderID()]);
            var_dump($oDocument->getFolderID());
            $res = KTDocumentUtil::copy($oDocument, $oChildDestinationFolder);
            if (PEAR::isError($res) || ($res === false)) {
                $oStorage->removeFolder($oNewBaseFolder);
                DBUtil::rollback();
                return PEAR::raiseError(_kt('Delete Aborted. Unexpected failure to copydocument: ') . $oDocument->getName() . $res->getMessage());
            }
        }
        
        // and store
        DBUtil::commit();
        
        return true;    
    }
    
    function updateSearchableText($oFolder) {
        // very simple function to rebuild the searchable text for this 
        // folder.
        
        // MyISAM table for fulltext index - no transactions.
        
        // get the folder text
        // XXX replace this with a trigger / producer set.
        $sSearchableText = $oFolder->getName();
        
        // do the update.
        $iFolderId = KTUtil::getId($oFolder);
        $sTable = KTUtil::getTableName('folder_searchable_text');
        $aDelete = array(
            "folder_id" => $iFolderId,
        );
        DBUtil::whereDelete($sTable, $aDelete);
        $aInsert = array(
            "folder_id" => $iFolderId,
            "folder_text" => $sSearchableText,
        );
        return DBUtil::autoInsert($sTable, $aInsert, array('noid' => true));
    }
}

?>
