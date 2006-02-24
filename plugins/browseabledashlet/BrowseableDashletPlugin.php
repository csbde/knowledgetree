<?php

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class BrowseableDashletPlugin extends KTPlugin {
    var $sNamespace = "nbm.browseable.plugin";

    function setup() {
        $this->registerDashlet('BrowseableFolderDashlet', 'nbm.browseable.dashlet', 'BrowseableDashlet.php');

        require_once(KT_LIB_DIR . "/templating/templating.inc.php");
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('browseabledashlet', '/plugins/browseabledashlet/templates');
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('BrowseableDashletPlugin', 'nbm.browseable.plugin', __FILE__);
?>
