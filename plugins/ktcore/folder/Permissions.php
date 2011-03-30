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

class KTFolderPermissionsAction extends KTFolderAction {

    var $sName = 'ktcore.actions.folder.permissions';

    var $_sEditShowPermission = 'ktcore.permissions.security';
    var $_sShowPermission = 'ktcore.permissions.security';
    var $_bAdminAlwaysAvailable = true;
    var $bAutomaticTransaction = true;

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

        $bEdit = KTPermissionUtil::userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder);
        if (KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $bEdit = true;
        }

        $sInherited = '';
        $oInherited = KTPermissionUtil::findRootObjectForPermissionObject($permissionObject);
        // This is fine, since a folder can only inherit permissions from a folder.
        if ($oInherited->getId() !== $this->oFolder->getId()) {
            $iInheritedFolderId = $oInherited->getId();
            $sInherited = join(' > ', $oInherited->getPathArray());
        }
        // only allow inheritance if not inherited, -and- folders is editable
        $bInheritable = $bEdit && ($oInherited->getId() !== $this->oFolder->getId());
        // only allow edit if the folder is editable.
        $bEdit = $bEdit && ($oInherited->getId() == $this->oFolder->getId());

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
                'name' => $c->getName(),
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
            'edit' => $bEdit,
            'inheritable' => $bInheritable,
            'inherited' => $sInherited,
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
        $iFolderId = $this->oFolder->getId();
        $permissionObject = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $aOptions = array('redirect_to' => array('main', 'fFolderId=' .  $iFolderId));

        if (!KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $aOptions);
        }

        // copy permissions if they were inherited
        $oInherited = KTPermissionUtil::findRootObjectForPermissionObject($permissionObject);
        if ($oInherited->getId() !== $iFolderId) {
            $override = KTUtil::arrayGet($_REQUEST, 'override', false);
            if (empty($override)) {
                $this->errorRedirectToMain(_kt('This folder does not override its permissions'), sprintf('fFolderId=%d', $iFolderId));
            }

            $this->startTransaction();

            if ($this->_copyPermissions() === false) {
                $this->rollbackTransaction();
                return $this->errorRedirectToMain(_kt('Could not override folder permissions'), sprintf('fFolderId=%d', $iFolderId));
            }

            $this->commitTransaction();

            $permissionObject = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        }

        // permissions in JS format
        $aPermissionsToJSON = array();
        $aPermList = KTPermission::getList();
        foreach($aPermList as $oP) {
            $aPermissionsToJSON[] = array('id' => $oP->getId(), 'name' => $oP->getHumanName());
        }

        $oJSON = new Services_JSON;
        $sJSONPermissions = $oJSON->encode($aPermissionsToJSON);

        // dynamic conditions
        $aDynamicConditions = KTPermissionDynamicCondition::getByPermissionObject($permissionObject);

        // templating
        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/folder/permissions');

        $bCanInherit = ($iFolderId != 1);

        global $default;
        if ($default->enableESignatures) {
            $sUrl = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
            $heading = _kt('You are attempting to modify permissions');
            $input['type'] = 'button';
            $input['onclick'] = "javascript: showSignatureForm('{$sUrl}', '{$heading}', 'ktcore.transactions.permissions_change', 'folder', 'update_permissions_form', 'submit', {$iFolderId});";
        }
        else {
            $input['type'] = 'submit';
            $input['onclick'] = '';
        }

        $perms = $aPermList;
        $docperms = KTPermission::getDocumentRelevantList();

        // FIXME This belongs in a shared function - see KTDocTypeAlerts.php
        $groupsAndRoles = array();
        $groups = GroupUtil::listGroups();
        // TODO checking of assigned groups and roles vs available, set active = false for assigned.
        foreach ($groups as $group) {
            $groupsAndRoles['Groups']["group_{$group->getId()}"]['name'] = $group->getName();
            $groupsAndRoles['Groups']["group_{$group->getId()}"]['active'] = 1;
        }

        $roles = Role::getList('id > 0');
        foreach ($roles as $role) {
            $groupsAndRoles['Roles']["role_{$role->getId()}"]['name'] = $role->getName();
            $groupsAndRoles['Roles']["role_{$role->getId()}"]['active'] = 1;
        }

        // Process list of existing users and groups into a format which can be easily parsed in the template.
        // Additionally set disabled (inactive) for any pre-selected select list option.
        $assigned['groups_roles'] = array();
        $assigned['users'] = array();
        foreach ($members as $key => $member) {
            $data = explode('_', $key);
            if ($data[0] != 'user') {
                $assigned['groups_roles'][] = '{id: "' . $key . '", name: "' . $member->getName() . '"}';
                $keyword = ucwords($data[0]) . 's';
                $groupsAndRoles[$keyword][$key]['active'] = 0;
            }
            else {
                $name = $member->getName();
                if (empty($name)) { $name = $member->getUserName(); }
                $assigned['users'][] = '{id: "' . $data[1] . '", name: "' . $name . '"}';
            }
        }

        global $main;
        $jsonWidget = new KTJSONLookupWidget(_kt('Groups & Roles'),
            _kt('Select groups or roles'),
            'members',
            '',
            $main,
            false,
            null,
            null,
            array(
                'groups_roles' => $groupsAndRoles,
                'assigned' => array(implode(',', $assigned['groups_roles']), implode(',', $assigned['users'])),
                'type' => 'alert',
                'parts' => 'groups',
                'selection_default' => 'Select groups and roles',
                'optgroups' => true
            )
        );

        $templateData = array(
            'iFolderId' => $iFolderId,
            'roles' => Role::getList(),
            'groups' => Group::getList(),
            'conditions' => KTSavedSearch::getConditions(),
            'dynamic_conditions' => $aDynamicConditions,
            'context' => &$this,
            'foldername' => $this->oFolder->getName(),
            'jsonpermissions' => $sJSONPermissions,
            'edit' => true,
            'permissions' => $perms,
            'document_permissions' => $docperms,
            'can_inherit' => $bCanInherit,
            'input' => $input,
            'jsonWidget' => $jsonWidget->render()
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

    function do_update() {
        $aOptions = array('redirect_to' => array('main', 'fFolderId=' .  $this->oFolder->getId()));
        if (!KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $aOptions);
        }

        $aFoo = $_REQUEST['foo'];
        $aPermissions = KTPermission::getList();

        require_once(KT_LIB_DIR . '/documentmanagement/observers.inc.php');
        $iObjectId = $this->oFolder->getPermissionObjectId();
        $iFolderId = $this->oFolder->getId();
        $permissionObject = KTPermissionObject::get($iObjectId);

        foreach ($aPermissions as $oPermission) {
            $iPermId = $oPermission->getId();

            $aAllowed = KTUtil::arrayGet($aFoo, $iPermId, array());
            $res = KTPermissionUtil::setPermissionForId($oPermission, $permissionObject, $aAllowed);

            if ($res === false) {
                $this->rollbackTransaction();
                $this->addErrorMessage(_kt('An error occurred while updating the permissions, please try again later'));
                $this->redirectToMain('action=edit&fFolderId=' . $this->oFolder->getId());
                exit;
            }
        }

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $iFolderId,
            'comment' => _kt('Updated permissions'),
            'transactionNS' => 'ktcore.transactions.permissions_change',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
            'parentid' => $this->oFolder->getParentID(),
        ));
        $aOptions = array(
            'defaultmessage' => _kt('Error updating permissions'),
            'redirect_to' => array('edit', sprintf('fFolderId=%d', $iFolderId)),
        );
        $this->oValidator->notErrorFalse($oTransaction, $aOptions);

        $observer = new JavascriptObserver($this);
        $observer->start();
        $oChannel =& KTPermissionChannel::getSingleton();
        $oChannel->addObserver($observer);

        KTUtil::startTiming(__FUNCTION__);
        //KTPermissionUtil::updatePermissionLookupForPO($permissionObject);
        $res = KTPermissionUtil::updatePermissionLookupForObject($iObjectId, $iFolderId);
        KTUtil::logTiming(__FUNCTION__);
        if ($res === false) {
            $this->rollbackTransaction();
            $this->errorRedirectTo('edit', _kt('An error occurred while updating the permissions'), 'fFolderId=' . $this->oFolder->getId());
            exit;
        }

        $this->commitTransaction();

        $this->addInfoMessage(_kt('Permissions on folder updated'));
        $observer->redirect(KTUtil::addQueryString($_SERVER['PHP_SELF'], 'action=edit&fFolderId=' . $this->oFolder->getId()));
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

}

?>
