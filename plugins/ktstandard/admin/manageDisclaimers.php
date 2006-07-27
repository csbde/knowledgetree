<?php

/**
 * $Id
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

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/help/helpreplacement.inc.php");
require_once(KT_LIB_DIR . "/help/helpentity.inc.php");
require_once(KT_LIB_DIR . "/help/help.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

class ManageDisclaimersDispatcher extends KTAdminDispatcher {

    var $sHelpPage = 'ktcore/admin/helpDisclaimers.html';

    function do_main() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Edit Disclaimers'));
        $this->oPage->setBreadcrumbDetails(_kt('select a section'));
        $this->oPage->setTitle(_kt('Edit Disclaimers'));
        $oTemplating =& KTTemplating::getSingleton();

	$oRegistry =& KTPluginRegistry::getSingleton();
	$oPlugin =& $oRegistry->getPlugin('ktstandard.disclaimers.plugin');

	$aDisclaimers = $oPlugin->getDisclaimerList();

        $oTemplate = $oTemplating->loadTemplate("ktstandard/disclaimers/manage_disclaimers");
        $aTemplateData = array(
            "context" => &$this,
            "disclaimers" => $aDisclaimers,
        );

        return $oTemplate->render($aTemplateData);
    }

    function do_edit() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        $oHelpReplacement = KTHelpReplacement::get($id);

        if (PEAR::isError($oHelpReplacement)) {
            return $this->errorRedirectToMain(_kt("Could not find specified item"));
        }

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Edit Disclaimers'));
        $this->aBreadcrumbs[] = array('name' => $oHelpReplacement->getTitle());
        $this->oPage->setTitle(_kt('Editing: ') . $oHelpReplacement->getTitle());

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktstandard/disclaimers/manage_disclaimers_item");
        $aTemplateData = array(
            "context" => &$this,
            "help" => $oHelpReplacement,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_update() {
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

	$subname = KTHelp::_getLocationInfo($name);
        $oHelpReplacement = KTHelpReplacement::getByName($subname['internal']);

        if (!PEAR::isError($oHelpReplacement)) {
            return $this->redirectTo('edit', 'id=' .  $oHelpReplacement->getId());
        }

	$info = KTHelp::getHelpInfo($name);

        $oHelpReplacement = KTHelpReplacement::createFromArray(array(
            'name' => $info['name'],
            'description' => $info['body'],
            'title' => $info['title'],
        ));

        if (PEAR::isError($oHelpReplacement)) {
	    print '<pre>';
	    var_dump($info);
	    exit(0);
            return $this->errorRedirectToMain(_kt("Unable to create disclaimer"));
        }

	return $this->redirectTo('edit', 'id=' .  $oHelpReplacement->getId());
    }

    function do_clear() {
        $name = KTUtil::arrayGet($_REQUEST, 'name');
	$subname = KTHelp::_getLocationInfo($name);
        $oHelpReplacement = KTHelpReplacement::getByName($subname['internal']);

        if (PEAR::isError($oHelpReplacement)) {
            return $this->errorRedirectToMain(_kt("Could not find specified item"));
        }
        $res = $oHelpReplacement->delete();
        if (PEAR::isError($res)) {
            return $this->errorRedirectToMain(_kt("Could not delete specified item"));
        }
        return $this->successRedirectToMain(_kt("Item deleted"));
    }
    
}


?>
