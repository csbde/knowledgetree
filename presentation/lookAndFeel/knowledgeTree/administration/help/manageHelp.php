<?php

require_once("../../../../../config/dmsDefaults.php");

require_once(KT_DIR . "/presentation/Html.inc");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/help/helpreplacement.inc.php");
require_once(KT_LIB_DIR . "/help/helpentity.inc.php");
require_once(KT_LIB_DIR . "/help/help.inc.php");

require_once(KT_LIB_DIR . "/dispatcher.inc.php");
$sectionName = "Administration";
require_once(KT_DIR . "/presentation/webpageTemplate.inc");

// require_once(KT_DIR . "/FCKeditor/fckeditor.php");

class ManageHelpDispatcher extends KTAdminDispatcher {
    function do_main() {
        return $this->getData();
    }

    function getData() {
        $oTemplating = new KTTemplating;
        $aHelpReplacements =& KTHelpReplacement::getList();
        $aHelps =& KTHelpEntity::getList();
        $oTemplate = $oTemplating->loadTemplate("ktcore/manage_help");
        $aTemplateData = array(
            "helps" => $aHelps,
            "helpreplacements" => $aHelpReplacements,
        );

        return $oTemplate->render($aTemplateData);
    }

    function getReplacementItemData($oHelpReplacement) {
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/manage_help_item");
        $aTemplateData = array(
            "help" => $oHelpReplacement,
        );
        return $oTemplate->render($aTemplateData);
    }

    function handleOutput($data) {
        global $main;
        $main->bFormDisabled = true;
        $main->setCentralPayload($data);
        $main->render();
    }

    function do_editReplacement() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        $oHelpReplacement = KTHelpReplacement::get($id);
        if (PEAR::isError($oHelpReplacement)) {
            return $this->errorRedirectToMain("Could not find specified item");
        }
        return $this->getReplacementItemData($oHelpReplacement);
    }

    function do_deleteReplacement() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        $oHelpReplacement = KTHelpReplacement::get($id);
        if (PEAR::isError($oHelpReplacement)) {
            return $this->errorRedirectToMain("Could not find specified item");
        }
        $res = $oHelpReplacement->delete();
        if (PEAR::isError($res)) {
            return $this->errorRedirectToMain("Could not delete specified item");
        }
        return $this->errorRedirectToMain("Item deleted");
    }
    
    function do_updateReplacement() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        $oHelpReplacement = KTHelpReplacement::get($id);
        if (PEAR::isError($oHelpReplacement)) {
            return $this->errorRedirectToMain("Could not find specified item");
        }
        $description = KTUtil::arrayGet($_REQUEST, 'description');
        if (empty($description)) {
            return $this->errorRedirectToMain("No description given");
        }
        $oHelpReplacement->setDescription($description);
        $res = $oHelpReplacement->update();
        if (PEAR::isError($res)) {
            return $this->errorRedirectToMain("Error updating item");
        }
        return $this->errorRedirectToMain("Item updated");
    }

    function do_customise() {
        $name = KTUtil::arrayGet($_REQUEST, 'name');
        $oHelpReplacement = KTHelpReplacement::getByName($name);
        // XXX: Check against "already exists"
        if (!PEAR::isError($oHelpReplacement)) {
            // Already exists...
            return $this->redirectTo('editReplacement', 'id=' .  $oHelpReplacement->getId());
        }
        $description = KTHelp::getHelpFromFile($name);
        $oHelpReplacement = KTHelpReplacement::createFromArray(array(
            'name' => $name,
            'description' => $description,
        ));
        if (PEAR::isError($oHelpReplacement)) {
            return $this->errorRedirectToMain("Unable to create replacement");
        }
        return $this->redirectTo('editReplacement', 'id=' .  $oHelpReplacement->getId());
    }
}

/*
$id = KTUtil::arrayGet($_REQUEST, 'id');
if ($id) {
    $action = KTUtil::arrayGet($_REQUEST, 'action');
    if ($action === "updateReplacement") {
        $oHelpReplacement =& KTHelpReplacement::get($id);
        if (PEAR::isError($oHelpReplacement)) {
            $_SESSION['KTErrorMessage'][] = "Help for that item not found";
            exit(controllerRedirect('manageHelp'));
        }
        $description = KTUtil::arrayGet($_REQUEST, 'description');
        if ($description) {
            $oHelpReplacement->setDescription($description);
            $res = $oHelpReplacement->update();
            if (PEAR::isError($res)) {
                $_SESSION['KTErrorMessage'][] = "Error updating
                    object";
                $data = getReplacementItemData($id);
            } else {
                $_SESSION['KTErrorMessage'][] = "Updated";
                exit(controllerRedirect('manageHelp'));
            }
        } else {
            $_SESSION['KTErrorMessage'][] = "No description given";
            $data = getReplacementItemData($id);
        }
    } else {
        $data = getReplacementItemData($id);
    }
} else {
    $data = getData();
}

// $sectionName = "Administration";

// require_once(KT_DIR . "/presentation/webpageTemplate.inc");
// $main->bFormDisabled = true;
// $main->setCentralPayload($data);
// $main->render();
*/

$oDispatcher = new ManageHelpDispatcher();
$oDispatcher->dispatch();

?>
