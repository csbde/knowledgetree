<?php
/*
 * $Id$
 *
 * Document Link Type management
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
 */

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . "/roles/Role.inc");

class RoleAdminDispatcher extends KTAdminDispatcher {

    var $sHelpPage = 'ktcore/admin/role administration.html';
    function check() {
        return true;
    }

    function do_main() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Role Management'));
        $this->oPage->setTitle(_kt('Role Management'));
        
        $edit_fields = array();
        $role_id = KTUtil::arrayGet($_REQUEST, 'role_id', null);
        if (is_null($role_id)) {
            $oRole = null; // handle broken case of role == -1
        } else {
            $oRole = Role::get($role_id);
        }
        
        if (PEAR::isError($oRole) || ($oRole == false)) { $for_edit = false; }
        else {
            $for_edit = true;
            $edit_fields[] = new KTStringWidget(_kt('Name'), _kt('A short, human-readable name for the role.'), 'name', $oRole->getName(), $this->oPage, true);        
        }
        
        $aRoles =& Role::getList('id > 0');
        
        $add_fields = array();
        $add_fields[] = new KTStringWidget(_kt('Name'), _kt('A short, human-readable name for the role.'), 'name', null, $this->oPage, true);        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/principals/roleadmin');       
        $oTemplate->setData(array(
            "context" => $this,
            "add_fields" => $add_fields,
            "for_edit" => $for_edit,
            'edit_role' => $oRole,
            'edit_fields' => $edit_fields,            
            'roles' => $aRoles,
        ));
        return $oTemplate;
    }
    
    function do_createRole() {
        $name = KTUtil::arrayGet($_REQUEST, 'name', null);
        if ($name === null) {
            $this->errorRedirectToMain(_kt('Please give the role a name.'));
        }
        
        $this->startTransaction();
        $oRole = Role::createFromArray(array('name' => $name));        
        
        if (PEAR::isError($oRole) || ($oRole == false)) {
            $this->errorRedirectToMain(_kt('Unable to create role.'));
        }
        
        $this->successRedirectToMain(sprintf(_kt('Role "%s" created.'), $name));
    }

    function do_updateRole() {
        $role_id = KTUtil::arrayGet($_REQUEST, 'role_id');
        $oRole = Role::get($role_id);
        if (PEAR::isError($oRole) || ($oRole == false)) {
            $this->errorRedirectToMain(_kt('Please select a valid role first.'));
        }
        
        $name = KTUtil::arrayGet($_REQUEST, 'name', null);
        if ($name === null) {
            $this->errorRedirectToMain(_kt('Please give the role a name.'));
        }
        
        $this->startTransaction();
        $oRole->setName($name);
        $res = $oRole->update();
        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectToMain(_kt('Unable to update role.'));
        }
        
        $this->successRedirectToMain(sprintf(_kt('Role "%s" updated.'), $name));
    }

    function do_deleteRole() {
        $role_id = KTUtil::arrayGet($_REQUEST, 'role_id');
        $oRole = Role::get($role_id);
        if (PEAR::isError($oRole) || ($oRole == false)) {
            $this->errorRedirectToMain(_kt('Please select a valid role first.'));
        }
        $name = $oRole->getName();
        
        $this->startTransaction();  
        $res = $oRole->delete();
        if (PEAR::isError($res) || ($res == false)) { 
            $this->errorRedirectToMain(_kt('Unable to delete the role.') . '  ' . _kt('Possible cause') . ': ' . $_SESSION['errorMessage']); 
        }
            
        $this->successRedirectToMain(sprintf(_kt('Role "%s" deleted. '), $name));
    }

}

