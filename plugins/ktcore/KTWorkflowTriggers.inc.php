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

require_once(KT_LIB_DIR . '/workflow/workflowtrigger.inc.php');

require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

require_once(KT_LIB_DIR . '/groups/GroupUtil.php');

class PermissionGuardTrigger extends KTWorkflowTrigger {
    var $sNamespace = 'ktcore.workflowtriggers.permissionguard';
    var $sFriendlyName;
    var $sDescription;
    var $oTriggerInstance;
    var $aConfig = array();

    // generic requirements - both can be true
    var $bIsGuard = true;
    var $bIsAction = false;

    function PermissionGuardTrigger() {
        $this->sFriendlyName = _kt('Permission Restrictions');
        $this->sDescription = _kt('Prevents users who do not have the specified permission from using this transition.');
    }

    // override the allow transition hook.
    function allowTransition($oDocument, $oUser) {
        if (!$this->isLoaded()) {
            return true;
        }
        // the actual permissions are stored in the array.
        if (!is_null($this->aConfig['perms']))
        {
        	foreach ($this->aConfig['perms'] as $sPermName) {
            $oPerm = KTPermission::getByName($sPermName);
            if (PEAR::isError($oPerm)) {
                continue; // possible loss of referential integrity, just ignore it for now.
            }
            $res = KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oDocument);
            if (!$res) {
                return false;
            }
        }
        }
        return true;
    }

    function displayConfiguration($args) {
        // permissions
        $aPermissions = KTPermission::getList();
        $aKeyPermissions = array();
        foreach ($aPermissions as $oPermission) { $aKeyPermissions[$oPermission->getName()] = $oPermission; }

        $current_perms = array();
        $this->aConfig['perms'] = KTUtil::arrayGet($this->aConfig, 'perms', array());
        foreach ($this->aConfig['perms'] as $sPermName) {
            $current_perms[$sPermName] = true;
        }

        $oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('ktcore/workflowtriggers/permissions');
		$aTemplateData = array(
              'context' => $this,
              'perms' => $aKeyPermissions,
              'current_perms' => $current_perms,
              'args' => $args,
		);
		return $oTemplate->render($aTemplateData);
    }

    function saveConfiguration() {
        $perms = KTUtil::arrayGet($_REQUEST, 'trigger_perms', array());
        if (!is_array($perms)) {
            $perms = (array) $perms;
        }
        $aFinalPerms = array();
        foreach ($perms as $sPermName => $ignore) {
            $oPerm = KTPermission::getByName($sPermName);
            if (!PEAR::isError($oPerm)) {
                $aFinalPerms[] = $sPermName;
            }
        }

        $config = array();
        $config['perms'] = $aFinalPerms;

        $this->oTriggerInstance->setConfig($config);
        $res = $this->oTriggerInstance->update();

        return $res;
    }

    function getConfigDescription() {
        if (!$this->isLoaded()) {
            return _kt('This trigger has no configuration.');
        }
        // the actual permissions are stored in the array.
        $perms = array();
        if (empty($this->aConfig) || is_null($this->aConfig['perms'])) {
             return _kt('No permissions are required to perform this transition');
        }
        foreach ($this->aConfig['perms'] as $sPermName) {
            $oPerm = KTPermission::getByName($sPermName);
            if (!PEAR::isError($oPerm)) {
                $perms[] = $oPerm->getHumanName();
            }
        }
        if (empty($perms)) {
            return _kt('No permissions are required to perform this transition');
        }

        $perm_string = implode(', ', $perms);
        return sprintf(_kt('The following permissions are required: %s'), $perm_string);
    }
}


class RoleGuardTrigger extends KTWorkflowTrigger {
    var $sNamespace = 'ktcore.workflowtriggers.roleguard';
    var $sFriendlyName;
    var $sDescription;
    var $oTriggerInstance;
    var $aConfig = array();

    // generic requirements - both can be true
    var $bIsGuard = true;
    var $bIsAction = false;

    function RoleGuardTrigger() {
        $this->sFriendlyName = _kt('Role Restrictions');
        $this->sDescription = _kt('Prevents users who do not have the specified role from using this transition.');
    }

    // override the allow transition hook.
    function allowTransition($oDocument, $oUser) {
        if (!$this->isLoaded()) {
            return true;
        }

        $iRoleId = $this->aConfig['role_id'];
        $oRole = Role::get($this->aConfig['role_id']);
        if (PEAR::isError($oRole)) {
            return true; // fail safe for cases where the role is deleted.
        }

        $bHaveRole = true;
        if ($iRoleId) {
            $bHaveRole = false;
            // handle the magic roles
            if ($iRoleId == -3) {
                // everyone:  just accept
                $bHaveRole = true;
            } else if (($iRoleId == -4) && !$oUser->isAnonymous()) {
                // authenticated
                $bHaveRole = true;
            } else {
                $bHaveRole = true;
                $oRoleAllocation = DocumentRoleAllocation::getAllocationsForDocumentAndRole($oDocument->getId(), $iRoleId);
                if ($oRoleAllocation == null) {   // no role allocation on the doc - check the folder.
                    $oRoleAllocation = RoleAllocation::getAllocationsForFolderAndRole($oDocument->getParentID(), $iRoleId);
                }
                // if that's -also- null
                if ($oRoleAllocation == null) {   // no role allocation, no fulfillment.
                    $bHaveRole = false;
                } else if (!$oRoleAllocation->hasMember($oUser)) {
                    $bHaveRole = false;
                }
            }
        }

        return $bHaveRole;
    }

    function displayConfiguration($args) {
        // permissions
        $aKeyedRoles = array();
        $aRoles = Role::getList();
        foreach ($aRoles as $oRole) { $aKeyedRoles[$oRole->getId()] = $oRole->getName(); }

        $oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('ktcore/workflowtriggers/roles');
		$aTemplateData = array(
              'context' => $this,
              'roles' => $aKeyedRoles,
              'current_role' => KTUtil::arrayGet($this->aConfig, 'role_id'),
              'args' => $args,
		);
		return $oTemplate->render($aTemplateData);
    }

    function saveConfiguration() {
        $role_id = KTUtil::arrayGet($_REQUEST, 'role_id', null);
        $oRole = Role::get($role_id);
        if (PEAR::isError($oRole)) {
            // silenty ignore
            $role_id = null;
            // $_SESSION['ktErrorMessages'][] = _kt('Unable to use the role you specified.');
        }

        $config = array();
        $config['role_id'] = $role_id;

        $this->oTriggerInstance->setConfig($config);
        $res = $this->oTriggerInstance->update();

        return $res;
    }

    function getConfigDescription() {
        if (!$this->isLoaded()) {
            return _kt('This trigger has no configuration.');
        }
        // the actual permissions are stored in the array.
        $perms = array();
        if (empty($this->aConfig) || is_null($this->aConfig['role_id'])) {
             return _kt('No role is required to perform this transition');
        }
        $oRole = Role::get($this->aConfig['role_id']);
        if (PEAR::isError($oRole)) {
            return _kt('The role required for this trigger has been deleted, so anyone can perform this action.');
        } else {
            return sprintf(_kt('The user will require the <strong>%s</strong> role.'), htmlentities($oRole->getName(), ENT_NOQUOTES, 'UTF-8'));
        }
    }
}


class GroupGuardTrigger extends KTWorkflowTrigger {
    var $sNamespace = 'ktcore.workflowtriggers.groupguard';
    var $sFriendlyName;
    var $sDescription;
    var $oTriggerInstance;
    var $aConfig = array();

    // generic requirements - both can be true
    var $bIsGuard = true;
    var $bIsAction = false;

    function GroupGuardTrigger() {
        $this->sFriendlyName = _kt('Group Restrictions');
        $this->sDescription = _kt('Prevents users who are not members of the specified group from using this transition.');
    }

    // override the allow transition hook.
    function allowTransition($oDocument, $oUser) {
        if (!$this->isLoaded()) {
            return true;
        }

        $iGroupId = $this->aConfig['group_id'];
        $oGroup = Group::get($this->aConfig['group_id']);
        if (PEAR::isError($oGroup)) {
            return true; // fail safe for cases where the role is deleted.
        }
        $res = GroupUtil::getMembershipReason($oUser, $oGroup);
        if (PEAR::isError($res) || empty($res)) { // broken setup, or no reason
            return false;
        } else {
            return true;
        }
    }

    function displayConfiguration($args) {
        // permissions
        $aKeyedGroups = array();
        $aGroups = Group::getList();
        foreach ($aGroups as $oGroup) { $aKeyedGroups[$oGroup->getId()] = $oGroup->getName(); }

        $oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('ktcore/workflowtriggers/group');
		$aTemplateData = array(
              'context' => $this,
              'groups' => $aKeyedGroups,
              'current_group' => KTUtil::arrayGet($this->aConfig, 'group_id'),
              'args' => $args,
		);
		return $oTemplate->render($aTemplateData);
    }

    function saveConfiguration() {
        $group_id = KTUtil::arrayGet($_REQUEST, 'group_id', null);
        $oGroup = Group::get($group_id);
        if (PEAR::isError($oGroup)) {
            // silenty ignore
            $group_id = null;
            // $_SESSION['ktErrorMessages'][] = _kt('Unable to use the group you specified.');
        }

        $config = array();
        $config['group_id'] = $group_id;

        $this->oTriggerInstance->setConfig($config);
        $res = $this->oTriggerInstance->update();

        return $res;
    }

    function getConfigDescription() {
        if (!$this->isLoaded()) {
            return _kt('This trigger has no configuration.');
        }
        // the actual permissions are stored in the array.
        $perms = array();
        if (empty($this->aConfig) || is_null($this->aConfig['group_id'])) {
             return _kt('No group is required to perform this transition');
        }
        $oGroup = Group::get($this->aConfig['group_id']);
        if (PEAR::isError($oGroup)) {
            return _kt('The group required for this trigger has been deleted, so anyone can perform this action.');
        } else {
            return sprintf(_kt('The user must be a member of the group "<strong>%s</strong>".'), htmlentities($oGroup->getName(), ENT_NOQUOTES, 'UTF-8'));
        }
    }
}


class ConditionGuardTrigger extends KTWorkflowTrigger {
    var $sNamespace = 'ktcore.workflowtriggers.conditionguard';
    var $sFriendlyName;
    var $sDescription;
    var $oTriggerInstance;
    var $aConfig = array();

    // generic requirements - both can be true
    var $bIsGuard = true;
    var $bIsAction = false;

    function ConditionGuardTrigger() {
        $this->sFriendlyName = _kt('Conditional Restrictions');
        $this->sDescription = _kt('Prevents this transition from occuring if the condition specified is not met for the document.');
    }

    // override the allow transition hook.
    function allowTransition($oDocument, $oUser) {
        if (!$this->isLoaded()) {
            return true;
        }

        $iConditionId = $this->aConfig['condition_id'];
        $oCondition = KTSavedSearch::get($this->aConfig['condition_id']);
        if (PEAR::isError($oCondition)) {
            return true; // fail safe for cases where the role is deleted.
        }
        return KTSearchUtil::testConditionOnDocument($iConditionId, $oDocument);
    }

    function displayConfiguration($args) {
        // permissions
        $aKeyedConditions = array();
        $aConditions = KTSavedSearch::getConditions();
        foreach ($aConditions as $oCondition) { $aKeyedConditions[$oCondition->getId()] = $oCondition->getName(); }

        $oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('ktcore/workflowtriggers/condition');
		$aTemplateData = array(
              'context' => $this,
              'conditions' => $aKeyedConditions,
              'current_condition' => KTUtil::arrayGet($this->aConfig, 'condition_id'),
              'args' => $args,
		);
		return $oTemplate->render($aTemplateData);
    }

    function saveConfiguration() {
        $condition_id = KTUtil::arrayGet($_REQUEST, 'condition_id', null);
        $oCondition = KTSavedSearch::get($condition_id);
        if (PEAR::isError($oCondition)) {
            // silenty ignore
            $condition_id = null;
            // $_SESSION['ktErrorMessages'][] = _kt('Unable to use the group you specified.');
        }

        $config = array();
        $config['condition_id'] = $condition_id;

        $this->oTriggerInstance->setConfig($config);
        $res = $this->oTriggerInstance->update();

        return $res;
    }

    function getConfigDescription() {
        if (!$this->isLoaded()) {
            return _kt('This trigger has no configuration.');
        }
        // the actual permissions are stored in the array.
        $perms = array();
        if (empty($this->aConfig) || is_null($this->aConfig['condition_id'])) {
             return _kt('No condition must be fulfilled before this transition becomes available.');
        }
        $oCondition = KTSavedSearch::get($this->aConfig['condition_id']);
        if (PEAR::isError($oCondition)) {
            return _kt('The condition required for this trigger has been deleted, so it is always available.');
        } else {
            return sprintf(_kt('The document must match condition "<strong>%s</strong>".'), htmlentities($oCondition->getName(), ENT_NOQUOTES, 'UTF-8'));
        }
    }
}


class BaseCopyActionTrigger extends KTWorkflowTrigger {
    var $sNamespace;
    var $sFriendlyName;
    var $sDescription;
    var $oTriggerInstance;
    var $aConfig = array();

    // generic requirements - both can be true
    var $bIsGuard = false;
    var $bIsAction = true;

    var $isCopy;

    function BaseCopyActionTrigger($namespace, $friendlyName, $description, $isCopy) {
    	$this->sNamespace = $namespace;
    	$this->sFriendlyName = $friendlyName;
    	$this->sDescription = $description;
    	$this->isCopy = $isCopy;
    }

    // perform more expensive checks -before- performTransition.
    function precheckTransition($oDocument, $oUser) {
        $iFolderId = KTUtil::arrayGet($this->aConfig, 'folder_id');
        $oFolder = Folder::get($iFolderId);
        if (PEAR::isError($oFolder)) {
        	if ($this->isCopy)
            	return PEAR::raiseError(_kt('The folder to which this document should be copied does not exist.  Cancelling the transition - please contact a system administrator.'));
            else
	            return PEAR::raiseError(_kt('The folder to which this document should be moved does not exist.  Cancelling the transition - please contact a system administrator.'));
        }

        return true;
    }

    function performTransition($oDocument, $oUser) {

        $iFolderId = KTUtil::arrayGet($this->aConfig, 'folder_id');
        $oToFolder = Folder::get($iFolderId);
        if (PEAR::isError($oFolder)) {
        	if ($this->isCopy)
	            return PEAR::raiseError(_kt('The folder to which this document should be copied does not exist.  Cancelling the transition - please contact a system administrator.'));
            else
            	return PEAR::raiseError(_kt('The folder to which this document should be moved does not exist.  Cancelling the transition - please contact a system administrator.'));
        }

        if ($this->isCopy)
        	return KTDocumentUtil::copy($oDocument, $oToFolder);
        else
	        return KTDocumentUtil::move($oDocument, $oToFolder, $oUser);
    }

    function displayConfiguration($args) {
        $oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('ktcore/workflowtriggers/moveaction');

        require_once(KT_LIB_DIR . '/browse/DocumentCollection.inc.php');
        require_once(KT_LIB_DIR . '/browse/columnregistry.inc.php');

        $collection = new AdvancedCollection;
        $oColumnRegistry = KTColumnRegistry::getSingleton();
        $aColumns = array();
        $aColumns[] = $oColumnRegistry->getColumn('ktcore.columns.singleselection');
        $aColumns[] = $oColumnRegistry->getColumn('ktcore.columns.title');

        $collection->addColumns($aColumns);

        $aOptions = $collection->getEnvironOptions(); // extract data from the environment


        $qsFrag = array();
        foreach ($args as $k => $v) {
            if ($k == 'action') { $v = 'editactiontrigger'; } // horrible hack - we really need iframe embedding.
            $qsFrag[] = sprintf('%s=%s',urlencode($k), urlencode($v));
        }
        $qs = implode('&',$qsFrag);
        $aOptions['result_url'] = KTUtil::addQueryStringSelf($qs);
        $aOptions['show_documents'] = false;

        $fFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', KTUtil::arrayGet($this->aConfig, 'folder_id', 1));

		$oFolder = Folder::get($fFolderId);
        if(PEAR::isError($oFolder))
		{
			$iRoot = 1;
			$oFolder = Folder::get($iRoot);
			$fFolderId = 1;
			
		}

        $collection->setOptions($aOptions);
        $collection->setQueryObject(new BrowseQuery($fFolderId, $this->oUser));
        $collection->setColumnOptions('ktcore.columns.singleselection', array(
            'rangename' => 'folder_id',
            'show_folders' => true,
            'show_documents' => false,
        ));

        $collection->setColumnOptions('ktcore.columns.title', array(
            'direct_folder' => false,
            'folder_link' => $aOptions['result_url'],
        ));

		
        $aBreadcrumbs = array();
        $folder_path_names = $oFolder->getPathArray();
        $folder_path_ids = explode(',', $oFolder->getParentFolderIds());
        $folder_path_ids[] = $oFolder->getId();
        if ($folder_path_ids[0] == 0) {
            array_shift($folder_path_ids);
            array_shift($folder_path_names);
        }

        foreach (range(0, count($folder_path_ids) - 1) as $index) {
            $id = $folder_path_ids[$index];
            $qsFrag2 = $qsFrag;
            $qsFrag2[] = sprintf('fFolderId=%d', $id);
            $qs2 = implode('&',$qsFrag2);
            $url = KTUtil::addQueryStringSelf($qs2);
            $aBreadcrumbs[] = sprintf('<a href="%s">%s</a>', $url, htmlentities($folder_path_names[$index], ENT_NOQUOTES, 'UTF-8'));
        }

        $sBreadcrumbs = implode(' &raquo; ', $aBreadcrumbs);

		$aTemplateData = array(
              'context' => $this,
              'breadcrumbs' => $sBreadcrumbs,
              'collection' => $collection,
              'args' => $args,
		);
		return $oTemplate->render($aTemplateData);
    }

    function saveConfiguration() {
        $folder_id = KTUtil::arrayGet($_REQUEST, 'folder_id', null);
        $oFolder = Folder::get($folder_id);
        if (PEAR::isError($oFolder)) {
            // silenty ignore
            $folder_id = null;
        }

        $config = array();
        $config['folder_id'] = $folder_id;

        $this->oTriggerInstance->setConfig($config);
        $res = $this->oTriggerInstance->update();

        return $res;
    }

    function getConfigDescription() {
        if (!$this->isLoaded()) {
            return _kt('This trigger has no configuration.');
        }
        // the actual permissions are stored in the array.
        $perms = array();
        if (empty($this->aConfig) || is_null($this->aConfig['folder_id'])) {
             return _kt('<strong>This transition cannot be performed:  no folder has been selected.</strong>');
        }
        $oFolder = Folder::get($this->aConfig['folder_id']);
        if (PEAR::isError($oFolder)) {
            return _kt('<strong>The folder required for this trigger has been deleted, so the transition cannot be performed.</strong>');
        } else {
        	if ($this->isCopy)
	            return sprintf(_kt('The document will be copied to folder "<a href="%s">%s</a>".'), KTBrowseUtil::getUrlForFolder($oFolder), htmlentities($oFolder->getName(), ENT_NOQUOTES, 'UTF-8'));
            else
    	        return sprintf(_kt('The document will be moved to folder "<a href="%s">%s</a>".'), KTBrowseUtil::getUrlForFolder($oFolder), htmlentities($oFolder->getName(), ENT_NOQUOTES, 'UTF-8'));
        }
    }
}


// This is actually the move!
class CopyActionTrigger extends BaseCopyActionTrigger
{
	function CopyActionTrigger()
	{
		parent::BaseCopyActionTrigger(
			'ktcore.workflowtriggers.copyaction',
			_kt('Moves Document'),
			_kt('Moves the document to another folder.'),
			false
		);
	}
}

// This is actually the copy! Note that we keep this naming issue
// so that there are no complications with the upgrade path!
class MoveActionTrigger extends BaseCopyActionTrigger
{
	function MoveActionTrigger()
	{
		parent::BaseCopyActionTrigger(
			'ktcore.workflowtriggers.moveaction',
			_kt('Copies Document'),
			_kt('Copies the document to another folder.'),
			true
		);
	}
}


class CheckoutGuardTrigger extends KTWorkflowTrigger {
    var $sNamespace = 'ktcore.workflowtriggers.checkoutguard';
    var $sFriendlyName;
    var $sDescription;
    var $oTriggerInstance;
    var $aConfig = array();

    // generic requirements - both can be true
    var $bIsGuard = true;
    var $bIsAction = false;

    var $bIsConfigurable = false;

    function CheckoutGuardTrigger() {
        $this->sFriendlyName = _kt('Checkout Guard');
        $this->sDescription = _kt('Prevents a transition from being followed if the document is checked out..');
    }

    // override the allow transition hook.
    function allowTransition($oDocument, $oUser) {
        return (!$oDocument->getIsCheckedOut());
    }


    function getConfigDescription() {
        return _kt('This transition cannot be performed while the document is checked out.');
    }
}



?>
