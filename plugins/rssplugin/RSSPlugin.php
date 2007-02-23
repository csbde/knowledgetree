<?php
/**
 *
 * Copyright (c) 2007 Jam Warehouse http://www.jamwarehouse.com
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
 *         http://www.knowledgetree.com/
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
