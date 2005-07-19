<?php

class UpgradeFunctions {
    var $upgrades = array(
        "2.0.0" => array("setPermissionFolder", "rebuildSearchPermissions"),
        "2.0.6" => array("addTemplateMimeTypes"),
    );
    var $descriptions = array(
        "rebuildSearchPermissions" => "Rebuild search permissions with updated algorithm",
        "setPermissionFolder" => "Set permission folder for each folder for simplified permissions management",
        "addTemplateMimeTypes" => "Add MIME types for Excel and Word templates",
    );
    function setPermissionFolder() {
        global $default;
        require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');

        $sQuery = "SELECT id FROM $default->folders_table WHERE permission_folder_id IS NULL";

        $aIDs = DBUtil::getResultArrayKey($sQuery, 'id');

        foreach ($aIDs as $iID) {
            $oFolder =& Folder::get($iID);
            $oFolder->calculatePermissionFolder();
            $oFolder->update();
        }
    }

    function rebuildSearchPermissions() {
        require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
        require_once(KT_LIB_DIR . '/security/Permission.inc');

        $aDocuments = Document::getList();
        foreach ($aDocuments as $oDocument) {
            Permission::updateSearchPermissionsForDocument($oDocument->getID());
        }
        return true;
    }

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
}

?>
