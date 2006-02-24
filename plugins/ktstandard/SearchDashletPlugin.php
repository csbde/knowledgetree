<?php

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class SearchDashletPlugin extends KTPlugin {
    var $sNamespace = "nbm.searchdashlet.plugin";

    function setup() {
        $this->registerDashlet('SearchDashlet', 'nbm.searchdashlet.dashlet', 'SearchDashlet.php');

        require_once(KT_LIB_DIR . "/templating/templating.inc.php");
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('searchdashlet', '/plugins/searchdashlet/templates');
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('SearchDashletPlugin', 'nbm.searchdashlet.plugin', __FILE__);
?>
