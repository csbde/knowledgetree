<?php

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
    function do_main() {
		$this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Group Management'));
		$this->oPage->setBreadcrumbDetails(_('select a group'));
		$this->oPage->setTitle(_("Group Management"));
		
		$KTConfig =& KTConfig::getSingleton();
        $alwaysAll = $KTConfig->get("alwaysShowAll");
		
		$name = KTUtil::arrayGet($_REQUEST, 'name');
		$show_all = KTUtil::arrayGet($_REQUEST, 'show_all', $alwaysAll);
		$group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
	
        $no_search = true;
        
        if (KTUtil::arrayGet($_REQUEST, 'do_search', false) != false) {
            $no_search = false;
        }
		
		$search_fields = array();
		$search_fields[] =  new KTStringWidget(_('Group Name'),_("Enter part of the group's name.  e.g. <strong>ad</strong> will match <strong>administrators</strong>."), 'name', $name, $this->oPage, true);
		
		if (!empty($name)) {
			$search_results =& Group::getList('WHERE name LIKE "%' . DBUtil::escapeSimple($name) . '%"');
		} else if ($show_all !== false) {
			$search_results =& Group::getList();
			$no_search = false;
		}

			
		$oTemplating =& KTTemplating::getSingleton();        
		$oTemplate = $oTemplating->loadTemplate("ktcore/principals/groupadmin");
		$aTemplateData = array(
			"context" => $this,
			"search_fields" => $search_fields,
			"search_results" => $search_results,
			'no_search' => $no_search,
		);
		return $oTemplate->render($aTemplateData);
    }
    // }}}

    // {{{ do_editGroup
    function do_editGroup() {
		$this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Group Management'));
		$this->oPage->setBreadcrumbDetails(_('edit group'));
		
		$group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
		$oGroup = Group::get($group_id);
		if (PEAR::isError($oGroup) || $oGroup == false) {
		    $this->errorRedirectToMain(_('Please select a valid group.'));
		}
	
		$this->oPage->setTitle(sprintf(_("Edit Group (%s)"), $oGroup->getName()));
		
		$edit_fields = array();
		$edit_fields[] =  new KTStringWidget(_('Group Name'),_('A short name for the group.  e.g. <strong>administrators</strong>.'), 'group_name', $oGroup->getName(), $this->oPage, true);
		$edit_fields[] =  new KTCheckboxWidget(_('Unit Administrators'),_('Should all the members of this group be given <strong>unit</strong> administration privileges?'), 'is_unitadmin', $oGroup->getUnitAdmin(), $this->oPage, false);
		$edit_fields[] =  new KTCheckboxWidget(_('System Administrators'),_('Should all the members of this group be given <strong>system</strong> administration privileges?'), 'is_sysadmin', $oGroup->getSysAdmin(), $this->oPage, false);
		
		// grab all units.
		$unitId = $oGroup->getUnitId();
		if ($unitId == null) { $unitId = 0; }		
		
		$oUnits = Unit::getList();
		$vocab = array();
		$vocab[0] = _('No Unit');
		foreach ($oUnits as $oUnit) { $vocab[$oUnit->getID()] = $oUnit->getName(); } 
		$aOptions = array('vocab' => $vocab);
		
		$edit_fields[] =  new KTLookupWidget(_('Unit'),_('Which Unit is this group part of?'), 'unit_id', $unitId, $this->oPage, false, null, null, $aOptions);
			
		$oTemplating =& KTTemplating::getSingleton();        
		$oTemplate = $oTemplating->loadTemplate("ktcore/principals/editgroup");
		$aTemplateData = array(
			"context" => $this,
			"edit_fields" => $edit_fields,
			"edit_group" => $oGroup,
		);
		return $oTemplate->render($aTemplateData);
    }
    // }}}

    // {{{ do_saveGroup
	function do_saveGroup() {
		$group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
		$oGroup = Group::get($group_id);
		if (PEAR::isError($oGroup) || $oGroup == false) {
		    $this->errorRedirectToMain(_('Please select a valid group.'));
		}
		$group_name = KTUtil::arrayGet($_REQUEST, 'group_name');
		if (empty($group_name)) { $this->errorRedirectToMain(_('Please specify a name for the group.')); }
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
		if (($res == false) || (PEAR::isError($res))) { return $this->errorRedirectToMain(_('Failed to set group details.')); }
		
		$this->commitTransaction();
		$this->successRedirectToMain(_('Group details updated.'));
	}
    // }}}
    function _do_manageUsers_source() {
        $oGroup =& $this->oValidator->validateGroup($_REQUEST['group_id']);

        $aGroupUsers = $oGroup->getMembers();

        $oTemplate = $this->oValidator->validateTemplate("ktcore/principals/groups_sourceusers");
        $aTemplateData = array(
            "context" => $this,
			'group_users' => $aGroupUsers,
			'group' => $oGroup,
        );
        return $oTemplate->render($aTemplateData);        
    }

    function do_synchroniseGroup() {
        require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');
        $oGroup =& $this->oValidator->validateGroup($_REQUEST['group_id']);
        $res = KTAuthenticationUtil::synchroniseGroupToSource($oGroup);
        $this->successRedirectTo('manageusers', 'Group synchronised', sprintf('group_id=%d', $oGroup->getId()));
        exit(0);
    }

    // {{{ do_manageusers
    function do_manageusers() {
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
        $oGroup = Group::get($group_id);
        if ((PEAR::isError($oGroup)) || ($oGroup === false)) {
            $this->errorRedirectToMain(_('No such group.'));
        }

		$this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Group Management'));
        $this->aBreadcrumbs[] = array('name' => $oGroup->getName());
        $this->oPage->setBreadcrumbDetails(_('manage members'));
        $this->oPage->setTitle(sprintf(_('Manage members of group %s'), $oGroup->getName()));

        $iSourceId = $oGroup->getAuthenticationSourceId();
        if (!empty($iSourceId)) {
            return $this->_do_manageUsers_source();
        }
        
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
        
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/groups_manageusers");
        $aTemplateData = array(
            "context" => $this,
            "edit_group" => $oGroup,
			'unused_users' => $aFreeUsers,
			'group_users' => $aGroupUsers,
        );
        return $oTemplate->render($aTemplateData);        
    }    
    // }}}

    // {{{ do_updateUserMembers
    function do_updateUserMembers() {
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
        $oGroup = Group::get($group_id);
        if ((PEAR::isError($oGroup)) || ($oGroup === false)) {
            $this->errorRedirectToMain(_('No such group.'));
        }
        
        $userAdded = KTUtil::arrayGet($_REQUEST, 'userAdded','');
        $userRemoved = KTUtil::arrayGet($_REQUEST, 'userRemoved','');
        
        
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
                    $this->errorRedirectToMain(sprintf(_('Unable to add user "%s" to group "%s"'), $oUser->getName(), $oGroup->getName()));
                } else { $usersAdded[] = $oUser->getName(); }
            }
        }
    
        // Remove groups
        foreach ($aUserToRemoveIDs as $iUserId ) {
            if ($iUserId > 0) {
                $oUser = User::get($iUserId);
                $res = $oGroup->removeMember($oUser);
                if (PEAR::isError($res) || $res == false) {
                    $this->errorRedirectToMain(sprintf(_('Unable to remove user "%s" from group "%s"'), $oUser->getName(), $oGroup->getName()));
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
		    $sWarnStr = _('Warning:  some users were already members of some subgroups') . ' &mdash; ';
			$sWarnStr .= implode(', ', $addWarnings);
			$_SESSION['KTInfoMessage'][] = $sWarnStr;
		}
		
		if (!empty($removeWarnings)) {
		    $sWarnStr = _('Warning:  some users are still members of some subgroups') . ' &mdash; ';
			$sWarnStr .= implode(', ', $removeWarnings);
			$_SESSION['KTInfoMessage'][] = $sWarnStr;
		}        
        
        $msg = '';
        if (!empty($usersAdded)) { $msg .= ' ' . _('Added') . ': ' . join(', ', $usersAdded) . ', <br />'; }
        if (!empty($usersRemoved)) { $msg .= ' ' . _('Removed') . ': ' . join(', ',$usersRemoved) . '.'; }
        
        $this->commitTransaction();
        $this->successRedirectToMain($msg);
    }
    // }}}
	
	// FIXME copy-paste ...
    // {{{ do_managesubgroups
    function do_managesubgroups() {
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id');
        $oGroup = Group::get($group_id);
        if ((PEAR::isError($oGroup)) || ($oGroup === false)) {
            $this->errorRedirectToMain(_('No such group.'));
        }
        
        $this->aBreadcrumbs[] = array('name' => $oGroup->getName());
        $this->oPage->setBreadcrumbDetails(_('manage members'));
        $this->oPage->setTitle(sprintf(_('Manage members of %s'), $oGroup->getName()));
        
        
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
		
		
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/groups_managesubgroups");
        $aTemplateData = array(
            "context" => $this,
            "edit_group" => $oGroup,
			'unused_groups' => $aAllowedGroups,
			'group_members' => $aMemberGroups,
        );
        return $oTemplate->render($aTemplateData);        
    }    
    // }}}

    // {{{ _getUnitName
	function _getUnitName($oGroup) {
        $iUnitId = $oGroup->getUnitId();
        if (empty($iUnitId)) {
            return null;
        }
		$u = Unit::get($iUnitId);
		
		return $u->getName();
	}  
    // }}}

	// FIXME copy-paste ...
    // {{{ do_updateGroupMembers
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
                    $this->errorRedirectToMain(sprintf(_("Failed to add %s to %s"), $oMemberGroup->getName(), $oGroup->getName()));
					exit(0);
                } else { $groupsAdded[] = $oMemberGroup->getName(); }
            }
        }

        foreach ($aGroupToRemoveIDs as $iMemberGroupID ) {
            if ($iMemberGroupID > 0) {
                $oMemberGroup = Group::get($iMemberGroupID);
                $res = $oGroup->removeMemberGroup($oMemberGroup);
                if (PEAR::isError($res)) {
                    $this->errorRedirectToMain(sprintf(_("Failed to remove %s from %s"), $oMemberGroup->getName(), $oGroup->getName()));
					exit(0);
                } else { $groupsRemoved[] = $oMemberGroup->getName(); }
            }
        }
        
        $msg = '';
        if (!empty($groupsAdded)) { $msg .= ' ' . _('Added') . ': ' . join(', ', $groupsAdded) . ', <br />'; }
        if (!empty($groupsRemoved)) { $msg .= ' '. _('Removed'). ': ' . join(', ',$groupsRemoved) . '.'; }
        
        $this->commitTransaction();
		
        $this->successRedirectToMain($msg);
    }	
    // }}}
	
    // {{{ do_addGroup
    function do_addGroup() {
		$this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Group Management'));
		$this->oPage->setBreadcrumbDetails(_('Add a new group'));
		
	
		$this->oPage->setTitle(_("Add a new group"));
		
		$edit_fields = array();
		$add_fields[] =  new KTStringWidget(_('Group Name'),_('A short name for the group.  e.g. <strong>administrators</strong>.'), 'group_name', null, $this->oPage, true);
		$add_fields[] =  new KTCheckboxWidget(_('Unit Administrators'),_('Should all the members of this group be given <strong>unit</strong> administration privileges?'), 'is_unitadmin', false, $this->oPage, false);
		$add_fields[] =  new KTCheckboxWidget(_('System Administrators'),_('Should all the members of this group be given <strong>system</strong> administration privileges?'), 'is_sysadmin', false, $this->oPage, false);
		// grab all units.
		
		$oUnits = Unit::getList();
		$vocab = array();
		$vocab[0] = _('No Unit');
		foreach ($oUnits as $oUnit) { $vocab[$oUnit->getID()] = $oUnit->getName(); } 
		$aOptions = array('vocab' => $vocab);
		
		$add_fields[] =  new KTLookupWidget(_('Unit'),_('Which Unit is this group part of?'), 'unit_id', 0, $this->oPage, false, null, null, $aOptions);

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
		if (empty($group_name)) { $this->errorRedirectToMain(_('Please specify a name for the group.')); }
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
		$this->successRedirectToMain(sprintf(_('Group "%s" created.'), $group_name));
	}
    // }}}

    // {{{ do_deleteGroup
    function do_deleteGroup() {
        $aErrorOptions = array(
            'redirect_to' => array('main'),
        );
        $oGroup = $this->oValidator->validateGroup($_REQUEST['group_id'], $aErrorOptions);
        $sGroupName = $oGroup->getName();
        $res = $oGroup->delete();
        $this->oValidator->notError($res, $aErrorOptions);
        $this->successRedirectToMain(sprintf(_('Group "%s" deleted.'), $sGroupName));
    }
    // }}}

    // {{{ authentication provider stuff

    // {{{ do_addGroupFromSource
    function do_addGroupFromSource() {
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $sProvider = $oSource->getAuthenticationProvider();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $oProvider =& $oRegistry->getAuthenticationProvider($sProvider);

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Group Management'));
        $this->aBreadcrumbs[] = array('url' => KTUtil::addQueryStringSelf('action=addGroup'), 'name' => _('add a new group'));
        $oProvider->aBreadcrumbs = $this->aBreadcrumbs;
        $oProvider->oPage->setBreadcrumbDetails($oSource->getName());
        $oProvider->oPage->setTitle(_("Modify Group Details"));

        $oProvider->dispatch();
        exit(0);
    }
    // }}}

    // }}}
}

?>
