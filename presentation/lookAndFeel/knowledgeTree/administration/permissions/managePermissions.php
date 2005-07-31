<?php
require_once("../../../../../config/dmsDefaults.php");

require_once(KT_DIR . "/presentation/Html.inc");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");

require_once(KT_LIB_DIR . "/permissions/permission.inc.php");

require_once(KT_LIB_DIR . "/dispatcher.inc.php");
$sectionName = "Administration";
require_once(KT_DIR . "/presentation/webpageTemplate.inc");

class ManagePermissionsDispatcher extends KTAdminDispatcher {
    function do_main() {
        $oTemplating = new KTTemplating;
        $aPermissions =& KTPermission::getList();
        $oTemplate = $oTemplating->loadTemplate("ktcore/manage_permissions");
        $aTemplateData = array(
            "permissions" => $aPermissions,
        );
        return $oTemplate->render($aTemplateData);
    }

    function handleOutput($data) {
        global $main;
        $main->bFormDisabled = true;
        $main->setCentralPayload($data);
        $main->render();
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
        return $this->errorRedirectToMain("Permission created");
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
        return $this->errorRedirectToMain("Permission deleted");
    }
}

$oDispatcher = new ManagePermissionsDispatcher();
$oDispatcher->dispatch();

?>
