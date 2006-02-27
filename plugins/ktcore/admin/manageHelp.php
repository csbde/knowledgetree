<?php

//require_once("../../../../../config/dmsDefaults.php");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/help/helpreplacement.inc.php");
require_once(KT_LIB_DIR . "/help/helpentity.inc.php");
require_once(KT_LIB_DIR . "/help/help.inc.php");

require_once(KT_LIB_DIR . "/dispatcher.inc.php");

require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

class ManageHelpDispatcher extends KTAdminDispatcher {
    function do_main() {
        return $this->getData();
    }

    function getData() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Help Administration'));
        $this->oPage->setBreadcrumbDetails(_('select a section'));
        $this->oPage->setTitle(_('Help Administration'));
        $oTemplating =& KTTemplating::getSingleton();
        $aHelpReplacements =& KTHelpReplacement::getList();
        //$aHelps =& KTHelpEntity::getList();
        $oTemplate = $oTemplating->loadTemplate("ktcore/manage_help");
        $aTemplateData = array(
            "context" => &$this,
            //"helps" => $aHelps,
            "helpreplacements" => $aHelpReplacements,
        );

        return $oTemplate->render($aTemplateData);
    }

    function getReplacementItemData($oHelpReplacement) {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Help Administration'));
        $this->oPage->setTitle(_('Editing: ') . $oHelpReplacement->getTitle());
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/manage_help_item");
        $aTemplateData = array(
            "context" => &$this,
            "help" => $oHelpReplacement,
        );
        $this->aBreadcrumbs[] = array(
            'name' => _('Edit help item'),
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_editReplacement() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        $oHelpReplacement = KTHelpReplacement::get($id);
        if (PEAR::isError($oHelpReplacement)) {
            return $this->errorRedirectToMain(_("Could not find specified item"));
        }
        return $this->getReplacementItemData($oHelpReplacement);
    }

    function do_deleteReplacement() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        $oHelpReplacement = KTHelpReplacement::get($id);
        if (PEAR::isError($oHelpReplacement)) {
            return $this->errorRedirectToMain(_("Could not find specified item"));
        }
        $res = $oHelpReplacement->delete();
        if (PEAR::isError($res)) {
            return $this->errorRedirectToMain(_("Could not delete specified item"));
        }
        return $this->successRedirectToMain(_("Item deleted"));
    }
    
    function do_updateReplacement() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        $oHelpReplacement = KTHelpReplacement::get($id);
        if (PEAR::isError($oHelpReplacement)) {
            return $this->errorRedirectToMain(_("Could not find specified item"));
        }
        $description = KTUtil::arrayGet($_REQUEST, 'description');
        if (empty($description)) {
            return $this->errorRedirectToMain(_("No description given"));
        }
        $oHelpReplacement->setDescription($description);
        
        $title = KTUtil::arrayGet($_REQUEST, 'title');
        if (empty($title)) {
            return $this->errorRedirectToMain(_("No title given"));
        }
        $oHelpReplacement->setTitle($title);
        
        $res = $oHelpReplacement->update();
        if (PEAR::isError($res)) {
            return $this->errorRedirectToMain(_("Error updating item"));
        }
        return $this->successRedirectToMain(_("Item updated"));
    }

    function do_customise() {
        $name = KTUtil::arrayGet($_REQUEST, 'name');
        $subname = KTHelp::getHelpSubPath($name);
        $oHelpReplacement = KTHelpReplacement::getByName($subname);
        // XXX: Check against "already exists"
        
        //var_dump($name);
        
        if (!PEAR::isError($oHelpReplacement)) {
            // Already exists...
            return $this->errorRedirectTo('editReplacement', _('Replacement already exists.'),'id=' .  $oHelpReplacement->getId());
        }
        $info = KTHelp::getHelpFromFile($name);
        if ($info === false) { 
            $info = array('name' => $name);
            $info['title'] = _('New Help File');
            $info['body'] = _('New Help File');
        }
        $oHelpReplacement = KTHelpReplacement::createFromArray(array(
            'name' => $info['name'],
            'description' => $info['body'],
            'title' => $info['title'],
        ));
        
        if (PEAR::isError($oHelpReplacement)) {
            return $this->errorRedirectToMain(_("Unable to create replacement"));
        }
        return $this->successRedirectTo('editReplacement', _('Created replacement.'), 'id=' .  $oHelpReplacement->getId());
    }
}

//$oDispatcher = new ManageHelpDispatcher();
//$oDispatcher->dispatch();

?>
