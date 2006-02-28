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

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/permissions/permission.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");

class ManagePermissionsDispatcher extends KTAdminDispatcher {
    function do_main() {
        $this->oPage->setTitle(_('Manage Permissions'));
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Manage Permissions'));
        
        $add_fields = array();
        $add_fields[] = new KTStringWidget(_('System Name'),_('The internal name used for the permission.  This should never be changed.'), 'name', null, $this->oPage, true);
        $add_fields[] = new KTStringWidget(_('Display Name'),_('A short name that is shown to users whenever permissions must be assigned.'), 'human_name', null, $this->oPage, true);
    
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
            return $this->errorRedirectToMain(_("Both names not given"));
        }
        $oPerm = KTPermission::createFromArray(array(
            'name' => $name,
            'humanname' => $human_name,
        ));
        if (PEAR::isError($oPerm)) {
            return $this->errorRedirectToMain(_("Error creating permission"));
        }
        return $this->successRedirectToMain(_("Permission created"));
    }

    function do_deletePermission() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        if (empty($id)) {
            return $this->errorRedirectToMain(_("Both names not given"));
        }
        $oPerm = KTPermission::get($id);
        if (PEAR::isError($oPerm)) {
            return $this->errorRedirectToMain(_("Error finding permission"));
        }
        if ($oPerm->getBuiltIn() === true) {
            return $this->errorRedirectToMain(_("Can't delete built-in permission"));
        }
        $res = $oPerm->delete();
        if (PEAR::isError($res)) {
            return $this->errorRedirectToMain(_("Error deleting permission"));
        }
        return $this->successRedirectToMain(_("Permission deleted"));
    }
}

?>
