<?php

/**
 * $Id$
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
 */

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/permissions/permission.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");

class ManagePermissionsDispatcher extends KTAdminDispatcher {
    var $sHelpPage = 'ktcore/admin/manage permissions.html'; 
    function do_main() {
        $this->oPage->setTitle(_kt('Manage Permissions'));
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Manage Permissions'));
        
        $add_fields = array();
        $add_fields[] = new KTStringWidget(_kt('System Name'), _kt('The internal name used for the permission.  This should never be changed.'), 'name', null, $this->oPage, true);
        $add_fields[] = new KTStringWidget(_kt('Display Name'), _kt('A short name that is shown to users whenever permissions must be assigned.'), 'human_name', null, $this->oPage, true);
    
        $oTemplating =& KTTemplating::getSingleton();
        $aPermissions =& KTPermission::getList();
        $oTemplate = $oTemplating->loadTemplate("ktcore/manage_permissions");
        $aTemplateData = array(
            'context' => $this,
            "permissions" => $aPermissions,
            'add_fields' => $add_fields,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_newPermission() {
        $name = KTUtil::arrayGet($_REQUEST, 'name');
        $human_name = KTUtil::arrayGet($_REQUEST, 'human_name');
        if (empty($name) || empty($human_name)) {
            return $this->errorRedirectToMain(_kt("Both names not given"));
        }
        $oPerm = KTPermission::createFromArray(array(
            'name' => $name,
            'humanname' => $human_name,
        ));
        if (PEAR::isError($oPerm)) {
            return $this->errorRedirectToMain(_kt("Error creating permission"));
        }
        return $this->successRedirectToMain(_kt("Permission created"));
    }

    function do_deletePermission() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        if (empty($id)) {
            return $this->errorRedirectToMain(_kt("Both names not given"));
        }
        $oPerm = KTPermission::get($id);
        if (PEAR::isError($oPerm)) {
            return $this->errorRedirectToMain(_kt("Error finding permission"));
        }
        if ($oPerm->getBuiltIn() === true) {
            return $this->errorRedirectToMain(_kt("Can't delete built-in permission"));
        }
        $res = $oPerm->delete();
        if (PEAR::isError($res)) {
            return $this->errorRedirectToMain(_kt("Error deleting permission"));
        }
        return $this->successRedirectToMain(_kt("Permission deleted"));
    }
}

?>
