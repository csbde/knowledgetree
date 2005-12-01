<?php

//require_once("../../../../../config/dmsDefaults.php");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/help/helpreplacement.inc.php");
require_once(KT_LIB_DIR . "/help/helpentity.inc.php");
require_once(KT_LIB_DIR . "/help/help.inc.php");

require_once(KT_LIB_DIR . "/dispatcher.inc.php");

require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

class ManageHelpDispatcher extends KTAdminDispatcher {
    // Breadcrumbs base - added to in methods
    var $aBreadcrumbs = array(
        array('action' => 'administration', 'name' => 'Administration'),
        
    );

    function do_main() {
        return $this->getData();
    }

    function getData() {
        $this->aBreadcrumbs[] = array('action' => 'manageHelp', 'name' => 'Help Administration');
        $this->oPage->setBreadcrumbDetails('select a section');
    
        $oTemplating = new KTTemplating;
        $aHelpReplacements =& KTHelpReplacement::getList();
        $aHelps =& KTHelpEntity::getList();
        $oTemplate = $oTemplating->loadTemplate("ktcore/manage_help");
        $aTemplateData = array(
            "context" => &$this,
            "helps" => $aHelps,
            "helpreplacements" => $aHelpReplacements,
        );

        return $oTemplate->render($aTemplateData);
    }

    function getReplacementItemData($oHelpReplacement) {
        $this->aBreadcrumbs[] = array('action' => 'manageHelp', 'name' => 'Help Administration');
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/manage_help_item");
        $aTemplateData = array(
            "context" => &$this,
            "help" => $oHelpReplacement,
        );
        $this->aBreadcrumbs[] = array(
            'name' => 'Edit help item',
        );
        return $oTemplate->render($aTemplateData);
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
        return $this->successRedirectToMain("Item deleted");
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
        return $this->successRedirectToMain("Item updated");
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
        return $this->successRedirectTo('editReplacement', 'id=' .  $oHelpReplacement->getId());
    }
}

//$oDispatcher = new ManageHelpDispatcher();
//$oDispatcher->dispatch();

?>
