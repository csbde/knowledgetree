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

class UpgradeFunctions {
    var $upgrades = array(
        "2.0.0" => array("setPermissionFolder"),
        "2.0.6" => array("addTemplateMimeTypes"),
        "2.0.8" => array("setPermissionObject"),
        "2.99.1" => array("createFieldSets"),
        "2.99.7" => array("normaliseDocuments", "applyDiscussionUpgrade"),
        "2.99.8" => array("fixUnits"),
        "2.99.9" => array("createLdapAuthenticationProvider", "createSecurityDeletePermissions"),
    );

    var $descriptions = array(
        "rebuildSearchPermissions" => "Rebuild search permissions with updated algorithm",
        "setPermissionFolder" => "Set permission folder for each folder for simplified permissions management",
        "addTemplateMimeTypes" => "Add MIME types for Excel and Word templates",
        "setPermissionObject" => "Set the permission object in charge of a document or folder",
        "createFieldSets" => "Create a fieldset for each field without one",
        "normaliseDocuments" => "Normalise the documents table",
        "createLdapAuthenticationProvider" => "Create an LDAP authentication source based on your KT2 LDAP settings (must keep copy of config/environment.php to work)",
        'createSecurityDeletePermissions' => 'Create the Core: Manage Security and Core: Delete permissions',
    );
    var $phases = array(
        "setPermissionObject" => 1,
        "createFieldSets" => 1,
        "normaliseDocuments" => 1,
        "fixUnits" => 1,
        'applyDiscussionUpgrade' => -1,
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
        require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
        require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
        $sTable = 'groups_folders_link';
        $oPermission = KTPermission::getByName('ktcore.permissions.read');
        $query = "SELECT group_id FROM $sTable WHERE folder_id = ? AND (can_read = ? OR can_write = ?)";
        $aParams = array($iID, true, true);
        $aGroupIDs = DBUtil::getResultArrayKey(array($query, $aParams), 'group_id');
        $aAllowed = array("group" => $aGroupIDs);
        KTPermissionUtil::setPermissionForID($oPermission, $oPO, $aAllowed);
    }
    // }}}

    // {{{ _setWrite
    function _setWrite($iID, $oPO) {
        require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
        require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
        $sTable = 'groups_folders_link';
        $oPermission = KTPermission::getByName('ktcore.permissions.write');
        $query = "SELECT group_id FROM $sTable WHERE folder_id = ? AND can_write = ?";
        $aParams = array($iID, true);
        $aGroupIDs = DBUtil::getResultArrayKey(array($query, $aParams), 'group_id');
        $aAllowed = array("group" => $aGroupIDs);
        KTPermissionUtil::setPermissionForID($oPermission, $oPO, $aAllowed);
    }
    // }}}
    
    // {{{ _setAddFolder
    function _setAddFolder($iID, $oPO) {
        require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
        require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
        $sTable = 'groups_folders_link';
        $oPermission = KTPermission::getByName('ktcore.permissions.addFolder');
        $query = "SELECT group_id FROM $sTable WHERE folder_id = ? AND can_write = ?";
        $aParams = array($iID, true);
        $aGroupIDs = DBUtil::getResultArrayKey(array($query, $aParams), 'group_id');
        $aAllowed = array("group" => $aGroupIDs);
        KTPermissionUtil::setPermissionForID($oPermission, $oPO, $aAllowed);
    }
    // }}}

    // {{{ setPermissionObject
    function setPermissionObject() {
        global $default;
        require_once(KT_LIB_DIR . '/permissions/permissionobject.inc.php');


        // First, set permission object on all folders that were
        // "permission folders".
        $query = "SELECT id FROM $default->folders_table WHERE permission_folder_id = id AND permission_object_id IS NULL";
        $aIDs = DBUtil::getResultArrayKey($query, 'id');
        foreach ($aIDs as $iID) {
            $oPO =& KTPermissionObject::createFromArray(array());
            if (PEAR::isError($oPO)) {
                var_dump($oPO);
                exit(0);
            }
            $sTableName = KTUtil::getTableName('folders');
            $query = sprintf("UPDATE %s SET permission_object_id = %d WHERE id = %d", $sTableName, $oPO->getId(), $iID);
            $res = DBUtil::runQuery($query);

            UpgradeFunctions::_setRead($iID, $oPO);
            UpgradeFunctions::_setWrite($iID, $oPO);
            UpgradeFunctions::_setAddFolder($iID, $oPO);
        }

        // Next, set permission object on all folders that weren't
        // "permission folders" by using the permission object on their
        // permission folders.
        $query = "SELECT id FROM $default->folders_table WHERE permission_object_id IS NULL";
        $aIDs = DBUtil::getResultArrayKey($query, 'id');
        foreach ($aIDs as $iID) {
            $sTableName = KTUtil::getTableName('folders');
            $query = sprintf("SELECT F2.permission_object_id AS poi FROM %s AS F LEFT JOIN %s AS F2 WHERE F2.id = F.permission_folder_id WHERE id = ?", $sTableName, $sTableName);
            $aParams = array($iID);
            $iPermissionObjectId = DBUtil::getOneResultKey(array($query, $aParams), 'poi');

            $sTableName = KTUtil::getTableName('folders');
            $query = sprintf("UPDATE %s SET permission_object_id = %d WHERE id = %d", $sTableName, $iPermissionObjectId, $iID);
            DBUtil::runQuery($query);
        }


        $sDocumentsTable = KTUtil::getTableName('documents');
        $sFoldersTable = KTUtil::getTableName('folders');

        $query = sprintf("UPDATE %s AS D, %s AS F SET D.permission_object_id = F.permission_object_id WHERE D.folder_id = F.id AND D.permission_object_id IS NULL", $sDocumentsTable, $sFoldersTable);
        DBUtil::runQuery($query);
    }
    // }}}

    // {{{ createFieldSets
    function createFieldSets () {
        global $default;
        require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');

        $sFieldsTable = KTUtil::getTableName('document_fields');
        $sQuery = sprintf("SELECT id, name, is_generic FROM %s", $sFieldsTable);
        $aFields = DBUtil::getResultArray($sQuery);

        foreach ($aFields as $aField) {
            $sName = $aField['name'];
            $sNamespace = 'local.' . str_replace(array(' '), array(), strtolower($sName));
            $iFieldId = $aField['id'];
            $bIsGeneric = $aField['is_generic'];
            $sFieldsetsTable = KTUtil::getTableName('fieldsets');
            $iFieldsetId = DBUtil::autoInsert($sFieldsetsTable, array(
                'name' => $sName,
                'namespace' => $sNamespace,
                'mandatory' => false,
                'is_conditional' => false,
                'master_field' => $iFieldId,
                'is_generic' => $bIsGeneric,
            ));
            if (PEAR::isError($iFieldsetId)) {
                return $iFieldsetId;
            }

            $sQuery = sprintf("UPDATE %s SET parent_fieldset = ? WHERE id = ?", $sFieldsTable);
            $aParams = array($iFieldsetId, $iFieldId);
            $res = DBUtil::runQuery(array($sQuery, $aParams));
            if (PEAR::isError($res)) {
                return $res;
            }

            $sTable = KTUtil::getTableName('document_type_fields');
            $aQuery = array(
                "SELECT document_type_id FROM $sTable WHERE field_id = ?",
                array($iFieldId)
            );
            $aDocumentTypeIds = DBUtil::getResultArrayKey($aQuery, 'document_type_id');
            $sTable = KTUtil::getTableName('document_type_fieldsets');
            foreach ($aDocumentTypeIds as $iDocumentTypeId) {
                $res = DBUtil::autoInsert($sTable, array(
                    'document_type_id' => $iDocumentTypeId,
                    'fieldset_id' => $iFieldsetId,
                ));
                if (PEAR::isError($res)) {
                    return $res;
                }
            }
        }
    }
    // }}}

    // {{{ normaliseDocuments
    function normaliseDocuments() {
        $sDocumentsTable = KTUtil::getTableName('documents');
        DBUtil::runQuery("SET FOREIGN_KEY_CHECKS=0");
        $aDocuments = DBUtil::getResultArray("SELECT * FROM $sDocumentsTable WHERE metadata_version_id IS NULL");
        $oConfig = KTConfig::getSingleton();

        foreach ($aDocuments as $aRow) {
            $aMetadataVersionIds = array();
            $sTransTable = KTUtil::getTableName("document_transactions");
            $sQuery = "SELECT DISTINCT version, datetime, user_id FROM $sTransTable WHERE document_id = ? AND transaction_namespace = ?";
            $aParams = array($aRow['id'], 'ktcore.transactions.check_out');
            $sCurrentVersion = sprintf("%d.%d", $aRow['major_version'], $aRow['minor_version']);
            $aVersions = DBUtil::getResultArray(array($sQuery, $aParams));

            $iMetadataVersion = 0;
            foreach ($aVersions as $sVersionInfo) {
                $sVersion = $sVersionInfo['version'];
                $sDate = $sVersionInfo['datetime'];
                $iUserId = $sVersionInfo['user_id'];
                $aVersionSplit = split("\.", $sVersion);
                $iMajor = $aVersionSplit[0];
                $iMinor = $aVersionSplit[1];
                $sStoragePath = $aRow['storage_path'] . "-" . $sVersion;
                $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $sStoragePath);
                
                if ($sCurrentVersion == $sVersion) {
                    continue;
                }

                if (file_exists($sPath)) {
                    $iFileSize = filesize($sPath);
                } else {
                    $iFileSize = $aRow['size'];
                }

                $aContentInfo = array(
                    'document_id' => $aRow['id'],
                    'filename' => $aRow['filename'],
                    'size' => $iFileSize,
                    'mime_id' => $aRow['mime_id'],
                    'major_version' => $iMajor,
                    'minor_version' => $iMinor,
                    'storage_path' => $sStoragePath,
                );
                $iContentId = DBUtil::autoInsert(KTUtil::getTableName('document_content_version'), $aContentInfo);
                $aMetadataInfo = array(
                    'document_id' => $aRow['id'],
                    'content_version_id' => $iContentId,
                    'document_type_id' => $aRow['document_type_id'],
                    'name' => $aRow['name'],
                    'description' => $aRow['description'],
                    'status_id' => $aRow['status_id'],
                    'metadata_version' => $iMetadataVersion,
                    'version_created' => $sDate,
                    'version_creator_id' => $iUserId,
                );
                $iMetadataId = DBUtil::autoInsert(KTUtil::getTableName('document_metadata_version'), $aMetadataInfo);
                $aMetadataVersionIds[] = $iMetadataId;
                $iMetadataVersion++;
            }
            $aContentInfo = array(
                'document_id' => $aRow['id'],
                'filename' => $aRow['filename'],
                'size' => $aRow['size'],
                'mime_id' => $aRow['mime_id'],
                'major_version' => $aRow['major_version'],
                'minor_version' => $aRow['minor_version'],
                'storage_path' => $aRow['storage_path'],
            );
            $iContentId = DBUtil::autoInsert(KTUtil::getTableName('document_content_version'), $aContentInfo);
            $aMetadataInfo = array(
                'document_id' => $aRow['id'],
                'content_version_id' => $iContentId,
                'document_type_id' => $aRow['document_type_id'],
                'name' => $aRow['name'],
                'description' => $aRow['description'],
                'status_id' => $aRow['status_id'],
                'metadata_version' => $iMetadataVersion,
                'version_created' => $aRow['modified'],
                'version_creator_id' => $aRow['modified_user_id'],
            );
            $iMetadataId = DBUtil::autoInsert(KTUtil::getTableName('document_metadata_version'), $aMetadataInfo);
            $aMetadataVersionIds[] = $iMetadataId;
            if (PEAR::isError($iMetadataId)) {
                var_dump($iMetadataId);
            }

            $sDFLTable = KTUtil::getTableName('document_fields_link');
            $aInfo = DBUtil::getResultArray(array("SELECT document_field_id, value FROM $sDFLTable WHERE metadata_version_id IS NULL AND document_id = ?", array($aRow['id'])));
            foreach ($aInfo as $aInfoRow) {
                unset($aInfoRow['id']);
                foreach ($aMetadataVersionIds as $iMetadataVersionId) {
                    $aInfoRow['metadata_version_id'] = $iMetadataVersionId;
                    DBUtil::autoInsert($sDFLTable, $aInfoRow);
                }
            }
            DBUtil::runQuery(array("UPDATE $sDocumentsTable SET metadata_version_id = ? WHERE id = ?", array($iMetadataId, $aRow['id'])));
            DBUtil::runQuery(array("DELETE FROM $sDFLTable WHERE metadata_version_id IS NULL AND document_id = ?", array($aRow['id'])));
        }
        DBUtil::runQuery("SET FOREIGN_KEY_CHECKS=1");
        
    }
    // }}}

    // {{{ applyDiscussionUpgrade
    function applyDiscussionUpgrade() {
        $sUpgradesTable = KTUtil::getTableName('upgrades');
        $bIsVersionApplied = DBUtil::getOneResultKey("SELECT MAX(result) AS result FROM $sUpgradesTable WHERE descriptor = 'upgrade*2.99.7*99*upgrade2.99.7'", "result");
        if (empty($bIsVersionApplied)) {
            // print "Version is not applied!<br />\n";
            return;
        }

        $bIsDiscussionApplied = DBUtil::getOneResultKey("SELECT MAX(result) AS result FROM $sUpgradesTable WHERE descriptor = 'sql*2.99.7*0*2.99.7/discussion.sql'", "result");
        if (!empty($bIsDiscussionApplied)) {
            // print "Discussion is applied!<br />\n";
            return;
        }
        // print "Discussion is not applied!<br />\n";

        $f = array(
            'descriptor' => 'sql*2.99.7*0*2.99.7/discussion.sql',
            'result' => true,
        );
        $res = DBUtil::autoInsert($sUpgradesTable, $f);
        return;
    }
    // }}}

    // {{{ fixUnits
    function fixUnits() {
        // First, assign the unit to a group directly on the group
        // table, not via the group_units table, since groups could only
        // belong to a single unit anyway.
        $sGULTable = KTUtil::getTableName("groups_units");
        $sGroupsTable = KTUtil::getTableName('groups');
        $aGroupUnits = DBUtil::getResultArray("SELECT group_id, unit_id FROM $sGULTable");
        foreach ($aGroupUnits as $aRow) {
            // $curunit = DBUtil::getOneResultKey(array("SELECT unit_id FROM $sGroupsTable WHERE id = ?", array($aRow['group_id'])), "unit_id");
            DBUtil::autoUpdate($sGroupsTable, array('unit_id' => $aRow['unit_id']), $aRow['group_id']);
        }

        // Now, assign the unit folder id to the unit directly, instead
        // of storing the unit_id on every folder beneath the unit
        // folder.
        $sFoldersTable = KTUtil::getTableName('folders');
        $sUnitsTable = KTUtil::getTableName('units');
        $sQuery = "SELECT id FROM folders WHERE unit_id = ? ORDER BY LENGTH(parent_folder_ids) LIMIT 1";
        $aUnitIds = DBUtil::getResultArrayKey("SELECT id FROM $sUnitsTable", 'id');
        foreach ($aUnitIds as $iUnitId) {
            $aParams = array($iUnitId);
            $iFolderId = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
            if (!empty($iFolderId)) {
                DBUtil::autoUpdate($sUnitsTable, array('folder_id' => $iFolderId), $iUnitId);
            }
        }
        return true;
    }
    // }}}

    // {{{ createLdapAuthenticationProvider
    function createLdapAuthenticationProvider() {
        if (!file_exists(KT_DIR . '/config/environment.php')) {
            return;
        }
        global $default;
        $new_default = $default;
        $default = null;
        require_once(KT_DIR . '/config/environment.php');
        $old_default = $default;
        $default = $new_default;
        if ($old_default->authenticationClass !== "LDAPAuthenticator") {
            return;
        }
        $sName = "Autocreated by upgrade";
        $sNamespace = KTUtil::nameToLocalNamespace("authenticationsources", $sName);
        $aConfig = array(
            'searchattributes' => split(',', 'cn,mail,sAMAccountName'),
            'objectclasses' => split(',', 'user,inetOrgPerson,posixAccount'),
            'servername' => $old_default->ldapServer,
            'basedn' => $old_default->ldapRootDn,
            'searchuser' => $old_default->ldapSearchUser,
            'searchpassword' => $old_default->ldapSearchPassword,
        );
        if ($old_default->ldapServerType == "ActiveDirectory") {
            $sProvider = "ktstandard.authentication.adprovider" ;
        } else {
            $sProvider = "ktstandard.authentication.ldapprovider" ;
        }

        require_once(KT_LIB_DIR . '/authentication/authenticationsource.inc.php');
        $oSource = KTAuthenticationSource::createFromArray(array(
            'name' => $sName,
            'namespace' => $sNamespace,
            'config' => serialize($aConfig),
            'authenticationprovider' => $sProvider,
        ));

        if (PEAR::isError($oSource)) {
            return $oSource;
        }

        $sUsersTable = KTUtil::getTableName('users');
        $sQuery = "UPDATE $sUsersTable SET authentication_source_id = ? WHERE authentication_source_id IS NULL AND LENGTH(authentication_details_s1)";
        $aParams = array($oSource->getId());
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        return $res;
    }
    // }}}

    // {{{ createSecurityDeletePermissions
    function createSecurityDeletePermissions() {
        $sPermissionsTable = KTUtil::getTableName('permissions');
        $aPermissionInfo = array(
            'human_name' => 'Core: Manage security',
            'name' => 'ktcore.permissions.security',
            'built_in' => true,
        );
        $res = DBUtil::autoInsert($sPermissionsTable, $aPermissionInfo);
        if (PEAR::isError($res)) {
            return $res;
        }
        $iSecurityPermissionId = $res;

        $aPermissionInfo = array(
            'human_name' => 'Core: Delete',
            'name' => 'ktcore.permissions.delete',
            'built_in' => true,
        );
        $res = DBUtil::autoInsert($sPermissionsTable, $aPermissionInfo);
        if (PEAR::isError($res)) {
            return $res;
        }
        $iDeletePermissionId = $res;

        $sQuery = "SELECT id FROM $sPermissionsTable WHERE name = ?";
        $aParams = array("ktcore.permissions.write");
        $iWritePermissionId = DBUtil::getOneResultKey(array($sQuery, $aParams), "id");

        $sPermissionAssignmentsTable = KTUtil::getTableName('permission_assignments');
        $sQuery = "SELECT permission_object_id, permission_descriptor_id FROM $sPermissionAssignmentsTable WHERE permission_id = ?";
        $aParams = array($iWritePermissionId);
        $aRows = DBUtil::getResultArray(array($sQuery, $aParams));
        foreach ($aRows as $aRow) {
            $aRow['permission_id'] = $iSecurityPermissionId;
            DBUtil::autoInsert($sPermissionAssignmentsTable, $aRow);
            $aRow['permission_id'] = $iDeletePermissionId;
            DBUtil::autoInsert($sPermissionAssignmentsTable, $aRow);
        }
        $sDocumentTable = KTUtil::getTableName('documents');
        $sFolderTable = KTUtil::getTableName('folders');
        DBUtil::runQuery("UPDATE $sDocumentTable SET permission_lookup_id = NULL");
        DBUtil::runQuery("UPDATE $sFolderTable SET permission_lookup_id = NULL");
    }
    // }}}
}

?>
