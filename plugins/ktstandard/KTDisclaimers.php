<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
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
    	        $sDisclaimer = str_replace('\r\n', '<br>', $aHelp['body']);
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

