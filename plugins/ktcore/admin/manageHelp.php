<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

//require_once("../../../../../config/dmsDefaults.php");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/help/helpreplacement.inc.php");
require_once(KT_LIB_DIR . "/help/helpentity.inc.php");
require_once(KT_LIB_DIR . "/help/help.inc.php");

require_once(KT_LIB_DIR . "/dispatcher.inc.php");

require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

class ManageHelpDispatcher extends KTAdminDispatcher {

    var $sHelpPage = 'ktcore/admin/help administration.html';

    function do_main() {
        return $this->getData();
    }

    function getData() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Help Administration'));
        $this->oPage->setBreadcrumbDetails(_kt('select a section'));
        $this->oPage->setTitle(_kt('Help Administration'));
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
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Help Administration'));
        $this->oPage->setTitle(_kt('Editing: ') . $oHelpReplacement->getTitle());
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/manage_help_item");
        $aTemplateData = array(
            "context" => &$this,
            "help" => $oHelpReplacement,
        );
        $this->aBreadcrumbs[] = array(
            'name' => _kt('Edit help item'),
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_editReplacement() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        $oHelpReplacement = KTHelpReplacement::get($id);
        if (PEAR::isError($oHelpReplacement)) {
            return $this->errorRedirectToMain(_kt("Could not find specified item"));
        }
        return $this->getReplacementItemData($oHelpReplacement);
    }

    function do_deleteReplacement() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        $oHelpReplacement = KTHelpReplacement::get($id);
        if (PEAR::isError($oHelpReplacement)) {
            return $this->errorRedirectToMain(_kt("Could not find specified item"));
        }
        $res = $oHelpReplacement->delete();
        if (PEAR::isError($res)) {
            return $this->errorRedirectToMain(_kt("Could not delete specified item"));
        }
        return $this->successRedirectToMain(_kt("Item deleted"));
    }
    
    function do_updateReplacement() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        $oHelpReplacement = KTHelpReplacement::get($id);
        if (PEAR::isError($oHelpReplacement)) {
            return $this->errorRedirectToMain(_kt("Could not find specified item"));
        }
        $description = KTUtil::arrayGet($_REQUEST, 'description');
        if (empty($description)) {
            return $this->errorRedirectToMain(_kt("No description given"));
        }
        $oHelpReplacement->setDescription($description);
        
        $title = KTUtil::arrayGet($_REQUEST, 'title');
        if (empty($title)) {
            return $this->errorRedirectToMain(_kt("No title given"));
        }
        $oHelpReplacement->setTitle($title);
        
        $res = $oHelpReplacement->update();
        if (PEAR::isError($res)) {
            return $this->errorRedirectToMain(_kt("Error updating item"));
        }
        return $this->successRedirectToMain(_kt("Item updated"));
    }

    function do_customise() {
        $name = KTUtil::arrayGet($_REQUEST, 'name');
        $aPathInfo = KTHelp::_getLocationInfo($name);
        $oHelpReplacement = KTHelpReplacement::getByName($aPathInfo['internal']);
        // XXX: Check against "already exists"
        
        //var_dump($name);
        
        if (!PEAR::isError($oHelpReplacement)) {
            // Already exists...
            return $this->successRedirectTo('editReplacement', _kt('Replacement already exists. Editing the existing copy instead of replacing.'),'id=' .  $oHelpReplacement->getId());
        }

	    $info = KTHelp::getHelpInfo($name);
        if (PEAR::isError($info)) { 
            $info = array('name' => $aPathInfo['internal']);
            $info['title'] = _kt('New Help File');
            $info['body'] = _kt('New Help File');
        }

        $oHelpReplacement = KTHelpReplacement::createFromArray(array(
            'name' => $aPathInfo['internal'],
            'description' => $info['body'],
            'title' => $info['title'],
        ));

        if (PEAR::isError($oHelpReplacement)) {
            return $this->errorRedirectToMain(_kt("Unable to create replacement"));
        }
        return $this->successRedirectTo('editReplacement', _kt('Created replacement.'), 'id=' .  $oHelpReplacement->getId());
    }
}

//$oDispatcher = new ManageHelpDispatcher();
//$oDispatcher->dispatch();

?>
