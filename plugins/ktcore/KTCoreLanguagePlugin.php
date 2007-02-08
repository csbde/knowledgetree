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

require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');

class KTCoreLanguagePlugin extends KTPlugin {
    var $bAlwaysInclude = true;
    var $sNamespace = "ktcore.language.plugin";
    var $iOrder = -75;
    var $sFriendlyName = null;

    function KTCoreLanguagePlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Core Language Support');
        return $res;
    }    

    function setup() {
        $this->registeri18n('knowledgeTree', KT_DIR . '/i18n');
        $this->registeri18nLang('knowledgeTree', "en", "default");
        $this->registerLanguage('en', 'English (United States)');
        $this->registerHelpLanguage('ktcore', 'en', sprintf("%s/kthelp/ktcore/EN/", KT_DIR));
    }
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTCoreLanguagePlugin', 'ktcore.language.plugin', __FILE__);
