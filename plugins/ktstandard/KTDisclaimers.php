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
	    $help_path = KTHelp::getHelpSubPath($sLocation);
	    $oReplacementHelp = KTHelpReplacement::getByName($help_path);


	    if(!PEAR::isError($oReplacementHelp) && strlen(trim($oReplacementHelp->getDescription()))) {
		$sDisclaimer = $oReplacementHelp->getDescription();
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

