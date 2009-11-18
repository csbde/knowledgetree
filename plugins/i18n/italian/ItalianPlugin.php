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
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class ItalianPlugin extends KTPlugin {
 	var $sNamespace = 'ktcore.i18.it_IT.plugin';
	var $autoRegister = true;
    var $iOrder = -50;

 	function ItalianPlugin($sFilename = null)
 	{
	 	parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Italian translation plugin');
    }

    function setup() {
        $this->registerI18nLang('knowledgeTree', "it_IT", KT_DIR . '/plugins/i18n/italian/translations/');
        $this->registerLanguage("it_IT", "Italiano (Italia)");
        $this->registerHelpLanguage('ktcore', 'it_IT', KT_DIR . '/plugins/i18n/italian/help/ktcore/');
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('ItalianPlugin', 'ktcore.i18.it_IT.plugin', __FILE__);
?>
