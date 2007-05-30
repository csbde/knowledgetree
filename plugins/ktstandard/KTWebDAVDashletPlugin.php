<?php

/**
 * $Id: KTWebDAVDashletPlugin.php 5758 2006-07-27 10:17:43Z kevin_fourie $
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

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/dashboard/dashlet.inc.php');

class KTWebDAVDashletPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.ktwebdavdashlet.plugin";
    var $autoRegister = true;

    function KTWebDAVDashletPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('WebDAV Dashlet Plugin');
        return $res;
    }        

    function setup() {
        $this->registerDashlet('KTWebDAVDashlet', 'ktstandard.ktwebdavdashlet.dashlet', __FILE__);

        require_once(KT_LIB_DIR . "/templating/templating.inc.php");
        $oTemplating =& KTTemplating::getSingleton();
    }
}

class KTWebDAVDashlet extends KTBaseDashlet {
    var $sClass = "ktInfo";
    
    function KTWebDAVDashlet( ) {
        $this->sTitle = "WebDAV Connection Information";
    }

    function render() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktstandard/ktwebdavdashlet/dashlet');

	$oConfig =& KTConfig::getSingleton();
	$bSSL = $oConfig->get('sslEnabled', false);
	$sRoot = $oConfig->get('rootUrl');

	if($bSSL) { $sProtocol = 'https'; }
	else { $sProtocol = 'http'; }

	$sURL = $sProtocol . '://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $sRoot . "/";

        $aTemplateData = array(
            'url' => $sURL,
        );
        return $oTemplate->render($aTemplateData);
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTWebDAVDashletPlugin', 'ktstandard.ktwebdavdashlet.plugin', __FILE__);
?>
