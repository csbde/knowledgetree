<?php

//require_once('../../../../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/groups/GroupUtil.php');
require_once(KT_LIB_DIR . '/groups/Group.inc');
require_once(KT_LIB_DIR . '/unitmanagement/Unit.inc');

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");

class KTGroupAdminDispatcher extends KTAdminDispatcher {
    // Breadcrumbs base - added to in methods
    var $aBreadcrumbs = array(
    array('action' => 'administration', 'name' => 'Administration'),
    );

    function do_main() {
		$this->aBreadcrumbs[] = array('action' => 'groupManagement', 'name' => 'Group Management');
		$this->oPage->setBreadcrumbDetails('select a group');
		$this->oPage->setTitle("Group Management");
		
		
		$name = KTUtil::arrayGet($_REQUEST, 'name');
		$show_all = KTUtil::arrayGet($_REQUEST, 'show_all', false);
		$group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
	
		
		$search_fields = array();
		$search_fields[] =  new KTStringWidget('Group Name','Enter part of the group\'s name.  e.g. <strong>ad</strong> will match <strong>administrators</strong>.', 'name', $name, $this->oPage, true);
		
		if (!empty($name)) {
			$search_results =& Group::getList('WHERE name LIKE "%' . DBUtil::escapeSimple($name) . '%"');
		} else if ($show_all !== false) {
			$search_results =& Group::getList();
		}

			
		$oTemplating = new KTTemplating;        
		$oTemplate = $oTemplating->loadTemplate("ktcore/principals/groupadmin");
		$aTemplateData = array(
			"context" => $this,
			"search_fields" => $search_fields,
			"search_results" => $search_results,
		);
		return $oTemplate->render($aTemplateData);
    }



    function do_editGroup() {
		$this->aBreadcrumbs[] = array('action' => 'groupManagement', 'name' => 'Group Management');
		$this->oPage->setBreadcrumbDetails('edit group');
		
		$group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
		$oGroup = Group::get($group_id);
		if (PEAR::isError($oGroup) || $oGroup == false) {
		    $this->errorRedirectToMain('Please select a valid group.');
		}
	
		$this->oPage->setTitle("Edit Group (" . $oGroup->getName() . ")");
		
		$edit_fields = array();
		$edit_fields[] =  new KTStringWidget('Group Name','A short name for the group.  e.g. <strong>administrators</strong>.', 'group_name', $oGroup->getName(), $this->oPage, true);
		$edit_fields[] =  new KTCheckboxWidget('Unit Administrators','Should all the members of this group be given <strong>unit</strong> administration privilidges?', 'is_unitadmin', $oGroup->getUnitAdmin(), $this->oPage, false);
		$edit_fields[] =  new KTCheckboxWidget('System Administrators','Should all the members of this group be given <strong>system</strong> administration privilidges?', 'is_sysadmin', $oGroup->getSysAdmin(), $this->oPage, false);
		
		// grab all units.
		$unit = $oGroup->getUnit();
		if ($unit == null) { $unitId = 0; }
		else { $unitId = $unit->getID(); }
		
		
		$oUnits = Unit::getList();
		$vocab = array();
		$vocab[0] = 'No Unit';
		foreach ($oUnits as $oUnit) { $vocab[$oUnit->getID()] = $oUnit->getName(); } 
		$aOptions = array('vocab' => $vocab);
		
		$edit_fields[] =  new KTLookupWidget('Unit','Which Unit is this group part of?', 'unit_id', $unitId, $this->oPage, false, null, null, $aOptions);
			
		$oTemplating = new KTTemplating;        
		$oTemplate = $oTemplating->loadTemplate("ktcore/principals/editgroup");
		$aTemplateData = array(
			"context" => $this,
			"edit_fields" => $edit_fields,
			"edit_group" => $oGroup,
		);
		return $oTemplate->render($aTemplateData);
    }

	function do_saveGroup() {
		$group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
		$oGroup = Group::get($group_id);
		if (PEAR::isError($oGroup) || $oGroup == false) {
		    $this->errorRedirectToMain('Please select a valid group.');
		}
		$group_name = KTUtil::arrayGet($_REQUEST, 'group_name');
		if (empty($group_name)) { $this->errorRedirectToMain('Please specify a name for the group.'); }
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
		    $oGroup->clearUnit(); // safe.
		} else {
		    $oGroup->setUnit($unit_id);
		}
		
		$res = $oGroup->update();
		if (($res == false) || (PEAR::isError($res))) { return $this->errorRedirectToMain('Failed to set group details.'); }
		
		$this->commitTransaction();
		$this->successRedirectToMain('Group details updated.');
	}


    function do_manageusers() {
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
        $oGroup = Group::get($group_id);
        if ((PEAR::isError($oGroup)) || ($oGroup === false)) {
            $this->errorRedirectToMain('No such group.');
        }
        
        $this->aBreadcrumbs[] = array('name' => $oGroup->getName());
        $this->oPage->setBreadcrumbDetails('manage members');
        $this->oPage->setTitle('Manage members of' . $oGroup->getName());
        
        
        // FIXME replace OptionTransfer.js.  me no-likey.
        
        // FIXME this is hideous.  refactor the transfer list stuff completely.
        $initJS = 'var optGroup = new OptionTransfer("userSelect","chosenUsers"); ' .
        'function startTrans() { var f = getElement("usergroupform"); ' .
        ' optGroup.saveAddedRightOptions("userAdded"); ' .
        ' optGroup.saveRemovedRightOptions("userRemoved"); ' .
        ' optGroup.init(f); }; ' .
        ' addLoadEvent(startTrans); '; 
        $this->oPage->requireJSStandalone($initJS);
        
        $aInitialUsers = $oGroup->getMembers();
        $aAllUsers = User::getList();
        
        
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
        
        $oTemplating = new KTTemplating;        
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/groups_manageusers");
        $aTemplateData = array(
            "context" => $this,
            "edit_group" => $oGroup,
			'unused_users' => $aFreeUsers,
			'group_users' => $aGroupUsers,
        );
        return $oTemplate->render($aTemplateData);        
    }    


    function do_updateUserMembers() {
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
        $oGroup = Group::get($group_id);
        if ((PEAR::isError($oGroup)) || ($oGroup === false)) {
            $this->errorRedirectToMain('No such group.');
        }
        
        $userAdded = KTUtil::arrayGet($_REQUEST, 'userAdded','');
        $userRemoved = KTUtil::arrayGet($_REQUEST, 'userRemoved','');
        
        
        $aUserToAddIDs = explode(",", $userAdded);
        $aUserToRemoveIDs = explode(",", $userRemoved);
        
        $this->startTransaction();
        $usersAdded = array();
        $usersRemoved = array();
        
        foreach ($aUserToAddIDs as $iUserId ) {
            if ($iUserId > 0) {
                $oUser= User::Get($iUserId);
                $res = $oGroup->addMember($oUser);
                if (PEAR::isError($res) || $res == false) {
                    $this->errorRedirectToMain('Unable to add "' . $oUser->getName() . '" to "' . $oGroup->getName() . '"');
                } else { $usersAdded[] = $oUser->getName(); }
            }
        }
    
        // Remove groups
        foreach ($aUserToRemoveIDs as $iUserId ) {
            if ($iUserId > 0) {
                $oUser = User::get($iUserId);
                $res = $oGroup->removeMember($oUser);
                if (PEAR::isError($res) || $res == false) {
                    $this->errorRedirectToMain('Unable to remove "' . $oUser->getName() . '" from "' . $oGroup->getName() . '"');			
                } else { $usersRemoved[] = $oUser->getName(); }
            }
        }        
        
        $msg = '';
        if (!empty($usersAdded)) { $msg .= ' Added: ' . join(', ', $usersAdded) . ', <br />'; }
        if (!empty($usersRemoved)) { $msg .= ' Removed: ' . join(', ',$usersRemoved) . '.'; }
        
        $this->commitTransaction();
        $this->successRedirectToMain($msg);
    }
	

	// FIXME copy-paste ...
    function do_managesubgroups() {
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
        $oGroup = Group::get($group_id);
        if ((PEAR::isError($oGroup)) || ($oGroup === false)) {
            $this->errorRedirectToMain('No such group.');
        }
        
        $this->aBreadcrumbs[] = array('name' => $oGroup->getName());
        $this->oPage->setBreadcrumbDetails('manage members');
        $this->oPage->setTitle('Manage members of' . $oGroup->getName());
        
        
        // FIXME replace OptionTransfer.js.  me no-likey.
        
        // FIXME this is hideous.  refactor the transfer list stuff completely.
        $initJS = 'var optGroup = new OptionTransfer("groupSelect","chosenGroups"); ' .
        'function startTrans() { var f = getElement("usergroupform"); ' .
        ' optGroup.saveAddedRightOptions("groupAdded"); ' .
        ' optGroup.saveRemovedRightOptions("groupRemoved"); ' .
        ' optGroup.init(f); }; ' .
        ' addLoadEvent(startTrans); '; 
        $this->oPage->requireJSStandalone($initJS);
		
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
            $aAllowedGroups[$iAllowedGroupID] =& Group::get($iAllowedGroupID);
		}
		
		
        $oTemplating = new KTTemplating;        
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/groups_managesubgroups");
        $aTemplateData = array(
            "context" => $this,
            "edit_group" => $oGroup,
			'unused_groups' => $aAllowedGroups,
			'group_members' => $aMemberGroups,
        );
        return $oTemplate->render($aTemplateData);        
    }    

	function _getUnitName($oGroup) {
		$u =  $oGroup->getUnit();
		
		return $u->getName();
	}  

	// FIXME copy-paste ...
    function do_updateGroupMembers() {
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
        $oGroup = Group::get($group_id);
        if ((PEAR::isError($oGroup)) || ($oGroup === false)) {
            $this->errorRedirectToMain('No such group.');
        }
        
        $groupAdded = KTUtil::arrayGet($_REQUEST, 'groupAdded','');
        $groupRemoved = KTUtil::arrayGet($_REQUEST, 'groupRemoved','');
        
        
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
                    $this->errorRedirectToMain("Failed to add " . $oMemberGroup->getName() . " to " . $oGroup->getName());
					exit(0);
                } else { $groupsAdded[] = $oMemberGroup->getName(); }
            }
        }

        foreach ($aGroupToRemoveIDs as $iMemberGroupID ) {
            if ($iMemberGroupID > 0) {
                $oMemberGroup = Group::get($iMemberGroupID);
                $res = $oGroup->removeMemberGroup($oMemberGroup);
                if (PEAR::isError($res)) {
				    $this->errorRedirectToMain("Failed to remove " . $oMemberGroup->getName() . " to " . $oGroup->getName());
					exit(0);
                } else { $groupsRemoved[] = $oMemberGroup->getName(); }
            }
        }
        
        $msg = '';
        if (!empty($groupsAdded)) { $msg .= ' Added: ' . join(', ', $groupsAdded) . ', <br />'; }
        if (!empty($groupsRemoved)) { $msg .= ' Removed: ' . join(', ',$groupsRemoved) . '.'; }
        
        $this->commitTransaction();
		
        $this->successRedirectToMain($msg);
    }	
	
	
    function do_addGroup() {
		$this->aBreadcrumbs[] = array('action' => 'groupManagement', 'name' => 'Group Management');
		$this->oPage->setBreadcrumbDetails('create new group');
		
	
		$this->oPage->setTitle("Create New Group");
		
		$edit_fields = array();
		$add_fields[] =  new KTStringWidget('Group Name','A short name for the group.  e.g. <strong>administrators</strong>.', 'group_name', null, $this->oPage, true);
		$add_fields[] =  new KTCheckboxWidget('Unit Administrators','Should all the members of this group be given <strong>unit</strong> administration privilidges?', 'is_unitadmin', false, $this->oPage, false);
		$add_fields[] =  new KTCheckboxWidget('System Administrators','Should all the members of this group be given <strong>system</strong> administration privilidges?', 'is_sysadmin', false, $this->oPage, false);
			
		$oTemplating = new KTTemplating;        
		$oTemplate = $oTemplating->loadTemplate("ktcore/principals/addgroup");
		$aTemplateData = array(
			"context" => $this,
			"add_fields" => $add_fields,
		);
		return $oTemplate->render($aTemplateData);
    }

	function do_createGroup() {

		$group_name = KTUtil::arrayGet($_REQUEST, 'group_name');
		if (empty($group_name)) { $this->errorRedirectToMain('Please specify a name for the group.'); }
		$is_unitadmin = KTUtil::arrayGet($_REQUEST, 'is_unitadmin', false);
		if ($is_unitadmin !== false) { $is_unitadmin = true; }
		$is_sysadmin = KTUtil::arrayGet($_REQUEST, 'is_sysadmin', false);
		if ($is_sysadmin !== false) { $is_sysadmin = true; }
		
		$this->startTransaction();
		
		$oGroup =& Group::createFromArray(array(
		     'sName' => $group_name,
			 'bIsUnitAdmin' => $is_unitadmin,
			 'bIsSysAdmin' => $is_sysadmin,
		));
		//$res = $oGroup->create();
		//if (($res == false) || (PEAR::isError($res))) { return $this->errorRedirectToMain('Failed to create group "' . $group_name . '"'); }
		// do i need to "create"
		$this->commitTransaction();
		$this->successRedirectToMain('Group "' . $group_name . '" created.');
	}
	
}

?>