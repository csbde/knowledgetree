<?php
/*
 * Document Link Type management
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 *
 * @version $Revision$
 * @author Brad Shuttleworth <brad@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
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

