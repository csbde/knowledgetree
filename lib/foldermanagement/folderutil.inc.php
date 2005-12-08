<?php /* vim: set expandtab softtabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 * $Id$
 *
 * High-level folder operations
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

require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
require_once(KT_LIB_DIR . "/subscriptions/subscriptions.inc.php"); 

class KTFolderUtil {
    function _add ($oParentFolder, $sFolderName, $oUser) {
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $oFolder =& Folder::createFromArray(array(
            'name' => $sFolderName,
            'description' => $sFolderName,
            'parentid' => $oParentFolder->getID(),
            'creatorid' => $oUser->getID(),
            'unitid' => $oParentFolder->getUnitID(),
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

        // fire subscription alerts for the new folder
        $oSubscriptionEvent = new SubscriptionEvent();
        $oSubscriptionEvent->AddFolder($oFolder, $oParentFolder);
        return $oFolder;
    }

    function move($oFolder, $oNewParentFolder, $oUser) {
        if (KTFolderUtil::exists($oNewParentFolder, $oFolder->getName())) {
            return PEAR::raiseError("Folder with the same name already exists in the new parent folder");
        }
        $oStorage =& KTStorageManagerUtil::getSingleton();

        // First, deal with SQL, as it, at least, is guaranteed to be atomic
        $table = "folders";
        
        // Update the moved folder first...
        $sQuery = "UPDATE $table SET full_path = ?, parent_folder_ids = ?, parent_id = ? WHERE id = ?";
        $aParams = array(
            sprintf("%s/%s", $oNewParentFolder->getFullPath(), $oNewParentFolder->getName()),
            sprintf("%s,%s", $oNewParentFolder->getParentFolderIDs(), $oNewParentFolder->getID()),
            $oNewParentFolder->getID(),
            $oFolder->getID(),
        );
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }
        
        $sQuery = "UPDATE $table SET full_path = CONCAT(?, SUBSTRING(full_path FROM ?)), parent_folder_ids = CONCAT(?, SUBSTRING(parent_folder_ids FROM ?)) WHERE full_path LIKE ?";
        $aParams = array(
            sprintf("%s/%s", $oNewParentFolder->getFullPath(), $oNewParentFolder->getName()),
            strlen($oFolder->getFullPath()) + 1,
            sprintf("%s,%s", $oNewParentFolder->getParentFolderIDs(), $oNewParentFolder->getID()),
            strlen($oFolder->getParentFolderIDs()) + 1,
            sprintf("%s/%s%%", $oFolder->getFullPath(), $oFolder->getName()),
        );
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }

        $table = "documents";
        $sQuery = "UPDATE $table SET full_path = CONCAT(?, SUBSTRING(full_path FROM ?)) WHERE full_path LIKE ?";
        $aParams = array(
            sprintf("%s/%s", $oNewParentFolder->getFullPath(), $oNewParentFolder->getName()),
            strlen($oFolder->getFullPath()) + 1,
            sprintf("%s/%s%%", $oFolder->getFullPath(), $oFolder->getName()),
        );
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }

        $res = $oStorage->moveFolder($oFolder, $oNewParentFolder);
        if (PEAR::isError($res)) {
            return $res;
        }
        return;
    }

    function exists($oParentFolder, $sName) {
        return Folder::folderExistsName($sName, $oParentFolder->getID());
    }
}

?>
