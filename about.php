<?php

/**
 * $Id: preferences.php 6203 2007-02-08 10:41:00Z kevin_fourie $
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
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
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

// main library routines and defaults
require_once("config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/unitmanagement/Unit.inc");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/widgets/forms.inc.php");

class KTAbout extends KTStandardDispatcher {
    var $sSection = 'aboutkt';

    function do_main() {
        $this->aBreadcrumbs = array(array('action' => 'aboutkt', 'name' => sprintf(_kt("About %s"), APP_NAME)));
        $oUser =& $this->oUser;

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/about");
        
        $aTemplateData = array(
              "context" => $this,
        );
        return $oTemplate->render($aTemplateData);    
    }
}

$oDispatcher = new KTAbout();
$oDispatcher->dispatch();

?>
