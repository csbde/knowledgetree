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

require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/groups/GroupUtil.php');
require_once(KT_LIB_DIR . '/groups/Group.inc');
require_once(KT_LIB_DIR . '/unitmanagement/Unit.inc');

require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/widgets/forms.inc.php');

require_once(KT_LIB_DIR . '/authentication/authenticationsource.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationproviderregistry.inc.php');
require_once(KT_LIB_DIR . '/authentication/builtinauthenticationprovider.inc.php');

class KTGroupAdminDispatcher extends KTAdminDispatcher {

    public $sHelpPage = 'ktcore/admin/manage groups.html';
    public $aCannotView = array('starter', 'professional');

    function predispatch()
    {
        $this->persistParams(array('old_search'));
    }

    function do_main()
    {
        //$this->oPage->setBreadcrumbDetails(_kt('select a group'));
        //$this->oPage->setTitle(_kt('Group Management'));

        $KTConfig =& KTConfig::getSingleton();
        $alwaysAll = 1; //$KTConfig->get('alwaysShowAll');

        $groupId = KTUtil::arrayGet($_REQUEST, 'group_id');
        $noSearch = (KTUtil::arrayGet($_REQUEST, 'do_search', false) === false);
        $name = KTUtil::arrayGet($_REQUEST, 'search_name', KTUtil::arrayGet($_REQUEST, 'old_search'));
        if ($name == '*') {
            $showAll = true;
            $name = '';
        }
        else {
            $showAll = KTUtil::arrayGet($_REQUEST, 'show_all', $alwaysAll);
        }

        $searchFields = array();
        $searchFields[] =  new KTStringWidget(_kt(''), _kt("Enter part of the group's name: <strong>ad</strong> will match <strong>administrators</strong>."), 'search_name', $name, $this->oPage, false);

        if (!empty($name)) {
            $searchResults =& Group::getList('WHERE name LIKE \'%' . DBUtil::escapeSimple($name) . '%\' AND id > 0');
        }
        else if ($showAll !== false) {
            $searchResults =& Group::getList('id > 0');
            $noSearch = false;
            $name = '*';
        }

        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/principals/groupadmin');
        $templateData = array(
            'context' => $this,
            'search_fields' => $searchFields,
            'search_results' => $searchResults,
            'no_search' => $noSearch,
            'old_search' => $name,
        );

        return $template->render($templateData);
    }

    function do_editGroup()
    {
        $this->oPage->setBreadcrumbDetails(_kt('edit group'));

        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');
        $groupId = KTUtil::arrayGet($_REQUEST, 'group_id');
        $group = Group::get($groupId);
        if (PEAR::isError($group) || $group == false) {
            $this->errorRedirectToMain(_kt('Please select a valid group.'), sprintf('old_search=%s&do_search=1', $oldSearch));
        }

        $this->oPage->setTitle(sprintf(_kt('Edit Group (%s)'), $group->getName()));

        $editFields = array();
        $editFields[] =  new KTStringWidget(_kt('Group Name'), _kt('A short name for the group.  e.g. <strong>administrators</strong>.'), 'group_name', $group->getName(), $this->oPage, true);
        $editFields[] =  new KTBooleanWidget(_kt('System Administration Privileges'), _kt('Should all the members of this group be given system administration privileges?'), 'is_sysadmin', $group->getSysAdmin(), $this->oPage, false);
        $editFields[] =  new KTBooleanWidget(_kt('Unit Administration Privileges'), _kt('Should all the members of this group be given unit administration privileges?'), 'is_unitadmin', $group->getUnitAdmin(), $this->oPage, false);


        // grab all units.
        $unitId = $group->getUnitId();
        if ($unitId == null) {
            $unitId = 0;
        }

        $units = Unit::getList();
        $vocab = array();
        $vocab[0] = _kt('No Unit');
        foreach ($units as $unit) { $vocab[$unit->getID()] = $unit->getName(); }
        $options = array('vocab' => $vocab);

        $editFields[] =  new KTLookupWidget(_kt('Unit'), _kt('Which Unit is this group part of?'), 'unit_id', $unitId, $this->oPage, false, null, null, $options);

        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/principals/editgroup');
        $templateData = array(
            'context' => $this,
            'edit_fields' => $editFields,
            'edit_group' => $group,
            'old_search' => $oldSearch,
        );

        return $template->render($templateData);
    }

    function do_saveGroup()
    {
        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');

        $group = $this->getGroupFromRequest('Please select a valid group.');
        $group_name = KTUtil::arrayGet($_REQUEST, 'group_name');
        if (empty($group_name)) { $this->errorRedirectToMain(_kt('Please specify a name for the group.')); }

        $isUnitadmin = KTUtil::arrayGet($_REQUEST, 'is_unitadmin', false);
        if ($isUnitadmin !== false) { $isUnitadmin = true; }

        $isSysadmin = KTUtil::arrayGet($_REQUEST, 'is_sysadmin', false);
        if ($isSysadmin !== false) { $isSysadmin = true; }

        $this->startTransaction();

        $group->setName($group_name);
        $group->setUnitAdmin($isUnitadmin);
        $group->setSysAdmin($isSysadmin);

        $unitId = KTUtil::arrayGet($_REQUEST, 'unit_id', 0);
        if ($unitId == 0) { // not set, or set to 0.
            $group->setUnitId(null); // safe.
        }
        else {
            $group->setUnitId($unitId);
        }

        $res = $group->update();
        if (($res == false) || (PEAR::isError($res))) { return $this->errorRedirectToMain(_kt('Failed to set group details.'), sprintf('old_search=%s&do_search=1', $oldSearch)); }

        if (!Permission::userIsSystemAdministrator($_SESSION['userID'])) {
            $this->rollbackTransaction();
            $this->errorRedirectTo('editGroup', _kt('For security purposes, you cannot remove your own administration priviledges.'), sprintf('group_id=%d', $group->getId()), sprintf('old_search=%s&do_search=1', $oldSearch));
            exit(0);
        }

        $this->commitTransaction();
        if ($unitId == 0 && $isUnitadmin) {
            $this->successRedirectToMain(_kt('Group details updated.') . _kt(' Note: group is set as unit administrator, but is not assigned to a unit.'), sprintf('old_search=%s&do_search=1', $oldSearch));
        }
        else {
            $this->successRedirectToMain(_kt('Group details updated.'), sprintf('old_search=%s&do_search=1', $oldSearch));
        }
    }

    function _do_manageUsers_source()
    {
        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');
        $group =& $this->oValidator->validateGroup($_REQUEST['group_id']);
        $groupUsers = $group->getMembers();

        $template = $this->oValidator->validateTemplate('ktcore/principals/groups_sourceusers');
        $templateData = array(
            'context' => $this,
            'group_users' => $groupUsers,
            'group' => $group,
            'old_search' => $oldSearch,
        );

        return $template->render($templateData);
    }

    function do_synchroniseGroup()
    {
        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');

        require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');
        $group =& $this->oValidator->validateGroup($_REQUEST['group_id']);
        $res = KTAuthenticationUtil::synchroniseGroupToSource($group);

        // Invalidate the permissions cache to force an update .
        KTPermissionUtil::clearCache();

        $this->successRedirectTo('manageusers', 'Group synchronised', sprintf('group_id=%d', $group->getId()), sprintf('old_search=%s&do_search=1', $oldSearch));
        exit(0);
    }

    function do_manageUsers()
    {
        $group = $this->getGroupFromRequest();
        $this->aBreadcrumbs[] = array('name' => $group->getName());
        $this->oPage->setBreadcrumbDetails(_kt('manage members'));
        $this->oPage->setTitle(sprintf(_kt('Manage members of group %s'), $group->getName()));

        $sourceId = $group->getAuthenticationSourceId();
        if (!empty($sourceId)) {
            return $this->_do_manageUsers_source();
        }

        // Set up and instantiate user search widget.
        $members = KTJSONLookupWidget::formatMemberUsers($group->getMembers());
        $label['header'] = 'Users';
        $label['text'] = 'Select the users which should be part of this group. Once you have added all the users that you require, click <strong>save changes</strong>.';
        $jsonWidget = KTJSONLookupWidget::getUserSearchWidget($label, 'group', 'users', $members);

        return $this->renderTemplateWithWidget($group, $jsonWidget, 'ktcore/principals/groups_manageusers');
    }

    private function getGroupFromRequest($message = 'No such group.')
    {
        $groupId = KTUtil::arrayGet($_REQUEST, 'group_id');
        $group = Group::get($groupId);
        if ((PEAR::isError($group)) || ($group === false)) {
            $this->errorRedirectToMain(_kt($message), sprintf('old_search=%s&do_search=1', $oldSearch));
        }

        return $group;
    }

    private function renderTemplateWithWidget($group, $jsonWidget, $template)
    {
        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate($template);
        $templateData = array(
            'context' => $this,
            'edit_group' => $group,
            'widget' => $jsonWidget,
            'old_search' => KTUtil::arrayGet($_REQUEST, 'old_search'),
        );

        return $template->render($templateData);
    }

    function do_updateUserMembers()
    {
        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');

        $group = $this->getGroupFromRequest();

        $this->startTransaction();

        // Detect existing group members (and diff with current, to see which were removed.)
        $currentUsers = $group->getMembers();
        // Probably should add a function for just getting this info, but shortcut for now.
        foreach ($currentUsers as $key => $user) {
            $name = $user->getName();
            $currentUsers[$key] = !empty($name) ? $name : $user->getUsername();
        }

        // Remove any current groups for this user.
        if (!empty($currentUsers) && !GroupUtil::removeUsersForGroup($group)) {
            $this->errorRedirectToMain(sprintf(_kt('Unable to remove existing group memberships')), sprintf('old_search=%s&do_search=1', $oldSearch));
        }

        // Insert submitted users for this group.
        $usersAdded = array();
        $addWarnings = array();
        // TODO I am sure we can do this much better, create a single insert query instead of one per added group.
        $users = trim(KTUtil::arrayGet($_REQUEST, 'users'), ',');
        if (!empty($users)) {
            $users = explode(',', $users);
            foreach ($users as $userId) {
                $user = User::get($userId);
                // Not sure this has any validity in the new method.
                $memberReason = GroupUtil::getMembershipReason($user, $group);
                if (!(PEAR::isError($memberReason) || is_null($memberReason))) {
                    $addWarnings[] = $memberReason;
                }

                $res = $group->addMember($user);
                if (PEAR::isError($res) || $res == false) {
                    $this->rollbackTransaction();
                    $this->errorRedirectToMain(sprintf(_kt('Unable to add user "%s" to group "%s"'), $user->getName(), $group->getName()), sprintf('old_search=%s&do_search=1', $oldSearch));
                }
                else {
                    $usersAdded[] = $user->getName();
                }
            }
        }

        $usersRemoved = array_diff($currentUsers, $usersAdded);
        $usersAdded = array_diff($usersAdded, $currentUsers);

        if (!empty($addWarnings)) {
            $warnStr = _kt('Warning:  some users were already members of some subgroups') . ' &mdash; ';
            $warnStr .= implode(', ', $addWarnings);
            $_SESSION['KTInfoMessage'][] = $warnStr;
        }

        $msg = '';
        if (!empty($usersAdded)) { $msg .= ' ' . _kt('Added') . ': ' . implode(', ', $usersAdded) . '. '; }
        if (!empty($usersRemoved)) { $msg .= ' ' . _kt('Removed') . ': ' . implode(', ',$usersRemoved) . '.'; }

        if (!Permission::userIsSystemAdministrator($_SESSION['userID'])) {
            $this->rollbackTransaction();
            $this->errorRedirectTo('manageUsers', _kt('For security purposes, you cannot remove your own administration priviledges.'), sprintf('group_id=%d', $group->getId()), sprintf('old_search=%s&do_search=1', $oldSearch));
            exit(0);
        }

        $this->commitTransaction();

        // Invalidate the permissions cache to force an update .
        // It is possible to update only the new / removed members of the group
        // but if there are a large number of members involved this can become an expensive operation.
        // It is cheaper to invalidate the cache and force a validation of each users permissions.
        KTPermissionUtil::clearCache();

        $this->successRedirectToMain($msg, sprintf('old_search=%s&do_search=1', $oldSearch));
    }

    function do_manageSubgroups()
    {
        $group = $this->getGroupFromRequest();
        $this->aBreadcrumbs[] = array('name' => $group->getName());
        $this->oPage->setBreadcrumbDetails(_kt('manage sub-groups'));
        $this->oPage->setTitle(sprintf(_kt('Manage sub-groups of %s'), $group->getName()));

        // Set up and instantiate group selector widget.
        $members = KTJSONLookupWidget::formatMemberGroups($group->getMemberGroups());
        $options = array('selection_default' => 'Select groups', 'optgroups' => false);
        $label['header'] = 'Groups';
        $label['text'] = 'Select the sub-groups which should be part of this group. Once you have added all the sub-groups that you require, click <strong>save changes</strong>.';
        $jsonWidget = KTJSONLookupWidget::getGroupSelectorWidget(
                                                                $label,
                                                                'group',
                                                                'groups',
                                                                $members,
                                                                $options,
                                                                $group->getId()
        );

        return $this->renderTemplateWithWidget($group, $jsonWidget, 'ktcore/principals/groups_managesubgroups');
    }

    function _getUnitName($group)
    {
        $unitId = $group->getUnitId();
        if (empty($unitId)) {
            return null;
        }

        $unit = Unit::get($unitId);
        if (PEAR::isError($unit)) {
            return null;   // XXX: prevent failure if the $unit is a PEAR::error
        }

        return $unit->getName();
    }

    function do_updateGroupMembers()
    {
        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');

        $group = $this->getGroupFromRequest();

        $this->startTransaction();

        // Detect existing sub-groups (and diff with current, to see which were removed.)
        $currentGroups = $group->getMemberGroups();
        // Probably should add a function for just getting this info, but shortcut for now.
        foreach ($currentGroups as $key => $subGroup) {
            $currentGroups[$key] = $subGroup->getName();
        }

        // Remove any current sub-groups for this group.
        if (!empty($currentGroups) && !GroupUtil::removeSubGroupsForGroup($group)) {
            $this->errorRedirectToMain(sprintf(_kt('Unable to remove existing sub-groups')), sprintf('old_search=%s&do_search=1', $oldSearch));
        }

        // Insert submitted groups for this user.

        $groupsAdded = array();
        // TODO I am sure we can do this much better, create a single insert query instead of one per added group.
        $groups = trim(KTUtil::arrayGet($_REQUEST, 'groups_roles'), ',');
        if (!empty($groups)) {
            $groups = explode(',', $groups);
            foreach ($groups as $idString) {
                $idData = explode('_', $idString);
                $subGroup = Group::get($idData[1]);

                $res = $group->addMemberGroup($subGroup);
                if (PEAR::isError($res) || $res == false) {
                    $this->errorRedirectToMain(sprintf(_kt('Failed to add %s to %s'), $subGroup->getName(), $group->getName()), sprintf('old_search=%s&do_search=1', $oldSearch));
                    exit(0);
                }
                else {
                    $groupsAdded[] = $subGroup->getName();
                }
            }
        }

        $groupsRemoved = array_diff($currentGroups, $groupsAdded);
        $groupsAdded = array_diff($groupsAdded, $currentGroups);

        $msg = '';
        if (!empty($groupsAdded)) { $msg .= ' ' . _kt('Added') . ': ' . implode(', ', $groupsAdded) . '. '; }
        if (!empty($groupsRemoved)) { $msg .= ' '. _kt('Removed'). ': ' . implode(', ',$groupsRemoved) . '.'; }

        $this->commitTransaction();

        // Invalidate the permissions cache to force an update .
        // It is possible to update only the members of the sub groups
        // but if there are a large number of members involved this can become an expensive operation.
        // It is cheaper to invalidate the cache and force a validation of each users permissions.
        KTPermissionUtil::clearCache();

        $this->successRedirectToMain($msg, sprintf('old_search=%s&do_search=1', $oldSearch));
    }

    // overloaded because i'm lazy
    // FIXME we probably want some way to generalise this
    // FIXME (its a common entity-problem)
    function form_addGroup()
    {
        $form = new KTForm;
        $form->setOptions(array(
            'identifier' => 'ktcore.groups.add',
            'label' => _kt('Create a new group'),
            'submit_label' => _kt('Create group'),
            'action' => 'createGroup',
            'fail_action' => 'addGroup',
            'cancel_action' => 'main',
            'context' => $this
        ));

        $form->setWidgets(array(
            array('ktcore.widgets.string',
                array(
                    'name' => 'group_name',
                    'label' => _kt('Group Name'),
                    'description' => _kt('A short name for the group.  e.g. <strong>administrators</strong>.'),
                    'value' => null,
                    'required' => true,
                )
            ),
            array('ktcore.widgets.boolean',
                array(
                    'name' => 'sysadmin',
                    'label' => _kt('System Administration Privileges'),
                    'description' => _kt('Should all the members of this group be given <strong>system</strong> administration privileges?'),
                    'value' => null,
                )
            ),
        ));

        $form->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'group_name',
                'output' => 'group_name',
            )),
            array('ktcore.validators.boolean', array(
                'test' => 'sysadmin',
                'output' => 'sysadmin',
            )),
        ));

        // If we have any units.
        $units = Unit::getList();
        if (!PEAR::isError($units) && !empty($units)) {
            $form->addWidgets(array(
                array('ktcore.widgets.boolean',
                    array(
                        'name' => 'unitadmin',
                        'label' => _kt('Unit Administration Privileges'),
                        'description' => _kt('Should all the members of this group be given <strong>unit</strong> administration privileges?'),
                        'important_description' => _kt('Note that its not possible to set a group without a unit as having unit administration privileges.'),
                        'value' => null,
                    )
                ),
                array('ktcore.widgets.entityselection',
                    array(
                        'name' => 'unit',
                        'label' => _kt('Unit'),
                        'description' => _kt('Which Unit is this group part of?'),
                        'vocab' => $units,
                        'label_method' => 'getName',
                        'simple_select' => false,
                        'unselected_label' => _kt('No unit'),
                    )
                )
            ));

            $form->addValidators(array(
                array('ktcore.validators.entity', array(
                    'test' => 'unit',
                    'class' => 'Unit',
                    'output' => 'unit',
                )),
                array('ktcore.validators.boolean', array(
                    'test' => 'unitadmin',
                    'output' => 'unitadmin',
                )),
            ));
        }

        return $form;
    }

    function do_addGroup()
    {
        $this->oPage->setBreadcrumbDetails(_kt('Add a new group'));

        $authenticationSources = array();
        $allAuthenticationSources =& KTAuthenticationSource::getList();
        foreach ($allAuthenticationSources as $authenticationSource) {
            $providerName = $authenticationSource->getAuthenticationProvider();
            $authenticationRegistry =& KTAuthenticationProviderRegistry::getSingleton();
            $authenticationProvider =& $authenticationRegistry->getAuthenticationProvider($providerName);
            if ($authenticationProvider->bGroupSource) {
                $authenticationSources[] = $authenticationSource;
            }
        }

        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/principals/addgroup');
        $templateData = array(
            'context' => $this,
            'add_fields' => $add_fields,
            'authentication_sources' => $authenticationSources,
            'form' => $this->form_addGroup(),
        );

        return $template->render($templateData);
    }

    function do_createGroup()
    {
        $form = $this->form_addGroup();
        $res = $form->validate();
        $data = $res['results'];
        $errors = $res['errors'];
        $extraErrors = array();

        if (is_null($data['unit']) && $data['unitadmin']) {
            $extraErrors['unitadmin'] = _kt('Groups without units cannot be Unit Administrators.');
        }

        $group = Group::getByName($data['group_name']);
        if (!PEAR::isError($group)) {
            $extraErrors['group_name'][] = _kt('There is already a group with that name.');
        }

        if (preg_match('/[\!\$\#\%\^\&\*]/', $data['group_name'])) {
        	$extraErrors['group_name'][] = _kt('You have entered an invalid character.');
        }

        if ($data['group_name'] == '') {
        	$extraErrors['group_name'][] = _kt('You have entered an invalid name.');
        }

        if (!empty($errors) || !empty($extraErrors)) {
            return $form->handleError(null, $extraErrors);
        }

        $this->startTransaction();

        $unit = null;
        if (!is_null($data['unit'])) {
            $unit = $data['unit']->getId();
        }

        $group = Group::createFromArray(array(
             'sName' => $data['group_name'],
             'bIsUnitAdmin' => KTUtil::arrayGet($data, 'unitadmin', false),
             'bIsSysAdmin' => $data['sysadmin'],
             'UnitId' => $unit,
        ));

        if (PEAR::isError($group)) {
            return $form->handleError(sprintf(_kt('Unable to create group: %s'), $group->getMessage()));
        }

        $this->commitTransaction();

        $this->successRedirectToMain(sprintf(_kt('Group "%s" created.'), $data['group_name']));
    }

    function do_deleteGroup()
    {
        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');

        $errorOptions = array(
            'redirect_to' => array('main', sprintf('old_search=%s&do_search=1', $oldSearch)),
        );
        $group = $this->oValidator->validateGroup($_REQUEST['group_id'], $errorOptions);
        $groupName = $group->getName();

        $this->startTransaction();

        foreach($group->getParentGroups() as $parentGroup) {
            $res = $parentGroup->removeMemberGroup($group);
        }

        $res = $group->delete();
        $this->oValidator->notError($res, $errorOptions);

        if (!Permission::userIsSystemAdministrator($_SESSION['userID'])) {
            $this->rollbackTransaction();
            $this->errorRedirectTo('main', _kt('For security purposes, you cannot remove your own administration priviledges.'), sprintf('old_search=%s&do_search=1', $oldSearch));
            exit(0);
        }

        $this->commitTransaction();

        // Invalidate the permissions cache to force an update .
        KTPermissionUtil::clearCache();

        $this->successRedirectToMain(sprintf(_kt('Group "%s" deleted.'), $groupName), sprintf('old_search=%s&do_search=1', $oldSearch));
    }

    // Authentication provider functions.

    function do_addGroupFromSource()
    {
        $authenticationSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $providerName = $authenticationSource->getAuthenticationProvider();
        $authenticationRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $authenticationProvider =& $authenticationRegistry->getAuthenticationProvider($providerName);

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Group Management'));
        $this->aBreadcrumbs[] = array('url' => KTUtil::addQueryStringSelf('action=addGroup'), 'name' => _kt('add a new group'));
        $authenticationProvider->aBreadcrumbs = $this->aBreadcrumbs;
        $authenticationProvider->oPage->setBreadcrumbDetails($authenticationSource->getName());
        $authenticationProvider->oPage->setTitle(_kt('Modify Group Details'));

        $authenticationProvider->dispatch();
    }

    function getGroupStringForGroup($group)
    {
        $groupNames = array();
        $groups = $group->getMemberGroups();
        $maxGroups = 6;
        $addElipsis = false;

        if (count($groups) == 0) { return _kt(''); }

        if (count($groups) > $maxGroups) {
            $groups = array_slice($groups, 0, $maxGroups);
            $addElipsis = true;
        }

        foreach ($groups as $group) {
            $groupNames[] = $group->getName();
        }

        if ($addElipsis) {
            $groupNames[] = '&hellip;';
        }

        return implode(', ', $groupNames);
    }

    public function handleOutput($output)
    {
        print $output;
    }

}

?>
