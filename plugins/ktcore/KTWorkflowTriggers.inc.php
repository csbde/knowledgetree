<?php

/**
 * $Id: KTWorkflowTriggers.php 5439 2006-05-25 13:20:15Z bryndivey $
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

require_once(KT_LIB_DIR . "/workflow/workflowtrigger.inc.php");

require_once(KT_LIB_DIR . "/permissions/permission.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionutil.inc.php");

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
        $this->sFriendlyName = _kt("Permission Restrictions");
        $this->sDescription = _kt("Prevents users who do not have the specified permission from using this transition.");
    }
    
    // override the allow transition hook.
    function allowTransition($oDocument, $oUser) {
        if (!$this->isLoaded()) {
            return true;
        }
        // the actual permissions are stored in the array.  
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
        return true;
    }
    
    function displayConfiguration($args) {
        // permissions
        $aPermissions = KTPermission::getList();
        $aKeyPermissions = array();
        foreach ($aPermissions as $oPermission) { $aKeyPermissions[$oPermission->getName()] = $oPermission; }

        $oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate("ktcore/workflowtriggers/permissions");
		$aTemplateData = array(
              "context" => $this,
              "perms" => $aKeyPermissions,
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
        $this->sFriendlyName = _kt("Role Restrictions");
        $this->sDescription = _kt("Prevents users who do not have the specified role from using this transition.");
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
		$oTemplate = $oTemplating->loadTemplate("ktcore/workflowtriggers/roles");
		$aTemplateData = array(
              "context" => $this,
              "roles" => $aKeyedRoles,
              "current_role" => KTUtil::arrayGet($this->aConfig, 'role_id'),
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
            return sprintf(_kt("The user will require the <strong>%s</strong> role."), htmlentities($oRole->getName(), ENT_NOQUOTES, 'UTF-8'));
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
        $this->sFriendlyName = _kt("Group Restrictions");
        $this->sDescription = _kt("Prevents users who are not members of the specified group from using this transition.");
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
        $res = KTGroupUtil::getMembershipReason($oUser, $oGroup);
        if (PEAR::isError($res) || is_empty($res)) { // broken setup, or no reason
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
		$oTemplate = $oTemplating->loadTemplate("ktcore/workflowtriggers/group");
		$aTemplateData = array(
              "context" => $this,
              "groups" => $aKeyedGroups,
              "current_group" => KTUtil::arrayGet($this->aConfig, 'group_id'),
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
            return sprintf(_kt("The user must be a member of the group \"<strong>%s</strong>\"."), htmlentities($oGroup->getName(), ENT_NOQUOTES, 'UTF-8'));
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
        $this->sFriendlyName = _kt("Conditional Restrictions");
        $this->sDescription = _kt("Prevents this transition from occuring if the condition specified is not met for the document.");
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
		$oTemplate = $oTemplating->loadTemplate("ktcore/workflowtriggers/condition");
		$aTemplateData = array(
              "context" => $this,
              "conditions" => $aKeyedConditions,
              "current_group" => KTUtil::arrayGet($this->aConfig, 'group_id'),
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
            return sprintf(_kt("The document must match condition \"<strong>%s</strong>\"."), htmlentities($oCondition->getName(), ENT_NOQUOTES, 'UTF-8'));
        }
    }    
}

?>