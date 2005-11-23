<?php
//require_once("../../../../../config/dmsDefaults.php");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/permissions/permission.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

class ManagePermissionsDispatcher extends KTAdminDispatcher {

    var $aBreadcrumbs = array( 
        array('name' => 'Administration', action => 'administration'),
    );

    function do_main() {
    
        $this->oPage->setTitle('Manage Permissions');
        $this->aBreadcrumbs[] = array('action' => 'managePermissions', 'name' => 'Manage Permissions');
        
        $add_fields = array();
        $add_fields[] = new KTStringWidget('System Name','The internal name used for the permission.  This should never be changed.', 'name', null, $this->oPage, true);
        $add_fields[] = new KTStringWidget('Human Name','A short name that is shown to users whenever permissions must be assigned.', 'human_name', null, $this->oPage, true);
    
        $oTemplating = new KTTemplating;
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
            return $this->errorRedirectToMain("Both names not given");
        }
        $oPerm = KTPermission::createFromArray(array(
            'name' => $name,
            'humanname' => $human_name,
        ));
        if (PEAR::isError($oPerm)) {
            return $this->errorRedirectToMain("Error creating permission");
        }
        return $this->successRedirectToMain("Permission created");
    }

    function do_deletePermission() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        if (empty($id)) {
            return $this->errorRedirectToMain("Both names not given");
        }
        $oPerm = KTPermission::get($id);
        if (PEAR::isError($oPerm)) {
            return $this->errorRedirectToMain("Error finding permission");
        }
        if ($oPerm->getBuiltIn() === true) {
            return $this->errorRedirectToMain("Can't delete built-in permission");
        }
        $res = $oPerm->delete();
        if (PEAR::isError($res)) {
            return $this->errorRedirectToMain("Error deleting permission");
        }
        return $this->successRedirectToMain("Permission deleted");
    }
}

//$oDispatcher = new ManagePermissionsDispatcher();
//$oDispatcher->dispatch();

?>
