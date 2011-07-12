<?php

/**
 * $Id$
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

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');
require_once(KT_LIB_DIR . '/roles/Role.inc');

include_once(KT_LIB_DIR . '/permissions/BackgroundPermissions.php');

class KTFolderPermissionsAction extends KTFolderAction {

    public $sName = 'ktcore.actions.folder.permissions';

    public $_sEditShowPermission = 'ktcore.permissions.security';
    public $_sShowPermission = 'ktcore.permissions.security';
    public $_bAdminAlwaysAvailable = true;
    public $bAutomaticTransaction = true;
    public $cssClass = 'permissions';

    function getDisplayName() {
        return _kt('Permissions');
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt('Permissions'));
        $template = $this->oValidator->validateTemplate('ktcore/folder/view_permissions');

        $permissionObject = KTPermissionObject::get($this->oFolder->getPermissionObjectID());
        $aPermissions = KTPermission::getList();
        $aMapPermissionGroup = array();
        $aMapPermissionRole = array();
        $aMapPermissionUser = array();

        $aAllGroups = Group::getList();   // probably small enough
        $aAllRoles = Role::getList();     // probably small enough.
        // users are _not_ fetched this way.

        $aActiveGroups = array();
        $aActiveUsers = array();
        $aActiveRoles = array();

        foreach ($aPermissions as $oPermission) {
            $oPLA = KTPermissionAssignment::getByPermissionAndObject($oPermission, $permissionObject);
            if (PEAR::isError($oPLA)) {
                continue;
            }

            $oDescriptor = KTPermissionDescriptor::get($oPLA->getPermissionDescriptorID());
            $iPermissionID = $oPermission->getID();
            $aIDs = $oDescriptor->getGroups();
            $aMapPermissionGroup[$iPermissionID] = array();

            foreach ($aIDs as $iID) {
                $aMapPermissionGroup[$iPermissionID][$iID] = true;
                $aActiveGroups[$iID] = true;
            }

            $aIds = $oDescriptor->getRoles();
            $aMapPermissionRole[$iPermissionID] = array();

            foreach ($aIds as $iId) {
                $aMapPermissionRole[$iPermissionID][$iId] = true;
                $aActiveRoles[$iId] = true;
            }

            $aIds = $oDescriptor->getUsers();
            $aMapPermissionUser[$iPermissionID] = array();

            foreach ($aIds as $iId) {
                $aMapPermissionUser[$iPermissionID][$iId] = true;
                $aActiveUsers[$iId] = true;
            }
        }

        // now we constitute the actual sets.
        $users = array();
        $groups = array();
        $roles = array(); // should _always_ be empty, barring a bug in permissions::updatePermissionLookup

        // this should be quite limited - direct role -> user assignment is typically rare.
        foreach ($aActiveUsers as $id => $marker) {
            $oUser = User::get($id);
            if (is_null($oUser)) continue; // this is just a patch in case there is a db integrity issue.
            $users[$oUser->getName()] = $oUser;
        }

        asort($users); // ascending, per convention.

        foreach ($aActiveGroups as $id => $marker) {
            $oGroup = Group::get($id);
            if (is_null($oGroup)) continue; // this is just a patch in case there is a db integrity issue.
            $groups[$oGroup->getName()] = $oGroup;
        }

        asort($groups);

        foreach ($aActiveRoles as $id => $marker) {
            $oRole = Role::get($id);
            if (is_null($oRole)) continue; // this is just a patch in case there is a db integrity issue.
            $roles[$oRole->getName()] = $oRole;
        }

        asort($roles);

        $inheritedFolderPath = '';
        $allowInherit = false;
        
        // This is fine, since a folder can only inherit permissions from a folder.
        $inheritedFolder = KTPermissionUtil::findRootObjectForPermissionObject($permissionObject);
        $inheritedFolderId = $inheritedFolder->getId();
        $folderId = $this->oFolder->getId();
        
        if ($inheritedFolderId !== $folderId) {
        	$allowInherit = true;
        	$inheritedFolderPath = join(' &raquo; ', $inheritedFolder->getPathArray());
        }
        
        $allowEdit = KTPermissionUtil::userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder);
        if (KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $allowEdit = true;
        }
        
        $isUpdateInProgress = $this->checkIfBackgrounded($inheritedFolderId);

        if ($allowEdit && !$isUpdateInProgress) {
	        $allowEdit = !$allowInherit;
        } else {
        	$allowEdit = false;
        	$allowInherit = false;
        }

        $aConditions = array();
        $aDynConditions = KTPermissionDynamicCondition::getByPermissionObject($permissionObject);

        foreach ($aDynConditions as $oDynCondition) {
            $group = Group::get($oDynCondition->getGroupId());
            if (is_null($group)) continue; // db integrity catch

            if (PEAR::isError($group)) { continue; }
            $savedSearch = KTSavedSearch::get($oDynCondition->getConditionId());
            if (is_null($savedSearch)) { continue; } // db integrity catch
            if (PEAR::isError($savedSearch)) { continue; }

            $aInfo = array(
                'group' => $group->getName(),
                'name' => $savedSearch->getName(),
            );

            $aAssign = $oDynCondition->getAssignment();
            $perms = array();
            foreach ($aAssign as $iPermissionId) {
                $perms[$iPermissionId] = true;
            }

            $aInfo['perms'] = $perms;
            $aConditions[] = $aInfo;
        }
        
        $templateData = array(
            'context' => $this,
            'permissions' => $aPermissions,
            'groups' => $groups,
            'users' => $users,
            'roles' => $roles,
            'oFolder' => $this->oFolder,
            'aMapPermissionGroup' => $aMapPermissionGroup,
            'aMapPermissionRole' => $aMapPermissionRole,
            'aMapPermissionUser' => $aMapPermissionUser,
            'edit' => $allowEdit,
            'inheritable' => $allowInherit,
            'inherited' => $inheritedFolderPath,
            'isUpdateInProgress' => $isUpdateInProgress,
            'conditions' => $aConditions,
        );

        return $template->render($templateData);
    }

    function do_resolved_users() {
        $this->oPage->setBreadcrumbDetails(_kt('Permissions'));
        $template = $this->oValidator->validateTemplate('ktcore/folder/resolved_permissions_user');

        $oPL = KTPermissionLookup::get($this->oFolder->getPermissionLookupID());
        $aPermissions = KTPermission::getList();
        $aMapPermissionGroup = array();
        $aMapPermissionRole = array();
        $aMapPermissionUser = array();
        $aActiveUsers = array();

        $aUsers = User::getList();

        foreach ($aPermissions as $oPermission) {
            $oPLA = KTPermissionLookupAssignment::getByPermissionAndLookup($oPermission, $oPL);
            if (PEAR::isError($oPLA)) {
                continue;
            }

            $oDescriptor =& KTPermissionDescriptor::get($oPLA->getPermissionDescriptorID());
            $iPermissionID = $oPermission->getID();
            $aMapPermissionGroup[$iPermissionID] = array();

            $hasPermission = false;
            $everyone = $oDescriptor->hasRoles(array(-3));
            $authenticated = $oDescriptor->hasRoles(array(-4));

            // TODO : paginate this page, when there are too many users
            foreach ($aUsers as $oUser) {
                if ($everyone || ($authenticated && $oUser->isAnonymous()) ||
                KTPermissionUtil::userHasPermissionOnItem($oUser, $oPermission, $this->oFolder)) {
                    $aMapPermissionUser[$iPermissionID][$oUser->getId()] = true;
                    $name = ($oUser->getDisabled() == 3) ? '(Invited) '.$oUser->getEmail() : $oUser->getName();
                    $aActiveUsers[$oUser->getId()] = $name;
                }
            }
        }

        // now we constitute the actual sets.
        $users = array();
        $groups = array();
        $roles = array(); // should _always_ be empty, barring a bug in permissions::updatePermissionLookup

        $users = $aActiveUsers;
        asort($users); // ascending, per convention.

        $bEdit = false;
        $sInherited = '';

        $templateData = array(
            'context' => $this,
            'permissions' => $aPermissions,
            'groups' => $groups,
            'users' => $users,
            'roles' => $roles,
            'oFolder' => $this->oFolder,
            'aMapPermissionGroup' => $aMapPermissionGroup,
            'aMapPermissionRole' => $aMapPermissionRole,
            'aMapPermissionUser' => $aMapPermissionUser,
            'edit' => $bEdit,
            'inherited' => $sInherited,
            'foldername' => $this->oFolder->getName(),
            'iFolderId' => $this->oFolder->getId(),
        );

        return $template->render($templateData);
    }

    function _copyPermissions() {
        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $this->oFolder->getId(),
            'comment' => _kt('Override permissions from parent'),
            'transactionNS' => 'ktcore.transactions.permissions_change',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
            'parentid' => $this->oFolder->getParentID(),
        ));

        $aOptions = array(
            'defaultmessage' => _kt('Error updating permissions'),
            'redirect_to' => array('edit', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );

        $this->oValidator->notErrorFalse($oTransaction, $aOptions);

        return KTPermissionUtil::copyPermissionObject($this->oFolder);
    }

    function do_edit() {
        $this->oPage->setBreadcrumbDetails(_kt('Viewing Permissions'));
        
        $folderId = $this->oFolder->getId();
        $permissionObject = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $options = array('redirect_to' => array('main', 'fFolderId=' .  $folderId));

        if (!KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $options);
        }

        // copy permissions if they were inherited
        $inheritedFolderObject = KTPermissionUtil::findRootObjectForPermissionObject($permissionObject);
        $inheritedFolderId = $inheritedFolderObject->getId();
        if ($inheritedFolderId !== $folderId) {
            $override = KTUtil::arrayGet($_REQUEST, 'override', false);
            if (empty($override)) {
                $this->errorRedirectToMain(_kt('This folder does not define its own permissions'), sprintf('fFolderId=%d', $folderId));
            }

            $this->startTransaction();

            if ($this->_copyPermissions() === false) {
                $this->rollbackTransaction();
                return $this->errorRedirectToMain(_kt('Could not override folder permissions'), sprintf('fFolderId=%d', $folderId));
            }

            $this->commitTransaction();

            $permissionObject = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        }
        
        if ($this->checkIfBackgrounded($folderId)) {
            $this->redirectToMain('fFolderId=' . $folderId);
            exit();
        }

        // permissions in JS format
        $permissionsToJSON = array();
        $permissionsList = KTPermission::getList();
        foreach($permissionsList as $permission) {
            $permissionsToJSON[] = array('id' => $permission->getId(), 'name' => $permission->getHumanName());
        }

        $jsonService = new Services_JSON;
        $jsonPermissions = $jsonService->encode($permissionsToJSON);

        // dynamic conditions
        $dynamicConditions = KTPermissionDynamicCondition::getByPermissionObject($permissionObject);

        // templating
        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/folder/permissions');

        $canInherit = ($folderId != 1);

        global $default;
        if ($default->enableESignatures) {
            $url = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
            $heading = _kt('You are attempting to modify permissions');
            $input['type'] = 'button';
            $input['onclick'] = "javascript: showSignatureForm('{$url}', '{$heading}', 'ktcore.transactions.permissions_change', 'folder', 'update_permissions_form', 'submit', {$folderId});";
        }
        else {
            $input['type'] = 'submit';
            $input['onclick'] = '';
        }

        $docPermissions = KTPermission::getDocumentRelevantList();

        $templateData = array(
            'iFolderId' => $folderId,
            'roles' => Role::getList(),
            'groups' => Group::getList(),
            'conditions' => KTSavedSearch::getConditions(),
            'dynamic_conditions' => $dynamicConditions,
            'context' => $this,
            'foldername' => $this->oFolder->getName(),
            'jsonpermissions' => $jsonPermissions,
            'edit' => true,
            'permissions' => $permissionsList,
            'document_permissions' => $docPermissions,
            'can_inherit' => $canInherit,
            'input' => $input
        );

        return $template->render($templateData);
    }

    function json_permissionError() {
        return array(
                    'error' => true,
                    'type' => 'kt.permission_denied',
                    'alert' => true,
                    'message' => _kt('You do not have permission to alter security settings.')
        );
    }

    function &_getPermissionsMap() {
        $permissionObject = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $aPermissions = KTPermission::getList();
        $aPermissionsMap = array('role'=>array(), 'group'=>array());

        foreach ($aPermissions as $oPermission) {
            $oPA = KTPermissionAssignment::getByPermissionAndObject($oPermission, $permissionObject);
            if (PEAR::isError($oPA)) {
                continue;
            }

            $oDescriptor = KTPermissionDescriptor::get($oPA->getPermissionDescriptorId());
            $iPermissionId = $oPermission->getId();

            // groups
            $aGroupIds = $oDescriptor->getGroups();
            foreach ($aGroupIds as $iId) {
                $aPermissionsMap['group'][$iId][$iPermissionId] = true;
            }

            // roles
            $aRoleIds = $oDescriptor->getRoles();
            foreach ($aRoleIds as $iId) {
                $aPermissionsMap['role'][$iId][$iPermissionId] = true;
            }
        }

        return $aPermissionsMap;
    }

    function json_getEntities($optFilter = null) {
        $sFilter = KTUtil::arrayGet($_REQUEST, 'filter', false);
        if ($sFilter == false && $optFilter != null) {
            $sFilter = $optFilter;
        }

        $bSelected = KTUtil::arrayGet($_REQUEST, 'selected', false);

        $aEntityList = array('off' => _kt('-- Please filter --'));

        // check permissions
        $permissionObject = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $aOptions = array('redirect_to' => array('json', 'json_action=permission_error&fFolderId=' .  $this->oFolder->getId()));

        if (!KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $aOptions);
        }

        // get permissions map
        $aPermissionsMap =& $this->_getPermissionsMap();

        if ($bSelected || $sFilter && trim($sFilter)) {
            if (!$bSelected) {
                $aEntityList = array();
            }

            $aGroups = Group::getList(sprintf('name like \'%%%s%%\'', $sFilter));
            foreach($aGroups as $oGroup) {
                $aPerm = @array_keys($aPermissionsMap['group'][$oGroup->getId()]);
                if (!is_array($aPerm)) {
                    $aPerm = array();
                }

                if ($bSelected) {
                    if (count($aPerm)) {
                        $aEntityList['g'.$oGroup->getId()] = array(
                                                                'type' => 'group',
                                                                'display' => _kt('Group') . ': ' . $oGroup->getName(),
                                                                'name' => $oGroup->getName(),
                                                                'permissions' => $aPerm,
                                                                'id' => $oGroup->getId(),
                                                                'selected' => true
                                                             );
                    }
                }
                else {
                    $aEntityList['g'.$oGroup->getId()] = array(
                                                            'type' => 'group',
                                                            'display' => _kt('Group') . ': ' . $oGroup->getName(),
                                                            'name' => $oGroup->getName(),
                                                            'permissions' => $aPerm,
                                                            'id' => $oGroup->getId()
                                                         );
                }
            }

            $aRoles = Role::getList(sprintf('name like \'%%%s%%\'', $sFilter));
            foreach($aRoles as $oRole) {
                $aPerm = @array_keys($aPermissionsMap['role'][$oRole->getId()]);
                if (!is_array($aPerm)) {
                    $aPerm = array();
                }

                if ($bSelected) {
                    if (count($aPerm)) {
                        $aEntityList['r'.$oRole->getId()] = array(
                                                                'type' => 'role',
                                                                'display' => _kt('Role') . ': ' . $oRole->getName(),
                                                                'name' => $oRole->getName(),
                                                                'permissions' => $aPerm,
                                                                'id' => $oRole->getId(),
                                                                'selected' => true
                                                             );
                    }
                }
                else {
                    $aEntityList['r'.$oRole->getId()] = array(
                                                            'type' => 'role',
                                                            'display' => _kt('Role') . ': ' . $oRole->getName(),
                                                            'name' => $oRole->getName(),
                                                            'permissions' => $aPerm,
                                                            'id' => $oRole->getId()
                                                         );
                }
            }
        }

        return $aEntityList;
    }

    function do_update() 
    {
    	$folderId = $this->oFolder->getId();
    	 
    	$options = array('redirect_to' => array('main', 'fFolderId=' .  $folderId));
        if (!KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $options);
        }
    	
        $selectedPermissions = $_REQUEST['foo'];
        $permissionObjectId = $this->oFolder->getPermissionObjectId();
        
        // Check folder against criteria for determining whether to background the task
        if ($this->checkIfNeedsBackgrounding($permissionObjectId)) {
        	$this->commitTransaction();
        	$this->backgroundPermissionsUpdate($permissionObjectId, $folderId, $selectedPermissions);
        	$this->addInfoMessage('Permissions update has been backgrounded');
        	$this->redirectToMain('fFolderId=' . $folderId);
        	exit();
        }
        
        $result = KTPermissionUtil::updatePermissionObject($this->oFolder, $permissionObjectId, $selectedPermissions, $_SESSION['userID']);
        
        if ($result === false) {
            $this->rollbackTransaction();
            $msg = _kt('An error occurred while updating the permissions, please try again later');
            $this->errorRedirectToMain($msg, 'fFolderId=' . $folderId);
            exit();
        }

        require_once(KT_LIB_DIR . '/documentmanagement/observers.inc.php');
        
        $observer = new JavascriptObserver($this);
        $observer->start();
        $channel = KTPermissionChannel::getSingleton();
        $channel->addObserver($observer);

        //KTPermissionUtil::updatePermissionLookupForPO($permissionObject);
        $result = KTPermissionUtil::updatePermissionLookupForObject($permissionObjectId, $folderId);
        if ($result === false) {
            $this->rollbackTransaction();
            $this->errorRedirectTo('edit', _kt('An error occurred while updating the permissions'), 'fFolderId=' . $folderId);
            exit;
        }

        $this->commitTransaction();

        $this->addInfoMessage(_kt('Permissions on folder updated'));
        $observer->redirect(KTUtil::addQueryString($_SERVER['PHP_SELF'], 'action=edit&fFolderId=' . $folderId));
        exit(0);
    }
    
    function do_inheritPermissions() {
        $aOptions = array('redirect_to' => array('main', 'fFolderId=' .  $this->oFolder->getId()));

        if (!KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $aOptions);
        }

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $this->oFolder->getId(),
            'comment' => _kt('Inherit permissions from parent'),
            'transactionNS' => 'ktcore.transactions.permissions_change',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
            'parentid' => $this->oFolder->getParentID(),
        ));
        $aOptions = array(
            'defaultmessage' => _kt('Error updating permissions'),
            'redirect_to' => array('edit', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );
        $this->oValidator->notErrorFalse($oTransaction, $aOptions);

        $res = KTPermissionUtil::inheritPermissionObject($this->oFolder);
        if ($res === false) {
            $this->rollbackTransaction();
            $this->errorRedirectTo('main', _kt('Error, permissions could not be updated'),
                array('fFolderId' => $this->oFolder->getId()));
        }

        return $this->successRedirectTo('main', _kt('Permissions updated'), array('fFolderId' => $this->oFolder->getId()));
    }

    function do_newDynamicPermission() {
        $aOptions = array('redirect_to' => array('main', 'fFolderId=' .  $this->oFolder->getId()));
        if (!KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $aOptions);
        }

        $aOptions = array(
            'redirect_to' => array('edit', 'fFolderId=' .  $this->oFolder->getId()),
        );

        $oGroup =& $this->oValidator->validateGroup($_REQUEST['fGroupId'], $aOptions);
        $oCondition =& $this->oValidator->validateCondition($_REQUEST['fConditionId'], $aOptions);

        $aPermissionIds = (array) $_REQUEST['fPermissionIds'];
        if (empty($aPermissionIds)) { $this->errorRedirectTo('edit', _kt('Please select one or more permissions.'), sprintf('fFolderId=%d', $this->oFolder->getId())); }

        $permissionObject = KTPermissionObject::get($this->oFolder->getPermissionObjectId());

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $this->oFolder->getId(),
            'comment' => _kt('Added dynamic permissions'),
            'transactionNS' => 'ktcore.transactions.permissions_change',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
            'parentid' => $this->oFolder->getParentID(),
        ));
        $aOptions = array(
            'defaultmessage' => _kt('Error updating permissions'),
            'redirect_to' => array('edit', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );
        $this->oValidator->notErrorFalse($oTransaction, $aOptions);

        $oDynamicCondition = KTPermissionDynamicCondition::createFromArray(array(
            'groupid' => $oGroup->getId(),
            'conditionid' => $oCondition->getId(),
            'permissionobjectid' => $permissionObject->getId(),
        ));
        $this->oValidator->notError($oDynamicCondition, $aOptions);
        $res = $oDynamicCondition->saveAssignment($aPermissionIds);
        $this->oValidator->notError($res, $aOptions);
        $res = KTPermissionUtil::updatePermissionLookupForPO($permissionObject);
        if ($res === false) {
            $this->rollbackTransaction();
            $this->errorRedirectTo('edit', _kt('An error occurred while adding the dynamic permission'), 'fFolderId=' . $this->oFolder->getId());
            exit;
        }
        $this->commitTransaction();
        $this->successRedirectTo('edit', _kt('Dynamic permission added'), 'fFolderId=' . $this->oFolder->getId());
    }

    function do_removeDynamicCondition() {
        $aOptions = array('redirect_to' => array('main', 'fFolderId=' .  $this->oFolder->getId()));
        if (!KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $aOptions);
        }
        $aOptions = array(
            'redirect_to' => array('edit', 'fFolderId=' .  $this->oFolder->getId()),
        );
        $oDynamicCondition =& $this->oValidator->validateDynamicCondition($_REQUEST['fDynamicConditionId'], $aOptions);
        $res = $oDynamicCondition->delete();
        $this->oValidator->notError($res, $aOptions);

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $this->oFolder->getId(),
            'comment' => _kt('Removed dynamic permissions'),
            'transactionNS' => 'ktcore.transactions.permissions_change',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
            'parentid' => $this->oFolder->getParentID(),
        ));
        $aOptions = array(
            'defaultmessage' => _kt('Error updating permissions'),
            'redirect_to' => array('edit', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );
        $this->oValidator->notErrorFalse($oTransaction, $aOptions);

        $permissionObject = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $res = KTPermissionUtil::updatePermissionLookupForPO($permissionObject);
        if ($res === false) {
            $this->rollbackTransaction();
            $this->errorRedirectTo('edit', _kt('An error occurred while removing the dynamic permission'), 'fFolderId=' . $this->oFolder->getId());
            exit;
        }
        $this->commitTransaction();
        $this->successRedirectTo('edit', _kt('Dynamic permission removed'), 'fFolderId=' . $this->oFolder->getId());
    }

    private function checkIfNeedsBackgrounding($permissionObjectId)
    {
    	// Check depth of folder tree
    	$query = "SELECT count(*) AS count FROM folders WHERE permission_object_id = {$permissionObjectId}";
    	$countFolders = DBUtil::getOneResultKey($query, 'count');
    	
    	$query = "SELECT count(*) AS count FROM documents WHERE permission_object_id = {$permissionObjectId}";
    	$countDocs = DBUtil::getOneResultKey($query, 'count');
    	
    	if ($countDocs + $countFolders < 50) {
    		return false;
    	}
    	
    	if ($countDocs > 1000) {
    		return true;
    	}
    	
    	// Check dynamic conditions
    	$dynamicConditions = KTPermissionDynamicCondition::getByPermissionObject($permissionObjectId);
    	$countDynamicConditions = (!PEAR::isError($dynamicConditions)) ? count($dynamicConditions) : 0;
    	
    	if ($countDynamicConditions > 1) {
    		return true;
    	}
    	
    	// Check workflow states
    	$wfStates = KTWorkflowUtil::getWorkflowStatesForPermissionObject($permissionObjectId);
    	$countWfStates = ($wfStates) ? count($wfStates) : 0;
    	
    	if ($countWfStates > 0) {
    		return true;
    	}
    	
    	// Check roles
    	$roleAllocations = RoleAllocation::getAllocationsForPO($permissionObjectId);
    	$countRoles = (!empty($roleAllocations)) ? count($roleAllocations) : 0;
    	
    	if ($countRoles > 0) {
    		return true;
    	}
    	
    	return false;
    }
    
    private function checkIfBackgrounded($folderId)
    {
        $accountName = (defined('ACCOUNT_NAME')) ? ACCOUNT_NAME : '';
        $backgroundPerms = new BackgroundPermissions($folderId, $accountName);
        return $backgroundPerms->checkIfBackgrounded();
    }
    
    private function backgroundPermissionsUpdate($permissionObjectId, $folderId, $selectedPermissions)
    {
    	$accountName = (defined('ACCOUNT_NAME')) ? ACCOUNT_NAME : '';
        $backgroundPerms = new BackgroundPermissions($folderId, $accountName);
        $backgroundPerms->backgroundPermissionsUpdate($permissionObjectId, $selectedPermissions, $_SESSION['userID']);
    }
}

?>
