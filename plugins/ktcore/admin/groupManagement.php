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

require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/groups/GroupUtil.php');
require_once(KT_LIB_DIR . '/groups/Group.inc');
require_once(KT_LIB_DIR . '/unitmanagement/Unit.inc');

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");

require_once(KT_LIB_DIR . "/authentication/authenticationsource.inc.php");
require_once(KT_LIB_DIR . "/authentication/authenticationproviderregistry.inc.php");
require_once(KT_LIB_DIR . "/authentication/builtinauthenticationprovider.inc.php");

class KTGroupAdminDispatcher extends KTAdminDispatcher {
    // {{{ do_main
    var $sHelpPage = 'ktcore/admin/manage groups.html';
    function do_main() {
		$this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Group Management'));
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
		
		$search_fields = array();
		$search_fields[] =  new KTStringWidget(_kt('Group Name'), _kt("Enter part of the group's name.  e.g. <strong>ad</strong> will match <strong>administrators</strong>."), 'search_name', $name, $this->oPage, true);
		
		if (!empty($name)) {
			$search_results =& Group::getList('WHERE name LIKE \'%' . DBUtil::escapeSimple($name) . '%\' AND id > 0');
		} else if ($show_all !== false) {
			$search_results =& Group::getList('id > 0');
			$no_search = false;
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
    
		$this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Group Management'));
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

	$this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Group Management'));
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
	$aUserList = array('off'=>'-- Please filter --');

	if($sFilter && trim($sFilter)) {
	    $aUsers = User::getList(sprintf('name like "%%%s%%"', $sFilter));
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
        if (!empty($usersAdded)) { $msg .= ' ' . _kt('Added') . ': ' . implode(', ', $usersAdded) . '. <br />'; }
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
	$aAllowedGroups = array('off'=>'-- Please filter --');

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
		$aAllowedGroups[$iAllowedGroupID] = $g->getName();
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
        if (!empty($groupsAdded)) { $msg .= ' ' . _kt('Added') . ': ' . implode(', ', $groupsAdded) . '. <br />'; }
        if (!empty($groupsRemoved)) { $msg .= ' '. _kt('Removed'). ': ' . implode(', ',$groupsRemoved) . '.'; }
        
        $this->commitTransaction();
		
        $this->successRedirectToMain($msg, sprintf("old_search=%s&do_search=1", $old_search));
    }	
    // }}}
	
    // {{{ do_addGroup
    function do_addGroup() {
		$this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Group Management'));
		$this->oPage->setBreadcrumbDetails(_kt('Add a new group'));
		
	
		$this->oPage->setTitle(_kt("Add a new group"));
		
		$edit_fields = array();
		$add_fields[] =  new KTStringWidget(_kt('Group Name'), _kt('A short name for the group.  e.g. <strong>administrators</strong>.'), 'group_name', null, $this->oPage, true);
		$add_fields[] =  new KTCheckboxWidget(_kt('Unit Administrators'), _kt('Should all the members of this group be given <strong>unit</strong> administration privileges?'), 'is_unitadmin', false, $this->oPage, false);
		$add_fields[] =  new KTCheckboxWidget(_kt('System Administrators'), _kt('Should all the members of this group be given <strong>system</strong> administration privileges?'), 'is_sysadmin', false, $this->oPage, false);
		// grab all units.
		
		$oUnits = Unit::getList();
		$vocab = array();
		$vocab[0] = _kt('No Unit');
		foreach ($oUnits as $oUnit) { $vocab[$oUnit->getID()] = $oUnit->getName(); } 
		$aOptions = array('vocab' => $vocab);
		
		$add_fields[] =  new KTLookupWidget(_kt('Unit'), _kt('Which Unit is this group part of?'), 'unit_id', 0, $this->oPage, false, null, null, $aOptions);

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
		);
		return $oTemplate->render($aTemplateData);
    }
    // }}}

    // {{{ do_createGroup
	function do_createGroup() {

		$group_name = KTUtil::arrayGet($_REQUEST, 'group_name');
		if (empty($group_name)) { $this->errorRedirectToMain(_kt('Please specify a name for the group.')); }
		$is_unitadmin = KTUtil::arrayGet($_REQUEST, 'is_unitadmin', false);
		if ($is_unitadmin !== false) { $is_unitadmin = true; }
		$is_sysadmin = KTUtil::arrayGet($_REQUEST, 'is_sysadmin', false);
		if ($is_sysadmin !== false) { $is_sysadmin = true; }
		$unit_id = KTUtil::arrayGet($_REQUEST, 'unit_id', null);
		
		$this->startTransaction();
		
		$oGroup =& Group::createFromArray(array(
		     'sName' => $group_name,
			 'bIsUnitAdmin' => $is_unitadmin,
			 'bIsSysAdmin' => $is_sysadmin,
			 'UnitId' => $unit_id,
		));
		//$res = $oGroup->create();
		//if (($res == false) || (PEAR::isError($res))) { return $this->errorRedirectToMain('Failed to create group "' . $group_name . '"'); }
		// do i need to "create"
		$this->commitTransaction();

		if($unit_id == 0 && $is_unitadmin) {
		    $this->successRedirectToMain(sprintf(_kt('Group "%s" created.'), $group_name) . _kt(' Note: group is set as unit administrator, but is not assigned to a unit.'));
		} else {
		    $this->successRedirectToMain(sprintf(_kt('Group "%s" created.'), $group_name));
		}   
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
