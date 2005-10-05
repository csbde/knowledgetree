<?php

class UpgradeFunctions {
    var $upgrades = array(
        "2.0.0" => array("setPermissionFolder"),
        "2.0.6" => array("addTemplateMimeTypes"),
        "2.0.8" => array("setPermissionObject"),
        "2.99.1" => array("createFieldSets"),
    );

    var $descriptions = array(
        "rebuildSearchPermissions" => "Rebuild search permissions with updated algorithm",
        "setPermissionFolder" => "Set permission folder for each folder for simplified permissions management",
        "addTemplateMimeTypes" => "Add MIME types for Excel and Word templates",
        "setPermissionObject" => "Set the permission object in charge of a document or folder",
        "createFieldSets" => "Create a fieldset for each field without one",
    );
    var $phases = array(
        "setPermissionObject" => 1,
        "createFieldSets" => 1,
    );

    // {{{ _setPermissionFolder
    function _setPermissionFolder($oFolder) {
        global $default;
        $oInheritedFolder = $oFolder;
        while ($bFoundPermissions !== true) {
            /*ok*/$aCheckQuery = array('SELECT id FROM groups_folders_link WHERE folder_id = ? LIMIT 1', $oInheritedFolder->getID());
            if (count(DBUtil::getResultArrayKey($aCheckQuery, 'id')) == 0) {
                $default->log->debug('No direct permissions on folder ' . $oInheritedFolder->getID());
                $bInherited = true;
                $oInheritedFolder =& Folder::get($oInheritedFolder->getParentID());
                if ($oInheritedFolder === false) {
                    break;
                }
                // if our parent knows the permission folder, use that.

                $aQuery = array("SELECT permission_folder_id FROM folders WHERE id = ?", array($oInheritedFolder->getID()));
                $iPermissionFolderID = DBUtil::getOneResultKey($aQuery, 'permission_folder_id');
                if (!empty($iPermissionFolderID)) {
                    $aQuery = array(
                        "UPDATE folders SET permission_folder_id = ? WHERE id = ?",
                        array($iPermissionFolderID, $oFolder->getID())
                    );
                    DBUtil::runQuery($aQuery);
                    return;
                }
                $default->log->debug('... trying parent: ' . $oInheritedFolder->getID());
            } else {
                $default->log->debug('Found direct permissions on folder ' . $oInheritedFolder->getID());
                $iPermissionFolderID = $oInheritedFolder->getID();
                $aQuery = array(
                    "UPDATE folders SET permission_folder_id = ? WHERE id = ?",
                    array($iPermissionFolderID, $oFolder->getID())
                );
                DBUtil::runQuery($aQuery);
                return;
            }
        }

        $default->log->error('No permissions whatsoever for folder ' . $oFolder->getID());
        // 0, which can never exist, for non-existent.  null for not set yet (database upgrade).
        $iPermissionFolderID = 0;
        $aQuery = array(
            "UPDATE folders SET permission_folder_id = ? WHERE id = ?",
            array($iPermissionFolderID, $oFolder->getID())
        );
        DBUtil::runQuery($aQuery);
    }
    // }}}

    // {{{ setPermissionFolder
    function setPermissionFolder() {
        global $default;
        require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');

        $sQuery = "SELECT id FROM $default->folders_table WHERE permission_folder_id IS NULL";

        $aIDs = DBUtil::getResultArrayKey($sQuery, 'id');

        foreach ($aIDs as $iID) {
            $oFolder =& Folder::get($iID);
            UpgradeFunctions::_setPermissionFolder($oFolder);
        }
    }
    // }}}

    // {{{ addTemplateMimeTypes
    function addTemplateMimeTypes() {
        global $default;
        $table = $default->mimetypes_table;
        $query = sprintf('SELECT id FROM %s WHERE filetypes = ?',
                $table);

        $newTypes = array(
            array(
                'filetypes' => 'xlt',
                'mimetypes' => 'application/vnd.ms-excel',
                'icon_path' => 'icons/excel.gif',
            ),
            array(
                'filetypes' => 'dot',
                'mimetypes' => 'application/msword',
                'icon_path' => 'icons/word.gif',
            ),
        );
        foreach ($newTypes as $types) {
            $res = DBUtil::getOneResultKey(array($query, $types['filetypes']), 'id');
            if (PEAR::isError($res)) {
                return $res;
            }
            if (is_null($res)) {
                $res = DBUtil::autoInsert($table, $types);
                if (PEAR::isError($res)) {
                    return $res;
                }
            }
        }
        return true;
    }
    // }}}

    // {{{ _setRead
    function _setRead($iID, $oPO) {
        global $default;
        $oPermission = KTPermission::getByName('ktcore.permissions.read');
        $query = "SELECT group_id FROM $default->groups_folders_table WHERE folder_id = ? AND (can_read = ? OR can_write = ?)";
        $aParams = array($iID, true, true);
        $aGroupIDs = DBUtil::getResultArrayKey(array($query, $aParams), 'group_id');
        $aAllowed = array("group" => $aGroupIDs);
        KTPermissionUtil::setPermissionForID($oPermission, $oPO, $aAllowed);
    }
    // }}}

    // {{{ _setWrite
    function _setWrite($iID, $oPO) {
        global $default;
        $oPermission = KTPermission::getByName('ktcore.permissions.write');
        $query = "SELECT group_id FROM $default->groups_folders_table WHERE folder_id = ? AND can_write = ?";
        $aParams = array($iID, true);
        $aGroupIDs = DBUtil::getResultArrayKey(array($query, $aParams), 'group_id');
        $aAllowed = array("group" => $aGroupIDs);
        KTPermissionUtil::setPermissionForID($oPermission, $oPO, $aAllowed);
    }
    // }}}
    
    // {{{ _setAddFolder
    function _setAddFolder($iID, $oPO) {
        global $default;
        $oPermission = KTPermission::getByName('ktcore.permissions.addFolder');
        $query = "SELECT group_id FROM $default->groups_folders_table WHERE folder_id = ? AND can_write = ?";
        $aParams = array($iID, true);
        $aGroupIDs = DBUtil::getResultArrayKey(array($query, $aParams), 'group_id');
        $aAllowed = array("group" => $aGroupIDs);
        KTPermissionUtil::setPermissionForID($oPermission, $oPO, $aAllowed);
    }
    // }}}

    // {{{ setPermissionObject
    function setPermissionObject() {
        global $default;
        require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
        require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
        require_once(KT_LIB_DIR . '/permissions/permissionobject.inc.php');
        require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
        require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
        $query = "SELECT id FROM $default->folders_table WHERE permission_folder_id = id AND permission_object_id IS NULL";
        $aIDs = DBUtil::getResultArrayKey($query, 'id');
        foreach ($aIDs as $iID) {
            $oFolder =& Folder::get($iID);
            if (PEAR::isError($oFolder)) {
                var_dump($oFolder);
                exit(0);
            }
            if ($oFolder === false) {
                print "Could not find folder...\n";
                exit(0);
            }
            $oPO =& KTPermissionObject::createFromArray(array());
            if (PEAR::isError($oFolder)) {
                var_dump($oPO);
                exit(0);
            }
            $oFolder->setPermissionObjectID($oPO->getId());
            $oFolder->update();

            UpgradeFunctions::_setRead($iID, $oPO);
            UpgradeFunctions::_setWrite($iID, $oPO);
            UpgradeFunctions::_setAddFolder($iID, $oPO);
        }
        $query = "SELECT id FROM $default->folders_table WHERE permission_object_id IS NULL";
        $aIDs = DBUtil::getResultArrayKey($query, 'id');
        foreach ($aIDs as $iID) {
            $oFolder =& Folder::get($iID);
            $query = "SELECT permission_folder_id FROM $default->folders_table WHERE id = ?";
            $aParams = array($iID);
            $iPermissionFolderID = DBUtil::getOneResultKey(array($query, $aParams), 'permission_folder_id');
            $oPermissionFolder =& Folder::get($iPermissionFolderID);
            $oFolder->setPermissionObjectID($oPermissionFolder->getPermissionObjectId());
            $oFolder->update();
        }
        $query = "SELECT id FROM $default->documents_table WHERE permission_object_id IS NULL";
        $aIDs = DBUtil::getResultArrayKey($query, 'id');
        foreach ($aIDs as $iID) {
            $oDocument =& Document::get($iID);
            $oFolder =& Folder::get($oDocument->getFolderID());
            if ($oFolder === false) {
                continue;
            }
            $oDocument->setPermissionObjectID($oFolder->getPermissionObjectID());
            $oDocument->update();
        }

        $query = "SELECT id FROM $default->documents_table WHERE permission_lookup_id IS NULL AND permission_object_id IS NOT NULL";
        $aIDs = DBUtil::getResultArrayKey($query, 'id');
        foreach ($aIDs as $iID) {
            $oDocument =& Document::get($iID);
            KTPermissionUtil::updatePermissionLookup($oDocument);
        }

        $query = "SELECT id FROM $default->folders_table WHERE permission_lookup_id IS NULL";
        $aIDs = DBUtil::getResultArrayKey($query, 'id');
        foreach ($aIDs as $iID) {
            $oFolder =& Folder::get($iID);
            KTPermissionUtil::updatePermissionLookup($oFolder);
        }
    }
    // }}}

    // {{{ createFieldSets
    function createFieldSets () {
        global $default;
        require_once(KT_LIB_DIR . '/documentmanagement/DocumentField.inc');
        require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
        $aFields = DocumentField::getList("parent_fieldset IS NULL");
        foreach ($aFields as $oField) {
            $sName = $oField->getName();
            $sNamespace = 'local.' . str_replace(array(' '), array(), strtolower($sName));
            $iFieldId = $oField->getId();
            $oFieldSet = KTFieldset::createFromArray(array(
                'name' => $sName,
                'namespace' => $sNamespace,
                'mandatory' => false,
                'isconditional' => false,
                'masterfield' => $iFieldId,
            ));
            $iFieldSetId = $oFieldSet->getId();
            $oField->setParentFieldset($iFieldSetId);
            $oField->update();
            $sTable = KTUtil::getTableName('document_type_fields');
            $aQuery = array(
                "SELECT document_type_id FROM $sTable WHERE field_id = ?",
                array($iFieldId)
            );
            $aDocumentTypeIds = DBUtil::getResultArrayKey($aQuery, 'document_type_id');
            var_dump($aDocumentTypeIds);
            $sTable = KTUtil::getTableName('document_type_fieldsets');
            foreach ($aDocumentTypeIds as $iDocumentTypeId) {
                $res = DBUtil::autoInsert($sTable, array(
                    'document_type_id' => $iDocumentTypeId,
                    'fieldset_id' => $iFieldSetId,
                ));
                var_dump($res);
            }
        }
    }
    // }}}
}

?>
