<?php
/**
 * $Id$
 *
 * Contains static functions used to determine whether the current user:
 *  o has permission to perform certain actions
 *  o has a certain role
 *  o is assigned to a certain group
 *  o has read/write access for a specific folder/directory
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
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
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 *
 * @version $Revision$
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.security
 */

require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

class Permission {
    
    /**
    * Checks if the current user has write permission for a specific document.
    *
    * @param $oDocument       Document to check
    *
    * @return boolean true if the current user has document write permission, false otherwise and set $_SESSION["errorMessage"]
    */  
    function userHasDocumentWritePermission($oDocument) {       
        $oUser = User::get($_SESSION["userID"]);
        $oPermission = KTPermission::getByName('ktcore.permissions.write');

        return KTPermissionUtil::userHasPermissionOnItem($oUser,
                $oPermission, $oDocument);
    }

    /**
    * Checks if the current user has read permission for a specific
    * document
    *
    * @param $oFolder     Document object to check
    *
    * @return boolean true if the user has document write permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasDocumentReadPermission($oDocument) {        
        $oUser = User::get($_SESSION["userID"]);
        $oPermission = KTPermission::getByName('ktcore.permissions.read');

        return KTPermissionUtil::userHasPermissionOnItem($oUser,
                $oPermission, $oDocument);
    }
    
    /**
    * Checks if the current user has write permission for a specific folder
    *   
    * @param $oFolder     Folder object to check
    *
    * @return boolean true if the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasFolderWritePermission($oFolder) {       
        $oUser = User::get($_SESSION["userID"]);
        $oPermission = KTPermission::getByName('ktcore.permissions.write');

        return KTPermissionUtil::userHasPermissionOnItem($oUser,
                $oPermission, $oFolder);
    }
    
    
    /**
    * Checks if the current user has read permission for a specific folder
    *
    * @param $oFolder     Folder object to check
    *
    * @return boolean true if the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasFolderReadPermission($oFolder) {        
        $oUser = User::get($_SESSION["userID"]);
        $oPermission = KTPermission::getByName('ktcore.permissions.read');

        return KTPermissionUtil::userHasPermissionOnItem($oUser,
                $oPermission, $oFolder);
    }
    
    /**
    * Check if the current user is a system administrator
    *
    * @return boolean true is user is system administrator, false otherwise and set $_SESSION["errorMessage"]
    *
    */
    function userIsSystemAdministrator($iUserID = "") {
        global $default;
        if ($iUserID == "") {
            $iUserID = $_SESSION["userID"];
        }
        $iUserID = KTUtil::getId($iUserID);
        $sql = $default->db;
        $sql->query(array("SELECT UGL.group_id " . /*ok*/
                    "FROM $default->users_groups_table AS UGL INNER JOIN $default->groups_table AS GL ON UGL.group_id = GL.id " .
                    "WHERE UGL.user_id = ? " .
                    "AND is_sys_admin = ?", array($iUserID, true)));
        if ($sql->next_record()) {
            return true;
        }
        return false;
    }
    
    function isUnitAdministratorForFolder($oUser, $oFolder) {
        $oFolder =& KTUtil::getObject('Folder', $oFolder);
        $oUser =& KTUtil::getObject('User', $oUser);
        $sUnitTable = KTUtil::getTableName('units');
        if (PEAR::isError($oFolder)) {  // can't be admin for a non-existant folder.
            return false;
        }
        $sFolderIds = $oFolder->getParentFolderIds();
        $aFolderIds = split(",", $sFolderIds);
        $aFolderIds[] = $oFolder->getId();

        $sParams = DBUtil::paramArray($aFolderIds);
        $aParams = $aFolderIds;
        $sQuery = "SELECT id FROM $sUnitTable WHERE folder_id IN ($sParams)";
        $aUnitIds = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');

        foreach($aUnitIds as $iUnitId) {
            $aGroups = Group::getUnitAdministratorGroupsByUnit($iUnitId);
            foreach ($aGroups as $oGroup) {
                if ($oGroup->hasMember($oUser)) {
                    return true;
                }
            }
        }
        return false;
    }
}

?>
