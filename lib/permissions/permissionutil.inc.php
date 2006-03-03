<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
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
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

require_once(KT_LIB_DIR . "/documentmanagement/Document.inc");
require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");
require_once(KT_LIB_DIR . "/permissions/permission.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionassignment.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissiondescriptor.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionlookup.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionlookupassignment.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionobject.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissiondynamiccondition.inc.php");
require_once(KT_LIB_DIR . "/groups/GroupUtil.php");
require_once(KT_LIB_DIR . "/roles/roleallocation.inc.php");
require_once(KT_LIB_DIR . "/roles/documentroleallocation.inc.php");

require_once(KT_LIB_DIR . "/workflow/workflowutil.inc.php");
require_once(KT_LIB_DIR . "/workflow/workflowstatepermissionsassignment.inc.php");

class KTPermissionUtil {
    // {{{ generateDescriptor
    /**
     * Generate a unique textual representation of a specific collection
     * of users/groups/roles described by a dictionary.
     *
     * This function _must_ always generate the same descriptor for a
     * given collection of users/groups/roles, no matter the order of
     * the keys or the order of the ids in the values of the collection.
     */
    function generateDescriptor ($aAllowed) {
        $aAllowedSort = array();
        // PHP5: clone
        $aTmp = $aAllowed;
        ksort($aTmp);
        $sOutput = "";
        foreach ($aTmp as $k => $v) {
            if (empty($v)) {
                continue;
            }
            $v = array_unique($v);
            $sOutput .= "$k(";
            sort($v);
            $sOutput .= join(",", $v);
            $sOutput .= ")";
        }

        return $sOutput;
    }
    // }}}

    // {{{ getOrCreateDescriptor
    /**
     * For a given collection of users/groups/roles, get the permission
     * descriptor object that describes that exact collection, creating
     * such an object if it does not already exist.
     */
    function getOrCreateDescriptor ($aAllowed) {
        $sDescriptor = KTPermissionUtil::generateDescriptor($aAllowed);
        $oDescriptor =& KTPermissionDescriptor::getByDescriptor(md5($sDescriptor));
        if (PEAR::isError($oDescriptor)) {
            $oDescriptor =& KTPermissionDescriptor::createFromArray(array(
                "descriptortext" => $sDescriptor,
            ));
            $oDescriptor->saveAllowed($aAllowed);
        }
        return $oDescriptor;
    }
    // }}}

    // {{{ getAllowedForDescriptor
    function getAllowedForDescriptor($oDescriptor) {
        $oDescriptor =& KTUtil::getObject('KTPermissionDescriptor', $oDescriptor);
        return $oDescriptor->getAllowed();
    }
    // }}}

    // {{{ getOrCreateAssignment
    /**
     * For a given permission object, get the assignment object for the
     * given permission, or create one if there isn't one already.
     *
     * This assignment object describes the group of users/groups/roles
     * that have the given permission.  If one is created, it is created
     * empty.
     */
    function getOrCreateAssignment ($sPermission, $iObjectID) {
        if (is_string($sPermission)) {
            $oPermission =& KTPermission::getByName($sPermission);
        } else {
            $oPermission =& $sPermission;
        }
        if (is_numeric($iObjectID)) {
            $oObject =& KTPermissionObject::get($iObjectID);
        } else {
            $oObject =& $iObjectID;
        }
        $oPA = KTPermissionAssignment::getByPermissionAndObject($oPermission, $oObject);
        if (PEAR::isError($oPA)) {
            $oPA = KTPermissionAssignment::createFromArray(array(
                'permissionid' => $oPermission->getID(),
                'permissionobjectid' => $oObject->getID(),
            ));
        }
        return $oPA;
    }
    // }}}

    // {{{ setPermissionForID
    /**
     * For a given permission object, set the given group of
     * users/groups/roles that have a given permission, removing any
     * previous assignment.
     */
    function setPermissionForID($sPermission, $iObjectID, $aAllowed) {
        $oPermissionAssignment = KTPermissionUtil::getOrCreateAssignment($sPermission, $iObjectID);
        $oDescriptor = KTPermissionUtil::getOrCreateDescriptor($aAllowed);
        $oPermissionAssignment->setPermissionDescriptorID($oDescriptor->getID());
        $res = $oPermissionAssignment->update();
        return $res;
    }
    // }}}

    // {{{ updatePermissionLookupForState
    function updatePermissionLookupForState($oState) {
        $aDocuments = Document::getByState($oState);
        foreach ($aDocuments as $oDocument) {
            KTPermissionUtil::updatePermissionLookup($oDocument);
        }
    }
    // }}}

    // {{{ updatePermissionLookupForPO
    /**
     * Updates permission lookups for all objects of a certain
     * permission object.
     *
     * It may be that you don't have or want to have the root item for a
     * permission object that you do have and have updates - then use
     * this.
     */
    function updatePermissionLookupForPO($oPO) {
        $sWhere = 'permission_object_id = ?';
        $aParams = array($oPO->getID());
        $aFolders =& Folder::getList(array($sWhere, $aParams));
        foreach ($aFolders as $oFolder) {
            KTPermissionUtil::updatePermissionLookup($oFolder);
        }
        $aDocuments =& Document::getList(array($sWhere, $aParams));
        foreach ($aDocuments as $oDocument) {
            KTPermissionUtil::updatePermissionLookup($oDocument);
        }
    }
    // }}}

    // {{{ updatePermissionLookupRecursive
    /**
     * Updates permission lookups for this folder and any ancestors, but
     * only if they use the same permission object.
     *
     * To be used any time a folder permission object is changed.
     */
    function updatePermissionLookupRecursive(&$oDocumentOrFolder) {
        if (is_a($oDocumentOrFolder, 'Document')) {
            // XXX: metadata versions may need attention here
            KTPermissionUtil::updatePermissionLookup($oDocumentOrFolder);
            return;
        }

        $iFolderID = $oDocumentOrFolder->getID();
        $sFolderIDs = Folder::generateFolderIDs($iFolderID);
        $sFolderIDs .= '%';

        $sWhere = 'permission_object_id = ? AND parent_folder_ids LIKE ?';
        $aParams = array($oDocumentOrFolder->getPermissionObjectID(), $sFolderIDs);

        $aFolders =& Folder::getList(array($sWhere, $aParams));
        foreach ($aFolders as $oFolder) {
            KTPermissionUtil::updatePermissionLookup($oFolder);
        }

        $aDocuments =& Document::getList(array($sWhere, $aParams));
        foreach ($aDocuments as $oDocument) {
            KTPermissionUtil::updatePermissionLookup($oDocument);
        }
    }
    // }}}

    // {{{ updatePermissionLookup
    /**
     * Update's the permission lookup on one folder or document,
     * non-recursively.
     */
    function updatePermissionLookup(&$oFolderOrDocument) {
        if (!is_a($oFolderOrDocument, 'Folder')) {
            if (!is_a($oFolderOrDocument, 'Document')) {
                if (!is_a($oFolderOrDocument, 'KTDocumentCore')) {
                    echo "<pre>";
                    var_dump($oFolderOrDocument);
                    echo "</pre>";
                }
            }
        }
        
        $oChannel =& KTPermissionChannel::getSingleton();
        if (is_a($oFolderOrDocument, 'Folder')) {
            $msg = sprintf("Updating folder %s", join("/", $oFolderOrDocument->getPathArray()));
        } else {
            if (is_a($oFolderOrDocument, 'Document')) {
                $msg = sprintf("Updating document %s", $oFolderOrDocument->getName());
            } else {
                $msg = sprintf("Updating document %d", $oFolderOrDocument->getId());
            }
        }
        $oChannel->sendMessage(new KTPermissionGenericMessage($msg));
        //var_dump($msg);            
        $iPermissionObjectId = $oFolderOrDocument->getPermissionObjectID();
        if (empty($iPermissionObjectId)) {
            return;
        }
        $oPO = KTPermissionObject::get($iPermissionObjectId);
        $aPAs = KTPermissionAssignment::getByObjectMulti($oPO);
        $aMapPermAllowed = array();
        foreach ($aPAs as $oPA) {
            $oPD = KTPermissionDescriptor::get($oPA->getPermissionDescriptorID());
            $aGroupIDs = $oPD->getGroups();
            $aUserIDs = array();
            $aRoleIDs = $oPD->getRoles();
            $aAllowed = array(
                "group" => $aGroupIDs,
                "user" => $aUserIDs,
                "role" => $aRoleIDs,
            );
            $aMapPermAllowed[$oPA->getPermissionID()] = $aAllowed;
        }

        if (!is_a($oFolderOrDocument, 'Folder')) {
            $aDynamicConditions = KTPermissionDynamicCondition::getByPermissionObject($oPO);
            if (!PEAR::isError($aDynamicConditions)) {
                foreach ($aDynamicConditions as $oDynamicCondition) {
                    $iConditionId = $oDynamicCondition->getConditionId();
                    if (KTSearchUtil::testConditionOnDocument($iConditionId, $oFolderOrDocument)) {
                        $iGroupId = $oDynamicCondition->getGroupId();
                        $aPermissionIds = $oDynamicCondition->getAssignment();
                        foreach ($aPermissionIds as $iPermissionId) {
                            $aCurrentAllowed = KTUtil::arrayGet($aMapPermAllowed, $iPermissionId, array());
                            $aCurrentAllowed["group"][] = $iGroupId;
                            $aMapPermAllowed[$iPermissionId] = $aCurrentAllowed;
                        }
                    }
                }
            }
        }

        if (!is_a($oFolderOrDocument, 'Folder')) {
            $oState = KTWorkflowUtil::getWorkflowStateForDocument($oFolderOrDocument);
            if (!(PEAR::isError($oState) || is_null($oState) || ($oState == false))) {
                $aWorkflowStatePermissionAssignments = KTWorkflowStatePermissionAssignment::getByState($oState);
                foreach ($aWorkflowStatePermissionAssignments as $oAssignment) {
                    $iPermissionId = $oAssignment->getPermissionId();
                    $iPermissionDescriptorId = $oAssignment->getDescriptorId();
                    
                    $oPD = KTPermissionDescriptor::get($iPermissionDescriptorId);
                    $aGroupIDs = $oPD->getGroups();
                    $aUserIDs = array();
                    $aRoleIDs = $oPD->getRoles();
                    $aAllowed = array(
                        "group" => $aGroupIDs,
                        "user" => $aUserIDs,
                        "role" => $aRoleIDs,
                    );
                    $aMapPermAllowed[$iPermissionId] = $aAllowed;
                }
            }
        }

        // if we have roles:  nearest folder.
        $iRoleSourceFolder = null;
        if (is_a($oFolderOrDocument, 'KTDocumentCore') || is_a($oFolderOrDocument, 'Document')) { $iRoleSourceFolder = $oFolderOrDocument->getFolderID(); }
        else { $iRoleSourceFolder = $oFolderOrDocument->getId(); }
            
        // very minor perf win:  map role_id (in context) to PD.
        $_roleCache = array(); 
            
        foreach ($aMapPermAllowed as $iPermissionId => $aAllowed) {
            if (array_key_exists('role', $aAllowed)) {
                foreach ($aAllowed['role'] as $iRoleId) {
                    // store the PD <-> RoleId map
                    if (!array_key_exists($iRoleId, $_roleCache)) {
                        $oRoleAllocation = null;
                        if (is_a($oFolderOrDocument, 'KTDocumentCore') || is_a($oFolderOrDocument, 'Document')) {
                            $oRoleAllocation =& DocumentRoleAllocation::getAllocationsForDocumentAndRole($oFolderOrDocument->getId(), $iRoleId);
                            if (PEAR::isError($oRoleAllocation)) { $oRoleAllocation = null; }
                        }
                        // if that's null - not set _on_ the document, then
                        if (is_null($oRoleAllocation)) {
                            $oRoleAllocation =& RoleAllocation::getAllocationsForFolderAndRole($iRoleSourceFolder, $iRoleId);
                        }
                        $_roleCache[$iRoleId] = $oRoleAllocation;
                    }
                    // roles are _not_ always assigned (can be null at root)
                    if (!is_null($_roleCache[$iRoleId])) {
                        $aMapPermAllowed[$iPermissionId]['user'] = array_merge($aAllowed['user'], $_roleCache[$iRoleId]->getUserIds());
                        $aMapPermAllowed[$iPermissionId]['group'] = array_merge($aAllowed['group'], $_roleCache[$iRoleId]->getGroupIds());
                        // naturally, roles cannot be assigned roles, or madness follows.
                    }
                }
                
            }
            unset($aMapPermAllowed[$iPermissionId]['role']);            
        }
        
        /*
        print '<pre>';
        print '=======' . $oFolderOrDocument->getName();
        print '<br />';
        var_dump($aMapPermAllowed);
        print '</pre>';
        */
        

        $aMapPermDesc = array();
        foreach ($aMapPermAllowed as $iPermissionId => $aAllowed) {
            $oLookupPD = KTPermissionUtil::getOrCreateDescriptor($aAllowed);
            $aMapPermDesc[$iPermissionId] = $oLookupPD->getID();
        }

        $oPL = KTPermissionLookupAssignment::findOrCreateLookupByPermissionDescriptorMap($aMapPermDesc);
        $oFolderOrDocument->setPermissionLookupID($oPL->getID());
        $oFolderOrDocument->update();
    }
    // }}}

    // {{{ userHasPermissionOnItem
    /**
     * Check whether a given user has the given permission on the given
     * object, by virtue of a direct or indirect assignment due to the
     * user, its groups, its roles, or the roles assigned to its groups,
     * and so forth.
     */
    function userHasPermissionOnItem($oUser, $oPermission, $oFolderOrDocument) {
        $oPL = KTPermissionLookup::get($oFolderOrDocument->getPermissionLookupID());
        $oPLA = KTPermissionLookupAssignment::getByPermissionAndLookup($oPermission, $oPL);
        if (PEAR::isError($oPLA)) {
            //print $oPL->getID();
            return false;
        }
        $oPD = KTPermissionDescriptor::get($oPLA->getPermissionDescriptorID());
        $aGroups = GroupUtil::listGroupsForUserExpand($oUser);
        if ($oPD->hasUsers(array($oUser))) { return true; }
        else { return $oPD->hasGroups($aGroups); }
    }
    // }}}

    // {{{ findRootObjectForPermissionObject
    /**
     * Given a specific permission object, find the object (Folder or
     * Document) that is the root of that permission object - the one
     * object that has this permission object, but its parent has a
     * different one.
     */
    function findRootObjectForPermissionObject($oPO) {
        global $default;
        /*
         * If there are any folders with the permission object, then it
         * is set by _a_ folder.  All folders found will have a common
         * ancestor folder, which will be the one with:
         *
         * Potential hack: The shortest parent_folder_ids
         *
         * Potential non-hack: Choose random folder, check parent for
         * permission object recurringly until it changes.  Last success
         * is the ancestor parent...
         */
        $sQuery = "SELECT id FROM $default->folders_table WHERE permission_object_id = ? ORDER BY LENGTH(parent_folder_ids) LIMIT 1";
        $aParams = array($oPO->getID());
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
        if (!is_null($res)) {
            return Folder::get($res);
        }
        $sQuery = "SELECT id FROM $default->documents_table WHERE permission_object_id = ? LIMIT 1";
        $aParams = array($oPO->getID());
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
        if (!is_null($res)) {
            return Document::get($res);
        }
        return false;
    }
    // }}}

    // {{{ copyPermissionObject
    /**
     * Copy the object's parents permission object details, in
     * preparation for the object to have different permissions from its
     * parent.
     */
    function copyPermissionObject(&$oDocumentOrFolder) {
        global $default;
        $oOrigPO = KTPermissionObject::get($oDocumentOrFolder->getPermissionObjectID());
        $aOrigPAs =& KTPermissionAssignment::getByObjectMulti($oOrigPO);
        $oNewPO = KTPermissionObject::createFromArray(array());
        foreach ($aOrigPAs as $oOrigPA) {
            $oNewPA = KTPermissionAssignment::createFromArray(array(
                'permissionid' => $oOrigPA->getPermissionID(),
                'permissionobjectid' => $oNewPO->getID(),
                'permissiondescriptorid' => $oOrigPA->getPermissionDescriptorID(),
            ));
        }
        $oDocumentOrFolder->setPermissionObjectID($oNewPO->getID());
        $oDocumentOrFolder->update();

        if (!is_a($oDocumentOrFolder, 'Folder')) {
            KTPermissionUtil::updatePermissionLookup($oDocumentOrFolder);
            return;
        }

        // For a folder - update permission object for all folders and
        // documents under this current folder if they're using the old
        // permission object id.  If they are, then they're getting the
        // permission object via this folder.  If they are not, then
        // they have their own permission object management, and thus
        // this folder has no effect on their permissions.

        $iFolderID = $oDocumentOrFolder->getID();
        $sFolderIDs = Folder::generateFolderIDs($iFolderID);
        $sFolderIDs .= '%';
        $sQuery = "UPDATE $default->folders_table SET
            permission_object_id = ? WHERE permission_object_id = ? AND
            parent_folder_ids LIKE ?";
        $aParams = array($oNewPO->getID(), $oOrigPO->getID(), $sFolderIDs);
        DBUtil::runQuery(array($sQuery, $aParams));

        $sQuery = "UPDATE $default->documents_table SET
            permission_object_id = ? WHERE permission_object_id = ? AND
            parent_folder_ids LIKE ?";
        DBUtil::runQuery(array($sQuery, $aParams));

        // All objects using this PO must be new and must need their
        // lookups updated...
        KTPermissionUtil::updatePermissionLookupForPO($oNewPO);
    }
    // }}}

    // {{{ isPermissionOwner
    /**
     * Verify if the given object is the root of the permission object
     * it has assigned to it - in other words, if its parent has a
     * different permission object than it.
     */
    function isPermissionOwner(&$oDocumentOrFolder) {
        $oPermissionObject = KTPermissionObject::get($oDocumentOrFolder->getPermissionObjectID());
        $oParentObject = KTPermissionUtil::findRootObjectForPermissionObject($oPermissionObject);

        // Documents might be permission owner, but then they'd be the
        // only users of that permission object.
        if (is_a($oParentObject, 'Document')) {
            return true;
        }

        // If you're a document and your permission owner isn't a
        // document, that means it's some ancestor, and thus not you.
        if (is_a($oDocumentOrFolder, 'Document')) {
            return false;
        }

        // We're dealing with folders, so just compare IDs...
        if ($oDocumentOrFolder->getID() == $oParentObject->getID()) {
            return true;
        }
        return false;
    }
    // }}}

    // {{{ inheritPermissionObject
    /**
     * Inherits permission object from parent, throwing away our own
     * permission object.
     */
    function inheritPermissionObject(&$oDocumentOrFolder) {
        global $default;
        if (!KTPermissionUtil::isPermissionOwner($oDocumentOrFolder)) {
            return PEAR::raiseError("Document or Folder doesn't own its permission object");
        }
        $oOrigPO =& KTPermissionObject::get($oDocumentOrFolder->getPermissionObjectID());
        $oFolder =& Folder::get($oDocumentOrFolder->getParentID());
        $iNewPOID = $oFolder->getPermissionObjectID();
        $oNewPO =& KTPermissionObject::get($iNewPOID);

        
        $oDocumentOrFolder->setPermissionObjectID($iNewPOID);
        $oDocumentOrFolder->update();

        if (is_a($oDocumentOrFolder, 'Document')) {
            // If we're a document, no niggly children to worry about.
            //
            // Well, except for document versions, which we don't know
            // how to deal with yet, really.
            KTPermissionUtil::updatePermissionLookup($oDocumentOrFolder);
            return;
        }
        
        $iFolderID = $oDocumentOrFolder->getID();
        $sFolderIDs = Folder::generateFolderIDs($iFolderID);
        $sFolderIDs .= '%';
        $sQuery = "UPDATE $default->folders_table SET
            permission_object_id = ? WHERE permission_object_id = ? AND
            parent_folder_ids LIKE ?";
        $aParams = array($oNewPO->getID(), $oOrigPO->getID(), $sFolderIDs);
        DBUtil::runQuery(array($sQuery, $aParams));

        $sQuery = "UPDATE $default->documents_table SET
            permission_object_id = ? WHERE permission_object_id = ? AND
            parent_folder_ids LIKE ?";
        DBUtil::runQuery(array($sQuery, $aParams));

        KTPermissionUtil::updatePermissionLookupForPO($oNewPO);
    }
    // }}}

    // {{{ rebuildPermissionLookups
    function rebuildPermissionLookups($bEmptyOnly = false) {
        if (empty($bEmptyOnly)) {
            $sTable = KTUtil::getTableName('folders');
            $sQuery = sprintf("SELECT id FROM %s WHERE permission_lookup_id IS NULL AND permission_object_id IS NOT NULL", $sTable);
        } else {
            $sTable = KTUtil::getTableName('folders');
            $sQuery = sprintf("SELECT id FROM %s WHERE permission_object_id IS NOT NULL", $sTable);
        }
        $aIds = DBUtil::getResultArrayKey($sQuery, 'id');
        foreach ($aIds as $iId) {
            $oFolder =& Folder::get($iId);
            KTPermissionUtil::updatePermissionLookup($oFolder);
        }

        if (empty($bEmptyOnly)) {
            $sTable = KTUtil::getTableName('documents');
            $sQuery = sprintf("SELECT id FROM %s WHERE permission_lookup_id IS NULL", $sTable);
        } else {
            $sTable = KTUtil::getTableName('documents');
            $sQuery = sprintf("SELECT id FROM %s", $sTable);
        }
        $aIds = DBUtil::getResultArrayKey($sQuery, 'id');
        foreach ($aIds as $iId) {
            $oDocument =& Document::get($iId);
            KTPermissionUtil::updatePermissionLookup($oDocument);
        }

    }
    // }}}

    // {{{ getPermissionDescriptorsForUser
    function getPermissionDescriptorsForUser($oUser) {
        $aGroups = GroupUtil::listGroupsForUserExpand($oUser);
        $aPermissionDescriptors = KTPermissionDescriptor::getByGroups($aGroups, array('ids' => true));
        return $aPermissionDescriptors;
    }
    // }}}
}

class KTPermissionChannel {
    var $observers = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS, 'KT_PermissionChannel')) {
            $GLOBALS['KT_PermissionChannel'] = new KTPermissionChannel;
        }
        return $GLOBALS['KT_PermissionChannel'];
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

class KTPermissionGenericMessage {
    function KTPermissionGenericMessage($sMessage) {
        $this->sMessage = $sMessage;
    }

    function getString() {
        return $this->sMessage;
    }
}



?>
