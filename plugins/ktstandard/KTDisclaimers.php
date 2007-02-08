<?php

/**
 * $Id$
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

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/help/help.inc.php');
require_once(KT_LIB_DIR . '/help/helpreplacement.inc.php');

class KTDisclaimersPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.disclaimers.plugin";
    var $aDisclaimers = array(
        'page' => array('name' => 'Footer Disclaimer', 'path' => 'ktcore/pageDisclaimer.html'),
        'login' => array('name' => 'Login Screen Disclaimer', 'path' => 'ktcore/loginDisclaimer.html'),
    );

    function KTDisclaimersPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Disclaimers Plugin');
        return $res;
    }        

    function setup() {
	$this->setupAdmin();
    }

    function setupAdmin() {
        $this->registerAdminPage("disclaimers", 'ManageDisclaimersDispatcher', 'misc',
            _kt('Edit Disclaimers'), _kt('Change disclaimers displayed on login and at the bottom of each page.'),
            'admin/manageDisclaimers.php', null);
    }

    function getDisclaimerList() {
	return $this->aDisclaimers;
    }

    function getDisclaimer($sLocation) {
	$sDisclaimer = false;

	if($this->isRegistered()) {
	    $aHelp = KTHelp::getHelpInfo($sLocation);
	    
	    if(!PEAR::isError($aHelp) && strlen(trim($aHelp['body']))) {
		$sDisclaimer = $aHelp['body'];
	    }
	}

	return $sDisclaimer;
    }

    function getPageDisclaimer() {
	return $this->getDisclaimer($this->aDisclaimers['page']['path']);
    }

    function getLoginDisclaimer() {
	return $this->getDisclaimer($this->aDisclaimers['login']['path']);
    }

}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTDisclaimersPlugin', 'ktstandard.disclaimers.plugin', __FILE__);

