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
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
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
        global $default, $lang_err_database;
        if ($iUserID == "") {
            $iUserID = $_SESSION["userID"];
        }
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
    
    /**
    * Checks if the current user is a unit administrator
    *
    * @return boolean true if the user is the unit administrator for the unit to which the folder belongs, false otherwise
    */
    function userIsUnitAdministrator($iUserID = "") {
        global $default;
        if ($iUserID == "") {
            $iUserID = $_SESSION["userID"];
        }        
        $sql = $default->db;
        $sql->query(array("SELECT UGL.group_id " ./*ok*/
            "FROM $default->users_groups_table AS UGL INNER JOIN $default->groups_units_table AS GUL ON GUL.group_id = UGL.group_id " .
            "INNER JOIN $default->groups_table AS GL ON GL.id = UGL.group_id " .
            "WHERE UGL.user_id = ? " .
            "AND GL.is_unit_admin = ?", array($iUserID, true)));
        return $sql->next_record();
    }
    
    /**
     * Checks if the current user is a guest user
     *
     * @return boolean true if the user is in the Anonymous group, else false
     */
    function userIsGuest($iUserID = "") {
        global $default;
        if ($iUserID == "") {
            $iUserID = $_SESSION["userID"];
        }        
        $sql = $default->db;
        // you're a guest user if you're in the Anonymous group
        $sql->query(array("SELECT UGL.group_id " ./*ok*/
             "FROM $default->users_groups_table AS UGL INNER JOIN $default->groups_table AS GL ON GL.id = UGL.group_id " .
             "WHERE GL.name = 'Anonymous' " .
             "AND UGL.user_id = ?", $iUserID));
        return $sql->next_record();        
    }
}

?>
