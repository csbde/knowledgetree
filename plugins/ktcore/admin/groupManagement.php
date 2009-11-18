<?php
/**
 * $Id$
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
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/groups/GroupUtil.php');
require_once(KT_LIB_DIR . '/groups/Group.inc');
require_once(KT_LIB_DIR . '/unitmanagement/Unit.inc');

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");
require_once(KT_LIB_DIR . "/widgets/forms.inc.php");

require_once(KT_LIB_DIR . "/authentication/authenticationsource.inc.php");
require_once(KT_LIB_DIR . "/authentication/authenticationproviderregistry.inc.php");
require_once(KT_LIB_DIR . "/authentication/builtinauthenticationprovider.inc.php");

class KTGroupAdminDispatcher extends KTAdminDispatcher {
    // {{{ do_main
    var $sHelpPage = 'ktcore/admin/manage groups.html';
    
    function predispatch() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Group Management'));    
        $this->persistParams(array('old_search'));
    }
    
    function do_main() {

        $this->oPage->setBreadcrumbDetails(_kt('select a group'));
        $this->oPage->setTitle(_kt("Group Management"));
        
        $KTConfig =& KTConfig::getSingleton();
        $alwaysAll = $KTConfig->get("alwaysShowAll");
        
        $name = KTUtil::arrayGet($_REQUEST, 'search_name', KTUtil::arrayGet($_REQUEST, 'old_search'));
        $show_all = KTUtil::arrayGet($_REQUEST, 'show_all', $alwaysAll);
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
    
        $no_search = true;
        
        if (KTUtil::arrayGet($_REQUEST, 'do_search', false) != false) {
            $no_search = false;
        }
        
        if ($name == '*') { 
            $show_all = true;
            $name = '';
        }    
                
        $search_fields = array();
        $search_fields[] =  new KTStringWidget(_kt('Group Name'), _kt("Enter part of the group's name.  e.g. <strong>ad</strong> will match <strong>administrators</strong>."), 'search_name', $name, $this->oPage, true);
        
        if (!empty($name)) {
            $search_results =& Group::getList('WHERE name LIKE \'%' . DBUtil::escapeSimple($name) . '%\' AND id > 0');
        } else if ($show_all !== false) {
            $search_results =& Group::getList('id > 0');
            $no_search = false;
            $name = '*';
        }

            
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/groupadmin");
        $aTemplateData = array(
            "context" => $this,
            "search_fields" => $search_fields,
            "search_results" => $search_results,
            'no_search' => $no_search,
            'old_search' => $name,             
        );
        return $oTemplate->render($aTemplateData);
    }
    // }}}

    // {{{ do_editGroup
    function do_editGroup() {
        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');    
    

        $this->oPage->setBreadcrumbDetails(_kt('edit group'));
        
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
        $oGroup = Group::get($group_id);
        if (PEAR::isError($oGroup) || $oGroup == false) {
            $this->errorRedirectToMain(_kt('Please select a valid group.'), sprintf("old_search=%s&do_search=1", $old_search));
        }
    
        $this->oPage->setTitle(sprintf(_kt("Edit Group (%s)"), $oGroup->getName()));
        
        $edit_fields = array();
        $edit_fields[] =  new KTStringWidget(_kt('Group Name'), _kt('A short name for the group.  e.g. <strong>administrators</strong>.'), 'group_name', $oGroup->getName(), $this->oPage, true);
        $edit_fields[] =  new KTCheckboxWidget(_kt('Unit Administrators'), _kt('Should all the members of this group be given <strong>unit</strong> administration privileges?'), 'is_unitadmin', $oGroup->getUnitAdmin(), $this->oPage, false);
        $edit_fields[] =  new KTCheckboxWidget(_kt('System Administrators'), _kt('Should all the members of this group be given <strong>system</strong> administration privileges?'), 'is_sysadmin', $oGroup->getSysAdmin(), $this->oPage, false);
        
        // grab all units.
        $unitId = $oGroup->getUnitId();
        if ($unitId == null) { $unitId = 0; }        
        
        $oUnits = Unit::getList();
        $vocab = array();
        $vocab[0] = _kt('No Unit');
        foreach ($oUnits as $oUnit) { $vocab[$oUnit->getID()] = $oUnit->getName(); } 
        $aOptions = array('vocab' => $vocab);
        
        $edit_fields[] =  new KTLookupWidget(_kt('Unit'), _kt('Which Unit is this group part of?'), 'unit_id', $unitId, $this->oPage, false, null, null, $aOptions);
            
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/editgroup");
        $aTemplateData = array(
            "context" => $this,
            "edit_fields" => $edit_fields,
            "edit_group" => $oGroup,
            "old_search" => $old_search,
        );
        return $oTemplate->render($aTemplateData);
    }
    // }}}

    // {{{ do_saveGroup
    function do_saveGroup() {
        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');    
    
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
        $oGroup = Group::get($group_id);
        if (PEAR::isError($oGroup) || $oGroup == false) {
            $this->errorRedirectToMain(_kt('Please select a valid group.'), sprintf("old_search=%s&do_search=1", $old_search));
        }
        $group_name = KTUtil::arrayGet($_REQUEST, 'group_name');
        if (empty($group_name)) { $this->errorRedirectToMain(_kt('Please specify a name for the group.')); }
        $is_unitadmin = KTUtil::arrayGet($_REQUEST, 'is_unitadmin', false);
        if ($is_unitadmin !== false) { $is_unitadmin = true; }
        $is_sysadmin = KTUtil::arrayGet($_REQUEST, 'is_sysadmin', false);
        if ($is_sysadmin !== false) { $is_sysadmin = true; }
        
        $this->startTransaction();
        
        $oGroup->setName($group_name);        
        $oGroup->setUnitAdmin($is_unitadmin);
        $oGroup->setSysAdmin($is_sysadmin);

        $unit_id = KTUtil::arrayGet($_REQUEST, 'unit_id', 0);
        if ($unit_id == 0) { // not set, or set to 0.
            $oGroup->setUnitId(null); // safe.
        } else {
            $oGroup->setUnitId($unit_id);
        }

        $res = $oGroup->update();
        if (($res == false) || (PEAR::isError($res))) { return $this->errorRedirectToMain(_kt('Failed to set group details.'), sprintf("old_search=%s&do_search=1", $old_search)); }

        if (!Permission::userIsSystemAdministrator($_SESSION['userID'])) {
            $this->rollbackTransaction();
            $this->errorRedirectTo('editGroup', _kt('For security purposes, you cannot remove your own administration priviledges.'), sprintf('group_id=%d', $oGroup->getId()), sprintf("old_search=%s&do_search=1", $old_search));
            exit(0);
        }

        
        $this->commitTransaction();
        if($unit_id == 0 && $is_unitadmin) {
            $this->successRedirectToMain(_kt('Group details updated.') . _kt(' Note: group is set as unit administrator, but is not assigned to a unit.'), sprintf("old_search=%s&do_search=1", $old_search));
        } else {
            $this->successRedirectToMain(_kt('Group details updated.'), sprintf("old_search=%s&do_search=1", $old_search));
        }   
    }
    // }}}

    function _do_manageUsers_source() {
        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');    
    
        $oGroup =& $this->oValidator->validateGroup($_REQUEST['group_id']);

        $aGroupUsers = $oGroup->getMembers();

        $oTemplate = $this->oValidator->validateTemplate("ktcore/principals/groups_sourceusers");
        $aTemplateData = array(
            "context" => $this,
            'group_users' => $aGroupUsers,
            'group' => $oGroup,
            "old_search" => $old_search,            
        );
        return $oTemplate->render($aTemplateData);        
    }

    function do_synchroniseGroup() {
        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');    
    
        require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');
        $oGroup =& $this->oValidator->validateGroup($_REQUEST['group_id']);
        $res = KTAuthenticationUtil::synchroniseGroupToSource($oGroup);
        $this->successRedirectTo('manageusers', 'Group synchronised', sprintf('group_id=%d', $oGroup->getId()), sprintf("old_search=%s&do_search=1", $old_search));
        exit(0);
    }

    // {{{ do_manageusers
    function do_manageUsers() {
        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');    
    
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
        $oGroup = Group::get($group_id);
        if ((PEAR::isError($oGroup)) || ($oGroup === false)) {
            $this->errorRedirectToMain(_kt('No such group.'));
        }


        $this->aBreadcrumbs[] = array('name' => $oGroup->getName());
        $this->oPage->setBreadcrumbDetails(_kt('manage members'));
        $this->oPage->setTitle(sprintf(_kt('Manage members of group %s'), $oGroup->getName()));

        $iSourceId = $oGroup->getAuthenticationSourceId();
        if (!empty($iSourceId)) {
            return $this->_do_manageUsers_source();
        }
        
        $aInitialUsers = $oGroup->getMembers();
        $aAllUsers = User::getList('id > 0');
        
        
        // FIXME this is massively non-performant for large userbases..
        $aGroupUsers = array();
        $aFreeUsers = array();
        foreach ($aInitialUsers as $oUser) {
            $aGroupUsers[$oUser->getId()] = $oUser;
        }
        foreach ($aAllUsers as $oUser) {
            if (!array_key_exists($oUser->getId(), $aGroupUsers)) {
                $aFreeUsers[$oUser->getId()] = $oUser;
            }
        }
        
    $oJSONWidget = new KTJSONLookupWidget(_kt('Users'), 
                          _kt('Select the users which should be part of this group from the left-hand list and then click the <strong>right pointing arrows</strong>. Once you have added all the users that you require, press <strong>save changes</strong>.'), 
                          'users', '', $this->oPage, false, null, null, 
                          array('action'=>'getUsers',
                            'assigned' => $aGroupUsers,
                            'multi'=>'true',
                            'size'=>'8'));
        
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/groups_manageusers");
        $aTemplateData = array(
            "context" => $this,
            "edit_group" => $oGroup,
        'widget' => $oJSONWidget,
            "old_search" => $old_search,            
        );
        return $oTemplate->render($aTemplateData);        
    }    
    // }}}


    function json_getUsers() {
    $sFilter = KTUtil::arrayGet($_REQUEST, 'filter', false);
    $aUserList = array('off' => _kt('-- Please filter --'));

    if($sFilter && trim($sFilter)) {
        $aUsers = User::getList(sprintf('name like "%%%s%%" AND disabled = 0 AND id > 0', $sFilter));
        $aUserList = array();
        foreach($aUsers as $oUser) {
        $aUserList[$oUser->getId()] = $oUser->getName();
        }
    }     
    return $aUserList;
    }



    // {{{ do_updateUserMembers
    function do_updateUserMembers() {
        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');    
    
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
        $oGroup = Group::get($group_id);
        if ((PEAR::isError($oGroup)) || ($oGroup === false)) {
            $this->errorRedirectToMain(_kt('No such group.'));
        }
        
        $userAdded = KTUtil::arrayGet($_REQUEST, 'users_items_added','');
        $userRemoved = KTUtil::arrayGet($_REQUEST, 'users_items_removed','');        
        
        $aUserToAddIDs = explode(",", $userAdded);
        $aUserToRemoveIDs = explode(",", $userRemoved);
        
        $this->startTransaction();
        $usersAdded = array();
        $usersRemoved = array();
        
    $addWarnings = array();
    $removeWarnings = array();    
        
    foreach ($aUserToAddIDs as $iUserId ) {
            if ($iUserId > 0) {
                $oUser= User::Get($iUserId);
        $memberReason = GroupUtil::getMembershipReason($oUser, $oGroup);
        //var_dump($memberReason);
        if (!(PEAR::isError($memberReason) || is_null($memberReason))) {
            $addWarnings[] = $memberReason;
        }                
                $res = $oGroup->addMember($oUser);
                if (PEAR::isError($res) || $res == false) {
                    $this->errorRedirectToMain(sprintf(_kt('Unable to add user "%s" to group "%s"'), $oUser->getName(), $oGroup->getName()), sprintf("old_search=%s&do_search=1", $old_search));
                } else { $usersAdded[] = $oUser->getName(); }
            }
        }
    
        // Remove groups
        foreach ($aUserToRemoveIDs as $iUserId ) {
            if ($iUserId > 0) {
                $oUser = User::get($iUserId);
                $res = $oGroup->removeMember($oUser);
                if (PEAR::isError($res) || $res == false) {
                    $this->errorRedirectToMain(sprintf(_kt('Unable to remove user "%s" from group "%s"'), $oUser->getName(), $oGroup->getName()), sprintf("old_search=%s&do_search=1", $old_search));
                } else { 
            $usersRemoved[] = $oUser->getName(); 
            $memberReason = GroupUtil::getMembershipReason($oUser, $oGroup);
            //var_dump($memberReason);
            if (!(PEAR::isError($memberReason) || is_null($memberReason))) {
            $removeWarnings[] = $memberReason;
            }
        }
            }
        }        
        
    if (!empty($addWarnings)) {
        $sWarnStr = _kt('Warning:  some users were already members of some subgroups') . ' &mdash; ';
        $sWarnStr .= implode(', ', $addWarnings);
        $_SESSION['KTInfoMessage'][] = $sWarnStr;
    }
    
    if (!empty($removeWarnings)) {
        $sWarnStr = _kt('Warning:  some users are still members of some subgroups') . ' &mdash; ';
        $sWarnStr .= implode(', ', $removeWarnings);
        $_SESSION['KTInfoMessage'][] = $sWarnStr;
    }        
        
        $msg = '';
        if (!empty($usersAdded)) { $msg .= ' ' . _kt('Added') . ': ' . implode(', ', $usersAdded) . '. '; }
        if (!empty($usersRemoved)) { $msg .= ' ' . _kt('Removed') . ': ' . implode(', ',$usersRemoved) . '.'; }
    
        if (!Permission::userIsSystemAdministrator($_SESSION['userID'])) {
            $this->rollbackTransaction();
            $this->errorRedirectTo('manageUsers', _kt('For security purposes, you cannot remove your own administration priviledges.'), sprintf('group_id=%d', $oGroup->getId()), sprintf("old_search=%s&do_search=1", $old_search));
            exit(0);
        }
        
        $this->commitTransaction();
        $this->successRedirectToMain($msg, sprintf("old_search=%s&do_search=1", $old_search));
    }
    // }}}
    
    // FIXME copy-paste ...
    // {{{ do_manageSubgroups
    function do_manageSubgroups() {
        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');    
    
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
        $oGroup = Group::get($group_id);
        if ((PEAR::isError($oGroup)) || ($oGroup === false)) {
            $this->errorRedirectToMain(_kt('No such group.'), sprintf("old_search=%s&do_search=1", $old_search));
        }
        
        $this->aBreadcrumbs[] = array('name' => $oGroup->getName());
        $this->oPage->setBreadcrumbDetails(_kt('manage members'));
        $this->oPage->setTitle(sprintf(_kt('Manage members of %s'), $oGroup->getName()));
        
        
        $aMemberGroupsUnkeyed = $oGroup->getMemberGroups();        
    $aMemberGroups = array();
        $aMemberIDs = array();
        foreach ($aMemberGroupsUnkeyed as $oMemberGroup) {
            $aMemberIDs[] = $oMemberGroup->getID();        
        $aMemberGroups[$oMemberGroup->getID()] = $oMemberGroup;
    }
        
    $oJSONWidget = new KTJSONLookupWidget(_kt('Groups'), 
                          _kt('Select the groups from the left-hand list that you would like to add to this group and then click the <b>right pointing arrows</b>. Once you have added all the groups that you require, press <b>save changes</b>. Only groups that are logically capable of being included in this group will be available to be added.'), 
                          'groups', '', $this->oPage, false, null, null, 
                          array('action'   => sprintf('getSubGroups&group_id=%d', $oGroup->getID()),
                            'assigned' => $aMemberGroups,
                            'multi'    => 'true',
                            'size'     => '8'));
        
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/groups_managesubgroups");
        $aTemplateData = array("context" => $this,
                   "edit_group" => $oGroup,
                   'widget'=>$oJSONWidget,
            "old_search" => $old_search,            
        );
        return $oTemplate->render($aTemplateData);        
    }    
    // }}}


    function json_getSubGroups() {
    $sFilter = KTUtil::arrayGet($_REQUEST, 'filter', false);
    $aAllowedGroups = array('off' => _kt('-- Please filter --'));

    if($sFilter && trim($sFilter)) {
        $iGroupID = KTUtil::arrayGet($_REQUEST, 'group_id', false);
        if(!$iGroupID) {
        return array('error'=>true, 'type'=>'kt.invalid_entity', 'message'=>_kt('An invalid group was selected'));
        }
    
        $oGroup = Group::get($iGroupID);
        $aMemberGroupsUnkeyed = $oGroup->getMemberGroups();        
        $aMemberGroups = array();
        $aMemberIDs = array();
        foreach ($aMemberGroupsUnkeyed as $oMemberGroup) {
        $aMemberIDs[] = $oMemberGroup->getID();        
        $aMemberGroups[$oMemberGroup->getID()] = $oMemberGroup;
        }
        
        $aGroupArray = GroupUtil::buildGroupArray();
        $aAllowedGroupIDs = GroupUtil::filterCyclicalGroups($oGroup->getID(), $aGroupArray);
        $aAllowedGroupIDs = array_diff($aAllowedGroupIDs, $aMemberIDs);
        $aAllowedGroups = array();
        foreach ($aAllowedGroupIDs as $iAllowedGroupID) {
        $g = Group::get($iAllowedGroupID);
        if (!PEAR::isError($g) && ($g != false)) {
            $aAllowedGroups[$iAllowedGroupID] = $g->getName();
        }
        }
    }

    return $aAllowedGroups;
    }



    // {{{ _getUnitName
    function _getUnitName($oGroup) {
        $iUnitId = $oGroup->getUnitId();
        if (empty($iUnitId)) {
            return null;
        }
        $u = Unit::get($iUnitId);
        if (PEAR::isError($u)) { 
            return null;   // XXX: prevent failure if the $u is a PEAR::error
        }
        
        return $u->getName();
    }  
    // }}}

    // FIXME copy-paste ...
    // {{{ do_updateGroupMembers
    function do_updateGroupMembers() {
        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');    
    
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
        $oGroup = Group::get($group_id);
        if ((PEAR::isError($oGroup)) || ($oGroup === false)) {
            $this->errorRedirectToMain('No such group.', sprintf("old_search=%s&do_search=1", $old_search));
        }
        
        $groupAdded = KTUtil::arrayGet($_REQUEST, 'groups_items_added','');
        $groupRemoved = KTUtil::arrayGet($_REQUEST, 'groups_items_removed','');
        
        
        $aGroupToAddIDs = explode(",", $groupAdded);
        $aGroupToRemoveIDs = explode(",", $groupRemoved);
        
        $this->startTransaction();
        $groupsAdded = array();
        $groupsRemoved = array();
        
        
        foreach ($aGroupToAddIDs as $iMemberGroupID ) {
            if ($iMemberGroupID > 0) {
                $oMemberGroup = Group::get($iMemberGroupID);
                $res = $oGroup->addMemberGroup($oMemberGroup);
                if (PEAR::isError($res)) {
                    $this->errorRedirectToMain(sprintf(_kt("Failed to add %s to %s"), $oMemberGroup->getName(), $oGroup->getName()), sprintf("old_search=%s&do_search=1", $old_search));
                    exit(0);
                } else { $groupsAdded[] = $oMemberGroup->getName(); }
            }
        }

        foreach ($aGroupToRemoveIDs as $iMemberGroupID ) {
            if ($iMemberGroupID > 0) {
                $oMemberGroup = Group::get($iMemberGroupID);
                $res = $oGroup->removeMemberGroup($oMemberGroup);
                if (PEAR::isError($res)) {
                    $this->errorRedirectToMain(sprintf(_kt("Failed to remove %s from %s"), $oMemberGroup->getName(), $oGroup->getName()), sprintf("old_search=%s&do_search=1", $old_search));
                    exit(0);
                } else { $groupsRemoved[] = $oMemberGroup->getName(); }
            }
        }
        
        $msg = '';
        if (!empty($groupsAdded)) { $msg .= ' ' . _kt('Added') . ': ' . implode(', ', $groupsAdded) . '. '; }
        if (!empty($groupsRemoved)) { $msg .= ' '. _kt('Removed'). ': ' . implode(', ',$groupsRemoved) . '.'; }
        
        $this->commitTransaction();
        
        $this->successRedirectToMain($msg, sprintf("old_search=%s&do_search=1", $old_search));
    }    
    // }}}
    
    // overloaded because i'm lazy
    // FIXME we probably want some way to generalise this 
    // FIXME (its a common entity-problem)
    function form_addgroup() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.groups.add',
            'label' => _kt("Create a new group"),
            'submit_label' => _kt("Create group"),
            'action' => 'creategroup',
            'fail_action' => 'addgroup',
            'cancel_action' => 'main',
            'context' => $this,
        ));
        $oForm->setWidgets(array(
            array('ktcore.widgets.string',array(
                'name' => 'group_name',
                'label' => _kt("Group Name"),
                'description' => _kt('A short name for the group.  e.g. <strong>administrators</strong>.'),
                'value' => null,
                'required' => true,
            )),
            array('ktcore.widgets.boolean',array(
                'name' => 'sysadmin',
                'label' => _kt("System Administrators"),
                'description' => _kt('Should all the members of this group be given <strong>system</strong> administration privileges?'),
                'value' => null,
            )),         
        ));
        
        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'group_name',
                'output' => 'group_name',
            )),
            array('ktcore.validators.boolean', array(
                'test' => 'sysadmin',
                'output' => 'sysadmin',
            )),
        ));
        
        // if we have any units.
        $aUnits = Unit::getList();
        if (!PEAR::isError($aUnits) && !empty($aUnits)) {
            $oForm->addWidgets(array(
                array('ktcore.widgets.entityselection', array(
                    'name' => 'unit',
                    'label' => _kt('Unit'),
                    'description' => _kt('Which Unit is this group part of?'),
                    'vocab' => $aUnits,
                    'label_method' => 'getName',
                    'simple_select' => false,
                    'unselected_label' => _kt("No unit"), 
                )),
                array('ktcore.widgets.boolean',array(
                    'name' => 'unitadmin',
                    'label' => _kt("Unit Administrators"),
                    'description' => _kt('Should all the members of this group be given <strong>unit</strong> administration privileges?'),
                    'important_description' => _kt("Note that its not possible to set a group without a unit as having unit administration privileges."),
                    'value' => null,
                )),                     
            ));        
            
            $oForm->addValidators(array(
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
        
        return $oForm;
    }
    
    // {{{ do_addGroup
    function do_addGroup() {
        $this->oPage->setBreadcrumbDetails(_kt('Add a new group'));
        
        $aAuthenticationSources = array();
        $aAllAuthenticationSources =& KTAuthenticationSource::getList();
        foreach ($aAllAuthenticationSources as $oSource) {
            $sProvider = $oSource->getAuthenticationProvider();
            $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
            $oProvider =& $oRegistry->getAuthenticationProvider($sProvider);
            if ($oProvider->bGroupSource) {
                $aAuthenticationSources[] = $oSource;
            }
        }
            
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/addgroup");
        $aTemplateData = array(
            "context" => $this,
            "add_fields" => $add_fields,
            "authentication_sources" => $aAuthenticationSources,      
            'form' => $this->form_addgroup(),      
        );
        return $oTemplate->render($aTemplateData);
    }
    // }}}

    // {{{ do_createGroup
    function do_creategroup() {
        $oForm = $this->form_addgroup();
        $res = $oForm->validate();
        $data = $res['results'];
        $errors = $res['errors'];
        $extra_errors = array();
       
        
        if (is_null($data['unit']) && $data['unitadmin']) {
            $extra_errors['unitadmin'] = _kt("Groups without units cannot be Unit Administrators.");
        } 
        
        $oGroup = Group::getByName($data['group_name']);
        if (!PEAR::isError($oGroup)) {
            $extra_errors['group_name'][] = _kt("There is already a group with that name.");
        }
		
		
        if(preg_match('/[\!\$\#\%\^\&\*]/', $data['group_name'])){
        	$extra_errors['group_name'][] = _kt("You have entered an invalid character.");
        }
		
        if ($data['group_name'] == ''){
        	$extra_errors['group_name'][] = _kt("You have entered an invalid name.");
        }

        
        if (!empty($errors) || !empty($extra_errors)) {
            
            return $oForm->handleError(null, $extra_errors);
            
        }
        
        $this->startTransaction();
        
        $unit = null;
        if (!is_null($data['unit'])) {
            $unit = $data['unit']->getId();
        }

        $oGroup =& Group::createFromArray(array(
             'sName' => $data['group_name'],
             'bIsUnitAdmin' => KTUtil::arrayGet($data, 'unitadmin', false),
             'bIsSysAdmin' => $data['sysadmin'],
             'UnitId' => $unit,
        ));

        if (PEAR::isError($oGroup)) {

            return $oForm->handleError(sprintf(_kt("Unable to create group: %s"), $oGroup->getMessage()));
        }
        $this->commitTransaction();

        $this->successRedirectToMain(sprintf(_kt('Group "%s" created.'), $data['group_name']));
    }
    // }}}

    // {{{ do_deleteGroup
    function do_deleteGroup() {
        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');    
    
        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf("old_search=%s&do_search=1", $old_search)),
        );
        $oGroup = $this->oValidator->validateGroup($_REQUEST['group_id'], $aErrorOptions);
        $sGroupName = $oGroup->getName();

        $this->startTransaction();
        
        foreach($oGroup->getParentGroups() as $oParentGroup) {
            $res = $oParentGroup->removeMemberGroup($oGroup);        
        }
        
        $res = $oGroup->delete();
        $this->oValidator->notError($res, $aErrorOptions);
        
        if (!Permission::userIsSystemAdministrator($_SESSION['userID'])) {
            $this->rollbackTransaction();
            $this->errorRedirectTo('main', _kt('For security purposes, you cannot remove your own administration priviledges.'), sprintf("old_search=%s&do_search=1", $old_search));
            exit(0);
        }
        $this->commitTransaction();
        $this->successRedirectToMain(sprintf(_kt('Group "%s" deleted.'), $sGroupName), sprintf("old_search=%s&do_search=1", $old_search));
    }
    // }}}

    // {{{ authentication provider stuff

    // {{{ do_addGroupFromSource
    function do_addGroupFromSource() {
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $sProvider = $oSource->getAuthenticationProvider();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $oProvider =& $oRegistry->getAuthenticationProvider($sProvider);

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Group Management'));
        $this->aBreadcrumbs[] = array('url' => KTUtil::addQueryStringSelf('action=addGroup'), 'name' => _kt('add a new group'));
        $oProvider->aBreadcrumbs = $this->aBreadcrumbs;
        $oProvider->oPage->setBreadcrumbDetails($oSource->getName());
        $oProvider->oPage->setTitle(_kt("Modify Group Details"));

        $oProvider->dispatch();
        exit(0);
    }
    // }}}

    function getGroupStringForGroup($oGroup) {
        $aGroupNames = array();
        $aGroups = $oGroup->getMemberGroups();
        $MAX_GROUPS = 6;
        $add_elipsis = false;
        if (count($aGroups) == 0) { return _kt('Group currently has no subgroups.'); }
        if (count($aGroups) > $MAX_GROUPS) { 
            $aGroups = array_slice($aGroups, 0, $MAX_GROUPS); 
            $add_elipsis = true;
        }
        foreach ($aGroups as $oGroup) { 
            $aGroupNames[] = $oGroup->getName();
        }
        if ($add_elipsis) {
            $aGroupNames[] = '&hellip;';
        }
        
        return implode(', ', $aGroupNames);
    }
    // }}}
}

?>
