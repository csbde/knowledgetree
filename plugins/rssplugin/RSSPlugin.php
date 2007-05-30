<?php
/*
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */
 
require_once(KT_LIB_DIR . "/plugins/plugin.inc.php");
require_once(KT_LIB_DIR . "/plugins/pluginregistry.inc.php");
require_once('manageRSSFeeds.php');
require_once('RSSFolderLinkAction.php');
require_once('RSSDocumentLinkAction.php');


 class RSSPlugin extends KTPlugin
 {
	var $autoRegister = true;
 	var $sNamespace = 'ktcore.rss.plugin';
 	
 	function RSSPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('RSS Plugin');
        return $res;
    }
    
    function setup() {
    	// automatically register the plugin
    	
		$this->registerAction('folderaction', 'RSSFolderLinkAction', 'ktcore.rss.plugin.folder.link', $sFilename = null);
		$this->registerAction('documentaction', 'RSSDocumentLinkAction', 'ktcore.rss.plugin.document.link', $sFilename = null);
		$this->registerDashlet('RSSDashlet', 'ktcore.rss.feed.dashlet', 'RSSDashlet.php');
		$this->registerPage('managerssfeeds', 'ManageRSSFeedsDispatcher');
		
        require_once(KT_LIB_DIR . "/templating/templating.inc.php");
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('RSS Plugin', '/plugins/rssplugin/templates');
    }
 }
$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('RSSPlugin', 'ktcore.rss.plugin', __FILE__);
?>
