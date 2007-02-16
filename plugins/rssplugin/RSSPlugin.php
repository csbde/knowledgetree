<?php
/*
 * Created on 03 Jan 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
require_once(KT_LIB_DIR . "/plugins/plugin.inc.php");
require_once(KT_LIB_DIR . "/plugins/pluginregistry.inc.php");
require_once('manageRSSFeeds.php');
require_once('RSSFolderLinkAction.php');
require_once('RSSDocumentLinkAction.php');


 class RSSPlugin extends KTPlugin
 {

 	var $sNamespace = 'ktcore.rss.plugin';
 	
 	function RSSPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('RSS Plugin');
        return $res;
    }
    
    function setup() {
    	// automatically register the plugin
    	$this->autoRegister = true;
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
