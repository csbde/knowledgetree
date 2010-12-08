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

require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/security/PermissionCache.php');

class Permission {

    /**
    * Checks if the current user has write permission for a specific document.
    *
    * @param $oDocument       Document to check
    *
    * @return boolean true if the current user has document write permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasDocumentWritePermission($oDocument)
    {
        KTUtil::startTiming(__FUNCTION__);

    	if ($oDocument->getImmutable())
		{
			return false;
		}

		if ($oDocument->getIsCheckedOut())
		{
			if ($oDocument->getCheckedOutUserID() != $_SESSION["userID"])
			{
				return false;
			}
		}

		$user_id = $_SESSION["userID"];
        $permission = 'ktcore.permissions.write';

        $result = KTPermissionUtil::userHasPermissionOnItem($user_id, $permission, $oDocument);

        KTUtil::logTiming(__FUNCTION__);
        return $result;
    }

    /**
    * Checks if the current user has read permission for a specific
    * document
    *
    * @param $oFolder     Document object to check
    *
    * @return boolean true if the user has document write permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasDocumentReadPermission($oDocument)
    {
        KTUtil::startTiming(__FUNCTION__);
        $user_id = $_SESSION["userID"];
        $permission = 'ktcore.permissions.read';

        $result = KTPermissionUtil::userHasPermissionOnItem($user_id, $permission, $oDocument);

        KTUtil::logTiming(__FUNCTION__);
        return $result;
    }

    /**
    * Checks if the current user has delete document permission
    *
    * @param $oDocument     Document object to check
    * @return boolean true if the user has delete document permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasDeleteDocumentPermission($oDocument)
    {
        KTUtil::startTiming(__FUNCTION__);

        $user_id = $_SESSION["userID"];
        $permission = 'ktcore.permissions.delete';

        $result = KTPermissionUtil::userHasPermissionOnItem($user_id, $permission, $oDocument);

        KTUtil::logTiming(__FUNCTION__);
        return $result;
    }

    /**
    * Checks if the current user has write permission for a specific folder
    *
    * @param $oFolder     Folder object to check
    *
    * @return boolean true if the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasFolderWritePermission($oFolder)
    {
        KTUtil::startTiming(__FUNCTION__);

        $user_id = $_SESSION["userID"];
        $permission = 'ktcore.permissions.write';

        $result = KTPermissionUtil::userHasPermissionOnItem($user_id, $permission, $oFolder);

        KTUtil::logTiming(__FUNCTION__);
        return $result;
    }


    /**
    * Checks if the current user has read permission for a specific folder
    *
    * @param $oFolder     Folder object to check
    *
    * @return boolean true if the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasFolderReadPermission($oFolder)
    {
        KTUtil::startTiming(__FUNCTION__);

        $user_id = $_SESSION["userID"];
        $permission = 'ktcore.permissions.read';

        $result = KTPermissionUtil::userHasPermissionOnItem($user_id, $permission, $oFolder);

        KTUtil::logTiming(__FUNCTION__);
        return $result;
    }

	/**
    * Checks if the current user has add folder permission for a specific folder
    *
    * @param $oFolder     Folder object to check
    *
    * @return boolean true if the user has add folder permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasAddFolderPermission($oFolder)
    {

        KTUtil::startTiming(__FUNCTION__);

        $user_id = $_SESSION["userID"];
        $permission = 'ktcore.permissions.addFolder';

        $result = KTPermissionUtil::userHasPermissionOnItem($user_id, $permission, $oFolder);

        KTUtil::logTiming(__FUNCTION__);
        return $result;
    }

    /**
    * Checks if the current user has rename folder permission for a specific folder
    *
    * @param $oFolder     Folder object to check
    * @return boolean true if the user has rename folder permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasRenameFolderPermission($oFolder)
    {
        KTUtil::startTiming(__FUNCTION__);

        $user_id = $_SESSION["userID"];
        $permission = 'ktcore.permissions.folder_rename';

        $result = KTPermissionUtil::userHasPermissionOnItem($user_id, $permission, $oFolder);

        KTUtil::logTiming(__FUNCTION__);
        return $result;
    }

    /**
    * Checks if the current user has delete folder permission for a specific folder
    *
    * @param $oFolder     Folder object to check
    * @return boolean true if the user has delete folder permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasDeleteFolderPermission($oFolder)
    {
        KTUtil::startTiming(__FUNCTION__);

        $user_id = $_SESSION["userID"];
        $permission = 'ktcore.permissions.delete';

        $result = KTPermissionUtil::userHasPermissionOnItem($user_id, $permission, $oFolder);

        KTUtil::logTiming(__FUNCTION__);
        return $result;
    }

    /**
    * Checks if the current user has security folder permission for a specific folder in order to update the folder permissions
    *
    * @param $oFolder     Folder object to check
    * @return boolean true if the user has security folder permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasSecurityFolderPermission($oFolder)
    {
        KTUtil::startTiming(__FUNCTION__);

	    $user_id = $_SESSION["userID"];
        $permission = 'ktcore.permissions.security';

        $result = KTPermissionUtil::userHasPermissionOnItem($user_id, $permission, $oFolder);

        KTUtil::logTiming(__FUNCTION__);
        return $result;
    }

    /**
    * Check if the current user is a system administrator
    *
    * @return boolean true is user is system administrator, false otherwise and set $_SESSION["errorMessage"]
    *
    */
    function userIsSystemAdministrator($iUserID = '') {
        global $default;
        if ($iUserID == '') {
            $iUserID = $_SESSION['userID'];
        }
        if (empty($iUserID)) {
        	return false;
        }
        $iUserID = KTUtil::getId($iUserID);
        if (PEAR::isError($iUserID))
        {
        	return false;
        }

        $query = 'SELECT UGL.group_id
            FROM '.$default->users_groups_table.' AS UGL
            INNER JOIN '.$default->groups_table.' AS GL ON UGL.group_id = GL.id
            WHERE UGL.user_id = '.$iUserID.' AND is_sys_admin = true';

        $res = DBUtil::getOneResult($query);

        if(!PEAR::isError($res) && !empty($res)){
            if($res['group_id']){
                return true;
            }
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

    /**
     * Tell us if the administrator is in admin mode
     *
     * @return bool
     */
    static function adminIsInAdminMode()
    {
    	if (!Permission::userIsSystemAdministrator())
    	{
    		return false;
    	}
    	return isset($_SESSION['adminmode']) && ($_SESSION['adminmode']+0);
    }

}

?>
