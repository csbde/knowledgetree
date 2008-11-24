<?php
/*
 * $Id: $
 *
 * The contents of this file are subject to the KnowledgeTree
 * Commercial Editions On-Premise License ("License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.knowledgetree.com/about/legal/
 * The terms of this license may change from time to time and the latest
 * license will be published from time to time at the above Internet address.
 *
 * This edition of the KnowledgeTree software
 * is NOT licensed to you under Open Source terms.
 * You may not redistribute this source code.;
 * For more information please see the License above.
 *
 * (c) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class FrenchPlugin extends KTPlugin {
 	var $sNamespace = 'ktcore.i18.fr_FR.plugin';
 	
 	function FrenchPlugin($sFilename = null)
 	{
	 	parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('French translation');
    }

    function setup() {
        $this->registerI18nLang('knowledgeTree', "fr_FR", KT_DIR . '/plugins/i18n/french/translations/');
        $this->registerLanguage("fr_FR", "French (France)");
        $this->registerHelpLanguage('ktcore', 'fr_FR', KT_DIR . '/plugins/i18n/french/help/ktcore/'); 
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('FrenchPlugin', 'ktcore.i18.fr_FR.plugin', __FILE__);
?>
