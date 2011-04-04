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
 *
 */

// FIXME all the copy/paste stuff

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');

require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/foldermanagement/foldertransaction.inc.php');

require_once(KT_LIB_DIR . '/groups/Group.inc');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/roles/Role.inc');
require_once(KT_LIB_DIR . '/roles/roleallocation.inc.php');
require_once(KT_LIB_DIR . '/roles/documentroleallocation.inc.php');

require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionobject.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionlookup.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionassignment.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissiondescriptor.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');

class KTDocumentPermissionsAction extends KTDocumentAction {

    var $sName = 'ktcore.actions.document.permissions';
    var $_sEditShowPermission = 'ktcore.permissions.security';
    var $_sShowPermission = 'ktcore.permissions.security';
    var $_bAdminAlwaysAvailable = true;

    function getDisplayName() {
        return _kt('Permissions');
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt('Document Permissions'));
        $template = $this->oValidator->validateTemplate('ktcore/document/document_permissions');

        $oPL = KTPermissionLookup::get($this->oDocument->getPermissionLookupID());
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
            $oPLA = KTPermissionLookupAssignment::getByPermissionAndLookup($oPermission, $oPL);
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
            $users[$oUser->getName()] = $oUser;
        }
        asort($users); // ascending, per convention.

        foreach ($aActiveGroups as $id => $marker) {
            $oGroup = Group::get($id);
            $groups[$oGroup->getName()] = $oGroup;
        }
        asort($groups);

        foreach ($aActiveRoles as $id => $marker) {
            $role = Role::get($id);
            $roles[$role->getName()] = $role;
        }
        asort($roles);

        $bEdit = KTPermissionUtil::userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oDocument);
        $sInherited = '';

        $aDynamicControls = array();
        $aWorkflowControls = array();

        // handle conditions
        $iPermissionObjectId = $this->oDocument->getPermissionObjectID();
        if (!empty($iPermissionObjectId)) {
            $oPO = KTPermissionObject::get($iPermissionObjectId);
            $aDynamicConditions = KTPermissionDynamicCondition::getByPermissionObject($oPO);
            if (!PEAR::isError($aDynamicConditions)) {
                foreach ($aDynamicConditions as $oDynamicCondition) {
                    $iConditionId = $oDynamicCondition->getConditionId();
                    if (KTSearchUtil::testConditionOnDocument($iConditionId, $this->oDocument)) {
                        $aPermissionIds = $oDynamicCondition->getAssignment();
                        foreach ($aPermissionIds as $iPermissionId) {
                            $aDynamicControls[$iPermissionId] = true;
                        }
                    }
                }
            }
        }

        // indicate that workflow controls a given permission
        $oState = KTWorkflowUtil::getWorkflowStateForDocument($this->oDocument);
        if (!(PEAR::isError($oState) || is_null($oState) || ($oState == false))) {
            $aWorkflowStatePermissionAssignments = KTWorkflowStatePermissionAssignment::getByState($oState);
            foreach ($aWorkflowStatePermissionAssignments as $oAssignment) {
                $aWorkflowControls[$oAssignment->getPermissionId()] = true;
                unset($aDynamicControls[$oAssignment->getPermissionId()]);
            }
        }


        $templateData = array(
                            'context' => $this,
                            'permissions' => $aPermissions,
                            'groups' => $groups,
                            'users' => $users,
                            'roles' => $roles,
                            'iDocumentID' => $_REQUEST['fDocumentID'],
                            'aMapPermissionGroup' => $aMapPermissionGroup,
                            'aMapPermissionRole' => $aMapPermissionRole,
                            'aMapPermissionUser' => $aMapPermissionUser,
                            'edit' => $bEdit,
                            'inherited' => $sInherited,
                            'workflow_controls' => $aWorkflowControls,
                            'conditions_control' => $aDynamicControls,
        );

        return $template->render($templateData);
    }

    function do_resolved_users() {
        $this->oPage->setBreadcrumbDetails(_kt('Permissions'));
        $template = $this->oValidator->validateTemplate('ktcore/document/resolved_permissions_user');

        $oPL = KTPermissionLookup::get($this->oDocument->getPermissionLookupID());
        $aPermissions = KTPermission::getList();
        $aMapPermissionGroup = array();
        $aMapPermissionRole = array();
        $aMapPermissionUser = array();

        $aUsers = User::getList();

        foreach ($aPermissions as $oPermission) {
            $oPLA = KTPermissionLookupAssignment::getByPermissionAndLookup($oPermission, $oPL);
            if (PEAR::isError($oPLA)) {
                continue;
            }

            $oDescriptor = KTPermissionDescriptor::get($oPLA->getPermissionDescriptorID());
            $iPermissionID = $oPermission->getID();
            $aMapPermissionGroup[$iPermissionID] = array();
            foreach ($aUsers as $oUser) {
                if (KTPermissionUtil::userHasPermissionOnItem($oUser, $oPermission, $this->oDocument)) {
                    $aMapPermissionUser[$iPermissionID][$oUser->getId()] = true;
                    $aActiveUsers[$oUser->getId()] = true;
                }
            }
        }

        // now we constitute the actual sets.
        $users = array();
        $groups = array();
        $roles = array(); // should _always_ be empty, barring a bug in permissions::updatePermissionLookup
        // this should be quite limited - direct role -> user assignment is typically rare.
        foreach ($aActiveUsers as $id => $marker) {
            $oUser = User::get($id);
            $users[$oUser->getName()] = $oUser;
        }
        asort($users); // ascending, per convention.

        $bEdit = false;
        $sInherited = '';
        $aDynamicControls = array();
        $aWorkflowControls = array();

        // handle conditions
        $iPermissionObjectId = $this->oDocument->getPermissionObjectID();
        if (!empty($iPermissionObjectId)) {
            $oPO = KTPermissionObject::get($iPermissionObjectId);
            $aDynamicConditions = KTPermissionDynamicCondition::getByPermissionObject($oPO);
            if (!PEAR::isError($aDynamicConditions)) {
                foreach ($aDynamicConditions as $oDynamicCondition) {
                    $iConditionId = $oDynamicCondition->getConditionId();
                    if (KTSearchUtil::testConditionOnDocument($iConditionId, $this->oDocument)) {
                        $aPermissionIds = $oDynamicCondition->getAssignment();
                        foreach ($aPermissionIds as $iPermissionId) {
                            $aDynamicControls[$iPermissionId] = true;
                        }
                    }
                }
            }
        }

        // indicate that workflow controls a given permission
        $oState = KTWorkflowUtil::getWorkflowStateForDocument($this->oDocument);
        if (!(PEAR::isError($oState) || is_null($oState) || ($oState == false))) {
            $aWorkflowStatePermissionAssignments = KTWorkflowStatePermissionAssignment::getByState($oState);
            foreach ($aWorkflowStatePermissionAssignments as $oAssignment) {
                $aWorkflowControls[$oAssignment->getPermissionId()] = true;
                unset($aDynamicControls[$oAssignment->getPermissionId()]);
            }
        }

        $templateData = array(
                            'context' => $this,
                            'permissions' => $aPermissions,
                            'groups' => $groups,
                            'users' => $users,
                            'roles' => $roles,
                            'oDocument' => $this->oDocument,
                            'aMapPermissionGroup' => $aMapPermissionGroup,
                            'aMapPermissionRole' => $aMapPermissionRole,
                            'aMapPermissionUser' => $aMapPermissionUser,
                            'edit' => $bEdit,
                            'inherited' => $sInherited,
                            'workflow_controls' => $aWorkflowControls,
                            'conditions_control' => $aDynamicControls,
        );

        return $template->render($templateData);
    }

}

class KTRoleAllocationPlugin extends KTFolderAction {

    var $sName = 'ktcore.actions.folder.roles';

    var $_sShowPermission = 'ktcore.permissions.security';
    var $bAutomaticTransaction = true;
    var $_bAdminAlwaysAvailable = true;

    function getDisplayName() {
        return _kt('Allocate Roles');
    }

    function do_main() {
        $this->oPage->setTitle(_kt('Allocate Roles'));
        $this->oPage->setBreadcrumbDetails(_kt('Allocate Roles'));
        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/folder/roles');

        // we need to have:
        //   - a list of roles
        //   - with their users / groups
        //   - and that allocation id
        $aRoles = array(); // stores data for display.
        $aRoleList = Role::getList('id > 0');
        foreach ($aRoleList as $role) {
            $iRoleId = $role->getId();
            $aRoles[$iRoleId] = array('name' => $role->getName());
            $roleAllocation = RoleAllocation::getAllocationsForFolderAndRole($this->oFolder->getId(), $iRoleId);

            $u = array();
            $g = array();
            $aid = null;
            $raid = null;
            if ($roleAllocation == null) {
                ; // nothing.
            } else {
                $raid = $roleAllocation->getId(); // real_alloc_id
                if ($roleAllocation->getFolderId() == $this->oFolder->getId()) {
                    $aid = $roleAllocation->getid(); // alloc_id
                }
                $oPermDesc = KTPermissionDescriptor::get($roleAllocation->getPermissionDescriptorId());
                if (!PEAR::isError($oPermDesc)) {
                    $allowed = $oPermDesc->getAllowed();
                    if (!empty($allowed['user'])) {
                        $u = $allowed['user'];
                    }
                    if (!empty($allowed['group'])) {
                        $g = $allowed['group'];
                    }
                }
            }
            $aRoles[$iRoleId]['users'] = $u;
            $aRoles[$iRoleId]['groups'] = $g;
            $aRoles[$iRoleId]['allocation_id'] = $aid;
            $aRoles[$iRoleId]['real_allocation_id'] = $raid;
        }

        /*
        print '<pre>';
        var_dump($aRoles);
        print '</pre>';
        */

        // FIXME this is test data.
        /*
        $aRoles = array(
        1 => array('name' => 'Manager', 'users' => array(1), 'groups' => array(1), 'allocation_id' => 1),
        2 => array('name' => 'Peasant', 'users' => array(1), 'groups' => array(), 'allocation_id' => 2),
        3 => array('name' => 'Inherited', 'users' => array(), 'groups' => array(1), 'allocation_id' => null),
        );
        */

        // final step.

        // Include the electronic signature
        global $default;
        $folderId = $this->oFolder->getId() ;
        if ($default->enableESignatures) {
            $sign = true;
            $url = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
            $heading = _kt('You are attempting to modify roles');
            $input_href = '#';
        } else {
            $sign = false;
            $input_onclick = '';
        }

        // map to users, groups.
        foreach ($aRoles as $key => $role) {
            $_users = array();
            foreach ($aRoles[$key]['users'] as $iUserId) {
                $oUser = User::get($iUserId);
                if (!(PEAR::isError($oUser) || ($oUser == false))) {
                    $_users[] = $oUser->getName();
                }
            }

            if (empty($_users)) {
                $aRoles[$key]['users'] = '<span class="descriptiveText"> ' . _kt('no users') . '</span>';
            } else {
                $aRoles[$key]['users'] = join(', ',$_users);
            }

            $_groups = array();
            foreach ($aRoles[$key]['groups'] as $groupId) {
                $oGroup = Group::get($groupId);
                if (!(PEAR::isError($oGroup) || ($oGroup == false))) {
                    $_groups[] = $oGroup->getName();
                }
            }

            if (empty($_groups)) {
                $aRoles[$key]['groups'] = '<span class="descriptiveText"> ' . _kt('no groups') . '</span>';
            } else {
                $aRoles[$key]['groups'] = join(', ',$_groups);
            }

            if ($sign) {
                $redirect_url = KTUtil::addQueryStringSelf("action=useParent&role_id={$key}&fFolderId={$folderId}");
                $input_onclick = "javascript: showSignatureForm('{$url}', '{$heading}', 'ktcore.transactions.role_allocations_change', 'folder', '{$redirect_url}', 'redirect', {$folderId});";
            } else {
                $input_href = KTUtil::addQueryStringSelf("action=useParent&role_id={$key}&fFolderId={$folderId}");
            }

            $aRoles[$key]['onclick'] =$input_onclick;
            $aRoles[$key]['href'] =$input_href;
        }

        $templateData = array(
                            'context' => &$this,
                            'roles' => $aRoles,
                            'folderName'=>$this->oFolder->getName(),
                            'is_root' => ($this->oFolder->getId() == 1),
        );

        return $template->render($templateData);
    }

    function do_overrideParent() {
        $role_id = KTUtil::arrayGet($_REQUEST, 'role_id', null);
        $role = Role::get($role_id);
        if (PEAR::isError($role)) {
            $this->errorRedirectToMain(_kt('Invalid Role.'));
        }

        // FIXME do we need to check that this role _isn't_ allocated?
        $roleAllocation = new RoleAllocation();
        $roleAllocation->setFolderId($this->oFolder->getId());
        $roleAllocation->setRoleId($role_id);

        // create a new permission descriptor.
        // FIXME we really want to duplicate the original (if it exists)

        $allowed = array(); // no-op, for now.
        $this->startTransaction();

        $roleAllocation->setAllowed($allowed);
        $res = $roleAllocation->create();

        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectToMain(_kt('Failed to create the role allocation.') . print_r($res, true), sprintf('fFolderId=%d', $this->oFolder->getId()));
        }

        $transaction = KTFolderTransaction::createFromArray(array(
                            'folderid' => $this->oFolder->getId(),
                            'comment' => _kt('Override parent allocation'),
                            'transactionNS' => 'ktcore.transactions.role_allocations_change',
                            'userid' => $_SESSION['userID'],
                            'ip' => Session::getClientIP(),
                            'parentid' => $this->oFolder->getParentID(),
        ));

        $options = array(
                        'defaultmessage' => _kt('Error creating allocation'),
                        'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );

        $this->oValidator->notErrorFalse($transaction, $options);

        // inherit parent permissions
        $oParentAllocation = RoleAllocation::getAllocationsForFolderAndRole($this->oFolder->getParentID(), $role_id);
        if (!is_null($oParentAllocation) && !PEAR::isError($oParentAllocation)) {
            $permissionDescriptor = $oParentAllocation->getPermissionDescriptor();

            $allowed = $permissionDescriptor->getAllowed();
            $userids=$allowed['user'];
            $groupids=$allowed['group'];

            // now lets update for the new allocation
            $permissionDescriptor = $roleAllocation->getPermissionDescriptor();

            $allowed = $permissionDescriptor->getAllowed();

            $allowed['user'] = $userids;
            $allowed['group'] = $groupids;

            $roleAllocation->setAllowed($allowed);
            $res = $roleAllocation->update();

            if (PEAR::isError($res) || ($res == false)) {
                $this->errorRedirectToMain(_kt('Failed to create the role allocation.') . print_r($res, true), sprintf('fFolderId=%d', $this->oFolder->getId()));
            }
        }

        $this->renegeratePermissionsForRole($roleAllocation->getRoleId());

        $this->successRedirectToMain(_kt('Role allocation created.'), sprintf('fFolderId=%d', $this->oFolder->getId()));
    }

    function do_useParent() {
        $role_id = KTUtil::arrayGet($_REQUEST, 'role_id', null);
        $role = Role::get($role_id);
        if (PEAR::isError($role)) {
            $this->errorRedirectToMain(_kt('Invalid Role.'), sprintf('fFolderId=%d',$this->oFolder->getId()));
        }

        $role_id = $role->getId(); // numeric, for various testing purposes.
        $roleAllocation = RoleAllocation::getAllocationsForFolderAndRole($this->oFolder->getId(), $role_id);
        if ($roleAllocation->getFolderId() != $this->oFolder->getId()) {
            $this->errorRedirectToMain(_kt('Already using a different descriptor.'), sprintf('fFolderId=%d',$this->oFolder->getId()));
        }

        $this->startTransaction();

        $res = $roleAllocation->delete();
        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectToMain(_kt('Unable to change role allocation.') . print_r($res, true), sprintf('fFolderId=%d',$this->oFolder->getId()));
            exit(0);
        }

        $transaction = KTFolderTransaction::createFromArray(array(
                            'folderid' => $this->oFolder->getId(),
                            'comment' => _kt('Use parent allocation'),
                            'transactionNS' => 'ktcore.transactions.role_allocations_change',
                            'userid' => $_SESSION['userID'],
                            'ip' => Session::getClientIP(),
                            'parentid' => $this->oFolder->getParentID(),
        ));

        $options = array(
                        'defaultmessage' => _kt('Problem assigning role to parent allocation'),
                        'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );

        $this->oValidator->notErrorFalse($transaction, $options);

        $this->renegeratePermissionsForRole($roleAllocation->getRoleId());

        $this->successRedirectToMain(_kt('Role now uses parent.'), sprintf('fFolderId=%d',$this->oFolder->getId()));
    }

    function rootoverride($role_id) {
        if ($this->oFolder->getId() != 1) {
            $this->errorRedirectToMain(_kt('Cannot create allocation for non-root locations.'));
        }

        $roleAllocation = new RoleAllocation();
        $roleAllocation->setFolderId($this->oFolder->getId());
        $roleAllocation->setRoleId($role_id);

        // create a new permission descriptor.
        // FIXME we really want to duplicate the original (if it exists)

        $allowed = array(); // no-op, for now.
        $this->startTransaction();

        $roleAllocation->setAllowed($allowed);
        $res = $roleAllocation->create();

        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectToMain(_kt('Failed to create the role allocation.') . print_r($res, true), sprintf('fFolderId=%d', $this->oFolder->getId()));
        }

        return $roleAllocation;
    }

    function do_editRoleUsers() {
        $folderId = $this->oFolder->getId();

        $roleAllocationId = KTUtil::arrayGet($_REQUEST, 'alloc_id');
        if (($folderId == 1) && is_null($roleAllocationId)) {
            $roleAllocation = $this->rootoverride($_REQUEST['role_id']);
        } else {
            $roleAllocation = RoleAllocation::get($roleAllocationId);
        }

        if ((PEAR::isError($roleAllocation)) || ($roleAllocation === false)) {
            $this->errorRedirectToMain(_kt('No such role allocation.'), sprintf('fFolderId=%d',$folderId));
        }

        $this->oPage->setBreadcrumbDetails(_kt('Manage Users for Role'));
        $this->oPage->setTitle(sprintf(_kt('Manage Users for Role')));

        $initJS = 'var optGroup = new OptionTransfer("userSelect","chosenUsers"); ' .
                            'function startTrans() { var f = getElement("userroleform"); ' .
                            ' optGroup.saveNewRightOptions("userFinal"); ' .
                            ' optGroup.init(f); }; ' .
                            ' addLoadEvent(startTrans); ';
        $this->oPage->requireJSStandalone($initJS);

        $initialUsers = $roleAllocation->getUsers();
        $allUsers = User::getList('id > 0 AND disabled = 0');

        // FIXME this is massively non-performant for large userbases..
        $roleUsers = array();
        $freeUsers = array();
        foreach ($initialUsers as $oUser) {
            $roleUsers[$oUser->getId()] = $oUser;
        }

        foreach ($allUsers as $oUser) {
            if (!array_key_exists($oUser->getId(), $roleUsers)) {
                $freeUsers[$oUser->getId()] = $oUser;
            }
        }

        // Include the electronic signature on the permissions action
        global $default;
        if ($default->enableESignatures) {
            $url = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
            $heading = _kt('You are attempting to modify roles');
            $input['type'] = 'button';
            $input['onclick'] = "javascript: showSignatureForm('{$url}', '{$heading}', 'ktcore.transactions.role_allocations_change', 'folder', 'userroleform', 'submit', {$folderId});";
        } else {
            $input['type'] = 'submit';
            $input['onclick'] = '';
        }

        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/folder/roles_manageusers');
        $templateData = array(
                            'context' => $this,
                            'edit_rolealloc' => $roleAllocation,
                            'unused_users' => $freeUsers,
                            'role_users' => $roleUsers,
                            'input' => $input
        );

        return $template->render($templateData);
    }

    function do_editRoleGroups() {
        $folderId = $this->oFolder->getId();

        $roleAllocationId = KTUtil::arrayGet($_REQUEST, 'alloc_id');
        if (($folderId == 1) && is_null($roleAllocationId)) {
            $roleAllocation = $this->rootoverride($_REQUEST['role_id']);
        } else {
            $roleAllocation = RoleAllocation::get($roleAllocationId);
        }

        if ((PEAR::isError($roleAllocation)) || ($roleAllocation === false)) {
            $this->errorRedirectToMain(_kt('No such role allocation.'), sprintf('fFolderId=%d', $folderId));
        }

        $role = Role::get($roleAllocation->getRoleId());
        $this->oPage->setBreadcrumbDetails(_kt('Manage Groups for Role'));
        $this->oPage->setTitle(sprintf(_kt('Manage Groups for Role "%s"'), $role->getName()));

        // NOTE Not sure what this code did, but don't think it is required for the new widget.
        // TODO remove this comment and the commented code if date after 1 May 2011 and no issues found.
        //$initJS = 'var optGroup = new OptionTransfer("groupSelect","chosenGroups"); ' .
        //                    'function startTrans() { var f = getElement("grouproleform"); ' .
        //                    ' optGroup.saveNewRightOptions("groupFinal"); ' .
        //                    ' optGroup.init(f); }; ' .
        //                    ' addLoadEvent(startTrans); ';
        //$this->oPage->requireJSStandalone($initJS);

        // FIXME This is massively non-performant for large userbases.
        $memberGroups = $roleAllocation->getGroups();
        $members = array();
        foreach ($memberGroups as $group) {
            $members["group_{$group->getId()}"] = $group;
        }

        // Include the electronic signature on the permissions action
        global $default;
        if ($default->enableESignatures) {
            $url = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
            $heading = _kt('You are attempting to modify roles');
            $input['type'] = 'button';
            $input['onclick'] = "javascript: showSignatureForm('{$url}', '{$heading}', 'ktcore.transactions.role_allocations_change', 'folder', 'grouproleform', 'submit', {$folderId});";
        } else {
            $input['type'] = 'submit';
            $input['onclick'] = '';
        }

        $groups = KTJSONLookupWidget::getGroupsForSelector();
        $assigned = KTJSONLookupWidget::getAssignedGroupsForSelector($groups, $members);
        $options = array('groups_roles' => $groups, 'selection_default' => 'Select groups', 'optgroups' => false);
        $label['header'] = 'Groups';
        $label['text'] = 'Select the groups which should be part of this role.';
        $jsonWidget = $this->getJsonWidget($label, 'groups', 'groups', $assigned, $options);

        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/folder/roles_managegroups');
        $templateData = array(
                            'context' => $this,
                            'edit_rolealloc' => $roleAllocation,
                            'rolename' => $role->getName(),
                            'input' => $input,
                            'jsonWidget' => $jsonWidget->render()
        );

        return $template->render($templateData);
    }

    private function getJsonWidget($label, $type, $parts, $assigned, $options)
    {
        global $main;

        $baseOptions = array(
                            'assigned' => $assigned,
                            'type' => $type,
                            'parts' => $parts
        );
        $options = array_merge($baseOptions, $options);

        $jsonWidget = new KTJSONLookupWidget(_kt($label['header']),
            _kt($label['text']),
            'members',
            '',
            $main,
            false,
            null,
            null,
            $options
        );

        return $jsonWidget;
    }

    function do_setRoleUsers() {
        $roleAllocationId = KTUtil::arrayGet($_REQUEST, 'allocation_id');
        $roleAllocation = RoleAllocation::get($roleAllocationId);
        if ((PEAR::isError($roleAllocation)) || ($roleAllocation === false)) {
            $this->errorRedirectToMain(_kt('No such role allocation.'), sprintf('fFolderId=%d',$this->oFolder->getId()));
        }

        $users = KTUtil::arrayGet($_REQUEST, 'userFinal', '');
        $aUserIds = explode(',', $users);

        // check that its not corrupt..
        $aFinalUserIds = array();
        foreach ($aUserIds as $iUserId) {
            $oUser =& User::get($iUserId);
            if (!(PEAR::isError($oUser) || ($oUser == false))) {
                $aFinalUserIds[] = $iUserId;
            }
        }

        if (empty($aFinalUserIds)) { $aFinalUserIds = null; }

        // hack straight in.
        $permissionDescriptor = $roleAllocation->getPermissionDescriptor();
        $allowed = $permissionDescriptor->getAllowed();

        // now, grab the existing allowed and modify.
        $allowed['user'] = $aFinalUserIds;
        $roleAllocation->setAllowed($allowed);
        $res = $roleAllocation->update();
        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectToMain(_kt('Failed to change the role allocation.') . print_r($res, true), sprintf('fFolderId=%d', $this->oFolder->getId()));
        }

        $transaction = KTFolderTransaction::createFromArray(array(
                            'folderid' => $this->oFolder->getId(),
                            'comment' => _kt('Set role users'),
                            'transactionNS' => 'ktcore.transactions.role_allocations_change',
                            'userid' => $_SESSION['userID'],
                            'ip' => Session::getClientIP(),
                            'parentid' => $this->oFolder->getParentID(),
        ));

        $options = array(
                            'defaultmessage' => _kt('Problem assigning role users'),
                            'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );

        $this->oValidator->notErrorFalse($transaction, $options);

        $this->renegeratePermissionsForRole($roleAllocation->getRoleId());

        $this->successRedirectToMain(_kt('Allocation changed.'), sprintf('fFolderId=%d',$this->oFolder->getId()));
    }

    function do_setRoleGroups() {
        $roleAllocationId = KTUtil::arrayGet($_REQUEST, 'allocation_id');
        $roleAllocation = RoleAllocation::get($roleAllocationId);
        if ((PEAR::isError($roleAllocation)) || ($roleAllocation === false)) {
            $this->errorRedirectToMain(_kt('No such role allocation.'), sprintf('fFolderId=%d', $this->oFolder->getId()));
        }

        // NOTE This checking that groups actually exist isn't done the same everywhere
        //      (if it is done elsewhere at all.)
        $finalGroupIds = array();
        $groups = trim(KTUtil::arrayGet($_REQUEST, 'groups_roles', ''), ',');
        if (!empty($groups)) {
            $groups = explode(',', $groups);
            foreach ($groups as $idString) {
                $idData = explode('_', $idString);
                $groupId = $idData[1];
                $group = Group::get($groupId);

                // NOTE This checking that groups actually exist isn't done the same everywhere
                //      (if it is done elsewhere at all.)
                if (!(PEAR::isError($group) || ($group == false))) {
                    $finalGroupIds[] = $groupId;
                }
            }
        }

        if (empty($finalGroupIds)) {
            $finalGroupIds = null;
        }

        // hack straight in.
        $permissionDescriptor = $roleAllocation->getPermissionDescriptor();
        $allowed = $permissionDescriptor->getAllowed();

        // now, grab the existing allowed and modify.
        $allowed['group'] = $finalGroupIds;
        $roleAllocation->setAllowed($allowed);
        $res = $roleAllocation->update();

        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectToMain(_kt('Failed to change the role allocation.') . print_r($res, true), sprintf('fFolderId=%d', $this->oFolder->getId()));
        }

        $transaction = KTFolderTransaction::createFromArray(array(
                            'folderid' => $this->oFolder->getId(),
                            'comment' => _kt('Set role groups'),
                            'transactionNS' => 'ktcore.transactions.role_allocations_change',
                            'userid' => $_SESSION['userID'],
                            'ip' => Session::getClientIP(),
                            'parentid' => $this->oFolder->getParentID(),
        ));

        $options = array(
                            'defaultmessage' => _kt('Problem assigning role groups'),
                            'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );

        $this->oValidator->notErrorFalse($transaction, $options);

        $this->renegeratePermissionsForRole($roleAllocation->getRoleId());

        $this->successRedirectToMain(_kt('Allocation changed.'), sprintf('fFolderId=%d', $this->oFolder->getId()));
    }

    function renegeratePermissionsForRole($iRoleId) {
        $iStartFolderId = $this->oFolder->getId();
        /*
        * 1. find all folders & documents 'below' this one which use the role
        *    definition _active_ (not necessarily present) at this point.
        * 2. tell permissionutil to regen their permissions.
        *
        * The find algorithm is:
        *
        *  folder_queue <- (iStartFolderId)
        *  while folder_queue is not empty:
        *     active_folder =
        *     for each folder in the active_folder:
        *         find folders in _this_ folder without a role-allocation on the iRoleId
        *            add them to the folder_queue
        *         update the folder's permissions.
        *         find documents in this folder:
        *            update their permissions.
        */

        $sRoleAllocTable = KTUtil::getTableName('role_allocations');
        $sFolderTable = KTUtil::getTableName('folders');
        $sQuery = sprintf('SELECT f.id as id FROM %s AS f LEFT JOIN %s AS ra ON (f.id = ra.folder_id) WHERE ra.id IS NULL AND f.parent_id = ?', $sFolderTable, $sRoleAllocTable);

        $folder_queue = array($iStartFolderId);
        while (!empty($folder_queue)) {
            $active_folder = array_pop($folder_queue);
            $aParams = array($active_folder);
            $aNewFolders = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');
            if (PEAR::isError($aNewFolders)) {
                $this->errorRedirectToMain(_kt('Failure to generate folderlisting.'));
            }

            $folder_queue = kt_array_merge ($folder_queue, (array) $aNewFolders); // push.

            // update the folder.
            $oFolder =& Folder::get($active_folder);
            if (PEAR::isError($oFolder) || ($oFolder == false)) {
                $this->errorRedirectToMain(_kt('Unable to locate folder: ') . $active_folder);
            }

            KTPermissionUtil::updatePermissionLookup($oFolder);
            $aDocList =& Document::getList(array('folder_id = ?', $active_folder));
            if (PEAR::isError($aDocList) || ($aDocList === false)) {
                $this->errorRedirectToMain(sprintf(_kt('Unable to get documents in folder %s: %s'), $active_folder, $aDocList->getMessage()));
            }

            foreach ($aDocList as $oDoc) {
                if (!PEAR::isError($oDoc)) {
                    KTPermissionUtil::updatePermissionLookup($oDoc);
                }
            }
        }

        // Force the permissions cache to update
        KTPermissionUtil::clearCache();
    }
}

class KTDocumentRolesAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.roles';

    var $_sShowPermission = 'ktcore.permissions.write';
    var $bAutomaticTransaction = true;

    function getDisplayName() {
        return _kt('View Roles');
    }

    function do_main() {
        $this->oPage->setTitle(_kt('View Roles'));
        $this->oPage->setBreadcrumbDetails(_kt('View Roles'));
        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/action/view_roles');

        // we need to have:
        //   - a list of roles
        //   - with their users / groups
        //   - and that allocation id
        $aRoles = array(); // stores data for display.

        $aRoleList = Role::getList();
        foreach ($aRoleList as $role) {
            $iRoleId = $role->getId();
            $aRoles[$iRoleId] = array('name' => $role->getName());
            $roleAllocation = DocumentRoleAllocation::getAllocationsForDocumentAndRole($this->oDocument->getId(), $iRoleId);
            if (is_null($roleAllocation)) {
                $roleAllocation = RoleAllocation::getAllocationsForFolderAndRole($this->oDocument->getFolderID(), $iRoleId);
            }

            $u = array();
            $g = array();
            $aid = null;
            $raid = null;
            if (is_null($roleAllocation)) {
                ; // nothing.
            } else {
                //var_dump($roleAllocation);
                $raid = $roleAllocation->getId(); // real_alloc_id
                $allowed = $roleAllocation->getAllowed();

                if (!empty($allowed['user'])) {
                    $u = $allowed['user'];
                }

                if (!empty($allowed['group'])) {
                    $g = $allowed['group'];
                }
            }
            $aRoles[$iRoleId]['users'] = $u;
            $aRoles[$iRoleId]['groups'] = $g;
            $aRoles[$iRoleId]['real_allocation_id'] = $raid;
        }

        // final step.

        // map to users, groups.
        foreach ($aRoles as $key => $role) {
            $_users = array();
            foreach ($aRoles[$key]['users'] as $iUserId) {
                $oUser = User::get($iUserId);
                if (!(PEAR::isError($oUser) || ($oUser == false))) {
                    $_users[] = $oUser->getName();
                }
            }

            if (empty($_users)) {
                $aRoles[$key]['users'] = '<span class="descriptiveText"> ' . _kt('no users') . '</span>';
            } else {
                $aRoles[$key]['users'] = implode(', ',$_users);
            }

            $_groups = array();
            foreach ($aRoles[$key]['groups'] as $groupId) {
                $oGroup = Group::get($groupId);
                if (!(PEAR::isError($oGroup) || ($oGroup == false))) {
                    $_groups[] = $oGroup->getName();
                }
            }

            if (empty($_groups)) {
                $aRoles[$key]['groups'] = '<span class="descriptiveText"> ' . _kt('no groups') . '</span>';
            } else {
                $aRoles[$key]['groups'] = implode(', ',$_groups);
            }
        }

        $templateData = array(
                            'context' => &$this,
                            'roles' => $aRoles,
        );

        return $template->render($templateData);
    }
}
