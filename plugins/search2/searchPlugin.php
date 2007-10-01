<?php

require_once(KT_LIB_DIR . "/plugins/plugin.inc.php");
require_once(KT_LIB_DIR . "/plugins/pluginregistry.inc.php");
require_once('Search2Triggers.php');

 class Search2Plugin extends KTPlugin
 {
	var $autoRegister = true;
 	var $sNamespace = 'ktcore.search2.plugin';

 	function Search2Plugin($sFilename = null)
 	{
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Search2 Plugin');
        return $res;
    }

    function setup()
    {
		$this->registerAction('documentaction', 'DocumentIndexAction', 'ktcore.search2.index.action', 'DocumentIndexAction.php');
		$this->registerTrigger('edit', 'postValidate', 'SavedSearchSubscriptionTrigger', 'ktcore.search2.savedsearch.subscription.edit', 'Search2Triggers.php');
		$this->registerTrigger('add', 'postValidate', 'SavedSearchSubscriptionTrigger', 'ktcore.search2.savedsearch.subscription.add', 'Search2Triggers.php');
		$this->registerTrigger('discussion', 'postValidate', 'SavedSearchSubscriptionTrigger', 'ktcore.search2.savedsearch.subscription.discussion', 'Search2Triggers.php');
		$this->registerPortlet(array('browse', 'dashboard'),
                'Search2Portlet', 'ktcore.search2.portlet',
                'Search2Portlet.php');
    }
 }

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('Search2Plugin', 'ktcore.search2.plugin', __FILE__);
?>