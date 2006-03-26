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
        $subname = KTHelp::getHelpSubPath($name);
        $oHelpReplacement = KTHelpReplacement::getByName($subname);
        // XXX: Check against "already exists"
        
        //var_dump($name);
        
        if (!PEAR::isError($oHelpReplacement)) {
            // Already exists...
            return $this->errorRedirectTo('editReplacement', _kt('Replacement already exists.'),'id=' .  $oHelpReplacement->getId());
        }
        $info = KTHelp::getHelpFromFile($name);
        if ($info === false) { 
            $info = array('name' => $name);
            $info['title'] = _kt('New Help File');
            $info['body'] = _kt('New Help File');
        }
        $oHelpReplacement = KTHelpReplacement::createFromArray(array(
            'name' => $info['name'],
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
