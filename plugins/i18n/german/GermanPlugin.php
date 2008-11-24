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
 * You may not redistribute this source code.
 * For more information please see the License above.
 *
 * (c) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class GermanPlugin extends KTPlugin {
 	var $sNamespace = 'ktcore.i18.de_DE.plugin';
 	
 	function GermanPlugin($sFilename = null)
 	{
	 	parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('German translation plugin');
    }

    function setup() {
        $this->registerI18nLang('knowledgeTree', "de_DE", KT_DIR . '/plugins/i18n/german/translations/');
        $this->registerLanguage("de_DE", "Deutsch (Deutschland)");
        $this->registerHelpLanguage('ktcore', 'de_DE', KT_DIR . '/plugins/i18n/german/help/ktcore/'); 
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('GermanPlugin', 'ktcore.i18.de_DE.plugin', __FILE__);
?>
