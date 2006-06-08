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

?>