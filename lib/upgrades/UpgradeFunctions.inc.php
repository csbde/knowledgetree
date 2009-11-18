<?php
/**
 * Implements the functions run during upgrades
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
 *
 * @copyright 2008-2009, KnowledgeTree Inc.
 * @license GNU General Public License version 3
 * @author KnowledgeTree Team
 * @package KnowledgeTree
 */

//debugger_start_debug();

require_once(KT_LIB_DIR . '/upgrades/Ini.inc.php');
require_once(KT_DIR . '/plugins/ktcore/scheduler/scheduler.php');
require_once(KT_LIB_DIR . '/database/schema.inc.php');

class UpgradeFunctions {
    var $upgrades = array(
            '2.0.0' => array('setPermissionFolder'),
            '2.0.6' => array('addTemplateMimeTypes'),
            '2.0.8' => array('setPermissionObject'),
            '2.99.1' => array('createFieldSets'),
            '2.99.7' => array('normaliseDocuments', 'applyDiscussionUpgrade'),
            '2.99.8' => array('fixUnits'),
            '2.99.9' => array('createLdapAuthenticationProvider', 'createSecurityDeletePermissions'),
            '3.0.1.3' => array('addTransactionTypes3013'),
            '3.0.1.4' => array('createWorkflowPermission'),
            '3.0.2' => array('fixDocumentRoleAllocation'),
            '3.0.3.2' => array('createFolderDetailsPermission'),
            '3.0.3.3' => array('generateWorkflowTriggers'),
            '3.0.3.7' => array('rebuildAllPermissions'),
            '3.1.5' => array('upgradeSavedSearches'),
            '3.1.6.3' => array('cleanupGroupMembership'),
            '3.5.0' => array('cleanupOldKTAdminVersionNotifier', 'updateConfigFile35', 'registerIndexingTasks'),
            '3.5.2' => array('setStorageEngine','dropForeignKeys','dropPrimaryKeys','dropIndexes','createPrimaryKeys','createForeignKeys','createIndexes', 'removeSlashesFromObjects'),
            '3.5.3' => array('moveConfigSettingsToDB','removeAdminVersionNotifier','removeOldSearchPlugins','addAutoIncrementToTables', 'addAutoIncrementToTables2'),
            '3.5.4' => array('createIndexes', 'updateServerConfigSettings','removeOldFilesAndFolders354','removeOldFilesAndFolders354a','removeOldFilesAndFolders354a1'),
            '3.6.1' => array('removeSlashesFromObjects')
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
            'addTransactionTypes3013' => 'Add new folder transaction types',
            'createWorkflowPermission' => 'Create the Core: Manage Workflow',
            'fixDocumentRoleAllocation' => 'Fix the document role allocation upgrade from 3.0.1',
            'createFolderDetailsPermission' => 'Create the Core: Folder Details permission',
            'generateWorkflowTriggers' => 'Migrate old in-transition guards to triggers',
            'rebuildAllPermissions' => 'Rebuild all permissions to ensure correct functioning of permission-definitions.',
            'upgradeSavedSearches' => 'Upgrade saved searches to use namespaces instead of integer ids',
            'cleanupGroupMembership' => 'Cleanup any old references to missing groups, etc.',
            'cleanupOldKTAdminVersionNotifier' => 'Cleanup any old files from the old KTAdminVersionNotifier',
            'updateConfigFile35' => 'Update the config.ini file for 3.5',
            'registerIndexingTasks'=>'Register the required indexing background tasks',
            'setStorageEngine'=>'Recreate db integrity: Set storage engine to InnoDB for transaction safety',
            'dropForeignKeys'=>'Recreate db integrity: Drop foreign keys on the database',
            'dropPrimaryKeys'=>'Recreate db integrity:Drop primary keys on the database',
            'dropIndexes'=>'Recreate db integrity:Drop indexes on the database',
            'createPrimaryKeys'=>'Recreate db integrity:Create primary keys on the database',
            'createForeignKeys'=>'Recreate db integrity:Create foreign keys on the database',
            'createIndexes'=>'Recreate db integrity:Create indexes on the database',
            'removeSlashesFromObjects'=>'Remove slashes from documents and folders',
            'moveConfigSettingsToDB' => 'Move the configuration settings from the config.ini file into the new database table.',
            'removeAdminVersionNotifier' => 'Remove the old Admin Version Notifier Plugin.',
            'removeOldSearchPlugins' => 'Remove the old Search Plugins.',
            'addAutoIncrementToTables' => 'Update all current db tables to use auto_increment.',
            'removeOldFilesAndFolders354' => 'Remove old files and folders that are no longer needed.',
            'removeOldFilesAndFolders354a' => 'Remove old files and folders that are no longer needed.',
            'removeOldFilesAndFolders354a1' => 'Remove old files and folders that are no longer needed.',
            'addAutoIncrementToTables2' => 'Update all new db tables to use auto_increment.',
            'updateServerConfigSettings' => 'Update the configuration settings for the server with the correct port'
            );
    var $phases = array(
            "setPermissionFolder" => 1,
            "setPermissionObject" => 1,
            "createFieldSets" => 1,
            "normaliseDocuments" => 1,
            "fixUnits" => 1,
            'applyDiscussionUpgrade' => -1,
            'fixDocumentRoleAllocation' => -1,
            'setStorageEngine'=>1,
            'dropForeignKeys'=>2,
            'dropPrimaryKeys'=>3,
            'dropIndexes'=>4,
            'createPrimaryKeys'=>5,
            'createForeignKeys'=>6,
            'createIndexes'=>7,
            'addAutoIncrementToTables2'=>-1
            );

    var $priority = array(
            'addAutoIncrementToTables'=>1,
            'removeOldFilesAndFolders354a'=>1
            );

    function addAutoIncrementToTables2()
    {
        return self::addAutoIncrementToTables();
    }

    /**
     * Set all tables in the DB to auto increment, thereby removing the use of the zseq tables
     */
    function addAutoIncrementToTables()
    {
        static $doneTables = array();

        global $default;
		DBUtil::setupAdminDatabase();
		$db = $default->_admindb;

        // Get all tables in the database
        $query = "SHOW TABLES";
        $tableList = DBUtil::getResultArray($query, $db);

        // Loop through tables and add auto increment
        foreach ($tableList as $tableArr){
            $key = key($tableArr);
            $tableName = $tableArr[$key];

            if(in_array($tableName, $doneTables)){
                // already been set - skip
                continue;
            }

            $doneTables[] = $tableName;

            if(strpos($tableName, 'zseq_', 0) !== false){
                // ignore zseq tables
                continue;
            }

            $query = "SELECT max(id) FROM {$tableName}";
            $aId = DBUtil::getOneResult($query);

            // If there's no result, then the table may be empty
            if(!PEAR::isError($aId)){
                $id = (int)$aId['max(id)'] + 1;

                $query = "UPDATE {$tableName} SET id = {$id} WHERE id = 0";
                $res = DBUtil::runQuery($query, $db);
            }else{
                $default->log->error('Add auto_increment, fail on get max id: '.$aId->getMessage());
            }

            // Update the table, set id to auto_increment
            $query = "ALTER TABLE {$tableName} CHANGE `id` `id` int (11) NOT NULL AUTO_INCREMENT";
            $res = DBUtil::runQuery($query, $db);

            if(PEAR::isError($res)){
                $default->log->error('Add auto_increment, fail on change id to auto_increment: '.$res->getMessage());

                // try again with mediumint
                $query = "ALTER TABLE {$tableName} CHANGE `id` `id` mediumint (9) NOT NULL AUTO_INCREMENT";
                $res = DBUtil::runQuery($query, $db);
            }
        }
    }

    /**
     * Copy the modified config values from the config.ini to the appropriate setting in the database
     */
    function moveConfigSettingsToDB()
    {
        require_once('Config.php');

        // Get config settings from config.ini
        $oKTConfig = KTConfig::getSingleton();
        $configPath = $oKTConfig->getConfigFilename();

		$c = new Config;
        $root =& $c->parseConfig($configPath, "IniCommented");

        if (PEAR::isError($root)) {
            return $root;
        }

        $confRoot = $root->toArray();
        $conf = $confRoot['root'];

        // Get the default settings from the database
        $query = "SELECT id, s.group_name, s.item, s.value, s.default_value
            FROM config_settings s
            ORDER BY group_name, item";

		$settings = DBUtil::getResultArray($query);

		if(PEAR::isError($settings)){
		    return $settings;
		}

        // update the settings in the database if not set to default or equal to the default value
        foreach ($settings as $item){
            if(!isset($conf[$item['group_name']][$item['item']])){
                continue; // Don't overwrite the default with a null value
            }

            $confValue = $conf[$item['group_name']][$item['item']];

            if($confValue == 'default'){
                continue; // skip over if its set to default
            }

            if($confValue == $item['value']){
                continue; // skip over if it already has the same value
            }

            if($confValue == $item['default_value']){
                if($item['value'] == 'default' || $item['value'] == $item['default_value']){
                    continue; // skip over if it has the same value as the default value
                }
                // Set the value to default
                $confValue = 'default';
            }

            // Update the setting
            $res = DBUtil::autoUpdate('config_settings', array('value' => $confValue), $item['id']);

            if(PEAR::isError($res)){
                return $res;
            }
        }
        return true;
    }

    function dropForeignKeys()
    {
		$schemautil = KTSchemaUtil::getSingleton();
		$schemautil->dropForeignKeys();
    }

    function dropPrimaryKeys()
    {
		$schemautil = KTSchemaUtil::getSingleton();
		$schemautil->dropPrimaryKeys();
    }

    function dropIndexes()
    {
		$schemautil = KTSchemaUtil::getSingleton();
		$schemautil->dropIndexes();
    }

    function createPrimaryKeys()
    {
		$schemautil = KTSchemaUtil::getSingleton();
		$schemautil->createPrimaryKeys();
    }

    function createForeignKeys()
    {
		$schemautil = KTSchemaUtil::getSingleton();
		$schemautil->createForeignKeys();
    }

    function createIndexes()
    {
		$schemautil = KTSchemaUtil::getSingleton();
		$schemautil->createIndexes();
    }

    // {{{ setStorageEngine
    function setStorageEngine()
    {
		$schemautil = KTSchemaUtil::getSingleton();
		$schemautil->setTablesToInnoDb();
    }
    // }}}

    function _removeSlashesFromFolders($folderid, $name, $full_path, $folder_ids)
    {
    	$name = str_replace(array('/','\\'),array('-','-'), $name);

    	// get folders
		$sql = "select id, name from folders where parent_id=$folderid";
		$ids = DBUtil::getResultArray($sql);

		// set to the latest values
		$parent_ids = implode(',', $folder_ids);
		if (empty($parent_ids)) $parent_ids = null;
		if (empty($full_path)) $full_path = null;

		if($folderid != 1) $full_path = (empty($full_path))?$name:($full_path . '/' . $name);
		$folder_ids [] = $folderid;

		$sql = "update folders set name=?,description=?, full_path=?, parent_folder_ids=? where id=?";
		DBUtil::runQuery(array($sql, array($name,$name, $full_path, $parent_ids, $folderid)));

		$sql = "Select id, metadata_version_id from documents where folder_id=$folderid";
		$documents = DBUtil::getResultArray($sql);

		foreach($documents as $document){
		    $dId = $document['id'];
		    $mId = $document['metadata_version_id'];

            $sql = "Select name from document_metadata_version where id=$mId";
    		$title = DBUtil::getOneResultKey($sql, 'name');

			// update documents
    		$sql = "update documents set full_path=CONCAT_WS('/',?,'$title'), parent_folder_ids=? where id=?";
	        DBUtil::runQuery(array($sql, array($full_path, $parent_ids, $dId)));
        }

		// recurse subfolders
		foreach($ids as $row)
		{
			$id = $row['id'];
			$name = $row['name'];
			UpgradeFunctions::_removeSlashesFromFolders($id, $name, $full_path, $folder_ids);
		}
    }

    function removeSlashesFromObjects()
    {
    	$GLOBALS["_OBJECTCACHE"] = array();

    	$sql = "select name from folders where id=1";
		$name = DBUtil::getOneResultKey($sql,'name');

		UpgradeFunctions::_removeSlashesFromFolders(1, $name, array(), array());
		DBUtil::commit();
    }


    // {{{ _setPermissionFolder
    function _setPermissionFolder($iFolderId) {
        global $default;
        $iInheritedFolderId = $iFolderId;
        if ($iInheritedFolderId == 1) {
            $sQuery = "UPDATE folders SET permission_folder_id = 1 WHERE id = 1";
            DBUtil::runQuery($sQuery);
            return;
        }
        while ($bFoundPermissions !== true) {
            /*ok*/$aCheckQuery = array('SELECT id FROM groups_folders_link WHERE folder_id = ? LIMIT 1', $iInheritedFolderId);
            if (count(DBUtil::getResultArrayKey($aCheckQuery, 'id')) == 0) {
                $default->log->debug('No direct permissions on folder ' . $iInheritedFolderId);
                $bInherited = true;

                $aParentQuery = array('SELECT parent_id FROM folders WHERE id = ? LIMIT 1', $iInheritedFolderId);
                $iParentId = DBUtil::getOneResultKey($aParentQuery, 'parent_id');
                $iInheritedFolderId = $iParentId;

                if ($iInheritedFolderId === false) {
                    return;
                }
                if ($iInheritedFolderId === null) {
                    return;
                }
                // if our parent knows the permission folder, use that.

                $aQuery = array("SELECT permission_folder_id FROM folders WHERE id = ?", array($iInheritedFolderId));
                $iPermissionFolderID = DBUtil::getOneResultKey($aQuery, 'permission_folder_id');
                if (!empty($iPermissionFolderID)) {
                    $aQuery = array(
                            "UPDATE folders SET permission_folder_id = ? WHERE id = ?",
                            array($iPermissionFolderID, $iFolderId)
                            );
                    DBUtil::runQuery($aQuery);
                    return;
                }
                $default->log->debug('... trying parent: ' . $iInheritedFolderId);
            } else {
                $default->log->debug('Found direct permissions on folder ' . $iInheritedFolderId);
                $iPermissionFolderID = $iInheritedFolderId;
                $aQuery = array(
                        "UPDATE folders SET permission_folder_id = ? WHERE id = ?",
                        array($iPermissionFolderID, $iFolderId)
                        );
                DBUtil::runQuery($aQuery);
                return;
            }
        }

        $default->log->error('No permissions whatsoever for folder ' . $iFolderId);
        // 0, which can never exist, for non-existent.  null for not set yet (database upgrade).
        $iPermissionFolderID = 0;
        $aQuery = array(
                "UPDATE folders SET permission_folder_id = ? WHERE id = ?",
                array($iPermissionFolderID, $iFolderId)
                );
        DBUtil::runQuery($aQuery);
    }
    // }}}

    // {{{ setPermissionFolder
    function setPermissionFolder() {
        global $default;
        require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');

        $sQuery = "SELECT id FROM $default->folders_table WHERE permission_folder_id IS NULL ORDER BY LENGTH(parent_folder_ids)";

        $aIDs = DBUtil::getResultArrayKey($sQuery, 'id');

        foreach ($aIDs as $iId) {
            $res = UpgradeFunctions::_setPermissionFolder($iId);
            if (PEAR::isError($res)) {
                return $res;
            }
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

        DBUtil::runQuery("UPDATE folders SET permission_folder_id = 1 WHERE id = 1");
        $aBrokenFolders = DBUtil::getResultArray('SELECT id, parent_id FROM folders WHERE permission_folder_id = 0 OR permission_folder_id IS NULL ORDER BY LENGTH(parent_folder_ids)');
        foreach ($aBrokenFolders as $aFolderInfo) {
            $iFolderId = $aFolderInfo['id'];
            $iParentFolderId = $aFolderInfo['parent_id'];
            $iParentFolderPermissionFolder = DBUtil::getOneResultKey(array("SELECT permission_folder_id FROM folders WHERE id = ?", array($iParentFolderId)), 'permission_folder_id');
            $res = DBUtil::whereUpdate('folders', array('permission_folder_id' => $iParentFolderPermissionFolder), array('id' => $iFolderId));
        }

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
            $query = sprintf("SELECT F2.permission_object_id AS poi FROM %s AS F LEFT JOIN %s AS F2 ON F2.id = F.permission_folder_id WHERE F.id = ?", $sTableName, $sTableName);
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

    // {{{ addTransactionTypes3013
    function addTransactionTypes3013() {
        $sTable = KTUtil::getTableName('transaction_types');
        $aTypes = array(
                'ktcore.transactions.permissions_change' => 'Permissions changed',
                'ktcore.transactions.role_allocations_change' => 'Role allocations changed',
                );
        foreach ($aTypes as $sNamespace => $sName) {
            $res = DBUtil::autoInsert($sTable, array(
                        'namespace' => $sNamespace,
                        'name' => $sName,
                        ));
        }
    }
    // }}}

    // {{{ createWorkflowPermission
    function createWorkflowPermission() {
        $sPermissionsTable = KTUtil::getTableName('permissions');
        $aPermissionInfo = array(
                'human_name' => 'Core: Manage workflow',
                'name' => 'ktcore.permissions.workflow',
                'built_in' => true,
                );
        $res = DBUtil::autoInsert($sPermissionsTable, $aPermissionInfo);
        if (PEAR::isError($res)) {
            return $res;
        }
        $iWorkflowPermissionId = $res;

        $sQuery = "SELECT id FROM $sPermissionsTable WHERE name = ?";
        $aParams = array("ktcore.permissions.security");
        $iSecurityPermissionId = DBUtil::getOneResultKey(array($sQuery, $aParams), "id");

        $sPermissionAssignmentsTable = KTUtil::getTableName('permission_assignments');
        $sQuery = "SELECT permission_object_id, permission_descriptor_id FROM $sPermissionAssignmentsTable WHERE permission_id = ?";
        $aParams = array($iSecurityPermissionId);
        $aRows = DBUtil::getResultArray(array($sQuery, $aParams));
        foreach ($aRows as $aRow) {
            $aRow['permission_id'] = $iWorkflowPermissionId;
            DBUtil::autoInsert($sPermissionAssignmentsTable, $aRow);
        }
        $sDocumentTable = KTUtil::getTableName('documents');
        $sFolderTable = KTUtil::getTableName('folders');
        DBUtil::runQuery("UPDATE $sDocumentTable SET permission_lookup_id = NULL");
        DBUtil::runQuery("UPDATE $sFolderTable SET permission_lookup_id = NULL");
    }
    // }}}

    // {{{ fixDocumentRoleAllocation
    function fixDocumentRoleAllocation() {
        $sUpgradesTable = KTUtil::getTableName('upgrades');

        $f = array(
                'descriptor' => 'sql*3.0.2*0*3.0.2/document_role_allocations.sql',
                'result' => true,
                );
        $res = DBUtil::autoInsert($sUpgradesTable, $f);
        return;
    }
    // }}}

    // {{{ createFolderDetailsPermission
    function createFolderDetailsPermission() {
        $sPermissionsTable = KTUtil::getTableName('permissions');
        $bExists = DBUtil::getOneResultKey("SELECT COUNT(id) AS cnt FROM $sPermissionsTable WHERE name = 'ktcore.permissions.folder_details'", 'cnt');
        if ($bExists) {
            return;
        }

        DBUtil::startTransaction();
        $aPermissionInfo = array(
                'human_name' => 'Core: Folder Details',
                'name' => 'ktcore.permissions.folder_details',
                'built_in' => true,
                );
        $res = DBUtil::autoInsert($sPermissionsTable, $aPermissionInfo);
        if (PEAR::isError($res)) {
            return $res;
        }
        $iFolderDetailsPermissionId = $res;

        $sQuery = "SELECT id FROM $sPermissionsTable WHERE name = ?";
        $aParams = array("ktcore.permissions.read");
        $iReadPermissionId = DBUtil::getOneResultKey(array($sQuery, $aParams), "id");

        $sPermissionAssignmentsTable = KTUtil::getTableName('permission_assignments');
        $sQuery = "SELECT permission_object_id, permission_descriptor_id FROM $sPermissionAssignmentsTable WHERE permission_id = ?";
        $aParams = array($iReadPermissionId);
        $aRows = DBUtil::getResultArray(array($sQuery, $aParams));
        foreach ($aRows as $aRow) {
            $aRow['permission_id'] = $iFolderDetailsPermissionId;
            DBUtil::autoInsert($sPermissionAssignmentsTable, $aRow);
        }
        $sDocumentTable = KTUtil::getTableName('documents');
        $sFolderTable = KTUtil::getTableName('folders');
        DBUtil::runQuery("UPDATE $sDocumentTable SET permission_lookup_id = NULL");
        DBUtil::runQuery("UPDATE $sFolderTable SET permission_lookup_id = NULL");
        DBUtil::commit();
    }
    //  }}}

    // {{{ generateWorkflowTriggers
    function generateWorkflowTriggers() {

        require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');

        // get all the transitions, and add a trigger to the util with the appropriate settings.
        $KTWFTriggerReg =& KTWorkflowTriggerRegistry::getSingleton();
        $aTransitions = KTWorkflowTransition::getList();
        foreach ($aTransitions as $oTransition) {

            // guard perm
            $iGuardPerm = $oTransition->getGuardPermissionId();
            if (!is_null($iGuardPerm)) {

                $sNamespace = 'ktcore.workflowtriggers.permissionguard';
                $oPerm = KTPermission::get($iGuardPerm);
                $oTrigger = $KTWFTriggerReg->getWorkflowTrigger($sNamespace);
                $oTriggerConfig = KTWorkflowTriggerInstance::createFromArray(array(
                            'transitionid' => KTUtil::getId($oTransition),
                            'namespace' =>  $sNamespace,
                            'config' => array('perms' => array($oPerm->getName())),
                            ));

            }
            // guard group
            $iGuardGroup = $oTransition->getGuardGroupId();
            if (!is_null($iGuardGroup)) {

                $sNamespace = 'ktcore.workflowtriggers.groupguard';
                $oTrigger = $KTWFTriggerReg->getWorkflowTrigger($sNamespace);
                $oTriggerConfig = KTWorkflowTriggerInstance::createFromArray(array(
                            'transitionid' => KTUtil::getId($oTransition),
                            'namespace' =>  $sNamespace,
                            'config' => array('group_id' => $iGuardGroup),
                            ));

            }
            // guard role
            $iGuardRole = $oTransition->getGuardRoleId();
            if (!is_null($iGuardRole)) {

                $sNamespace = 'ktcore.workflowtriggers.roleguard';
                $oTrigger = $KTWFTriggerReg->getWorkflowTrigger($sNamespace);
                $oTriggerConfig = KTWorkflowTriggerInstance::createFromArray(array(
                            'transitionid' => KTUtil::getId($oTransition),
                            'namespace' =>  $sNamespace,
                            'config' => array('role_id' => $iGuardRole),
                            ));

            }
            // guard condition
            $iGuardCondition = $oTransition->getGuardConditionId();
            if (!is_null($iGuardCondition)) {

                $sNamespace = 'ktcore.workflowtriggers.conditionguard';
                $oTrigger = $KTWFTriggerReg->getWorkflowTrigger($sNamespace);
                $oTriggerConfig = KTWorkflowTriggerInstance::createFromArray(array(
                            'transitionid' => KTUtil::getId($oTransition),
                            'namespace' =>  $sNamespace,
                            'config' => array('condition_id' => $iGuardCondition),
                            ));

            }
        }

    }
    //  }}}

    // {{{ rebuildAllPermissions
    function rebuildAllPermissions() {
        $oRootFolder = Folder::get(1);
        KTPermissionUtil::updatePermissionLookupRecursive($oRootFolder);
    }
    // }}}

    // {{{ _upgradeSavedSearch
    function _upgradeSavedSearch($aSearch) {
        $aMapping = array('-1' =>  'ktcore.criteria.name',
                '-6' =>  'ktcore.criteria.id',
                '-2' =>  'ktcore.criteria.title',
                '-3' =>  'ktcore.criteria.creator',
                '-4' =>  'ktcore.criteria.datecreated',
                '-5' =>  'ktcore.criteria.documenttype',
                '-7' =>  'ktcore.criteria.datemodified',
                '-8' =>  'ktcore.criteria.size',
                '-9' =>  'ktcore.criteria.content',
                '-10' =>  'ktcore.criteria.workflowstate',
                '-13' =>  'ktcore.criteria.discussiontext',
                '-12' =>  'ktcore.criteria.searchabletext',
                '-11' =>  'ktcore.criteria.transactiontext');

        $aFieldsets =& KTFieldset::getList('disabled != true');
        foreach($aFieldsets as $oFieldset) {
            $aFields =& DocumentField::getByFieldset($oFieldset);
            foreach($aFields as $oField) {
                $sNamespace = $oFieldset->getNamespace() . '.' . $oField->getName();
                $sId = (string) $oField->getId();
                $aMapping[$sId] = $sNamespace;
            }
        }

        foreach(array_keys($aSearch['subgroup']) as $sgkey) {
            $sg =& $aSearch['subgroup'][$sgkey];
            foreach(array_keys($sg['values']) as $vkey) {
                $item =& $sg['values'][$vkey];
                $type = $item['type'];
                $toreplace = 'bmd' . ((int)$type < 0 ? '_' : '') . abs((int)$type);
                $item['type'] = $aMapping[$type];
                $nData = array();
                foreach($item['data'] as $k=>$v) {
                    $k = str_replace($toreplace, $aMapping[$type], $k);
                    $nData[$k] = $v;
                }
                $item['data'] = $nData;
            }
        }
        return $aSearch;
    }
    // }}}

    // {{{ upgradeSavedSearches
    function upgradeSavedSearches() {
        foreach(KTSavedSearch::getSearches() as $oS) {
            $sS = $oS->getSearch();
            $aSearch = UpgradeFunctions::_upgradeSavedSearch($sS);
            $oS->setSearch($aSearch);
            $oS->update();
        }
    }
    // }}}

    // {{{ cleanupGroupMembership
    function cleanupGroupMembership() {
        // 4 cases.
        $child_query = 'select L.id as link_id FROM groups_groups_link as L left outer join groups_lookup as G on (L.member_group_id = G.id) WHERE G.id IS NULL';
        $parent_query = 'select L.id as link_id FROM groups_groups_link as L left outer join groups_lookup as G on (L.parent_group_id = G.id) WHERE G.id IS NULL';
        $group_query = 'select L.id as link_id FROM users_groups_link as L left outer join groups_lookup as G on (L.group_id = G.id) WHERE G.id IS NULL';
        $user_query = 'select L.id as link_id FROM users_groups_link as L left outer join users as U on (L.user_id = U.id) WHERE U.id IS NULL';

        $bad_group_links = array();
        $res = DBUtil::getResultArrayKey(array($child_query, null), 'link_id');
        if (PEAR::isError($res)) {
            return $res;
        } else {
            $bad_group_links = $res;
        }

        $res = DBUtil::getResultArrayKey(array($parent_query, null), 'link_id');
        if (PEAR::isError($res)) {
            return $res;
        } else {
            $bad_group_links = kt_array_merge($bad_group_links, $res);
        }

        foreach ($bad_group_links as $link_id) {
            $res = DBUtil::runQuery(array("DELETE FROM groups_groups_link WHERE id = ?", $link_id));
            if (PEAR::isError($res)) {
                return $res;
            }
        }

        $res = DBUtil::getResultArrayKey(array($group_query, null), 'link_id');
        if (PEAR::isError($res)) {
            return $res;
        } else {
            $bad_user_links = $res;
        }

        $res = DBUtil::getResultArrayKey(array($user_query, null), 'link_id');
        if (PEAR::isError($res)) {
            return $res;
        } else {
            $bad_user_links = kt_array_merge($bad_user_links, $res);
        }

        foreach ($bad_user_links as $link_id) {
            $res = DBUtil::runQuery(array("DELETE FROM users_groups_link WHERE id = ?", $link_id));
            if (PEAR::isError($res)) {
                return $res;
            }
        }

        return true;

    }
    // }}}

    // {{{  cleanupOldKTAdminVersionNotifier
    function cleanupOldKTAdminVersionNotifier() {
        global $default;
        $oldFile = KT_DIR . "/plugins/ktstandard/KTAdminVersionPlugin.php";

        if(file_exists($oldFile)) return unlink($oldFile);

        return true;
    }
    // }}}

    // {{{ updateConfigFile35
    function updateConfigFile35()
    {
        // The following is no longer needed due to defaults now being in place.
        // It also was corrupting the file "sometimes".
        // We leave the function in but do nothing so we don't mess with the upgrades table.

        return;

/*    	$config = KTConfig::getSingleton();
        $configPath = KTConfig::getConfigFilename();
    	$configPath = str_replace(array("\n","\r"), array('',''), $configPath);

        if(file_exists($configPath)) {

            $ini = new Ini($configPath);

            // Webservices Section
            $ini->addItem('webservice', 'uploadDirectory', '${varDirectory}/uploads');
            $ini->addItem('webservice', 'downloadUrl', '${rootUrl}/ktwebservice/download.php');
            $ini->addItem('webservice', 'uploadExpiry', '30');
            $ini->addItem('webservice', 'downloadExpiry', '30');
            $ini->addItem('webservice', 'randomKeyText', 'bkdfjhg23yskjdhf2iu');
            $ini->addItem('webservice', 'validateSessionCount', 'false');

            // externalBinary Section
            $ini->delSection('externalBinary');
            if(OS_WINDOWS){
                $ini->addItem('externalBinary', 'xls2csv', KT_STACK_DIR . '/bin/catdoc/xls2csv.exe', '', 'The following are external binaries that may be used by various parts of knowledgeTree.');
                $ini->addItem('externalBinary', 'pdftotext', KT_STACK_DIR . '/bin/xpdf/pdftotext.exe');
                $ini->addItem('externalBinary', 'catppt', KT_STACK_DIR . '/bin/catdoc/catppt.exe');
                $ini->addItem('externalBinary', 'pstotext', KT_STACK_DIR . '/bin/pstotext/pstotext.exe');
                $ini->addItem('externalBinary', 'catdoc', KT_STACK_DIR . '/bin/catdoc/catdoc.exe');
                $ini->addItem('externalBinary', 'antiword', KT_STACK_DIR . '/bin/antiword/antiword.exe');
                $ini->addItem('externalBinary', 'python', KT_STACK_DIR . '/openoffice/program/python.bat');
                $ini->addItem('externalBinary', 'java', KT_STACK_DIR . '/java/jre/bin/java.exe');
                $ini->addItem('externalBinary', 'php', KT_STACK_DIR . '/php/php.exe');
                $ini->addItem('externalBinary', 'df', KT_STACK_DIR . '/bin/gnuwin32/df.exe');

            } else {
                $ini->addItem('externalBinary', 'xls2csv', KT_STACK_DIR . '/common/bin/xls2csv', '', 'The following are external binaries that may be used by various parts of knowledgeTree.');
                $ini->addItem('externalBinary', 'pdftotext', KT_STACK_DIR . '/common/bin/pdftotext');
                $ini->addItem('externalBinary', 'catppt', KT_STACK_DIR . '/common/bin/catppt');
                $ini->addItem('externalBinary', 'pstotext', KT_STACK_DIR . '/common/bin/pstotext');
                $ini->addItem('externalBinary', 'catdoc', KT_STACK_DIR . '/common/bin/catdoc');
                $ini->addItem('externalBinary', 'antiword', KT_STACK_DIR . '/common/bin/antiword');
                $ini->addItem('externalBinary', 'python', KT_STACK_DIR . '/openoffice/program/python');
                $ini->addItem('externalBinary', 'java', KT_STACK_DIR . '/java/jre/bin/java');
                $ini->addItem('externalBinary', 'php', KT_STACK_DIR . '/php/bin/php');
                $ini->addItem('externalBinary', 'df', ';'. KT_STACK_DIR . '/common/bin/df');
            }

            // search Section
            $ini->addItem('search', 'resultsPerPage', 'default', "The number of results per page\r\n; defaults to 25");
            $ini->addItem('search', 'dateFormat', 'default', "The date format used when making queries using widgets\r\n; defaults to Y-m-d");

            // indexer Section
            $ini->addItem('indexer', 'coreClass', 'JavaXMLRPCLuceneIndexer', "The core indexing class\r\n;coreClass=PHPLuceneIndexer");
            $ini->addItem('indexer', 'batchDocuments', 'default', "The number of documents to be indexed in a cron session\r\n; defaults to 20");
            $ini->addItem('indexer', 'batchMigrateDocuments', 'default', "The number of documents to be migrated in a cron session\r\n; defaults to 500");
            $ini->addItem('indexer', 'luceneDirectory', '${varDirectory}/indexes', "The location of the lucene indexes");
            $ini->addItem('indexer', 'javaLuceneURL', 'default', "The url for the Java Lucene Server. This should match up with the Lucene Server configuration.\r\n; defaults to http://127.0.0.1:8875");

            // openoffice Section
            $ini->addItem('openoffice', 'host', 'default', "The host on which open office is installed\r\n; defaults to 127.0.0.1");
            $ini->addItem('openoffice', 'port', 'default', "The port on which open office is listening\r\n; defaults to 8100");

            // user_prefs Section
            $ini->addItem('user_prefs', 'passwordLength', '6', "The minimum password length on password-setting\r\n; could be moved into DB-auth-config");
            $ini->addItem('user_prefs', 'restrictAdminPasswords', 'default', "Apply the minimum password length to admin while creating / editing accounts?\r\n; default is set to \"false\" meaning that admins can create users with shorter passwords.");
            $ini->addItem('user_prefs', 'restrictPreferences', 'false', "Restrict users from accessing their preferences menus?");

            // builtinauth Section
            $ini->addItem('builtinauth', 'password_change_interval', '30', "This would force users that use the built-in authentication provider\r\n; to have to change their passwords every 30 days." ,"This is configuration for the built-in authentication provider");

            // cache Section
            if(OS_WINDOWS){
                $ini->addItem('cache', 'cacheEnabled', 'false', '', "Enable/disable the cache and set the cache location");
            } else {
                $ini->addItem('cache', 'cacheEnabled', 'true', '', "Enable/disable the cache and set the cache location");
            }

            $ini->addItem('cache', 'cacheDirectory', '${varDirectory}/cache');
            $ini->addItem('cache', 'cachePlugins', 'true');

            // KTWebDAVSettings Section
            $ini->addItem('KTWebDAVSettings', 'debug', 'off', '_LOTS_ of debug info will be logged if the following is "on"', 'This section is for KTWebDAV only');
            $ini->addItem('KTWebDAVSettings', 'safemode', 'on', 'To allow write access to WebDAV clients set safe mode to "off" below');

            // BaobabSettings Section
            $ini->addItem('BaobabSettings', 'debug', 'off', '_LOTS_ of debug info will be logged if the following is "on"', 'This section is for Boabab only');
            $ini->addItem('BaobabSettings', 'safemode', 'on', 'To allow write access to WebDAV clients set safe mode to "off" below');

            // backup Section
            $ini->addItem('backup', 'backupDirectory', 'default', "Identify location of kt-backup for database backups\r\n;backupDirectory = c:/kt-backups\r\n;backupDirectory = /tmp/kt-backups");
            $ini->addItem('backup', 'mysqlDirectory', 'default', "Identify the location of the mysql.exe and mysqldump.exe\r\n;mysqlDirectory = c:/program files/ktdms/mysql/bin");

            // clientToolPolicies Section
            $ini->addItem('clientToolPolicies', 'explorerMetadataCapture', 'true', "These two settings control whether or not the client is prompted for metadata when a\r\n;document is added to knowledgetree via KTtools. They default to true.");
            $ini->addItem('clientToolPolicies', 'officeMetadataCapture', 'true');
            $ini->addItem('clientToolPolicies', 'captureReasonsDelete', 'true', "These settings govern whether reasons are asked for in KTtools.");
            $ini->addItem('clientToolPolicies', 'captureReasonsCheckin', 'true');
            $ini->addItem('clientToolPolicies', 'captureReasonsCheckout', 'true');
            $ini->addItem('clientToolPolicies', 'captureReasonsCancelCheckout', 'true');
            $ini->addItem('clientToolPolicies', 'captureReasonsCopyInKT', 'true');
            $ini->addItem('clientToolPolicies', 'captureReasonsMoveInKT', 'true');
            $ini->addItem('clientToolPolicies', 'allowRememberPassword', 'true');

            // DiskUsage Section
            $ini->addItem('DiskUsage', 'warningThreshold', '10', "When free space in a mount point is less than this percentage,\r\n; the disk usage dashlet will highlight the mount in ORANGE", "settings for the Disk Usage dashlet");
            $ini->addItem('DiskUsage', 'urgentThreshold', '5', "When free space in a mount point is less than this percentage,\r\n; the disk usage dashlet will highlight the mount in RED");

            // CustomErrorMessages Section
            $ini->addItem('CustomErrorMessages', 'customerrormessages', 'on', 'Turn custom error messages on or off here (account wide)');
            $ini->addItem('CustomErrorMessages', 'customerrorpagepath', 'customerrorpage.php', 'Name or url of custom error page');
            $ini->addItem('CustomErrorMessages', 'customerrorhandler', 'on', 'Turn custom error handler on or off');

            // URLS Section
            $ini->delSection('urls');
            $ini->addItem('urls', 'varDirectory', '"'.str_replace('\\', '/', $config->get("urls/varDirectory")).'"', 'directories');
            $ini->addItem('urls', 'logDirectory', '${varDirectory}/log');
            $ini->addItem('urls', 'documentRoot', '"'.str_replace('\\', '/', $config->get("urls/documentRoot")).'"');
            $ini->addItem('urls', 'uiDirectory', '${fileSystemRoot}/presentation/lookAndFeel/knowledgeTree');
            $ini->addItem('urls', 'tmpDirectory', '${varDirectory}/tmp');
            $ini->addItem('urls', 'graphicsUrl', '${rootUrl}/graphics', 'urls');
            $ini->addItem('urls', 'uiUrl', '${rootUrl}/presentation/lookAndFeel/knowledgeTree');
            $ini->addItem('urls', ';dedicatedrsstitle', 'RSS Feed Title', 'RSS dedicated feed url');
            $ini->addItem('urls', ';dedicatedrssurl', '');
            $ini->addItem('urls', 'stopwordsFile', '${fileSystemRoot}/config/stopwords.txt', 'files');

            $ini->write();
        }
*/
    }
      // }}}

    // {{{ registerIndexingTasks
    /**
     * Registers the functions that are required by the indexing sub-system.
     *
     */
    function registerIndexingTasks()
    {
    	$ext = OS_WINDOWS?'bat':'sh';

    	$year = date('Y');
    	$mon = date('m');
    	$day = date('d');
    	$hour = date('H');
    	$min = date('i');
    	$min = floor( $min / 5) * 5;

		$oScheduler = new Scheduler('Indexing');
		$oScheduler->setScriptPath(KT_DIR . '/bin/indexingTask.' . $ext);
		$oScheduler->setFrequency('1min');
		$oScheduler->setFirstRunTime(date('Y-m-d H:i',mktime($hour, $min, 0, $mon, $day, $year)));
		$oScheduler->registerTask();

		$oScheduler = new Scheduler('Index Migration');
		$oScheduler->setScriptPath(KT_DIR . '/bin/indexMigrationTask.' . $ext);
		$oScheduler->setFrequency('5mins');
		$oScheduler->setFirstRunTime(date('Y-m-d H:i',mktime($hour, $min, 0, $mon, $day, $year)));
		$oScheduler->registerTask();

		$oScheduler = new Scheduler('Index Optimisation');
		$oScheduler->setScriptPath(KT_DIR . '/bin/optimizeIndexes.' . $ext);
		$oScheduler->setFrequency('weekly');
		$oScheduler->setFirstRunTime(date('Y-m-d 00:00'));
		$oScheduler->registerTask();
    }
     // }}}

    // {{{  removeAdminVersionNotifier
    function removeAdminVersionNotifier() {
        global $default;
        $oldPath = KT_DIR . "/plugins/ktstandard/AdminVersionPlugin";

        if(file_exists($oldPath)) return rmdir($oldPath);
    }
    // }}}

    // {{{  removeOldSearchPlugins
    function removeOldSearchPlugins() {
        global $default;
        $oldFile = KT_DIR . "/plugins/ktstandard/SearchDashletPlugin.php";
        if(file_exists($oldFile)) unlink($oldFile);
        $oldFile = KT_DIR . "/plugins/ktstandard/SearchDashlet.php";
        if(file_exists($oldFile)) unlink($oldFile);

        // Files MUST be removed before folders and folders MUST be empty
        $oldPath1 = KT_DIR . "/templates/ktstandard/searchdashlet/";
        UpgradeFunctions::rm_recursive($oldPath1);
        $oldPath2 = KT_DIR . "/plugins/generalmetadata/";
        UpgradeFunctions::rm_recursive($oldPath2);

        // FIXME: We should check that they all worked
        return true;
    }

    // {{{  removeOldFilesAndFolders354
    function removeOldFilesAndFolders354() {
        global $default;
        $oldFile = KT_DIR . "/lib/sanitize.inc";
        if(file_exists($oldFile)) unlink($oldFile);

        // Files MUST be removed before folders and folders MUST be empty
        $oldPath1 = KT_DIR . "/plugins/toolsdashlet/";
        UpgradeFunctions::rm_recursive($oldPath1);

        // FIXME: We should check that they all worked
        return true;
    }

    // {{{  removeOldFilesAndFolders354a
    function removeOldFilesAndFolders354a() {
        global $default;
        $oldFile = KT_DIR . "/sql/mysql/upgrade/3.5.4/server_name.sql";
        if(file_exists($oldFile)) unlink($oldFile);

        return true;
    }

    // {{{  removeOldFilesAndFolders354a1
    function removeOldFilesAndFolders354a1() {
        global $default;

        $oldFile1 = KT_DIR . "/baobab/tests/BaobabServer.test.php";
        if(file_exists($oldFile1)) unlink($oldFile1);

        $oldPath1 = KT_DIR . "/ktwsapi/";
        UpgradeFunctions::rm_recursive($oldPath1);

        $oldFile2 = KT_DIR . "/lib/Log.inc";
        if(file_exists($oldFile2)) unlink($oldFile2);

        $oldFile3 = KT_DIR . "/tests/api/authentication.php";
        if(file_exists($oldFile3)) unlink($oldFile3);

        $oldFile4 = KT_DIR . "/tests/api/folder.php";
        if(file_exists($oldFile4)) unlink($oldFile4);

        $oldFile5 = KT_DIR . "/tests/api/document.php";
        if(file_exists($oldFile5)) unlink($oldFile5);

        $oldPath2 = KT_DIR . "/thirdparty/pear/HTTP/WebDAV/";
        UpgradeFunctions::rm_recursive($oldPath2);

        // FIXME: We should check that they all worked
        return true;
    }

    function updateServerConfigSettings() {
        global $default;
        $port = $_SERVER['SERVER_PORT']+0;

        if($port > 0){
            DBUtil::whereUpdate('config_settings',
                array('value' => $port),
                array('item' => 'internal_server_port', 'group_name' => 'server'));
        }
    }


    function rm_recursive($filepath)
    {
        if (is_dir($filepath) && !is_link($filepath))
        {
            if ($dh = opendir($filepath))
            {
                while (($sf = readdir($dh)) !== false)
                {
                    if ($sf == '.' || $sf == '..')
                    {
                        continue;
                    }
                    if (!UpgradeFunctions::rm_recursive($filepath.'/'.$sf))
                    {
                        //throw new Exception( $filepath.'/'.$sf.' could not be deleted.');
                        return false;
                    }
                }
                closedir($dh);
            }
            return rmdir($filepath);
        }
        return unlink($filepath);
    }
    // }}}
}

?>
